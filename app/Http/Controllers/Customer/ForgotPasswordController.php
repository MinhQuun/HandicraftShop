<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    // Hiển thị form quên mật khẩu
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    // Gửi email OTP hoặc link reset
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = Str::random(6); // tạo mã OTP 6 ký tự

        // Lưu vào bảng password_resets (hoặc custom)
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Gửi email
        Mail::raw("Mã OTP đặt lại mật khẩu của bạn là: $token", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Mã OTP đặt lại mật khẩu');
        });

        return response()->json(['status' => 'Mã OTP đã được gửi tới email của bạn.']);

    }

    // Form nhập OTP + mật khẩu mới
    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json([
                'errors' => ['token' => ['Mã OTP không hợp lệ hoặc đã hết hạn']]
            ], 422); // 422 để client biết có lỗi validate
        }

        // Cập nhật mật khẩu
        DB::table('users')->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        // Xoá token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['status' => 'Mật khẩu đã được đặt lại thành công']);
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP hợp lệ, bạn có thể đặt mật khẩu mới'
        ]);
    }


}
