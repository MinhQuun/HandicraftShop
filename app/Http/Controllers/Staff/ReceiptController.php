<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; 

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $ncc     = $request->get('ncc');
        $status  = $request->get('status');
        $from    = $request->get('from');
        $to      = $request->get('to');

        $receipts = DB::table('PHIEUNHAP as p')
            ->join('NHACUNGCAP as n', 'n.MANHACUNGCAP', '=', 'p.MANHACUNGCAP')
            ->join('users as u', 'u.id', '=', 'p.NHANVIEN_ID')
            ->leftJoin('CT_PHIEUNHAP as ct', 'ct.MAPN', '=', 'p.MAPN')
            ->when($q, function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($x) use ($like) {
                    $x->whereRaw('CAST(p.MAPN AS CHAR) LIKE ?', [$like])
                        ->orWhere('n.TENNHACUNGCAP', 'like', $like)
                        ->orWhere('u.name', 'like', $like);
                });
            })
            ->when($ncc,    fn($q2) => $q2->where('p.MANHACUNGCAP', $ncc))
            ->when($status, fn($q2) => $q2->where('p.TRANGTHAI', $status))
            ->when($from,   fn($q2) => $q2->whereDate('p.NGAYNHAP', '>=', $from))
            ->when($to,     fn($q2) => $q2->whereDate('p.NGAYNHAP', '<=', $to))
            ->select(
                'p.MAPN',
                'p.NGAYNHAP',
                'p.TRANGTHAI',
                'p.GHICHU',
                'n.TENNHACUNGCAP',
                'n.MANHACUNGCAP',
                'u.name as NHANVIEN',
                DB::raw('COALESCE(SUM(ct.SOLUONG * ct.DONGIA), 0) as TONGTIEN')
            )
            ->groupBy('p.MAPN', 'p.NGAYNHAP', 'p.TRANGTHAI', 'p.GHICHU', 'n.TENNHACUNGCAP', 'n.MANHACUNGCAP', 'u.name')
            ->orderBy('p.MAPN', 'asc')
            ->paginate(10)
            ->withQueryString();

        $suppliers = DB::table('NHACUNGCAP')
            ->select('MANHACUNGCAP', 'TENNHACUNGCAP')
            ->orderBy('TENNHACUNGCAP')
            ->get();

        // Lấy thêm GIANHAP để JS tự fill đơn giá
        $products = DB::table('SANPHAM')
            ->select('SANPHAM.MASANPHAM', 'SANPHAM.TENSANPHAM', 'SANPHAM.MANHACUNGCAP', 'SANPHAM.GIANHAP', 'SANPHAM.SOLUONGTON')
            ->orderBy('SANPHAM.MASANPHAM')
            ->get();

        // ==== Combobox Nhân viên: chỉ người có quyền Q02 (nhân viên) ====
        $roleNhanVien = DB::table('QUYEN')
            ->whereRaw('LOWER(TENQUYEN) = "nhanvien"')
            ->value('MAQUYEN') ?? 'Q02';

        $employees = DB::table('users as u')
            ->join('QUYEN_NGUOIDUNG as qnd', 'qnd.user_id', '=', 'u.id')
            ->where('qnd.MAQUYEN', $roleNhanVien)
            ->select('u.id', 'u.name')
            ->distinct()
            ->orderBy('u.name')
            ->get();

        return view('staff.receipts', compact('receipts', 'suppliers', 'products', 'employees', 'q', 'ncc', 'status', 'from', 'to'));
    }

    public function store(Request $request)
    {
        // Ràng buộc NHÂN VIÊN thuộc quyền Q02
        $roleNhanVien = DB::table('QUYEN')
            ->whereRaw('LOWER(TENQUYEN) = "nhanvien"')
            ->value('MAQUYEN') ?? 'Q02';

        $data = $request->validate([
            'MANHACUNGCAP'   => ['required', 'exists:NHACUNGCAP,MANHACUNGCAP'],
            'NHANVIEN_ID'    => [
                'required',
                Rule::exists('users', 'id'),
                Rule::exists('QUYEN_NGUOIDUNG', 'user_id')->where('MAQUYEN', $roleNhanVien),
            ],
            'GHICHU'         => ['nullable', 'string', 'max:255'],
            'ITEM_MASP.*'    => ['required', 'exists:SANPHAM,MASANPHAM'],
            'ITEM_SOLUONG.*' => ['required', 'integer', 'min:1'],
            // DONGIA phía client có thể gửi, nhưng server sẽ luôn ghi theo GIANHAP
            'ITEM_DONGIA.*'  => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            DB::table('PHIEUNHAP')->insert([
                'MANHACUNGCAP' => $data['MANHACUNGCAP'],
                'NGAYNHAP'     => now(),
                'NHANVIEN_ID'  => $data['NHANVIEN_ID'],
                'TRANGTHAI'    => 'NHAP',
                'GHICHU'       => $data['GHICHU'] ?? null,
            ]);

            $mapn = DB::getPdo()->lastInsertId();

            $masp    = $request->input('ITEM_MASP', []);
            $soluong = $request->input('ITEM_SOLUONG', []);

            // Map giá nhập theo sản phẩm (server-side enforce)
            $priceMap = DB::table('SANPHAM')
                ->whereIn('MASANPHAM', $masp)
                ->pluck('GIANHAP', 'MASANPHAM');

            $count = count($masp);
            for ($i = 0; $i < $count; $i++) {
                $pId   = $masp[$i];
                $qty   = (int)($soluong[$i] ?? 0);
                $price = (float)($priceMap[$pId] ?? 0); // luôn dùng GIANHAP

                DB::table('CT_PHIEUNHAP')->insert([
                    'MAPN'      => $mapn,
                    'MASANPHAM' => $pId,
                    'SOLUONG'   => $qty,
                    'DONGIA'    => $price,
                ]);
            }

            DB::commit();
            return redirect()->route('staff.receipts.index')->with('success', 'Đã tạo phiếu nhập nháp. Bạn có thể Xác nhận để cộng tồn.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi tạo phiếu nhập: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $header = DB::table('PHIEUNHAP as p')
            ->join('NHACUNGCAP as n', 'n.MANHACUNGCAP', '=', 'p.MANHACUNGCAP')
            ->join('users as u', 'u.id', '=', 'p.NHANVIEN_ID')
            ->where('p.MAPN', $id)
            ->select(
                'p.MAPN',
                'p.NGAYNHAP',
                'p.TRANGTHAI',
                'p.GHICHU',
                'n.TENNHACUNGCAP',
                'n.MANHACUNGCAP',
                'u.name as NHANVIEN'
            )
            ->first();

        if (!$header) {
            return response()->json(['error' => 'Không tìm thấy phiếu nhập.'], 404);
        }

        $details = DB::table('CT_PHIEUNHAP as ct')
            ->join('SANPHAM as sp', 'sp.MASANPHAM', '=', 'ct.MASANPHAM')
            ->where('ct.MAPN', $id)
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
        $row = DB::table('PHIEUNHAP')->where('MAPN', $id)->first();
        if (!$row) return back()->with('error', 'Không tìm thấy phiếu nhập.');
        if ($row->TRANGTHAI !== 'NHAP') {
            return back()->with('error', 'Chỉ xác nhận phiếu ở trạng thái NHAP.');
        }

        DB::beginTransaction();
        try {
            DB::table('PHIEUNHAP')->where('MAPN', $id)->update(['TRANGTHAI' => 'DA_XAC_NHAN']);
            // Inventory được cập nhật bởi trigger DB (vd: trg_pn_after_update)
            DB::commit();
            return back()->with('success', 'Đã xác nhận phiếu nhập (đã cộng tồn).');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi xác nhận phiếu nhập: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $row = DB::table('PHIEUNHAP')->where('MAPN', $id)->first();
        if (!$row) return back()->with('error', 'Không tìm thấy phiếu nhập.');
        if ($row->TRANGTHAI === 'HUY') {
            return back()->with('info', 'Phiếu đã ở trạng thái HUY.');
        }

        DB::beginTransaction();
        try {
            DB::table('PHIEUNHAP')->where('MAPN', $id)->update(['TRANGTHAI' => 'HUY']);
            // Inventory được cập nhật bởi trigger DB (vd: trg_pn_after_update)
            DB::commit();
            return back()->with('success', 'Đã hủy phiếu nhập.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hủy phiếu nhập: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $row = DB::table('PHIEUNHAP')->where('MAPN', $id)->first();
        if (!$row) return back()->with('error', 'Không tìm thấy phiếu nhập.');
        if ($row->TRANGTHAI !== 'NHAP') {
            return back()->with('error', 'Chỉ xoá phiếu ở trạng thái NHAP.');
        }
        $countDetails = DB::table('CT_PHIEUNHAP')->where('MAPN', $id)->count();
        if ($countDetails > 0) {
            return back()->with('error', 'Phiếu đã có chi tiết, không thể xoá.');
        }
        DB::table('PHIEUNHAP')->where('MAPN', $id)->delete();
        return back()->with('success', 'Đã xoá phiếu nhập.');
    }
    public function exportPdf($id)
    {
        $header = DB::table('PHIEUNHAP as p')
            ->join('NHACUNGCAP as n', 'n.MANHACUNGCAP', '=', 'p.MANHACUNGCAP')
            ->join('users as u', 'u.id', '=', 'p.NHANVIEN_ID')
            ->where('p.MAPN', $id)
            ->select(
                'p.MAPN',
                'p.NGAYNHAP',
                'p.TRANGTHAI',
                'p.GHICHU',
                'n.TENNHACUNGCAP',
                'u.name as NHANVIEN'
            )
            ->first();

        if (!$header) {
            return back()->with('error', 'Không tìm thấy phiếu nhập.');
        }

        $details = DB::table('CT_PHIEUNHAP as ct')
            ->join('SANPHAM as sp', 'sp.MASANPHAM', '=', 'ct.MASANPHAM')
            ->where('ct.MAPN', $id)
            ->select(
                'ct.MASANPHAM',
                'sp.TENSANPHAM',
                'ct.SOLUONG',
                'ct.DONGIA',
                DB::raw('ct.SOLUONG * ct.DONGIA as THANHTIEN')
            )
            ->get();

        $tongtien = $details->sum(fn($d) => $d->THANHTIEN);

        $pdf = Pdf::loadView('staff.receipts_pdf', [
            'header'   => $header,
            'details'  => $details,
            'TONGTIEN' => $tongtien,
        ]);

        return $pdf->download("phieu_nhap_{$header->MAPN}.pdf");
    }
}