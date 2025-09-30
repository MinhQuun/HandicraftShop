@extends('layouts.main')

@section('title', 'Xác Nhận Đơn Hàng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}"> <!-- reuse -->
@endpush

@section('content')
<main class="page-checkout">
    <section class="checkout-shell">
        <h2 class="checkout-title">XÁC NHẬN ĐƠN HÀNG</h2>

        <div class="alert ok">
            <strong>Thành công!</strong>&nbsp;Đơn hàng của bạn đã được đặt. Mã đơn: {{ $order->MADONHANG }}
        </div>

        <div class="row g-4">
            {{-- Chi tiết đơn --}}
            <div class="col-lg-6">
                <div class="checkout-summary card">
                    <h4 class="card-title">Chi tiết đơn hàng</h4>
                    <div class="table-responsive">
                        <table class="checkout-table">
                            <thead>
                                <tr>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Số Lượng</th>
                                    <th>Đơn Giá</th>
                                    <th>Thành Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $d)
                                    @php $sub = ((int)$d->SOLUONG) * ((int)$d->DONGIA); @endphp
                                    <tr>
                                        <td>{{ $d->TENSANPHAM }}</td>
                                        <td class="text-center">{{ $d->SOLUONG }}</td>
                                        <td class="text-end">{{ number_format($d->DONGIA, 0, ',', '.') }} VNĐ</td>
                                        <td class="text-end">{{ number_format($sub, 0, ',', '.') }} VNĐ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="checkout-totals">
                        <h5>Tổng Thành Tiền: {{ number_format($order->TONGTHANHTIEN, 0, ',', '.') }} VNĐ</h5>
                    </div>
                </div>
            </div>

            {{-- Thông tin giao hàng/ thanh toán --}}
            <div class="col-lg-6">
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
    </section>
</main>
@endsection