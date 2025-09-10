<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SanPham;

class CartController extends Controller
{
    /** Lấy giỏ hàng từ session dạng array */
    private function getCart(): array
    {
        return session('cart', []);
    }

    /** Lưu lại giỏ vào session */
    private function putCart(array $cart): void
    {
        session(['cart' => $cart]);
    }

    /** Trang xem giỏ */
    public function show(Request $r)
    {
        $items = collect($this->getCart());

        $totalQty   = (int) $items->sum('SOLUONG');
        $totalPrice = (int) $items->sum(fn($i) => (int)$i['SOLUONG'] * (int)$i['GIABAN']);

        return view('pages.cart', compact('items', 'totalQty', 'totalPrice'));
    }

    /** Thêm vào giỏ (được gọi từ AJAX ở trang sản phẩm) */
    public function add(Request $r)
    {
        $data = $r->validate([
            'product_id' => 'required|string',
            'qty'        => 'nullable|integer|min:1',
        ]);

        $qty = (int)($data['qty'] ?? 1);
        $id  = $data['product_id'];

        // Tìm SP trong DB
        $p = SanPham::where('MASANPHAM', $id)->first();
        if (!$p) {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $cart = $this->getCart();

        if (!isset($cart[$id])) {
            $cart[$id] = [
                'MASANPHAM'   => $p->MASANPHAM,
                'TENSANPHAM'  => $p->TENSANPHAM,
                'HINHANH'     => (string)($p->HINHANH ?? ''),
                'GIABAN'      => (int)($p->GIABAN ?? 0),
                'SOLUONG'     => $qty,
            ];
        } else {
            $cart[$id]['SOLUONG'] += $qty;
        }

        $this->putCart($cart);

        return response()->json([
            'message' => 'Đã thêm vào giỏ hàng!',
            'count'   => array_sum(array_column($cart, 'SOLUONG')),
        ]);
    }

    /** Tăng 1 đơn vị */
    public function increase(Request $r, string $id)
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            $cart[$id]['SOLUONG'] += 1;
            $this->putCart($cart);
        }
        return back();
    }

    /** Giảm 1 đơn vị (về 0 thì xoá) */
    public function decrease(Request $r, string $id)
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            $cart[$id]['SOLUONG'] -= 1;
            if ($cart[$id]['SOLUONG'] <= 0) {
                unset($cart[$id]);
            }
            $this->putCart($cart);
        }
        return back();
    }

    /** Xoá hẳn 1 dòng */
    public function remove(Request $r, string $id)
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->putCart($cart);
        }
        return back();
    }

    /** (Tuỳ chọn) Trang xác nhận/checkout mock */
    public function checkout()
    {
        // Ở đây chỉ demo — bạn có thể chuyển qua flow đặt hàng thực tế
        if (empty($this->getCart())) {
            return redirect()->route('cart')->with('message', 'Giỏ hàng trống.');
        }
        return view('pages.checkout'); // tạo sau nếu cần
    }
}
