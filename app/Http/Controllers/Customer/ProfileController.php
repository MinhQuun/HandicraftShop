<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\KhachHang;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    // Hiển thị thông tin cá nhân
    public function show()
    {
        $user = Auth::user();
        return view('pages.profile_customer', compact('user'));
    }

    // Cập nhật thông tin cá nhân (ngoại trừ mật khẩu)
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        DB::transaction(function() use ($user, $request) {

            // 1. Cập nhật bảng users
            $user->update([
                'name'  => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            // 2. Cập nhật bảng KHACHHANG
            $khachhang = KhachHang::where('user_id', $user->id)->first();
            if ($khachhang) {
                $khachhang->update([
                    'HOTEN'      => $request->name,
                    'SODIENTHOAI'=> $request->phone,
                    'EMAIL'      => $request->email,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Cập nhật thông tin thành công!');
    }

    // Đổi mật khẩu
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => ['required', 'confirmed', Password::min(6)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
