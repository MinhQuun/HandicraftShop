<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                DB::raw('COALESCE(SUM(ct.SOLUONG * ct.DONGIA), 0) as TONGTIEN')
            )
            ->groupBy('p.MAPX', 'p.NGAYXUAT', 'p.TRANGTHAI', 'p.TONGSL', 'p.GHICHU', 'k.HOTEN', 'k.MAKHACHHANG', 'u.name')
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
                'dc.DIACHI'
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

        $tongtien = $details->sum(fn($d) => $d->THANHTIEN);

        return response()->json([
            'header'   => $header,
            'lines'    => $details,
            'TONGTIEN' => $tongtien,
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
}
