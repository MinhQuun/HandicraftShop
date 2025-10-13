<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Reports_SalesController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->get('mode', 'year');
        $start_year = $request->get('start_year', date('Y') - 5);
        $end_year = $request->get('end_year', date('Y'));
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $start_date = $request->get('start_date', date('Y-m-01'));
        $end_date = $request->get('end_date', date('Y-m-d'));

        $chartLabels = $chartRevenues = $chartCosts = $chartProfits = [];
        $tableData = [];
        $topProducts = $topCustomers = [];

        if ($mode == 'year') {
            $data = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereBetween(DB::raw('YEAR(d.NGAYDAT)'), [$start_year, $end_year])
                ->select(
                    DB::raw('YEAR(d.NGAYDAT) as label'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'),
                    DB::raw('SUM(c.SOLUONG * s.GIANHAP) as cost'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) - SUM(c.SOLUONG * s.GIANHAP) as profit')
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            foreach ($data as $item) {
                $chartLabels[] = $item->label;
                $chartRevenues[] = round($item->revenue, 0);
                $chartCosts[] = round($item->cost, 0);
                $chartProfits[] = round($item->profit, 0);
                $tableData[] = (array) $item;
            }

            // Top products and customers (kept as is)
            $topProducts = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereBetween(DB::raw('YEAR(d.NGAYDAT)'), [$start_year, $end_year])
                ->select('s.TENSANPHAM', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('s.TENSANPHAM')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();

            $topCustomers = DB::table('DONHANG as d')
                ->join('KHACHHANG as k', 'd.MAKHACHHANG', 'k.MAKHACHHANG')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereBetween(DB::raw('YEAR(d.NGAYDAT)'), [$start_year, $end_year])
                ->select('k.HOTEN', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('k.MAKHACHHANG', 'k.HOTEN')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();
        } elseif ($mode == 'month') {
            $data = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereYear('d.NGAYDAT', $year)
                ->select(
                    DB::raw('MONTH(d.NGAYDAT) as label'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'),
                    DB::raw('SUM(c.SOLUONG * s.GIANHAP) as cost'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) - SUM(c.SOLUONG * s.GIANHAP) as profit')
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($data as $item) {
                $chartLabels[] = $monthNames[$item->label - 1];
                $chartRevenues[] = round($item->revenue, 0);
                $chartCosts[] = round($item->cost, 0);
                $chartProfits[] = round($item->profit, 0);
                $tableData[] = (array) $item;
            }

            $topProducts = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereYear('d.NGAYDAT', $year)
                ->select('s.TENSANPHAM', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('s.TENSANPHAM')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();

            $topCustomers = DB::table('DONHANG as d')
                ->join('KHACHHANG as k', 'd.MAKHACHHANG', 'k.MAKHACHHANG')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereYear('d.NGAYDAT', $year)
                ->select('k.HOTEN', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('k.MAKHACHHANG', 'k.HOTEN')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();
        } elseif ($mode == 'day') {
            $data = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereYear('d.NGAYDAT', $year)
                ->whereMonth('d.NGAYDAT', $month)
                ->select(
                    DB::raw('DAY(d.NGAYDAT) as label'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'),
                    DB::raw('SUM(c.SOLUONG * s.GIANHAP) as cost'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) - SUM(c.SOLUONG * s.GIANHAP) as profit')
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            foreach ($data as $item) {
                $chartLabels[] = $item->label;
                $chartRevenues[] = round($item->revenue, 0);
                $chartCosts[] = round($item->cost, 0);
                $chartProfits[] = round($item->profit, 0);
                $tableData[] = (array) $item;
            }

            $topProducts = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereYear('d.NGAYDAT', $year)
                ->whereMonth('d.NGAYDAT', $month)
                ->select('s.TENSANPHAM', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('s.TENSANPHAM')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();

            $topCustomers = DB::table('DONHANG as d')
                ->join('KHACHHANG as k', 'd.MAKHACHHANG', 'k.MAKHACHHANG')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereYear('d.NGAYDAT', $year)
                ->whereMonth('d.NGAYDAT', $month)
                ->select('k.HOTEN', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('k.MAKHACHHANG', 'k.HOTEN')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();
        } elseif ($mode == 'custom') {
            $data = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereBetween('d.NGAYDAT', [$start_date, $end_date])
                ->select(
                    DB::raw('DATE(d.NGAYDAT) as label'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'),
                    DB::raw('SUM(c.SOLUONG * s.GIANHAP) as cost'),
                    DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) - SUM(c.SOLUONG * s.GIANHAP) as profit')
                )
                ->groupBy('label')
                ->orderBy('label', 'asc')
                ->get();

            foreach ($data as $item) {
                $chartLabels[] = $item->label;
                $chartRevenues[] = round($item->revenue, 0);
                $chartCosts[] = round($item->cost, 0);
                $chartProfits[] = round($item->profit, 0);
                $tableData[] = (array) $item;
            }

            $topProducts = DB::table('DONHANG as d')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->join('SANPHAM as s', 'c.MASANPHAM', 's.MASANPHAM')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereBetween('d.NGAYDAT', [$start_date, $end_date])
                ->select('s.TENSANPHAM', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('s.TENSANPHAM')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();

            $topCustomers = DB::table('DONHANG as d')
                ->join('KHACHHANG as k', 'd.MAKHACHHANG', 'k.MAKHACHHANG')
                ->join('CHITIETDONHANG as c', 'd.MADONHANG', 'c.MADONHANG')
                ->leftJoin('KHUYENMAI as km', 'd.MAKHUYENMAI', 'km.MAKHUYENMAI')
                ->where('d.TRANGTHAI', 'Hoàn thành')
                ->whereBetween('d.NGAYDAT', [$start_date, $end_date])
                ->select('k.HOTEN', DB::raw('SUM(c.SOLUONG) as total_qty'), DB::raw('SUM(c.SOLUONG * c.DONGIA * (1 - IFNULL(km.GIAMGIA,0)/100)) as revenue'))
                ->groupBy('k.MAKHACHHANG', 'k.HOTEN')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();
        }

        // Đảm bảo luôn có dữ liệu mặc định nếu rỗng
        if (empty($chartLabels)) {
            $chartLabels = [$year]; // Sử dụng năm hiện tại làm label mặc định
            $chartRevenues = [0];
            $chartCosts = [0];
            $chartProfits = [0];
            $tableData = [['label' => $year, 'revenue' => 0, 'cost' => 0, 'profit' => 0]];
        }

        return view('staff.sales', compact(
            'mode', 'start_year', 'end_year', 'year', 'month', 'start_date', 'end_date',
            'chartLabels', 'chartRevenues', 'chartCosts', 'chartProfits',
            'tableData', 'topProducts', 'topCustomers'
        ));
    }
}