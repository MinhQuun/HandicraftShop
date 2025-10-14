<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\SanPham;
use App\Models\PhieuXuat;
use App\Models\CTPhieuXuat;
use App\Models\KhachHang;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    // Danh sách trạng thái hợp lệ (KHÔNG còn "Chờ thanh toán")
    private $statuses = [
        'Chờ xử lý',
        'Đã xác nhận',
        'Đang giao',
        'Hoàn thành',
        'Hủy',
    ];

    // ============ LIST ============
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

        $orders    = $query->orderBy('NGAYDAT', 'desc')->paginate(10);
        $customers = KhachHang::all();

        return view('staff.orders', [
            'orders'    => $orders,
            'customers' => $customers,
            'statuses'  => $this->statuses,
        ]);
    }

    // ============ SHOW (AJAX) ============
    public function show($id)
    {
        $order = DonHang::with([
            'khachHang',
            'diaChi',
            'chiTiets.sanPham',
            'khuyenMai'   // load khuyến mãi
        ])->findOrFail($id);

        // Tính tổng tiền trước khuyến mãi
        $subtotal = $order->chiTiets->sum(fn($ct) => $ct->SOLUONG * $ct->DONGIA);

        // Tiền giảm = subtotal - TONGTHANHTIEN
        $discountAmount = max(0, $subtotal - $order->TONGTHANHTIEN);

        return response()->json([
            'MADONHANG'       => $order->MADONHANG,
            'khachHang'       => $order->khachHang ? [
                'MAKHACHHANG' => $order->khachHang->MAKHACHHANG,
                'HOTEN'       => $order->khachHang->HOTEN
            ] : null,
            'diaChi'          => $order->diaChi ? [
                'MADIACHI' => $order->diaChi->MADIACHI,
                'DIACHI'   => $order->diaChi->DIACHI
            ] : null,
            'NGAYDAT'         => $order->NGAYDAT,
            'MATT'            => $order->MATT,
            'GHICHU'          => $order->GHICHU,
            'TONGTHANHTIEN'   => $order->TONGTHANHTIEN,
            'subtotal'        => $subtotal,
            'TIEN_GIAM'       => $discountAmount, // tiền giảm để hiển thị
            'TRANGTHAI'       => $order->TRANGTHAI,
            'chiTiets'        => $order->chiTiets->map(fn($ct) => [
                'MASANPHAM' => $ct->MASANPHAM,
                'TENSP'     => $ct->sanPham->TENSANPHAM ?? '—',
                'SOLUONG'   => $ct->SOLUONG,
                'DONGIA'    => $ct->DONGIA,
                'THANHTIEN' => $ct->SOLUONG * $ct->DONGIA
            ])->toArray(),
            'khuyenMai'       => $order->khuyenMai ? [
                'MAKHUYENMAI'   => $order->khuyenMai->MAKHUYENMAI,
                'LOAIKHUYENMAI' => $order->khuyenMai->LOAIKHUYENMAI,
                'GIAMGIA'       => $order->khuyenMai->GIAMGIA,
            ] : null,
        ]);
    }

    // ============ UPDATE STATUS (từ combobox + nút Xác nhận) ============
    public function updateStatus(Request $request, $id)
    {
        $order     = DonHang::findOrFail($id);
        $newStatus = $request->input('status');

        if (!in_array($newStatus, $this->statuses)) {
            return back()->with('error', 'Trạng thái không hợp lệ.');
        }

        // Không cho đổi nếu đã Hủy/Hoàn thành
        if (in_array($order->TRANGTHAI, ['Hủy', 'Hoàn thành'])) {
            return back()->with('error', 'Không thể thay đổi trạng thái của đơn hàng đã hủy hoặc hoàn thành.');
        }

        if ($newStatus === $order->TRANGTHAI) {
            return back()->with('info', 'Trạng thái không thay đổi.');
        }

        DB::beginTransaction();
        try {
            if ($newStatus === 'Hoàn thành') {
                // 1) Kiểm kho tất cả dòng
                $details = ChiTietDonHang::where('MADONHANG', $id)->get();

                foreach ($details as $detail) {
                    $product = SanPham::where('MASANPHAM', $detail->MASANPHAM)
                        ->lockForUpdate()
                        ->first();

                    if (!$product || $product->SOLUONGTON < $detail->SOLUONG) {
                        throw new \Exception('Sản phẩm ' . ($product->TENSANPHAM ?? $detail->MASANPHAM) . ' không đủ tồn kho.');
                    }
                }

                // 2) Cập nhật đơn sang Hoàn thành
                $order->TRANGTHAI = 'Hoàn thành';
                $order->NGAYGIAO  = now();
                $order->save();

                // 3) Tạo Phiếu Xuất với thông tin khuyến mãi
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
                    'MAKHUYENMAI' => $order->MAKHUYENMAI, // Truyền khuyến mãi từ đơn hàng
                    'TONGTIEN'    => $order->TONGTHANHTIEN, // Tổng tiền đã áp dụng khuyến mãi
                ]);

                // 4) Ghi chi tiết PX
                foreach ($details as $detail) {
                    CTPhieuXuat::updateOrCreate(
                        ['MAPX' => $issueId, 'MASANPHAM' => $detail->MASANPHAM],
                        ['SOLUONG' => $detail->SOLUONG, 'DONGIA' => $detail->DONGIA]
                    );
                }

                // 5) Xác nhận PX
                DB::table('PHIEUXUAT')->where('MAPX', $issueId)->update(['TRANGTHAI' => 'DA_XAC_NHAN']);
            } else {
                // Các trạng thái còn lại chỉ cập nhật đơn
                // Lưu ý UI chỉ cho sửa khi đơn ở Chờ xử lý / Đã xác nhận / Đang giao
                $order->TRANGTHAI = $newStatus;
                $order->save();
            }

            DB::commit();
            return redirect()->route('staff.orders.index')->with('success', 'Cập nhật trạng thái thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ============ XÁC NHẬN (giữ cho tương thích, UI không bắt buộc dùng) ============
    public function confirm(Request $request, $id)
    {
        $order = DonHang::findOrFail($id);
        if (!in_array($order->TRANGTHAI, ['Chờ xử lý'])) {
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
                'MAKHUYENMAI' => $order->MAKHUYENMAI, // Truyền khuyến mãi từ đơn hàng
                'TONGTIEN'    => $order->TONGTHANHTIEN, // Tổng tiền đã áp dụng khuyến mãi
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

    // ============ HỦY (giữ cho tương thích, UI không dùng) ============
    public function cancel(Request $request, $id)
    {
        $order = DonHang::findOrFail($id);
        if (!in_array($order->TRANGTHAI, ['Chờ xử lý'])) {
            return back()->with('error', 'Đơn hàng không thể hủy.');
        }

        $order->TRANGTHAI = 'Hủy';
        $order->save();

        return redirect()->route('staff.orders.index')->with('success', 'Hủy đơn hàng thành công.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $file = 'don-hang-' . now()->format('Ymd-His') . '.csv';

        $response = new StreamedResponse(function () use ($request) {
            echo "\xEF\xBB\xBF"; // BOM cho Excel
            $out = fopen('php://output', 'w');
            fputcsv($out, ['STT','Mã Đơn','Khách hàng','Địa chỉ','Ngày đặt','Tổng thành tiền (đ)','Trạng thái']);

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

            $i = 0;
            $query->orderBy('NGAYDAT', 'desc')->chunk(500, function ($rows) use (&$i, $out) {
                foreach ($rows as $r) {
                    $i++;
                    fputcsv($out, [
                        $i,
                        $r->MADONHANG,
                        optional($r->khachHang)->HOTEN,
                        optional($r->diaChi)->DIACHI,
                        (string) ($r->NGAYDAT ? \Carbon\Carbon::parse($r->NGAYDAT)->format('d/m/Y H:i') : ''),
                        (float) $r->TONGTHANHTIEN,
                        $r->TRANGTHAI,
                    ]);
                }
            });

            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$file.'"');

        return $response;
    }
}