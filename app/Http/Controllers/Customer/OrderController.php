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
    /* ===== Helpers: user & cart ===== */

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

    /* ===== Voucher helpers (schema khuyến mãi mới) ===== */

    /** Tìm voucher còn hiệu lực theo code (PHAMVI=ORDER, trong khoảng ngày) */
    protected function findActiveVoucher(string $code): ?KhuyenMai
    {
        $code = mb_strtoupper(trim($code), 'UTF-8');
        if ($code === '') return null;

        return KhuyenMai::query()
            ->where('PHAMVI', 'ORDER')
            ->where('MAKHUYENMAI', $code)
            ->where('NGAYBATDAU', '<=', now())
            ->where('NGAYKETTHUC', '>=', now())
            ->first();
    }

    /** Chuẩn hoá model KM -> mảng lưu session/view */
    protected function normalizeVoucher(KhuyenMai $km): array
    {
        // Nếu Model chưa cast DIEUKIEN_JSON => decode thủ công
        $rules = is_array($km->DIEUKIEN_JSON)
            ? $km->DIEUKIEN_JSON
            : (json_decode($km->DIEUKIEN_JSON ?? '[]', true) ?: []);

        $type = ($km->LOAIKHUYENMAI === 'Giảm %') ? 'percent' : 'fixed';

        return [
            'code'          => $km->MAKHUYENMAI,
            'name'          => $km->TENKHUYENMAI,
            'type'          => $type,                         // percent | fixed
            'value'         => (float)$km->GIAMGIA,           // % hoặc số tiền (đ)
            'min_total'     => (float)($rules['min_order_total'] ?? 0),
            'max_discount'  => (float)($rules['max_discount'] ?? 0),
            'non_stackable' => (bool)($rules['non_stackable'] ?? false),
            'from'          => optional($km->NGAYBATDAU)->toDateString(),
            'to'            => optional($km->NGAYKETTHUC)->toDateString(),
        ];
    }

    /**
     * Tính tiền với voucher: trả về ['subtotal'=>x, 'discount'=>y, 'total'=>z]
     *  - %: discount = round(subtotal * value/100), cap bởi max_discount (>0)
     *  - fixed: discount = min(value, subtotal)
     *  - chỉ áp khi subtotal >= min_total
     */
    protected function computeTotals($items, ?array $voucher): array
    {
        // Tính lại đơn giá theo khuyến mãi sản phẩm (nếu có) để đảm bảo tổng tiền chuẩn
        $subtotal = (int) collect($items)->sum(function ($i) {
            $id = $i['MASANPHAM'] ?? null;
            $qty = (int) ($i['SOLUONG'] ?? 0);
            if (!$id || $qty <= 0) return 0;
            $p = SanPham::where('MASANPHAM', $id)->first();
            $unit = $p ? (int) ($p->gia_sau_km ?? $p->GIABAN ?? 0) : (int) ($i['GIABAN'] ?? 0);
            return $qty * $unit;
        });
        $discount = 0;

        if ($voucher && $subtotal > 0) {
            if ($subtotal >= (int)$voucher['min_total']) {
                if ($voucher['type'] === 'percent') {
                    $discount = (int) round($subtotal * ($voucher['value'] / 100));
                    $cap = (int)$voucher['max_discount'];
                    if ($cap > 0) $discount = min($discount, $cap);
                } else { // fixed
                    $discount = (int) min($voucher['value'], $subtotal);
                }
            }
        }

        $total = max(0, $subtotal - $discount);
        return ['subtotal' => $subtotal, 'discount' => $discount, 'total' => $total];
    }

    /* ===== Hiển thị trang thanh toán ===== */

    public function create()
    {
        $cart = $this->getCart();
        if (empty($cart)) return redirect()->route('cart')->with('message', 'Giỏ hàng trống.');

        // Tạm thời dùng GIABAN gốc, CHƯA áp KM theo sản phẩm (sẽ làm sau)
        $items = collect($cart)->map(function ($item, $id) {
            $p = SanPham::where('MASANPHAM', $id)->first();
            if ($p) {
                $item['GIABAN']      = (int)$p->GIABAN;
                $item['SOLUONG']     = min((int)$item['SOLUONG'], (int)$p->SOLUONGTON);
                $item['TENSANPHAM']  = $item['TENSANPHAM'] ?? $p->TENSANPHAM;
            }
            return $item;
        });

        // Loại item hết tồn sau chuẩn hoá
        $items = $items->filter(function ($item, $id) {
            $p = SanPham::where('MASANPHAM', $id)->first();
            return $p && $p->SOLUONGTON >= (int)$item['SOLUONG'];
        });
        $this->putCart($items->toArray());
        if ($items->isEmpty()) return redirect()->route('cart')->with('message', 'Giỏ hàng trống sau khi kiểm tra tồn kho.');

        $totalQty = $items->sum('SOLUONG');
        $voucher  = session('voucher'); // mảng chuẩn hoá
        $totals   = $this->computeTotals($items, $voucher);

        $paymentMethods = collect();
        $customer = null;
        $currentAddress = null;
        $maKhachHang = $this->resolveMaKhachHang();

        if ($maKhachHang) {
            $currentAddress = DiaChiGiaoHang::where('MAKHACHHANG', $maKhachHang)
                ->orderByDesc('MADIACHI')->first();

            $paymentMethods = HinhThucTT::all();
            $customer = DB::table('KHACHHANG')->where('MAKHACHHANG', $maKhachHang)->first();
        }

        return view('pages.checkout', [
            'items'          => $items,
            'totalQty'       => $totalQty,
            'subtotal'       => $totals['subtotal'],
            'discount'       => $totals['discount'],
            'totalPrice'     => $totals['total'],
            'paymentMethods' => $paymentMethods,
            'customer'       => $customer,
            'currentAddress' => $currentAddress,
            'voucher'        => $voucher,
        ]);
    }

    /* ===== Áp mã voucher (AJAX) ===== */

    public function applyPromo(Request $request)
    {
        $code = (string) $request->input('promo_code', '');
        $km = $this->findActiveVoucher($code);
        if (!$km) {
            session()->forget('voucher');
            return response()->json(['success' => false, 'message' => 'Mã không hợp lệ hoặc đã hết hạn.'], 422);
        }

        $voucher = $this->normalizeVoucher($km);

        // Tính lại theo giỏ hiện tại để trả kết quả tức thì
        $items  = collect($this->getCart());
        if ($items->isEmpty()) {
            session()->forget('voucher');
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống.'], 422);
        }

        $totals = $this->computeTotals($items, $voucher);

        // Lưu voucher (mảng) vào session
        session(['voucher' => $voucher]);

        return response()->json([
            'success'      => true,
            'message'      => 'Áp dụng mã thành công!',
            'code'         => $voucher['code'],
            'discount'     => $totals['discount'],
            'subtotal'     => $totals['subtotal'],
            'total'        => $totals['total'],
            'min_total'    => $voucher['min_total'],
            'max_discount' => $voucher['max_discount'],
            'type'         => $voucher['type'],
            'value'        => $voucher['value'],
        ]);
    }

    /* ===== Đặt hàng ===== */

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
            // Re-validate tồn kho dưới lock + giữ GIABAN gốc (chưa áp KM theo SP)
            $items = collect($cart)->map(function ($item, $id) {
                $p = SanPham::where('MASANPHAM', $id)->lockForUpdate()->first();
                if (!$p || $p->SOLUONGTON < (int)$item['SOLUONG']) {
                    throw new \Exception('Sản phẩm ' . ($item['TENSANPHAM'] ?? $id) . ' đã hết hàng.');
                }
                $item['GIABAN'] = (int)$p->GIABAN;
                return $item;
            });

            $voucher = session('voucher'); // mảng chuẩn hoá
            // Tính lại totals để chốt (tránh thao túng phía client)
            $totals  = $this->computeTotals($items, $voucher);
            $totalQty = (int) $items->sum('SOLUONG');

            /* ===== Địa chỉ ===== */
            $maDiaChi  = $validated['address_id'] ?? null;
            // Chuẩn hoá chuỗi địa chỉ: trim + gộp khoảng trắng
            $textDiaChi = preg_replace('/\s+/u', ' ', trim($validated['DIACHI']));

            // Hàm so sánh không phân biệt hoa/thường
            $toLower = fn($s) => mb_strtolower($s ?? '', 'UTF-8');

            if ($maDiaChi) {
                $addr = DiaChiGiaoHang::where('MADIACHI', $maDiaChi)
                    ->where('MAKHACHHANG', $maKhachHang)->first();
                if (!$addr) throw new \Exception('Địa chỉ không hợp lệ.');

                if ($toLower($addr->DIACHI) !== $toLower($textDiaChi)) {
                    $existing = DiaChiGiaoHang::where('MAKHACHHANG', $maKhachHang)
                        ->whereRaw('LOWER(DIACHI) = ?', [$toLower($textDiaChi)])->first();
                    if ($existing) $maDiaChi = $existing->MADIACHI;
                    else {
                        $new = new DiaChiGiaoHang();
                        $new->MAKHACHHANG = $maKhachHang;
                        $new->DIACHI      = $textDiaChi;
                        $new->save();
                        $maDiaChi = $new->MADIACHI;
                    }
                }
            } else {
                $existing = DiaChiGiaoHang::where('MAKHACHHANG', $maKhachHang)
                    ->whereRaw('LOWER(DIACHI) = ?', [$toLower($textDiaChi)])->first();
                if ($existing) $maDiaChi = $existing->MADIACHI;
                else {
                    $addr = new DiaChiGiaoHang();
                    $addr->MAKHACHHANG = $maKhachHang;
                    $addr->DIACHI      = $textDiaChi;
                    $addr->save();
                    $maDiaChi = $addr->MADIACHI;
                }
            }

            /* ===== Tạo đơn hàng ===== */
            $order = new DonHang();
            $order->MAKHACHHANG   = $maKhachHang;
            $order->MADIACHI      = $maDiaChi;
            $order->NGAYDAT       = now();
            $order->MATT          = $validated['MATT'];
            $order->GHICHU        = $validated['GHICHU'] ?? null;
            $order->TONGSLHANG    = $totalQty;
            $order->TONGTHANHTIEN = $totals['total'];
            $order->MAKHUYENMAI   = $voucher['code'] ?? null;

            $methodsCfg = config('payment_methods.methods', []);
            $type = $methodsCfg[$order->MATT]['type'] ?? 'offline';
            $order->TRANGTHAI = ($type === 'online') ? 'Chờ thanh toán' : 'Chờ xử lý';
            $order->save();

            foreach ($items as $id => $item) {
                $detail = new ChiTietDonHang();
                $detail->MADONHANG = $order->MADONHANG;
                $detail->MASANPHAM = $id;
                $detail->SOLUONG   = (int)$item['SOLUONG'];
                $detail->DONGIA    = (int)$item['GIABAN']; // đơn giá gốc (chưa trừ voucher)
                $detail->save();
            }

            // Xoá giỏ & voucher
            session()->forget(['cart', 'voucher']);
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
            ->where('d.MADONHANG', $order->MADONHANG)
            ->get();

        // Tính lại tạm tính & giảm giá từ dữ liệu đã chốt
        $subtotal = (int) $details->sum(fn($d) => (int)$d->SOLUONG * (int)$d->DONGIA);
        $discount = max(0, $subtotal - (int)$order->TONGTHANHTIEN);

        // Thông tin voucher (nếu có)
        $voucher = null;
        if (!empty($order->MAKHUYENMAI)) {
            $voucher = KhuyenMai::find($order->MAKHUYENMAI); // chỉ để hiển thị tên/loại
        }

        $address  = DiaChiGiaoHang::find($order->MADIACHI);
        $payment  = HinhThucTT::find($order->MATT);
        $customer = DB::table('KHACHHANG')->where('MAKHACHHANG', $order->MAKHACHHANG)->first();

        return view('pages.order_confirm', [
            'order'     => $order,
            'details'   => $details,
            'address'   => $address,
            'payment'   => $payment,
            'customer'  => $customer,
            'subtotal'  => $subtotal,
            'discount'  => $discount,
            'voucher'   => $voucher, // có thể null
        ]);
    }
}
