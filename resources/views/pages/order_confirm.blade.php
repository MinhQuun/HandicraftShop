@extends('layouts.main')

@section('title', 'Xác Nhận Đơn Hàng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
@endpush

@section('content')
<main class="page-checkout">
    <section class="checkout-shell">
        <div class="checkout-progress">
            <div class="progress-step complete"><i class="fas fa-shopping-cart"></i> Giỏ hàng</div>
            <div class="progress-step complete"><i class="fas fa-file-invoice"></i> Thanh toán</div>
            <div class="progress-step active"><i class="fas fa-check"></i> Xác nhận</div>
        </div>

        <h2 class="checkout-title">ĐẶT HÀNG THÀNH CÔNG</h2>

        {{-- Banner thành công nhẹ nhàng, không show mã đơn --}}
        <div class="alert ok">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Cảm ơn bạn!</strong> Đơn hàng của bạn đã được ghi nhận.
                <div class="text-muted" style="font-size: 13px;">
                    Chúng tôi đã gửi thông tin chi tiết qua email / tài khoản của bạn.
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Bảng chi tiết sản phẩm --}}
            <div class="col-lg-7">
                <div class="checkout-summary card">
                    <h4 class="card-title">Chi tiết đơn hàng</h4>
                    <div class="table-responsive">
                        <table class="checkout-table">
                            <thead>
                                <tr>
                                    <th>Tên Sản Phẩm</th>
                                    <th style="width:110px;">Số Lượng</th>
                                    <th style="width:160px;">Đơn Giá</th>
                                    <th style="width:180px;">Thành Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $d)
                                    @php $sub = ((int)$d->SOLUONG) * ((int)$d->DONGIA); @endphp
                                    <tr>
                                        <td>{{ $d->TENSANPHAM }}</td>
                                        <td class="text-center">{{ (int)$d->SOLUONG }}</td>
                                        <td class="text-end">{{ number_format((int)$d->DONGIA, 0, ',', '.') }} VNĐ</td>
                                        <td class="text-end">{{ number_format($sub, 0, ',', '.') }} VNĐ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tổng tiền + Voucher + Thông tin giao hàng --}}
            <div class="col-lg-5">
                <div class="card" style="margin-bottom:16px;">
                    <h4 class="card-title">Thanh toán</h4>
                    <ul class="totals-list">
                        <li>
                            <span>Tạm tính</span>
                            <span>{{ number_format($subtotal, 0, ',', '.') }} VNĐ</span>
                        </li>
                        @if($discount > 0)
                            <li class="discount-line">
                                <span>Giảm giá 
                                    @if(!empty($order->MAKHUYENMAI))
                                        <span class="voucher-badge" title="Mã đã áp">
                                            <i class="fas fa-tag"></i> {{ $order->MAKHUYENMAI }}
                                        </span>
                                        @if($voucher)
                                            <small class="text-muted" style="margin-left:6px;">
                                                ({{ $voucher->TENKHUYENMAI }})
                                            </small>
                                        @endif
                                    @endif
                                </span>
                                <span>-{{ number_format($discount, 0, ',', '.') }} VNĐ</span>
                            </li>
                        @endif
                        <li class="grand">
                            <span>Tổng thanh toán</span>
                            <span>{{ number_format((int)$order->TONGTHANHTIEN, 0, ',', '.') }} VNĐ</span>
                        </li>
                    </ul>
                </div>

                @php
                    $voucherDisc = 0;
                    if (!empty($order->MAKHUYENMAI) && $voucher) {
                        $rules = is_array($voucher->DIEUKIEN_JSON) ? $voucher->DIEUKIEN_JSON : (json_decode($voucher->DIEUKIEN_JSON ?? '[]', true) ?: []);
                        $min   = (float)($rules['min_order_total'] ?? 0);
                        $cap   = (float)($rules['max_discount'] ?? 0);
                        if ($subtotal >= $min) {
                            if (($voucher->LOAIKHUYENMAI ?? '') === 'Giảm %') {
                                $voucherDisc = (int) round($subtotal * ((float)$voucher->GIAMGIA / 100));
                                if ($cap > 0) $voucherDisc = min($voucherDisc, (int)$cap);
                            } else {
                                $voucherDisc = (int) min((float)$voucher->GIAMGIA, $subtotal);
                            }
                        }
                    }
                    $productDisc = max(0, (int)$discount - (int)$voucherDisc);
                @endphp

                <div class="card" style="margin-bottom:16px;">
                    <h4 class="card-title">Chi tiết khuyến mãi</h4>
                    <ul class="totals-list">
                        @if($productDisc > 0)
                        <li class="discount-line">
                            <span>Giảm theo sản phẩm</span>
                            <span>-{{ number_format($productDisc, 0, ',', '.') }} VND</span>
                        </li>
                        @endif
                        @if($voucherDisc > 0)
                        <li class="discount-line">
                            <span>GiẢM THEO MÃ
                                @if(!empty($order->MAKHUYENMAI))
                                    <span class="voucher-badge" title="Mã đã áp dụng">
                                        <i class="fas fa-tag"></i> {{ $order->MAKHUYENMAI }}
                                    </span>
                                @endif
                            </span>
                            <span>-{{ number_format($voucherDisc, 0, ',', '.') }} VND</span>
                        </li>
                        @endif
                        @if($productDisc <= 0 && $voucherDisc <= 0)
                        <li>
                            <span class="text-muted">Không có khuyến mãi áp dụng.</span>
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="checkout-form card">
                    <h4 class="card-title">Thông tin giao hàng</h4>
                    <p><strong>Khách hàng:</strong> {{ $customer->HOTEN ?? '—' }}</p>
                    <p><strong>Số điện thoại:</strong> {{ $customer->SODIENTHOAI ?? '—' }}</p>
                    <p><strong>Địa chỉ:</strong> {{ $address->DIACHI ?? '—' }}</p>
                    <p><strong>Hình thức thanh toán:</strong> {{ $payment->LOAITT ?? '—' }}</p>
                    <p><strong>Ghi chú:</strong> {{ $order->GHICHU ?? 'Không có' }}</p>
                    <p><strong>Trạng thái:</strong> {{ $order->TRANGTHAI }}</p>
                    <p><strong>Ngày đặt:</strong> {{ \Carbon\Carbon::parse($order->NGAYDAT)->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="checkout-actions justify-content-center">
            <a href="{{ route('home') }}" class="btn-gradient">
                <i class="fas fa-home"></i>&nbsp;Về Trang Chủ
            </a>
        </div>

        {{-- Thông tin nội bộ (ẩn khỏi mắt khách) nếu bạn muốn giữ mã đơn để CSKH tra cứu nhanh:
        <div style="display:none">OrderRef: #{{ $order->MADONHANG }}</div>
        --}}
    </section>
</main>
@endsection