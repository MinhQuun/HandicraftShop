<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use App\Services\EnsureCustomerProfile;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng
     */
    public function index()
    {
        $users = User::all(); // Lấy tất cả user
        return view('users.index', compact('users'));
        //return view('/', compact('users'));
    }

    /**
     * Hiển thị form tạo mới user
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Lưu user mới vào DB
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required','string','min:2','max:255'],
            'email'    => ['required','email:rfc,dns','max:255','unique:users,email'],
            'password' => ['required','confirmed', Password::min(6)], // ≥ 6 ký tự
            'phone'    => ['nullable','regex:/^0\d{9}$/'], // SĐT Việt Nam 10 số
        ],[
            'name.required'      => 'Vui lòng nhập họ và tên.',
            'name.min'           => 'Họ và tên phải có ít nhất :min ký tự.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không hợp lệ.',
            'email.unique'       => 'Email đã được sử dụng.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min'       => 'Mật khẩu phải từ :min ký tự trở lên.',
            'phone.regex'        => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
        ]);

        return DB::transaction(function () use ($request) {
            // 1) Tạo user
            $user = \App\Models\User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'phone'    => $request->phone,
            ]);

            // 2) Lấy MAQUYEN 'khachhang' (so sánh lowercase để an toàn)
            $roleId = DB::table('QUYEN')
                ->whereRaw('LOWER(TENQUYEN) = ?', ['khachhang'])
                ->value('MAQUYEN');

            if ($roleId) {
                // GÁN QUYỀN CHỈ 1 LẦN → không dùng insert thủ công nữa
                $user->assignRole($roleId); // dùng belongsToMany + syncWithoutDetaching
                // 3) Đảm bảo có hồ sơ KHACHHANG
                app(EnsureCustomerProfile::class)->handle($user);
            }

            // 4) Đăng nhập
            \Illuminate\Support\Facades\Auth::login($user);

            return redirect()->route('home')->with('success', 'Đăng ký thành công!');
        });
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ],[
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $role = DB::table('QUYEN_NGUOIDUNG')
                ->join('QUYEN', 'QUYEN.MAQUYEN', '=', 'QUYEN_NGUOIDUNG.MAQUYEN')
                ->where('user_id', $user->id)
                ->value('TENQUYEN');

            $role = strtolower($role ?? '');

            // Map đích đến theo role
            $destinations = [
                'admin'     => route('admin.dashboard'),
                'nhanvien'  => route('staff.dashboard'),
                // Nếu muốn về trang chủ khách:
                'khachhang' => route('home'),
                // Nếu bạn có dashboard khách: đổi dòng trên thành:
                // 'khachhang' => route('khach.dashboard'),
            ];

            $fallback = $destinations[$role] ?? route('home');

            // Nếu user vừa bị chặn bởi trang cần đăng nhập -> quay lại trang đó,
            // còn không thì về đúng đích theo role.
            return redirect()->intended($fallback)->with('success', 'Đăng nhập thành công!');
        }

        $user = Auth::user();
        $role = DB::table('QUYEN_NGUOIDUNG')
            ->join('QUYEN','QUYEN.MAQUYEN','=','QUYEN_NGUOIDUNG.MAQUYEN')
            ->where('user_id', $user->id)
            ->value('TENQUYEN');

        if (mb_strtolower((string) $role) === 'khachhang') {
            app(\App\Services\EnsureCustomerProfile::class)->handle($user);
        }


        return back()
            ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])
            ->with('error', 'Email hoặc mật khẩu không đúng.')
            ->onlyInput('email');
    }


    public function logout(Request $request)
    {
        Auth::logout(); // đăng xuất
        $request->session()->invalidate(); // hủy session hiện tại
        $request->session()->regenerateToken(); // chống CSRF
        return redirect()->route('home')->with('info', 'Bạn đã đăng xuất.');
    }
    /**
     * Hiển thị chi tiết 1 user
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Hiển thị form sửa user
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => ['required','string','min:2','max:255'],
            'email' => ['required','email:rfc,dns','max:255','unique:users,email,' . $user->id],
            'phone' => ['nullable','regex:/^0\d{9}$/'],
        ],[
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.',
        ]);

        $data = $request->only(['name', 'email', 'phone']);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(6)]
            ],[
                'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
                'password.min'       => 'Mật khẩu phải từ :min ký tự trở lên.',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Cập nhật user thành công!');
    }

    /**
     * Xoá user
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Xoá user thành công!');
    }
}
