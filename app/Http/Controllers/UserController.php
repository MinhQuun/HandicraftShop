<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng
     */
    public function index()
    {
        $users = User::all(); // Lấy tất cả user
        return view('/', compact('users'));
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
        // Validate dữ liệu
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
        ]);

        // Tạo user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        // Tự động login sau khi đăng ký
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Đăng ký thành công!');
    }
    public function login(Request $request)
    {
        // Validate dữ liệu nhập vào
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        // Thử đăng nhập
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // chống session fixation
            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }

        // Sai email hoặc password
        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không đúng.',
        ])->onlyInput('email');
    }
    public function logout(Request $request)
    {
        Auth::logout(); // đăng xuất
        $request->session()->invalidate(); // hủy session hiện tại
        $request->session()->regenerateToken(); // chống CSRF
        return redirect()->route('home')->with('success', 'Đăng xuất thành công!');
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
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $data = $request->only(['name', 'email', 'phone']);
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
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
