<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SanPham;

class ProductApiController extends Controller
{
    public function price($id)
    {
        $p = SanPham::where('MASANPHAM', $id)->first();
        if (!$p) return response()->json(['ok'=>false,'msg'=>'Not found'], 404);
        return response()->json([
            'ok' => true,
            'id' => $p->MASANPHAM,
            'name' => $p->TENSANPHAM,
            'GIABAN' => (float) ($p->GIABAN ?? 0),
        ]);
    }
}

