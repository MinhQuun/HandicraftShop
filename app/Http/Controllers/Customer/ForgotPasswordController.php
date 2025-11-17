<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = Str::random(6); // hoặc: str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        Mail::raw("Mã OTP đặt lại mật khẩu của bạn là: $token (hiệu lực 10 phút).", function ($message) use ($request) {
            $message->to($request->email)->subject('Mã OTP đặt lại mật khẩu');
        });

        return response()->json([
            'status' => true,
            'message' => 'Mã OTP đã được gửi tới email của bạn.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        // Kiểm tra tồn tại + hạn 10 phút
        if (!$record || Carbon::parse($record->created_at)->lt(now()->subMinutes(10))) {
            return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'
            ], 422);
        }

        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP hợp lệ, bạn có thể đặt mật khẩu mới',
            'token' => $request->token,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();
        if (
            !$record ||
            Carbon::parse($record->created_at)->lt(now()->subMinutes(10)) ||
            !Hash::check($request->token, $record->token)
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'
            ], 422);
        }

        // Không cho trùng mật khẩu hiện tại
        $user = DB::table('users')->where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'errors' => ['password' => ['Mật khẩu mới không được trùng với mật khẩu hiện tại.']]
            ], 422);
        }

        // Cập nhật mật khẩu
        DB::table('users')->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công'
        ]);
    }
}
