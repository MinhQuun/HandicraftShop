<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KhuyenMai;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionsController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $loai = $request->input('loai');

        $query = KhuyenMai::query();

        if ($q) {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('MAKHUYENMAI', 'like', "%$q%")
                            ->orWhere('TENKHUYENMAI', 'like', "%$q%")
                            ->orWhere('LOAIKHUYENMAI', 'like', "%$q%");
            });
        }

        if ($loai) {
            $query->where('LOAIKHUYENMAI', $loai);
        }

        $promotions = $query->paginate(10);

        // Giả sử có predefined types, bạn có thể thay bằng config hoặc db nếu cần
        $promotionTypes = [
            'Giảm %' => 'Giảm %',
            'Giảm fixed' => 'Giảm fixed',
            'Mua X tặng Y' => 'Mua X tặng Y',
            'Flash Sale' => 'Flash Sale',
        ];

        $products = SanPham::all(); // Để dùng trong modal nếu cần

        return view('staff.promotions', compact('promotions', 'promotionTypes', 'q', 'loai', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'MAKHUYENMAI' => 'required|string|unique:KHUYENMAI',
            'LOAIKHUYENMAI' => 'required|string',
            'TENKHUYENMAI' => 'required|string',
            'NGAYBATDAU' => 'required|date',
            'NGAYKETTHUC' => 'required|date|after:NGAYBATDAU',
            'GIAMGIA' => 'required|numeric|min:0',
            'sanphams' => 'array', // Để gán sản phẩm
        ]);

        $promotion = KhuyenMai::create($validated);

        if (isset($validated['sanphams'])) {
            $promotion->sanphams()->sync($validated['sanphams']);
        }

        return redirect()->route('staff.promotions.index')->with('success', 'Khuyến mãi đã được tạo.');
    }

    public function update(Request $request, $id)
    {
        $promotion = KhuyenMai::findOrFail($id);

        $validated = $request->validate([
            'LOAIKHUYENMAI' => 'required|string',
            'TENKHUYENMAI' => 'required|string',
            'NGAYBATDAU' => 'required|date',
            'NGAYKETTHUC' => 'required|date|after:NGAYBATDAU',
            'GIAMGIA' => 'required|numeric|min:0',
            'sanphams' => 'array',
        ]);

        $promotion->update($validated);

        if (isset($validated['sanphams'])) {
            $promotion->sanphams()->sync($validated['sanphams']);
        }

        return redirect()->route('staff.promotions.index')->with('success', 'Khuyến mãi đã được cập nhật.');
    }

    public function destroy($id)
    {
        $promotion = KhuyenMai::findOrFail($id);
        $promotion->delete();

        return redirect()->route('staff.promotions.index')->with('success', 'Khuyến mãi đã được xóa.');
    }
}