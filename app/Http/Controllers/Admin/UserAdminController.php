<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserAdminController extends Controller
{
    /** Lấy MAQUYEN theo TENQUYEN (không phân biệt hoa/thường) */
    private function roleId(string $ten): ?string
    {
        return DB::table('QUYEN')
            ->whereRaw('LOWER(TENQUYEN) = ?', [mb_strtolower($ten)])
            ->value('MAQUYEN');
    }

    /** Đếm số admin hiện có */
    private function adminCount(): int
    {
        $adminId = $this->roleId('admin');
        if (!$adminId) return 0;

        return (int) DB::table('QUYEN_NGUOIDUNG')
            ->where('MAQUYEN', $adminId)
            ->count();
    }

    /** ============ INDEX ============ */
    public function index(Request $r)
    {
        $q          = trim((string) $r->query('q', ''));
        $roleFilter = mb_strtolower((string) $r->query('role', ''));
        $roles      = DB::table('QUYEN')->orderBy('MAQUYEN')->get();

        $users = User::query()
            ->when($q !== '', function ($qB) use ($q) {
                $qB->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%$q%")
                        ->orWhere('email','like',"%$q%")
                        ->orWhere('phone','like',"%$q%");
                });
            })
            ->when($roleFilter !== '', function ($qB) use ($roleFilter) {
                $qB->whereHas('roles', function ($qr) use ($roleFilter) {
                    $qr->whereRaw('LOWER(TENQUYEN) = ?', [$roleFilter])
                        ->orWhereRaw('LOWER(QUYEN.MAQUYEN) = ?', [$roleFilter]);
                });
            })
            ->with('roles')  // để blade đọc nhanh
            ->paginate(10)->withQueryString();

        // id của 3 quyền để blade tiện disable nút
        $adminId      = $this->roleId('admin');
        $nhanvienId   = $this->roleId('nhanvien');
        $khachhangId  = $this->roleId('khachhang');

        // Thống kê cho Dashboard (cũng dùng được ở đây nếu muốn)
        $counts = [
            'admin'     => (int) DB::table('QUYEN_NGUOIDUNG')->where('MAQUYEN', $adminId)->count(),
            'nhanvien'  => (int) DB::table('QUYEN_NGUOIDUNG')->where('MAQUYEN', $nhanvienId)->count(),
            'khachhang' => (int) DB::table('QUYEN_NGUOIDUNG')->where('MAQUYEN', $khachhangId)->count(),
            'total'     => (int) User::count(),
        ];

        return view('admin.index', compact(
            'users','roles','q','roleFilter',
            'adminId','nhanvienId','khachhangId','counts'
        ));
    }

    /** ============ STORE (thêm mới) ============ */
    public function store(Request $r)
    {
        $r->validate([
            'name'     => ['required','string','min:2','max:255'],
            'email'    => ['required','email:rfc,dns','max:255','unique:users,email'],
            'password' => ['required','confirmed', Password::min(6)],
            'phone'    => ['nullable','regex:/^0\d{9}$/'],
            'MAQUYEN'  => ['required','exists:QUYEN,MAQUYEN'],
        ],[
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
        ]);

        $u = User::create([
            'name'     => $r->name,
            'email'    => $r->email,
            'phone'    => $r->phone,
            'password' => Hash::make($r->password),
        ]);

        // Không cho tạo mới với quyền admin nếu bạn muốn chặt chẽ hơn:
        // if ($r->MAQUYEN === $this->roleId('admin')) { abort(403); }

        $u->roles()->sync([$r->MAQUYEN]);

        $ten = DB::table('QUYEN')->where('MAQUYEN', $r->MAQUYEN)->value('TENQUYEN');
        if (mb_strtolower((string) $ten) === 'khachhang') {
            app(\App\Services\EnsureCustomerProfile::class)->handle($u);
        }


        return back()->with('success','Tạo người dùng thành công.');
    }

    /** ============ UPDATE (sửa info) ============ */
    public function update(Request $r, User $user)
    {
        $r->validate([
            'name'     => ['required','string','min:2','max:255'],
            'email'    => ['required','email:rfc,dns','max:255','unique:users,email,' . $user->id],
            'phone'    => ['nullable','regex:/^0\d{9}$/'],
            'password' => ['nullable','confirmed', Password::min(6)],
        ],[
            'phone.regex' => 'SĐT phải đủ 10 số và bắt đầu bằng 0.',
        ]);

        $data = $r->only('name','email','phone');
        if ($r->filled('password')) {
            $data['password'] = Hash::make($r->password);
        }

        $user->update($data);

        return back()->with('success','Cập nhật người dùng thành công.');
    }

    /** ============ UPDATE ROLE (phân quyền) ============ */
    public function updateRole(Request $r, User $user)
    {
        $r->validate([
            'MAQUYEN' => ['required','exists:QUYEN,MAQUYEN']
        ]);

        $new    = $r->MAQUYEN;
        $admin  = $this->roleId('admin');
        $staff  = $this->roleId('nhanvien');
        $khach  = $this->roleId('khachhang');

        $currentRoleId = optional($user->roles()->first())->MAQUYEN;

        // 1. KHÁCH HÀNG cố định
        if ($currentRoleId === $khach && $new !== $khach) {
            return back()->with('error','Khách hàng không thể đổi quyền.');
        }

        // 2. ADMIN không được hạ xuống
        if ($currentRoleId === $admin && $new !== $admin) {
            return back()->with('error','Tài khoản Admin không thể đổi sang quyền khác.');
        }

        // 3. NHÂN VIÊN không được hạ xuống khách hàng
        if ($currentRoleId === $staff && $new === $khach) {
            return back()->with('error','Nhân viên không thể hạ xuống Khách hàng.');
        }

        // 4. Admin cuối cùng thì càng không được đụng
        if ($currentRoleId === $admin && $this->adminCount() <= 1) {
            return back()->with('error','Đây là admin cuối cùng, không thể thay đổi.');
        }

        $user->roles()->sync([$new]);

        return back()->with('success','Cập nhật quyền thành công.');
    }

    /** ============ DESTROY (xoá) ============ */
    public function destroy(User $user)
    {
        $admin  = $this->roleId('admin');
        $khach  = $this->roleId('khachhang');

        $isAdmin  = $user->roles()->where('QUYEN.MAQUYEN', $admin)->exists();
        $isKhach  = $user->roles()->where('QUYEN.MAQUYEN', $khach)->exists();

        // 1. Không cho xóa admin
        if ($isAdmin) {
            return back()->with('error','Không thể xoá tài khoản có quyền Admin.');
        }

        // 2. Nếu là khách hàng: kiểm tra đơn hàng
        if ($isKhach && $user->khachHang) {
            if ($user->khachHang->donHangs()->exists()) {
                return back()->with('error','Khách hàng đã có đơn hàng, không thể xoá.');
            }
        }

        // Nếu qua được rule thì xoá bình thường
        $user->roles()->detach();
        $user->delete();

        return back()->with('success','Đã xoá người dùng.');
    }
}
