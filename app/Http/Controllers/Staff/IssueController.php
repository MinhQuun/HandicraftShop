<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PhieuXuat;
use App\Models\CTPhieuXuat;
use App\Models\DonHang;
use App\Models\KhachHang;
use Illuminate\Support\Facades\DB;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $query = PhieuXuat::query()->with(['khachHang', 'diaChi']);

        // Lọc theo tìm kiếm
        if ($q = $request->input('q')) {
            $query->where('MAPHIEUXUAT', 'like', "%{$q}%")
                  ->orWhereHas('khachHang', function ($qBuilder) use ($q) {
                      $qBuilder->where('HOTEN', 'like', "%{$q}%");
                  });
        }

        // Lọc theo khách hàng
        if ($customer = $request->input('customer')) {
            $query->where('MAKHACHHANG', $customer);
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('TRANGTHAI', $status);
        }

        // Lọc theo khoảng thời gian
        if ($from = $request->input('from')) {
            $query->where('NGAYXUAT', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('NGAYXUAT', '<=', $to);
        }

        $issues = $query->orderBy('NGAYXUAT', 'desc')->paginate(10);
        $customers = KhachHang::all();

        return view('staff.issues', compact('issues', 'customers'));
    }

    public function show($id)
    {
        $issue = PhieuXuat::with(['khachHang', 'diaChi', 'chiTiets.sanPham'])->findOrFail($id);
        return response()->json([
            'MAPHIEUXUAT' => $issue->MAPHIEUXUAT,
            'khachHang' => $issue->khachHang ? [
                'MAKHACHHANG' => $issue->khachHang->MAKHACHHANG,
                'HOTEN' => $issue->khachHang->HOTEN
            ] : null,
            'diaChi' => $issue->diaChi ? [
                'MADIACHI' => $issue->diaChi->MADIACHI,
                'DIACHI' => $issue->diaChi->DIACHI
            ] : null,
            'NGAYXUAT' => $issue->NGAYXUAT,
            'TONGSL' => $issue->TONGSL,
            'TRANGTHAI' => $issue->TRANGTHAI,
            'chiTiets' => $issue->chiTiets->map(function ($chiTiet) {
                return [
                    'MASANPHAM' => $chiTiet->MASANPHAM,
                    'TENSP' => $chiTiet->sanPham->TENSP ?? '—',
                    'SOLUONG' => $chiTiet->SOLUONG,
                    'DONGIA' => $chiTiet->DONGIA,
                    'THANHTIEN' => $chiTiet->SOLUONG * $chiTiet->DONGIA
                ];
            })->toArray()
        ]);
    }

    public function confirm(Request $request, $id)
    {
        $issue = PhieuXuat::findOrFail($id);
        if ($issue->TRANGTHAI !== 'NHAP') {
            return back()->with('error', 'Phiếu xuất không thể xác nhận.');
        }

        DB::beginTransaction();
        try {
            $issue->TRANGTHAI = 'DA_XAC_NHAN';
            $issue->save();

            // Trigger trg_px_after_update sẽ tự động trừ SOLUONGTON
            DB::commit();
            return redirect()->route('staff.issues.index')->with('success', 'Xác nhận phiếu xuất thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $issue = PhieuXuat::findOrFail($id);
        if ($issue->TRANGTHAI !== 'NHAP') {
            return back()->with('error', 'Phiếu xuất không thể hủy.');
        }

        DB::beginTransaction();
        try {
            $issue->TRANGTHAI = 'HUY';
            $issue->save();

            // Cập nhật trạng thái đơn hàng liên quan
            $order = DonHang::where('MAKHACHHANG', $issue->MAKHACHHANG)
                ->where('MADIACHI', $issue->MADIACHI)
                ->where('TRANGTHAI', 'DA_XAC_NHAN')
                ->first();
            if ($order) {
                $order->TRANGTHAI = 'HUY';
                $order->save();
            }

            DB::commit();
            return redirect()->route('staff.issues.index')->with('success', 'Hủy phiếu xuất thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}