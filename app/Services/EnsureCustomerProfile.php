<?php

namespace App\Services;

use App\Models\KhachHang;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnsureCustomerProfile
{
    /**
     * Đảm bảo user có 1 hồ sơ KHACHHANG (mô hình A).
     * Trả về KhachHang.
     */
    public function handle(User $user): KhachHang
    {
        return DB::transaction(function () use ($user) {
            if ($user->relationLoaded('khachHang') && $user->khachHang) {
                return $user->khachHang;
            }

            $kh = KhachHang::where('user_id', $user->id)->first();
            if ($kh) return $kh;

            return KhachHang::create([
                'user_id'      => $user->id,
                'HOTEN'        => $user->name,
                'SODIENTHOAI'  => $user->phone,
                'EMAIL'        => $user->email,
            ]);
        });
    }
}
