@extends('layouts.main')

@section('title', 'Đặt Hàng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<main class="page-checkout">
    <section class="checkout-shell">
        <div class="checkout-progress">
            <div class="progress-step complete"><i class="fas fa-shopping-cart"></i> Giỏ hàng</div>
            <div class="progress-step active"><i class="fas fa-file-invoice"></i> Thanh toán</div>
            <div class="progress-step"><i class="fas fa-check"></i> Xác nhận</div>
        </div>

        <h2 class="checkout-title">THÔNG TIN ĐẶT HÀNG</h2>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="checkout-summary card">
                    <h4 class="card-title">Tóm tắt đơn hàng</h4>
                    <div class="table-responsive">
                        <table class="checkout-table">
                            <thead>
                                <tr>
                                    <th style="min-width:220px;">Sản phẩm</th>
                                    <th style="width:90px;">SL</th>
                                    <th style="width:120px;">Giá gốc</th>
                                    <th style="width:90px;">% giảm</th>
                                    <th style="width:130px;">Giảm (đ)</th>
                                    <th style="width:130px;">Giá sau</th>
                                    <th style="width:150px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    @php
                                        $id    = $item['MASANPHAM'] ?? null;
                                        $qty   = (int)$item['SOLUONG'];
                                        $sp    = $id ? \App\Models\SanPham::where('MASANPHAM',$id)->first() : null;
                                        $orig  = $sp ? (float)($sp->GIABAN ?? ($item['GIABAN'] ?? 0)) : (float)($item['GIABAN'] ?? 0);
                                        $sale  = $sp ? (float)($sp->gia_sau_km ?? $orig) : (float)($item['GIABAN'] ?? 0);
                                        $save  = max(0, $orig - $sale);
                                        $pct   = $orig > 0 && $sale < $orig ? round(100 * ($orig - $sale) / $orig) : 0;
                                        $sub   = $sale * $qty;
                                    @endphp
                                    <tr>
                                        <td>{{ $item['TENSANPHAM'] }}</td>
                                        <td class="text-center">{{ $qty }}</td>
                                        <td class="text-end">{{ number_format($orig, 0, ',', '.') }}</td>
                                        <td class="text-center">{{ $pct }}%</td>
                                        <td class="text-end">{{ number_format($save, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($sale, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($sub, 0, ',', '.') }} VNĐ</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">Giỏ hàng trống.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="promo-section">
                        <label class="form-label">Mã khuyến mãi</label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="promo_code"
                                class="form-control"
                                placeholder="Nhập mã..."
                                value="{{ old('promo_code', $voucher['code'] ?? '') }}">
                            <button
                                id="apply_promo"
                                class="btn-gradient"
                                data-url="{{ route('checkout.applyPromo') }}">
                                <i class="fas fa-tag"></i> Áp dụng
                            </button>
                        </div>

                        {{-- Đang áp dụng --}}
                        <div id="voucher_applied" style="margin-top:8px;{{ empty($voucher) ? 'display:none;' : '' }}">
                            <span class="badge bg-success" style="padding:6px 10px;border-radius:999px;">
                                Đang dùng mã: <strong id="voucher_code">{{ $voucher['code'] ?? '' }}</strong>
                            </span>
                            <small class="text-muted ms-2" id="voucher_desc">
                                @if(!empty($voucher))
                                    ({{ $voucher['type']==='percent' ? $voucher['value'].'%' : number_format($voucher['value'],0,',','.') . 'đ' }}
                                    @if(($voucher['min_total'] ?? 0) > 0) – tối thiểu {{ number_format($voucher['min_total'],0,',','.') }}đ @endif
                                    @if(($voucher['max_discount'] ?? 0) > 0) – tối đa {{ number_format($voucher['max_discount'],0,',','.') }}đ @endif
                                    )
                                @endif
                            </small>
                        </div>

                        <small id="promo_message" class="text-muted d-block" style="margin-top:6px;"></small>
                    </div>

                    @php
                        $productSaveSum = 0;
                        foreach ($items as $it) {
                            $iid  = $it['MASANPHAM'] ?? null;
                            $qty  = (int)($it['SOLUONG'] ?? 0);
                            $sp   = $iid ? \App\Models\SanPham::where('MASANPHAM',$iid)->first() : null;
                            $orig = $sp ? (float)($sp->GIABAN ?? ($it['GIABAN'] ?? 0)) : (float)($it['GIABAN'] ?? 0);
                            $sale = $sp ? (float)($sp->gia_sau_km ?? $orig) : (float)($it['GIABAN'] ?? 0);
                            $productSaveSum += max(0, $orig - $sale) * $qty;
                        }
                        $voucherApplied = !empty($voucher ?? null);
                        $voucherSave = (int) ($discount ?? 0);
                    @endphp

                    <div class="card mt-3">
                        <h5 class="card-title">Thanh toán</h5>
                        <ul class="totals-list">
                            <li>
                                <span>Tổng số lượng</span>
                                <span>{{ $totalQty }}</span>
                            </li>
                            <li>
                                <span>Tạm tính</span>
                                <span id="subtotal_value">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</span>
                            </li>
                            @if($productSaveSum > 0)
                            <li class="discount-line">
                                <span>Giảm theo sản phẩm</span>
                                <span>-{{ number_format($productSaveSum, 0, ',', '.') }} VNĐ</span>
                            </li>
                            @endif
                            <li
                                class="discount-line {{ $voucherApplied ? '' : 'd-none' }}"
                                id="discount_row">
                                <span>Giảm theo mã
                                    <span class="voucher-badge" title="Mã đang áp dụng">
                                        <i class="fas fa-tag"></i>
                                        <span id="discount_code_text">{{ $voucher['code'] ?? '' }}</span>
                                    </span>
                                </span>
                                <span id="discount_value">- {{ number_format($voucherSave, 0, ',', '.') }} VNĐ</span>
                            </li>
                            <li class="grand">
                                <span>Tổng thành tiền</span>
                                <span id="total_value">{{ number_format($totalPrice, 0, ',', '.') }} VNĐ</span>
                            </li>
                        </ul>
                    </div>

                    <div class="trust-signals">
                        <i class="fas fa-lock"></i> Thanh toán an toàn & bảo mật
                        <i class="fas fa-shield-alt"></i> Hoàn tiền 100% nếu không hài lòng
                    </div>
                </div>
                
                <!-- QR CODE THANH TOÁN (Ẩn mặc định, hiện khi chọn phương thức) -->
                <div class="checkout-qr card mt-3" id="payQRCode" style="display:none;">
                    <h5 class="card-title">Quét QR để thanh toán</h5>
                    <div class="text-center">
                        <img id="payQRImage" src="" alt="QR Code" style="max-width:220px;">
                    </div>
                </div>                
            </div>

            <div class="col-lg-6">
                <div class="checkout-form card summary-sticky">
                    <h4 class="card-title">Thông tin giao hàng</h4>

                    @if(!empty($customer))
                        <div class="mb-3 customer-info">
                            <div class="form-label">Khách hàng</div>
                            <div><strong>Họ tên:</strong> {{ $customer->HOTEN ?? '—' }}</div>
                            <div><strong>SĐT:</strong> {{ $customer->SODIENTHOAI ?? '—' }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('orders.store') }}" id="checkoutForm">
                        @csrf
                        <input type="hidden" name="address_id" value="{{ $currentAddress->MADIACHI ?? '' }}">
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ giao hàng</label>
                            <input type="text" name="DIACHI" class="form-control" required
                                placeholder="Ví dụ: 144 Lê Trọng Tấn, Tân Phú, TP.HCM"
                                value="{{ old('DIACHI', $currentAddress->DIACHI ?? '') }}">
                            <small class="text-muted">Bạn có thể chỉnh sửa địa chỉ; hệ thống sẽ cập nhật tự động.</small>
                        </div>

                        <input type="hidden" name="MATT" id="MATT" value="">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <label class="form-label m-0">Hình thức thanh toán</label>
                            <small id="payError" class="text-danger" style="display:none;">Vui lòng chọn.</small>
                        </div>

                        <div class="pay-grid">
                            @foreach ($paymentMethods as $pm)
                                <button type="button" 
                                    class="pay-card" 
                                    data-matt="{{ $pm->MATT }}" 
                                    data-qr="{{ asset('assets/images/qrcodes/' . strtolower($pm->MATT) . '.jpg') }}">
                                    
                                    <img class="pay-logo" 
                                        src="{{ asset('assets/images/payments/' . strtolower($pm->MATT) . '.png') }}" 
                                        alt="{{ $pm->LOAITT }}">

                                    <div class="pay-card-content">
                                        <div class="pay-card-title">{{ $pm->LOAITT }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="GHICHU" class="form-control" rows="3" placeholder="Ghi chú cho đơn hàng (nếu có)"></textarea>
                        </div>

                        <div class="checkout-actions">
                            <a href="{{ route('cart') }}" class="btn-outline"><i class="fas fa-arrow-left"></i> Trở lại</a>
                            <button type="submit" class="btn-gradient"><i class="fas fa-check-circle"></i> Xác nhận</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/checkout.js') }}"></script>
@endpush
