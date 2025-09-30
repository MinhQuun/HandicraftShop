<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\SanPham;
use App\Models\PhieuXuat;
use App\Models\CTPhieuXuat;
use App\Models\KhachHang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DonHang::query()->with(['khachHang', 'diaChi']);

        // Lọc theo tìm kiếm
        if ($q = $request->input('q')) {
            $query->where('MADONHANG', 'like', "%{$q}%")
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
            $query->where('NGAYDAT', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('NGAYDAT', '<=', $to);
        }

        $orders = $query->orderBy('NGAYDAT', 'desc')->paginate(10);
        $customers = KhachHang::all();

        return view('staff.orders', compact('orders', 'customers'));
    }

    public function show($id)
    {
        $order = DonHang::with(['khachHang', 'diaChi', 'chiTiets.sanPham'])->findOrFail($id);
        return response()->json([
            'MADONHANG' => $order->MADONHANG,
            'khachHang' => $order->khachHang ? [
                'MAKHACHHANG' => $order->khachHang->MAKHACHHANG,
                'HOTEN' => $order->khachHang->HOTEN
            ] : null,
            'diaChi' => $order->diaChi ? [
                'MADIACHI' => $order->diaChi->MADIACHI,
                'DIACHI' => $order->diaChi->DIACHI
            ] : null,
            'NGAYDAT' => $order->NGAYDAT,
            'MAHTTHANHTOAN' => $order->MAHTTHANHTOAN,
            'GHICHU' => $order->GHICHU,
            'TONGTIEN' => $order->TONGTIEN,
            'TRANGTHAI' => $order->TRANGTHAI,
            'chiTiets' => $order->chiTiets->map(function ($chiTiet) {
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
        $order = DonHang::findOrFail($id);
        if (!in_array($order->TRANGTHAI, ['Chờ xử lý', 'Chờ thanh toán'])) {
            return back()->with('error', 'Đơn hàng không thể xác nhận.');
        }

        DB::beginTransaction();
        try {
            // Kiểm tra tồn kho
            $details = ChiTietDonHang::where('MADONHANG', $id)->get();
            foreach ($details as $detail) {
                $product = SanPham::where('MASANPHAM', $detail->MASANPHAM)->lockForUpdate()->first();
                if (!$product || $product->SOLUONGTON < $detail->SOLUONG) {
                    throw new \Exception('Sản phẩm ' . $product->TENSANPHAM . ' không đủ tồn kho.');
                }
            }

            // Cập nhật trạng thái đơn hàng
            $order->TRANGTHAI = 'DA_XAC_NHAN';
            $order->save();

            // Tạo phiếu xuất
            $issue = new PhieuXuat();
            $issue->MAKHACHHANG = $order->MAKHACHHANG;
            $issue->MADIACHI = $order->MADIACHI;
            $issue->NGAYXUAT = now();
            $issue->NHANVIEN_ID = Auth::id();
            $issue->TRANGTHAI = 'NHAP';
            $issue->TONGSL = $order->TONGSLHANG;
            $issue->save();

            // Tạo chi tiết phiếu xuất
            foreach ($details as $detail) {
                $ctIssue = new CTPhieuXuat();
                $ctIssue->MAPHIEUXUAT = $issue->MAPHIEUXUAT;
                $ctIssue->MASANPHAM = $detail->MASANPHAM;
                $ctIssue->SOLUONG = $detail->SOLUONG;
                $ctIssue->DONGIA = $detail->DONGIA;
                $ctIssue->save();
            }

            DB::commit();
            return redirect()->route('staff.orders.index')->with('success', 'Xác nhận đơn hàng và tạo phiếu xuất thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $order = DonHang::findOrFail($id);
        if (!in_array($order->TRANGTHAI, ['Chờ xử lý', 'Chờ thanh toán'])) {
            return back()->with('error', 'Đơn hàng không thể hủy.');
        }

        $order->TRANGTHAI = 'HUY';
        $order->save();

        return redirect()->route('staff.orders.index')->with('success', 'Hủy đơn hàng thành công.');
    }
}