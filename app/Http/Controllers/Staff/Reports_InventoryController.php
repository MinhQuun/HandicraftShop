<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Reports_InventoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));
        $perPage = (int) $request->input('per_page', 15);
        if ($perPage <= 0) $perPage = 15;

        $productsQuery = DB::table('SANPHAM as s')
            ->select(
                's.MASANPHAM',
                's.TENSANPHAM',
                's.HINHANH',
                's.SOLUONGTON',
                's.GIABAN',
                's.GIANHAP', // thêm cột GIANHAP
                'l.TENLOAI',
                DB::raw('COALESCE(n.TENNHACUNGCAP, "—") as TENNHACUNGCAP'), // nếu NULL thì hiển thị —
                's.MOTA'
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

        $products = $productsQuery->orderBy('s.TENSANPHAM')
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('staff.inventory', [
            'products' => $products,
            'filters' => [
                'q' => $q,
                'per_page' => $perPage,
            ]
        ]);
    }
}
