<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DonHang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    // Trạng thái hợp lệ
    protected $currentStatuses = ['Chờ xử lý', 'Đã xác nhận', 'Đang giao'];
    protected $historyStatuses = ['Hoàn thành', 'Hủy'];

    // Lấy mã khách hàng hiện tại
    protected function resolveMaKhachHang(): ?int
    {
        if (!Auth::check()) return null;
        return DB::table('KHACHHANG')
            ->where('user_id', Auth::id())
            ->value('MAKHACHHANG');
    }

    // ======= Đơn hàng hiện tại (đang xử lý) =======
    public function index(Request $request)
    {
        $maKhachHang = $this->resolveMaKhachHang();
        if (!$maKhachHang) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem đơn hàng.');
        }

        $query = DonHang::with(['chiTiets.sanPham', 'diaChi', 'hinhThucTT'])
            ->where('MAKHACHHANG', $maKhachHang)
            ->whereIn('TRANGTHAI', $this->currentStatuses);

        // Lọc trạng thái
        if ($status = $request->input('status')) {
            $query->where('TRANGTHAI', $status);
        }

        // Lọc theo ngày đặt
        if ($from = $request->input('from')) {
            $query->where('NGAYDAT', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('NGAYDAT', '<=', $to);
        }

        $orders = $query->orderByDesc('NGAYDAT')
            ->paginate(10)
            ->withQueryString();

        return view('pages.list_order', [
            'orders'   => $orders,
            'statuses' => $this->currentStatuses, // cho combobox lọc
        ]);
    }

    // ======= Lịch sử đơn hàng (Hoàn thành / Hủy) =======
    public function history(Request $request)
    {
        $maKhachHang = $this->resolveMaKhachHang();
        if (!$maKhachHang) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem lịch sử đơn hàng.');
        }

        $query = DonHang::with(['chiTiets.sanPham', 'diaChi', 'hinhThucTT'])
            ->where('MAKHACHHANG', $maKhachHang)
            ->whereIn('TRANGTHAI', $this->historyStatuses);

        // Lọc trạng thái
        if ($status = $request->input('status')) {
            $query->where('TRANGTHAI', $status);
        }

        // Lọc ngày đặt
        if ($from = $request->input('from')) {
            $query->where('NGAYDAT', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('NGAYDAT', '<=', $to);
        }

        // Lọc ngày giao
        if ($delFrom = $request->input('delivery_from')) {
            $query->where('NGAYGIAO', '>=', $delFrom);
        }
        if ($delTo = $request->input('delivery_to')) {
            $query->where('NGAYGIAO', '<=', $delTo);
        }

        $orders = $query->orderByDesc('NGAYDAT')
            ->paginate(10)
            ->withQueryString();

        return view('pages.list_order_history', [
            'orders'   => $orders,
            'statuses' => $this->historyStatuses,
        ]);
    }

    // ======= Chi tiết đơn hàng dạng JSON =======
    public function showJson($id)
    {
        $maKhachHang = $this->resolveMaKhachHang();

        $order = DonHang::with(['chiTiets.sanPham', 'diaChi', 'hinhThucTT'])
            ->when($maKhachHang, fn($q) => $q->where('MAKHACHHANG', $maKhachHang))
            ->findOrFail($id);

        $details = $order->chiTiets->map(fn($ct) => [
            'MASANPHAM' => $ct->MASANPHAM,
            'TENSP'     => $ct->sanPham->TENSANPHAM ?? '—',
            'SOLUONG'   => $ct->SOLUONG,
            'DONGIA'    => $ct->DONGIA,
            'THANHTIEN' => $ct->SOLUONG * $ct->DONGIA,
        ]);

        return response()->json([
            'MADONHANG'     => $order->MADONHANG,
            'NGAYDAT'       => $order->NGAYDAT,
            'NGAYGIAO'      => $order->NGAYGIAO,
            'TRANGTHAI'     => $order->TRANGTHAI,
            'TONGTHANHTIEN' => $order->TONGTHANHTIEN ?? 0,
            'chiTiets'      => $details,
            'diaChi'        => $order->diaChi ? ['DIACHI' => $order->diaChi->DIACHI] : null,
            'hinhThucTT'    => $order->hinhThucTT ? ['LOAITT' => $order->hinhThucTT->LOAITT] : null,
        ]);
    }

    // ======= Hủy đơn hàng =======
    public function cancel($id)
    {
        $maKhachHang = $this->resolveMaKhachHang();

        $order = DonHang::where('MADONHANG', $id)
            ->when($maKhachHang, fn($q) => $q->where('MAKHACHHANG', $maKhachHang))
            ->firstOrFail();

        if (in_array($order->TRANGTHAI, ['Hoàn thành', 'Hủy'])) {
            return back()->with('error', 'Không thể hủy đơn này.');
        }

        $order->TRANGTHAI = 'Hủy';
        $order->save();

        return back()->with('success', 'Đơn hàng đã được hủy.');
    }
}
