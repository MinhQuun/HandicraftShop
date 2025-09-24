<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;

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

    /** 
     * Lấy tổng số sản phẩm khác nhau trong giỏ (dùng cho icon cart)
     * -> Mỗi sản phẩm chỉ +1 lần dù SOLUONG bao nhiêu
     */
    private function getCartCount(array $cart): int
    {
        return count($cart);
    }

    /** Trang xem giỏ */
    public function show(Request $r)
    {
        $items = collect($this->getCart());

        // Trong giỏ vẫn phải tính đúng số lượng và tổng tiền
        $totalQty   = (int) $items->sum('SOLUONG');
        $totalPrice = (int) $items->sum(fn($i) => (int)$i['SOLUONG'] * (int)$i['GIABAN']);

        return view('pages.cart', compact('items', 'totalQty', 'totalPrice'));
    }

    /** Thêm vào giỏ (AJAX từ trang sản phẩm) */
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

        $maxStock = (int)($p->SOLUONGTON ?? 999999);
        $cart = $this->getCart();

        if (!isset($cart[$id])) {
            // Nếu sản phẩm chưa có trong giỏ thì thêm mới
            $cart[$id] = [
                'MASANPHAM'   => $p->MASANPHAM,
                'TENSANPHAM'  => $p->TENSANPHAM,
                'HINHANH'     => (string)($p->HINHANH ?? ''),
                'GIABAN'      => (int)($p->GIABAN ?? 0),
                'SOLUONG'     => min($qty, $maxStock),
            ];
        } else {
            // Nếu đã có thì chỉ tăng số lượng
            $cart[$id]['SOLUONG'] = min(
                $cart[$id]['SOLUONG'] + $qty,
                $maxStock
            );
        }

        $this->putCart($cart);

        return response()->json([
            'message'    => 'Đã thêm vào giỏ hàng!',
            'cart_count' => $this->getCartCount($cart), // chỉ tính số sản phẩm khác nhau
        ]);
    }

    /** Tăng 1 đơn vị */
    public function increase(Request $r, string $id)
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            $p = SanPham::where('MASANPHAM', $id)->first();
            $maxStock = (int)($p->SOLUONGTON ?? 999999);

            $cart[$id]['SOLUONG'] = min(
                $cart[$id]['SOLUONG'] + 1,
                $maxStock
            );

            $this->putCart($cart);
        }

        return back();
    }

    /** Giảm 1 đơn vị (<=0 thì xoá) */
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

    /** Xoá hẳn 1 sản phẩm khỏi giỏ */
    public function remove(Request $r, string $id)
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->putCart($cart);
        }

        return back();
    }

    /** Trang checkout (mock) */
    public function checkout()
    {
        if (empty($this->getCart())) {
            return redirect()->route('cart')->with('message', 'Giỏ hàng trống.');
        }

        return view('pages.checkout');
    }
}
