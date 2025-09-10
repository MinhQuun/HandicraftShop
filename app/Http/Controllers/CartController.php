<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required'],
            'qty' => ['nullable','integer','min:1']
        ]);

        // TODO: logic thêm vào giỏ (session hoặc DB)
        // session()->push('cart.items', ['id' => $request->product_id, 'qty' => $request->qty ?? 1]);

        return response()->json(['ok' => true, 'message' => 'Đã thêm sản phẩm vào giỏ.']);
    }
}
