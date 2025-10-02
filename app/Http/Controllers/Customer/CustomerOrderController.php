<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DonHang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    /**
     * Lấy MAKHACHHANG của user hiện tại
     */
    protected function resolveMaKhachHang(): ?int
    {
        if (!Auth::check()) return null;
        return DB::table('KHACHHANG')
            ->where('user_id', Auth::id())
            ->value('MAKHACHHANG');
    }

    /**
     * Danh sách đơn hàng của khách
     */
    public function index(Request $request)
    {
        $maKhachHang = $this->resolveMaKhachHang();
        if (!$maKhachHang) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem đơn hàng.');
        }

        $allowedStatuses = ['Chờ xử lý', 'Đã xác nhận', 'Đang giao', 'Hủy'];

        $orders = DonHang::with(['chiTiets.sanPham', 'diaChi', 'hinhThucTT'])
            ->where('MAKHACHHANG', $maKhachHang)
            ->whereIn('TRANGTHAI', $allowedStatuses)
            ->orderByDesc('NGAYDAT')
            ->paginate(10)
            ->withQueryString();

        return view('pages.list_order', compact('orders'));
    }

    /**
     * JSON chi tiết đơn hàng cho modal
     */
    public function showJson($id)
    {
        $maKhachHang = $this->resolveMaKhachHang();

        $order = DonHang::with(['chiTiets.sanPham', 'diaChi', 'hinhThucTT'])
            ->when($maKhachHang, fn($q) => $q->where('MAKHACHHANG', $maKhachHang))
            ->findOrFail($id); // findOrFail trực tiếp theo ID

        // Map chi tiết sản phẩm
        $details = $order->chiTiets->map(function ($ct) {
            return [
                'MASANPHAM' => $ct->MASANPHAM,
                'TENSP' => $ct->sanPham->TENSANPHAM ?? '—',
                'SOLUONG' => $ct->SOLUONG,
                'DONGIA' => $ct->DONGIA,
                'THANHTIEN' => $ct->SOLUONG * $ct->DONGIA,
            ];
        });

        return response()->json([
            'MADONHANG' => $order->MADONHANG,
            'NGAYDAT' => $order->NGAYDAT,
            'NGAYGIAO' => $order->NGAYGIAO,
            'TRANGTHAI' => $order->TRANGTHAI,
            'TONGTHANHTIEN' => $order->TONGTHANHTIEN ?? 0,
            'chiTiets' => $details,
            'diaChi' => $order->diaChi ? ['DIACHI' => $order->diaChi->DIACHI] : null,
            'hinhThucTT' => $order->hinhThucTT ? ['LOAITT' => $order->hinhThucTT->LOAITT] : null,
        ]);
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel($id)
    {
        $maKhachHang = $this->resolveMaKhachHang();

        $order = DonHang::where('MADONHANG', $id)
            ->when($maKhachHang, fn($q) => $q->where('MAKHACHHANG', $maKhachHang))
            ->firstOrFail();

        if (in_array($order->TRANGTHAI, ['Đã giao', 'Hủy'])) {
            return back()->with('error', 'Không thể hủy đơn này.');
        }

        $order->TRANGTHAI = 'Hủy';
        $order->save();

        return back()->with('success', 'Đơn hàng đã được hủy.');
    }
}
