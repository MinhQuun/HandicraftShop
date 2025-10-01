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
    // Danh sách trạng thái hợp lệ
    private $statuses = [
        'Chờ xử lý',
        'Đã xác nhận',  // Đã xác nhận
        'Đang giao',
        'Hoàn thành',
        'Hủy'
    ];

    // Danh sách đơn hàng
    public function index(Request $request)
    {
        $query = DonHang::query()->with(['khachHang', 'diaChi']);

        if ($q = $request->input('q')) {
            $query->where('MADONHANG', 'like', "%{$q}%")
                  ->orWhereHas('khachHang', fn($qb) => $qb->where('HOTEN', 'like', "%{$q}%"));
        }

        if ($customer = $request->input('customer')) {
            $query->where('MAKHACHHANG', $customer);
        }

        if ($status = $request->input('status')) {
            $query->where('TRANGTHAI', $status);
        }

        if ($from = $request->input('from')) {
            $query->where('NGAYDAT', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('NGAYDAT', '<=', $to);
        }

        $orders = $query->orderBy('NGAYDAT', 'desc')->paginate(10);
        $customers = KhachHang::all();

        return view('staff.orders', [
            'orders' => $orders,
            'customers' => $customers,
            'statuses' => $this->statuses, // truyền trạng thái sang view
        ]);
    }

    // Xem chi tiết đơn hàng (AJAX)
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
            'MATT' => $order->MATT,
            'GHICHU' => $order->GHICHU,
            'TONGTIEN' => $order->TONGTIEN,
            'TRANGTHAI' => $order->TRANGTHAI,
            'chiTiets' => $order->chiTiets->map(fn($ct) => [
                'MASANPHAM' => $ct->MASANPHAM,
                'TENSP' => $ct->sanPham->TENSANPHAM ?? '—',
                'SOLUONG' => $ct->SOLUONG,
                'DONGIA' => $ct->DONGIA,
                'THANHTIEN' => $ct->SOLUONG * $ct->DONGIA
            ])->toArray()
        ]);
    }

    // Cập nhật trạng thái đơn hàng từ combobox
    public function updateStatus(Request $request, $id)
    {
        $order = DonHang::findOrFail($id);
        $newStatus = $request->input('status');

        if (!in_array($newStatus, $this->statuses)) {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        if (in_array($order->TRANGTHAI, ['Hủy', 'Hoàn thành'])) {
            return back()->with('error', 'Không thể thay đổi trạng thái của đơn hàng đã hủy hoặc hoàn thành.');
        }

        $order->TRANGTHAI = $newStatus;
        $order->save();

        return redirect()->route('staff.orders.index')->with('success', 'Cập nhật trạng thái thành công.');
    }

    // Xác nhận đơn hàng (giống như trước)
    public function confirm(Request $request, $id)
    {
        $order = DonHang::findOrFail($id);
        if (!in_array($order->TRANGTHAI, ['Chờ xử lý', 'Chờ thanh toán'])) {
            return back()->with('error', 'Đơn hàng không thể xác nhận.');
        }

        DB::beginTransaction();
        try {
            $details = ChiTietDonHang::where('MADONHANG', $id)->get();

            foreach ($details as $detail) {
                $product = SanPham::where('MASANPHAM', $detail->MASANPHAM)
                    ->lockForUpdate()
                    ->first();
                if (!$product || $product->SOLUONGTON < $detail->SOLUONG) {
                    throw new \Exception('Sản phẩm ' . ($product->TENSANPHAM ?? $detail->MASANPHAM) . ' không đủ tồn kho.');
                }
            }

            $order->TRANGTHAI = 'Hoàn thành';
            $order->save();

            $addrId = $order->MADIACHI ?? DB::table('DIACHI_GIAOHANG')
                ->where('MAKHACHHANG', $order->MAKHACHHANG)
                ->value('MADIACHI');

            $issueId = DB::table('PHIEUXUAT')->insertGetId([
                'MAKHACHHANG' => $order->MAKHACHHANG,
                'MADIACHI'    => $addrId,
                'NGAYXUAT'    => now(),
                'NHANVIEN_ID' => Auth::id(),
                'TRANGTHAI'   => 'NHAP',
                'TONGSL'      => $order->TONGSLHANG,
            ]);

            $issue = PhieuXuat::findOrFail($issueId);

            foreach ($details as $detail) {
                CTPhieuXuat::updateOrCreate(
                    ['MAPX' => $issue->MAPX, 'MASANPHAM' => $detail->MASANPHAM],
                    ['SOLUONG' => $detail->SOLUONG, 'DONGIA' => $detail->DONGIA]
                );
            }

            $issue->TRANGTHAI = 'DA_XAC_NHAN';
            $issue->save();

            DB::commit();
            return redirect()->route('staff.orders.index')->with('success', 'Xác nhận đơn hàng thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // Hủy đơn
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
