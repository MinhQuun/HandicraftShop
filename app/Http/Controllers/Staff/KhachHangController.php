<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class KhachHangController extends Controller
{
    /** Lấy MAQUYEN theo TENQUYEN (không phân biệt hoa/thường) */
    private function roleId(string $ten): ?string
    {
        return DB::table('QUYEN')
            ->whereRaw('LOWER(TENQUYEN) = ?', [mb_strtolower($ten)])
            ->value('MAQUYEN');
    }

    /** Danh sách + filter (q, has_account) */
    public function index(Request $r)
    {
        $q          = trim((string) $r->query('q', ''));
        $hasAccount = $r->query('has_account'); // '1' | '0' | null

        $customers = KhachHang::query()
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('HOTEN', 'like', "%$q%")
                        ->orWhere('EMAIL', 'like', "%$q%")
                        ->orWhere('SODIENTHOAI', 'like', "%$q%");
                });
            })
            ->when($hasAccount === '1', fn ($qb) => $qb->whereNotNull('user_id'))
            ->when($hasAccount === '0', fn ($qb) => $qb->whereNull('user_id'))
            ->with('user:id,name,email,phone')
            ->orderBy('MAKHACHHANG')
            ->paginate(12)
            ->withQueryString();

        return view('staff.customers', [
            'customers'   => $customers,
            'q'           => $q,
            'has_account' => $hasAccount,
        ]);
    }

    /**
     * STORE: Nhân viên tạo khách = tạo USER + gán role 'khachhang' + hồ sơ KH
     * - Email bắt buộc (để đăng nhập)
     * - Nếu không nhập mật khẩu → sinh mật khẩu tạm
     * - Thử GHÉP hồ sơ KH cũ (user_id NULL) nếu trùng email/điện thoại
     */
    public function store(Request $r)
    {
        $r->validate([
            'HOTEN'        => ['required', 'string', 'min:2', 'max:50'],
            'EMAIL'        => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'SODIENTHOAI'  => ['nullable', 'regex:/^0\d{9}$/'],
            'password'     => ['nullable', 'confirmed', Password::min(6)],
        ],[
            'SODIENTHOAI.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
        ]);

        $roleId = $this->roleId('khachhang');
        if (!$roleId) {
            return back()->with('error', 'Chưa cấu hình quyền "khachhang". Hãy seed bảng QUYEN.');
        }

        $tempPassword = $r->filled('password') ? $r->password : Str::random(10);

        DB::beginTransaction();
        try {
            // 1) Tạo tài khoản
            $user = User::create([
                'name'     => $r->HOTEN,
                'email'    => $r->EMAIL,
                'phone'    => $r->SODIENTHOAI,
                'password' => Hash::make($tempPassword),
            ]);

            // 2) Gán quyền KH (User::assignRole dùng syncWithoutDetaching)
            $user->assignRole($roleId);

            // 3) Ghép hồ sơ KH cũ (user_id NULL) nếu trùng email/sđt
            $kh = KhachHang::whereNull('user_id')
                ->where(function ($q) use ($user) {
                    $q->where('EMAIL', $user->email);
                    if (!empty($user->phone)) {
                        $q->orWhere('SODIENTHOAI', $user->phone);
                    }
                })
                ->first();

            if ($kh) {
                $kh->update([
                    'user_id'     => $user->id,
                    'HOTEN'       => $user->name,
                    'EMAIL'       => $user->email,
                    'SODIENTHOAI' => $user->phone,
                ]);
            } else {
                KhachHang::create([
                    'user_id'     => $user->id,
                    'HOTEN'       => $user->name,
                    'EMAIL'       => $user->email,
                    'SODIENTHOAI' => $user->phone,
                ]);
            }

            DB::commit();

            $msg = $r->filled('password')
                ? 'Đã tạo khách hàng và tài khoản đăng nhập.'
                : 'Đã tạo khách hàng và tài khoản đăng nhập. Hệ thống đã sinh mật khẩu tạm—hãy yêu cầu khách đổi mật khẩu.';

            return redirect()->route('staff.customers.index')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi tạo khách hàng: ' . $e->getMessage());
        }
    }

    /** UPDATE: cập nhật hồ sơ KH + sync sang users (nếu có) + đổi mật khẩu (tuỳ chọn) */
    public function update(Request $r, KhachHang $customer)
    {
        $r->validate([
            'HOTEN'        => ['required', 'string', 'min:2', 'max:50'],
            'EMAIL'        => ['nullable', 'email:rfc,dns', 'max:255'],
            'SODIENTHOAI'  => ['nullable', 'regex:/^0\d{9}$/'],
            'password'     => ['nullable', 'confirmed', Password::min(6)],
        ],[
            'SODIENTHOAI.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
        ]);

        DB::beginTransaction();
        try {
            // 1) Cập nhật hồ sơ KH
            $customer->update([
                'HOTEN'       => $r->HOTEN,
                'EMAIL'       => $r->EMAIL,
                'SODIENTHOAI' => $r->SODIENTHOAI,
            ]);

            // 2) Nếu có user liên kết: sync thông tin + (tuỳ chọn) đổi mật khẩu
            if ($customer->user) {
                if ($r->filled('EMAIL') && $r->EMAIL !== $customer->user->email) {
                    $r->validate(['EMAIL' => ['unique:users,email']]);
                }

                $customer->user->update([
                    'name'  => $r->HOTEN,
                    'email' => $r->EMAIL ?: $customer->user->email,
                    'phone' => $r->SODIENTHOAI,
                ]);

                if ($r->filled('password')) {
                    $customer->user->update([
                        'password' => Hash::make($r->password),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('staff.customers.index')->with('success', 'Đã cập nhật khách hàng.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    /**
     * DESTROY: “có đơn thì cấm xoá”; nếu không có đơn → xoá hồ sơ + (nếu có) xoá luôn user
     */
    public function destroy(KhachHang $customer)
    {
        if (method_exists($customer, 'donHangs') && $customer->donHangs()->exists()) {
            return back()->with('error', 'Khách hàng đã có đơn hàng, không thể xoá.');
        }

        DB::beginTransaction();
        try {
            // Xoá hồ sơ KH
            $customer->delete();

            // Xoá luôn user (nếu có)
            if ($customer->user) {
                $customer->user->roles()->detach();
                $customer->user->delete();
            }

            DB::commit();
            return back()->with('success', 'Đã xoá khách hàng.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi xoá: ' . $e->getMessage());
        }
    }
}
