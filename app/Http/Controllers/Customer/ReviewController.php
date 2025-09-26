<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DanhGia;
use App\Models\KhachHang;

class ReviewController extends Controller
{
    public function store(Request $r, string $masp)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $r->validate([
            'DIEMSO'  => 'required|integer|min:1|max:5',
            'NHANXET' => 'nullable|string|max:1000',
        ]);

        // Tìm hoặc tạo KH ánh xạ từ users
        $kh = KhachHang::where('user_id', Auth::id())->first();
        if (!$kh) {
            $u = Auth::user();
            $kh = KhachHang::create([
                'user_id' => $u->id,
                'HOTEN'   => $u->name ?? 'Người dùng',
                'EMAIL'   => $u->email ?? null,
            ]);
        }

        // Ghi hoặc cập nhật (nhờ unique MAKHACHHANG+MASANPHAM)
        $rv = DanhGia::updateOrCreate(
            ['MAKHACHHANG' => $kh->MAKHACHHANG, 'MASANPHAM' => $masp],
            [
                'DIEMSO'       => $validated['DIEMSO'],
                'NHANXET'      => $validated['NHANXET'] ?? null,
                'NGAYDANHGIA'  => now(),
            ]
        );

        // (Nếu dùng cache) cập nhật RATING_AVG/RATING_COUNT tại đây…

        return response()->json([
            'message' => 'Cảm ơn bạn! Đánh giá đã được ghi nhận.',
        ]);
    }
}
