<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $customer = $request->get('customer');
        $status   = $request->get('status');
        $from     = $request->get('from');
        $to       = $request->get('to');

        $issues = DB::table('PHIEUXUAT as p')
            ->join('KHACHHANG as k', 'k.MAKHACHHANG', '=', 'p.MAKHACHHANG')
            ->join('users as u', 'u.id', '=', 'p.NHANVIEN_ID')
            ->leftJoin('CT_PHIEUXUAT as ct', 'ct.MAPX', '=', 'p.MAPX')
            ->leftJoin('KHUYENMAI as km', 'km.MAKHUYENMAI', '=', 'p.MAKHUYENMAI')
            ->when($q, function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($x) use ($like) {
                    $x->whereRaw('CAST(p.MAPX AS CHAR) LIKE ?', [$like])
                      ->orWhere('k.HOTEN', 'like', $like)
                      ->orWhere('u.name', 'like', $like);
                });
            })
            ->when($customer, fn($q2) => $q2->where('p.MAKHACHHANG', $customer))
            ->when($status, fn($q2) => $q2->where('p.TRANGTHAI', $status))
            ->when($from, fn($q2) => $q2->whereDate('p.NGAYXUAT', '>=', $from))
            ->when($to, fn($q2) => $q2->whereDate('p.NGAYXUAT', '<=', $to))
            ->select(
                'p.MAPX',
                'p.NGAYXUAT',
                'p.TRANGTHAI',
                'p.TONGSL',
                'p.GHICHU',
                'k.HOTEN as KHACHHANG',
                'k.MAKHACHHANG',
                'u.name as NHANVIEN',
                DB::raw('COALESCE(SUM(ct.SOLUONG * ct.DONGIA), 0) as TONGTIEN'),
                'km.MAKHUYENMAI',
                'km.LOAIKHUYENMAI',
                'km.GIAMGIA'
            )
            ->groupBy('p.MAPX', 'p.NGAYXUAT', 'p.TRANGTHAI', 'p.TONGSL', 'p.GHICHU', 'k.HOTEN', 'k.MAKHACHHANG', 'u.name', 'p.TONGTIEN', 'km.MAKHUYENMAI', 'km.LOAIKHUYENMAI', 'km.GIAMGIA')
            ->orderBy('p.MAPX', 'asc')
            ->paginate(10)
            ->withQueryString();

        $customers = DB::table('KHACHHANG')
            ->select('MAKHACHHANG', 'HOTEN')
            ->orderBy('HOTEN')
            ->get();

        return view('staff.issues', compact('issues', 'customers', 'q', 'customer', 'status', 'from', 'to'));
    }

    public function show($id)
    {
        // Lấy header kèm địa chỉ giao hàng
        $header = DB::table('PHIEUXUAT as p')
            ->join('KHACHHANG as k', 'k.MAKHACHHANG', '=', 'p.MAKHACHHANG')
            ->join('users as u', 'u.id', '=', 'p.NHANVIEN_ID')
            ->leftJoin('DIACHI_GIAOHANG as dc', 'dc.MADIACHI', '=', 'p.MADIACHI')
            ->leftJoin('KHUYENMAI as km', 'km.MAKHUYENMAI', '=', 'p.MAKHUYENMAI')
            ->where('p.MAPX', $id)
            ->select(
                'p.MAPX',
                'p.NGAYXUAT',
                'p.TRANGTHAI',
                'p.TONGSL',
                'p.GHICHU',
                'k.HOTEN as KHACHHANG',
                'k.MAKHACHHANG',
                'u.name as NHANVIEN',
                'dc.DIACHI',
                'p.TONGTIEN',
                'km.MAKHUYENMAI',
                'km.LOAIKHUYENMAI',
                'km.GIAMGIA'
            )
            ->first();

        if (!$header) {
            return response()->json(['error' => 'Không tìm thấy phiếu xuất.'], 404);
        }

        // Chi tiết phiếu xuất
        $details = DB::table('CT_PHIEUXUAT as ct')
            ->join('SANPHAM as sp', 'sp.MASANPHAM', '=', 'ct.MASANPHAM')
            ->where('ct.MAPX', $id)
            ->select(
                'ct.MASANPHAM',
                'sp.TENSANPHAM',
                'ct.SOLUONG',
                'ct.DONGIA',
                DB::raw('ct.SOLUONG * ct.DONGIA as THANHTIEN')
            )
            ->get();

        $subtotal       = $details->sum(fn($d) => $d->THANHTIEN);
        $discountAmount = $header->GIAMGIA > 0 ? $subtotal * ($header->GIAMGIA / 100) : 0;
        $tongtien       = $subtotal - $discountAmount;

        return response()->json([
            'header'         => $header,
            'lines'          => $details,
            'subtotal'       => $subtotal,
            'discountAmount' => $discountAmount,
            'TONGTIEN'       => $tongtien,
        ]);
    }

    public function confirm($id)
    {
        $row = DB::table('PHIEUXUAT')->where('MAPX', $id)->first();
        if (!$row) return back()->with('error', 'Không tìm thấy phiếu xuất.');
        if ($row->TRANGTHAI !== 'NHAP') {
            return back()->with('error', 'Chỉ xác nhận phiếu ở trạng thái NHAP.');
        }

        DB::beginTransaction();
        try {
            DB::table('PHIEUXUAT')->where('MAPX', $id)->update(['TRANGTHAI' => 'DA_XAC_NHAN']);
            // Trigger trg_px_after_update sẽ tự động trừ SOLUONGTON
            DB::commit();
            return back()->with('success', 'Đã xác nhận phiếu xuất.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi xác nhận phiếu xuất: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $row = DB::table('PHIEUXUAT')->where('MAPX', $id)->first();
        if (!$row) return back()->with('error', 'Không tìm thấy phiếu xuất.');
        if ($row->TRANGTHAI === 'HUY') {
            return back()->with('info', 'Phiếu đã ở trạng thái HUY.');
        }

        DB::beginTransaction();
        try {
            DB::table('PHIEUXUAT')->where('MAPX', $id)->update(['TRANGTHAI' => 'HUY']);
            DB::commit();
            return back()->with('success', 'Đã hủy phiếu xuất.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hủy phiếu xuất: ' . $e->getMessage());
        }
    }

    public function exportPdf($id)
    {
        // Lấy thông tin phiếu xuất
        $header = DB::table('PHIEUXUAT as px')
            ->leftJoin('KHACHHANG as kh', 'px.MAKHACHHANG', '=', 'kh.MAKHACHHANG')
            ->leftJoin('users as nv', 'px.NHANVIEN_ID', '=', 'nv.id')
            ->leftJoin('DIACHI_GIAOHANG as dc', 'dc.MADIACHI', '=', 'px.MADIACHI')
            ->leftJoin('KHUYENMAI as km', 'km.MAKHUYENMAI', '=', 'px.MAKHUYENMAI')
            ->select(
                'px.MAPX',
                'px.NGAYXUAT',
                'px.TRANGTHAI',
                'px.GHICHU',
                'px.TONGSL',
                'kh.HOTEN as KHACHHANG',
                'kh.MAKHACHHANG',
                DB::raw('COALESCE(dc.DIACHI, "Chưa cập nhật") as DIACHI'),
                'nv.name as NHANVIEN',
                'px.TONGTIEN',
                'km.MAKHUYENMAI',
                'km.LOAIKHUYENMAI',
                'km.GIAMGIA'
            )
            ->where('px.MAPX', $id)
            ->first();

        if (!$header) {
            return back()->with('error', 'Không tìm thấy phiếu xuất.');
        }

        // Lấy chi tiết sản phẩm
        $lines = DB::table('CT_PHIEUXUAT as ct')
            ->join('SANPHAM as sp', 'ct.MASANPHAM', '=', 'sp.MASANPHAM')
            ->where('ct.MAPX', $id)
            ->select(
                'ct.MASANPHAM',
                'sp.TENSANPHAM',
                'ct.SOLUONG',
                'ct.DONGIA',
                DB::raw('ct.SOLUONG * ct.DONGIA as THANHTIEN')
            )
            ->get();

        $tongSL = $lines->sum('SOLUONG');
        $subtotal = $lines->sum('THANHTIEN');

        // Tính tiền giảm nếu có khuyến mãi
        $discountAmount = $header->GIAMGIA > 0 ? $subtotal * ($header->GIAMGIA / 100) : 0;
        $tongTien       = $subtotal - $discountAmount;

        $data = [
            'header' => $header,
            'lines' => $lines,
            'tongSL' => $tongSL,
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'tongTien' => $tongTien,
        ];

        $pdf = Pdf::loadView('staff.issues_pdf', $data)->setPaper('a4', 'portrait');
        $fileName = 'PhieuXuat_' . $header->MAPX . '.pdf';
        return $pdf->download($fileName);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $q        = trim($request->get('q', ''));
        $customer = $request->get('customer');
        $status   = $request->get('status');
        $from     = $request->get('from');
        $to       = $request->get('to');

        $file = 'phieu-xuat-' . now()->format('Ymd-His') . '.csv';

        $response = new StreamedResponse(function () use ($q, $customer, $status, $from, $to) {
            echo "\xEF\xBB\xBF"; // BOM for Excel
            $out = fopen('php://output', 'w');

            // Header CSV
            fputcsv($out, [
                'STT',
                'MÃ PX',
                'Khách hàng',
                'Nhân viên',
                'Ngày xuất',
                'Khuyến mãi',
                'Tổng trước KM (đ)',
                'Tổng sau KM (đ)',
                'Trạng thái',
            ]);

            $query = DB::table('PHIEUXUAT as p')
                ->join('KHACHHANG as k', 'k.MAKHACHHANG', '=', 'p.MAKHACHHANG')
                ->join('users as u', 'u.id', '=', 'p.NHANVIEN_ID')
                ->leftJoin('CT_PHIEUXUAT as ct', 'ct.MAPX', '=', 'p.MAPX')
                ->leftJoin('KHUYENMAI as km', 'km.MAKHUYENMAI', '=', 'p.MAKHUYENMAI')
                ->when($q, function ($query) use ($q) {
                    $like = "%{$q}%";
                    $query->where(function ($x) use ($like) {
                        $x->whereRaw('CAST(p.MAPX AS CHAR) LIKE ?', [$like])
                            ->orWhere('k.HOTEN', 'like', $like)
                            ->orWhere('u.name', 'like', $like);
                    });
                })
                ->when($customer, fn($q2) => $q2->where('p.MAKHACHHANG', $customer))
                ->when($status, fn($q2) => $q2->where('p.TRANGTHAI', $status))
                ->when($from, fn($q2) => $q2->whereDate('p.NGAYXUAT', '>=', $from))
                ->when($to, fn($q2) => $q2->whereDate('p.NGAYXUAT', '<=', $to))
                ->select(
                    'p.MAPX',
                    'p.NGAYXUAT',
                    'p.TRANGTHAI',
                    'k.HOTEN as KHACHHANG',
                    'u.name as NHANVIEN',
                    DB::raw('COALESCE(SUM(ct.SOLUONG * ct.DONGIA), 0) as SUBTOTAL'),
                    'km.LOAIKHUYENMAI',
                    'km.GIAMGIA'
                )
                ->groupBy(
                    'p.MAPX',
                    'p.NGAYXUAT',
                    'p.TRANGTHAI',
                    'k.HOTEN',
                    'u.name',
                    'km.LOAIKHUYENMAI',
                    'km.GIAMGIA'
                )
                ->orderBy('p.MAPX', 'asc');

            $i = 0;
            $query->chunk(1000, function ($rows) use (&$i, $out) {
                foreach ($rows as $r) {
                    $i++;

                    $totalBefore = (float) $r->SUBTOTAL;  // Tổng trước khuyến mãi
                    $discount    = $r->GIAMGIA ? (float) $r->GIAMGIA : 0;
                    $totalAfter  = $totalBefore - ($discount > 0 ? $totalBefore * $discount / 100 : 0);
                    $kmText      = $r->LOAIKHUYENMAI ? ($r->LOAIKHUYENMAI . ' (' . $discount . '%)') : '';

                    fputcsv($out, [
                        $i,
                        $r->MAPX,
                        $r->KHACHHANG ?? '',
                        $r->NHANVIEN ?? '',
                        (string) \Carbon\Carbon::parse($r->NGAYXUAT)->format('d/m/Y H:i'),
                        $kmText,
                        $totalBefore,
                        $totalAfter,
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
