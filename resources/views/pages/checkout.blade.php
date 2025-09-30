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

        @if (session('error'))
            <div class="alert warn"><i class="fas fa-exclamation-triangle"></i> <strong>Lỗi:</strong> {{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert warn">
                <i class="fas fa-exclamation-triangle"></i> <strong>Vui lòng kiểm tra:</strong>
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="checkout-summary card">
                    <h4 class="card-title">Tóm tắt đơn hàng</h4>
                    <div class="table-responsive">
                        <table class="checkout-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá</th>
                                    <th>Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    @php
                                        $price = (float)$item['GIABAN'];
                                        $qty = (int)$item['SOLUONG'];
                                        $sub = $price * $qty;
                                    @endphp
                                    <tr>
                                        <td>{{ $item['TENSANPHAM'] }}</td>
                                        <td class="text-center">{{ $qty }}</td>
                                        <td class="text-end">{{ number_format($price, 0, ',', '.') }} VNĐ</td>
                                        <td class="text-end">{{ number_format($sub, 0, ',', '.') }} VNĐ</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">Giỏ hàng trống.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="promo-section">
                        <label class="form-label">Mã khuyến mãi</label>
                        <div class="input-group">
                            <input type="text" id="promo_code" class="form-control" placeholder="Nhập mã...">
                            <button id="apply_promo" class="btn-gradient"><i class="fas fa-tag"></i> Áp dụng</button>
                        </div>
                        <small id="promo_message" class="text-muted"></small>
                    </div>

                    <div class="checkout-totals">
                        <h5>Tổng số lượng: {{ $totalQty }}</h5>
                        @if($promo)
                            <h5 class="discount">Giảm giá: {{ $promo->GIAMGIA }}% (Mã: {{ $promo->MAKHUYENMAI }})</h5>
                        @endif
                        <h5 id="total_price">Tổng thành tiền: {{ number_format($totalPrice, 0, ',', '.') }} VNĐ</h5>
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
                <div class="checkout-form card">
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
