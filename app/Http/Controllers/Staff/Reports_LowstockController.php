<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Reports_LowstockController extends Controller
{
    public function index(Request $request)
    {
        // Không cần filters vì không có lọc thời gian hoặc sản phẩm
        $filters = [];

        // Chuẩn bị query cho sản phẩm tồn kho thấp dựa trên SOLUONGTON
        $products = DB::table('SANPHAM as s')
            ->select(
                's.MASANPHAM',
                's.TENSANPHAM',
                's.SOLUONGTON as stock',
                's.GIABAN',
                's.MOTA'
            )
            ->where('s.SOLUONGTON', '<=', 10)
            ->orderBy('s.SOLUONGTON') // Sắp xếp theo tồn kho tăng dần
            ->paginate(10);

        // Chuẩn bị dữ liệu cho biểu đồ (số lượng sản phẩm theo tất cả trạng thái)
        $chartData = DB::table('SANPHAM as s')
            ->select(
                DB::raw('CASE 
                    WHEN s.SOLUONGTON <= 0 THEN "Hết hàng" 
                    WHEN s.SOLUONGTON <= 10 THEN "Sắp hết" 
                    ELSE "Còn đủ" 
                END as status'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();

        $chart = [
            'labels' => ['Hết hàng', 'Sắp hết', 'Còn đủ'],
            'counts' => [
                $chartData['Hết hàng']->count ?? 0,
                $chartData['Sắp hết']->count ?? 0,
                $chartData['Còn đủ']->count ?? 0
            ]
        ];

        // Trả về view với dữ liệu
        return view('staff.lowstock', compact('products', 'chart'));
    }
}