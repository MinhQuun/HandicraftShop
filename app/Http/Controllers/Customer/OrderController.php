<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SanPham;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\DiaChiGiaoHang;
use App\Models\HinhThucTT;
use App\Models\KhuyenMai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected function resolveMaKhachHang(): ?int
    {
        if (!Auth::check()) return null;
        return DB::table('KHACHHANG')->where('user_id', Auth::id())->value('MAKHACHHANG');
    }

    protected function getCart(): array
    {
        return session('cart', []);
    }

    protected function putCart(array $cart): void
    {
        session(['cart' => $cart]);
    }

    protected function calculateTotal($items, $promo = null)
    {
        $totalPrice = $items->sum(fn($i) => (int)$i['SOLUONG'] * (int)$i['GIABAN']);
        if ($promo) {
            if ($promo->LOAI === 'percent') {
                $totalPrice = round($totalPrice * (1 - $promo->GIAMGIA / 100));
            } elseif ($promo->LOAI === 'fixed') {
                $totalPrice = max(0, $totalPrice - $promo->GIAMGIA);
            }
        }
        return (int) $totalPrice;
    }

    public function create()
    {
        $cart = $this->getCart();
        if (empty($cart)) return redirect()->route('cart')->with('message', 'Giỏ hàng trống.');

        $items = collect($cart)->map(function ($item, $id) {
            $product = SanPham::where('MASANPHAM', $id)->first();
            if ($product) {
                $giaBan = (int) $product->GIABAN;
                if ($product->MAKHUYENMAI) {
                    $km = KhuyenMai::where('MAKHUYENMAI', $product->MAKHUYENMAI)
                        ->where('NGAYBATDAU', '<=', now())
                        ->where('NGAYKETTHUC', '>=', now())
                        ->first();
                    if ($km && $km->GIAMGIA > 0 && $km->LOAI === 'product') {
                        $giaBan = (int) round($giaBan * (1 - ($km->GIAMGIA / 100)));
                    }
                }
                $item['GIABAN'] = $giaBan;
                $item['SOLUONG'] = min((int) $item['SOLUONG'], (int) $product->SOLUONGTON);
            }
            return $item;
        });

        $items = $items->filter(function ($item, $id) {
            $p = SanPham::where('MASANPHAM', $id)->first();
            return $p && $p->SOLUONGTON >= $item['SOLUONG'];
        });
        $this->putCart($items->toArray());
        if ($items->isEmpty()) return redirect()->route('cart')->with('message', 'Giỏ hàng trống sau khi kiểm tra tồn kho.');

        $totalQty = $items->sum('SOLUONG');
        $promo = session('promo');
        $totalPrice = $this->calculateTotal($items, $promo);

        $paymentMethods = collect();
        $customer = null;
        $currentAddress = null;
        $maKhachHang = $this->resolveMaKhachHang();

        if ($maKhachHang) {
            $currentAddress = DiaChiGiaoHang::where('MAKHACHHANG', $maKhachHang)
                ->orderByDesc('MADIACHI')
                ->first();

            $paymentMethods = HinhThucTT::all();
            $customer = DB::table('KHACHHANG')->where('MAKHACHHANG', $maKhachHang)->first();
        }

        return view('pages.checkout', compact(
            'items', 'totalQty', 'totalPrice', 'paymentMethods', 'customer', 'currentAddress', 'promo'
        ));
    }

    public function applyPromo(Request $request)
    {
        $code = $request->input('promo_code');
        $promo = KhuyenMai::where('MAKHUYENMAI', $code)
            ->where('NGAYBATDAU', '<=', now())
            ->where('NGAYKETTHUC', '>=', now())
            ->where('LOAI', 'order')
            ->first();

        if ($promo) {
            session(['promo' => $promo]);
            return response()->json(['success' => true, 'message' => 'Áp dụng mã thành công!', 'discount' => $promo->GIAMGIA]);
        } else {
            session()->forget('promo');
            return response()->json(['success' => false, 'message' => 'Mã không hợp lệ.']);
        }
    }

    public function store(Request $request)
    {
        $maKhachHang = $this->resolveMaKhachHang();
        if (!$maKhachHang) return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt hàng.');

        $validated = $request->validate([
            'address_id' => 'nullable|exists:DIACHI_GIAOHANG,MADIACHI',
            'DIACHI'     => 'required|string|max:255',
            'GHICHU'     => 'nullable|string|max:255',
            'MATT'       => 'required|exists:HINHTHUCTT,MATT',
        ]);

        $cart = $this->getCart();
        if (empty($cart)) return redirect()->route('cart')->with('message', 'Giỏ hàng trống.');

        DB::beginTransaction();
        try {
            $items = collect($cart);

            // Kiểm tra tồn kho với lock
            $items = $items->map(function ($item, $id) {
                $product = SanPham::where('MASANPHAM', $id)->lockForUpdate()->first();
                if (!$product || $product->SOLUONGTON < (int)$item['SOLUONG']) {
                    throw new \Exception('Sản phẩm ' . ($item['TENSANPHAM'] ?? $id) . ' đã hết hàng.');
                }
                $giaBan = (int)$product->GIABAN;
                if ($product->MAKHUYENMAI) {
                    $km = KhuyenMai::where('MAKHUYENMAI', $product->MAKHUYENMAI)
                        ->where('NGAYBATDAU', '<=', now())
                        ->where('NGAYKETTHUC', '>=', now())
                        ->first();
                    if ($km && $km->GIAMGIA > 0 && $km->LOAI === 'product') {
                        $giaBan = (int)round($giaBan * (1 - ($km->GIAMGIA / 100)));
                    }
                }
                $item['GIABAN'] = $giaBan;
                return $item;
            })->all();

            $totalQty = array_sum(array_column($items, 'SOLUONG'));
            $promo = session('promo');
            $totalPrice = $this->calculateTotal(collect($items), $promo);

            $maDiaChi  = $validated['address_id'] ?? null;
            // Chuẩn hoá chuỗi địa chỉ: trim + gộp khoảng trắng
            $textDiaChi = preg_replace('/\s+/u', ' ', trim($validated['DIACHI']));

            // Hàm so sánh không phân biệt hoa/thường
            $toLower = fn($s) => mb_strtolower($s ?? '', 'UTF-8');

            // Nếu có chọn 1 địa chỉ sẵn (address_id) nhưng người dùng sửa nội dung khác đi,
            // -> KHÔNG ghi đè bản ghi cũ, mà tạo bản ghi mới (hoặc tái sử dụng nếu đã có bản ghi trùng y hệt trước đó).
            if ($maDiaChi) {
                $addr = DiaChiGiaoHang::where('MADIACHI', $maDiaChi)
                    ->where('MAKHACHHANG', $maKhachHang)
                    ->first();

                if (!$addr) {
                    throw new \Exception('Địa chỉ không hợp lệ.');
                }

                if ($toLower($addr->DIACHI) === $toLower($textDiaChi)) {
                    // Nội dung không đổi -> dùng lại địa chỉ hiện tại
                    // $maDiaChi giữ nguyên
                } else {
                    // Tìm xem đã có địa chỉ giống hệt chưa (tránh trùng)
                    $existing = DiaChiGiaoHang::where('MAKHACHHANG', $maKhachHang)
                        ->whereRaw('LOWER(DIACHI) = ?', [$toLower($textDiaChi)])
                        ->first();

                    if ($existing) {
                        $maDiaChi = $existing->MADIACHI; // tái sử dụng
                    } else {
                        $new = new DiaChiGiaoHang();
                        $new->MAKHACHHANG = $maKhachHang;
                        $new->DIACHI      = $textDiaChi;
                        $new->save();
                        $maDiaChi = $new->MADIACHI;      // dùng địa chỉ mới
                    }
                }
            } else {
                // Không chọn địa chỉ sẵn -> thêm mới hoặc tái sử dụng nếu đã tồn tại địa chỉ y hệt
                $existing = DiaChiGiaoHang::where('MAKHACHHANG', $maKhachHang)
                    ->whereRaw('LOWER(DIACHI) = ?', [$toLower($textDiaChi)])
                    ->first();

                if ($existing) {
                    $maDiaChi = $existing->MADIACHI;
                } else {
                    $addr = new DiaChiGiaoHang();
                    $addr->MAKHACHHANG = $maKhachHang;
                    $addr->DIACHI      = $textDiaChi;
                    $addr->save();
                    $maDiaChi = $addr->MADIACHI;
                }
            }

            $order = new DonHang();
            $order->MAKHACHHANG   = $maKhachHang;
            $order->MADIACHI      = $maDiaChi;
            $order->NGAYDAT       = now();
            $order->MATT          = $validated['MATT'];
            $order->GHICHU        = $validated['GHICHU'] ?? null;
            $order->TONGSLHANG    = (int)$totalQty;
            $order->TONGTHANHTIEN = (int)$totalPrice;
            $order->MAKHUYENMAI   = $promo ? $promo->MAKHUYENMAI : null;

            $methodsCfg = config('payment_methods.methods', []);
            $type = $methodsCfg[$order->MATT]['type'] ?? 'offline';
            $order->TRANGTHAI = ($type === 'online') ? 'Chờ thanh toán' : 'Chờ xử lý';

            $order->save();

            foreach ($items as $id => $item) {
                $detail = new ChiTietDonHang();
                $detail->MADONHANG = $order->MADONHANG;
                $detail->MASANPHAM = $id;
                $detail->SOLUONG   = (int)$item['SOLUONG'];
                $detail->DONGIA    = (int)$item['GIABAN'];
                $detail->save();
            }

            session()->forget(['cart', 'promo']);
            DB::commit();

            return redirect()->route('orders.confirm', $order->MADONHANG)
                ->with('success', 'Đặt hàng thành công! Mã đơn: ' . $order->MADONHANG);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirm($id)
    {
        $maKhachHang = $this->resolveMaKhachHang();
        $order = DonHang::where('MADONHANG', $id)
            ->when($maKhachHang, fn($q) => $q->where('MAKHACHHANG', $maKhachHang))
            ->firstOrFail();

        $details = DB::table('CHITIETDONHANG as d')
            ->join('SANPHAM as s', 's.MASANPHAM', '=', 'd.MASANPHAM')
            ->select('d.MASANPHAM', 'd.SOLUONG', 'd.DONGIA', 's.TENSANPHAM')
            ->where('d.MADONHANG', $order->MADONHANG)->get();

        $address  = DiaChiGiaoHang::find($order->MADIACHI);
        $payment  = HinhThucTT::find($order->MATT);
        $customer = DB::table('KHACHHANG')->where('MAKHACHHANG', $order->MAKHACHHANG)->first();

        return view('pages.order_confirm', compact('order', 'details', 'address', 'payment', 'customer'));
    }
}