<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Reports_InoutController extends Controller
{
    /**
     * Hiển thị báo cáo Nhập - Xuất - Tồn
     * URL: staff/reports/inout (đã có route stub, hãy thay bằng controller này)
     */
    public function index(Request $request)
    {
        // Lọc ngày: nếu không có thì lấy 30 ngày gần nhất (mặc định)
        $end = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        $start = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : (clone $end)->subDays(29)->startOfDay();

        $q = trim($request->input('q', ''));

        $perPage = (int) $request->input('per_page', 15);
        if ($perPage <= 0) $perPage = 15;

        // 1) Lấy nhập theo sản phẩm trong kỳ
        $imports = DB::table('CT_PHIEUNHAP as ctpn')
            ->join('PHIEUNHAP as pn', 'ctpn.MAPN', '=', 'pn.MAPN')
            ->select('ctpn.MASANPHAM', DB::raw('SUM(ctpn.SOLUONG) as total_in'))
            ->whereBetween('pn.NGAYNHAP', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('ctpn.MASANPHAM')
            ->pluck('total_in', 'MASANPHAM'); // [MASANPHAM => total_in]

        // 2) Lấy xuất theo sản phẩm trong kỳ
        $exports = DB::table('CT_PHIEUXUAT as ctp')
            ->join('PHIEUXUAT as px', 'ctp.MAPX', '=', 'px.MAPX')
            ->select('ctp.MASANPHAM', DB::raw('SUM(ctp.SOLUONG) as total_out'))
            ->whereBetween('px.NGAYXUAT', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('ctp.MASANPHAM')
            ->pluck('total_out', 'MASANPHAM');

        // 3) Lấy danh sách sản phẩm (kèm SOLUONGTON)
        $productsQuery = DB::table('SANPHAM as s')
            ->select(
                's.MASANPHAM',
                's.TENSANPHAM',
                's.HINHANH',
                's.SOLUONGTON',
                's.GIABAN',
                's.GIANHAP',
                's.MOTA', 
                'l.TENLOAI',
                'n.TENNHACUNGCAP'
            )
            ->leftJoin('LOAI as l', 's.MALOAI', '=', 'l.MALOAI')
            ->leftJoin('NHACUNGCAP as n', 's.MANHACUNGCAP', '=', 'n.MANHACUNGCAP');

        if ($q !== '') {
            $productsQuery->where(function($wr) use ($q) {
                $wr->where('s.TENSANPHAM', 'like', "%{$q}%")
                   ->orWhere('l.TENLOAI', 'like', "%{$q}%")
                   ->orWhere('n.TENNHACUNGCAP', 'like', "%{$q}%");
            });
        }

        // Pagination manual with query builder
        $products = $productsQuery->orderBy('s.TENSANPHAM')->paginate($perPage)->appends($request->except('page'));

        // 4) Build result rows: tính nhập/xuất/tồn đầu/tồn cuối
        $rows = [];
        foreach ($products as $p) {
            $masp = $p->MASANPHAM;
            $in = (int) ($imports[$masp] ?? 0);
            $out = (int) ($exports[$masp] ?? 0);
            $current_stock = (int) ($p->SOLUONGTON ?? 0);

            // Tồn đầu kỳ = hiện tại - (in - out)
            $opening = $current_stock - ($in - $out);
            // Nếu opening âm thì vẫn hiển thị (do dữ liệu không đồng bộ)
            $closing = $current_stock; // hiện tại
            $rows[] = (object) [
                'MASANPHAM' => $masp,
                'TENSANPHAM' => $p->TENSANPHAM,
                'HINHANH' => $p->HINHANH,
                'TENLOAI' => $p->TENLOAI,
                'NHACUNGCAP' => $p->TENNHACUNGCAP,
                'opening' => $opening,
                'in' => $in,
                'out' => $out,
                'closing' => $closing,
                'GIANHAP' => $p->GIANHAP,
                'GIABAN' => $p->GIABAN,
                'MOTA' => $p->MOTA, 
            ];
        }

        // 5) Dữ liệu cho biểu đồ: chọn top N sản phẩm theo biến động (in + out)
        // Tính tổng biến động cho tất cả sản phẩm (chỉ sản phẩm đang trang)
        // Để có biểu đồ đại diện hơn, lấy top 10 theo (in + out) tổng (sắp xếp từ lớn -> nhỏ)
        $allMovements = [];
        // we need to compute movement for products that match filter (not only paged), so query a bit broader
        $allProductsForChartQuery = DB::table('SANPHAM as s')
            ->select('s.MASANPHAM', 's.TENSANPHAM', 's.SOLUONGTON')
            ->leftJoin('LOAI as l', 's.MALOAI', '=', 'l.MALOAI')
            ->leftJoin('NHACUNGCAP as n', 's.MANHACUNGCAP', '=', 'n.MANHACUNGCAP');

        if ($q !== '') {
            $allProductsForChartQuery->where(function($wr) use ($q) {
                $wr->where('s.TENSANPHAM', 'like', "%{$q}%")
                   ->orWhere('l.TENLOAI', 'like', "%{$q}%")
                   ->orWhere('n.TENNHACUNGCAP', 'like', "%{$q}%");
            });
        }
        $allProductsForChart = $allProductsForChartQuery->get();

        foreach ($allProductsForChart as $p) {
            $masp = $p->MASANPHAM;
            $in = (int) ($imports[$masp] ?? 0);
            $out = (int) ($exports[$masp] ?? 0);
            $movement = $in + $out;
            $allMovements[$masp] = [
                'MASANPHAM' => $masp,
                'TENSANPHAM' => $p->TENSANPHAM,
                'in' => $in,
                'out' => $out,
                'closing' => (int) ($p->SOLUONGTON ?? 0),
                'movement' => $movement,
            ];
        }

        // sort by movement desc and take top 10
        uasort($allMovements, function($a, $b) {
            return $b['movement'] <=> $a['movement'];
        });
        $top = array_slice($allMovements, 0, 10, true);

        // prepare chart datasets
        $chartLabels = [];
        $chartIn = [];
        $chartOut = [];
        $chartClosing = [];
        foreach ($top as $t) {
            $chartLabels[] = $t['TENSANPHAM'];
            $chartIn[] = $t['in'];
            $chartOut[] = $t['out'];
            $chartClosing[] = $t['closing'];
        }

        // truyền dữ liệu sang view
        return view('staff.inout', [
            'products' => $products,
            'rows' => $rows,
            'chart' => [
                'labels' => $chartLabels,
                'in' => $chartIn,
                'out' => $chartOut,
                'closing' => $chartClosing,
            ],
            'filters' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'q' => $q,
                'per_page' => $perPage,
            ]
        ]);
    }
}
