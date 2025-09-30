@extends('layouts.main')

@section('title', 'Xem Giỏ Hàng')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endpush

@section('content')
<main class="page-cart">
  <section class="cart-shell">
    <!-- Progress Bar -->
    <div class="checkout-progress">
      <div class="progress-step active"><i class="fas fa-shopping-cart"></i> Giỏ hàng</div>
      <div class="progress-step"><i class="fas fa-file-invoice"></i> Thanh toán</div>
      <div class="progress-step"><i class="fas fa-check"></i> Xác nhận</div>
    </div>

    <h2 class="cart-title">THÔNG TIN GIỎ HÀNG</h2>

    {{-- Thông báo --}}
    @if (session('message'))
      <div class="alert warn">
        <strong>Thông báo:</strong>&nbsp;{{ session('message') }}
      </div>
    @endif

    {{-- Bảng giỏ hàng --}}
    <table class="cart-table">
      <thead class="cart-thead">
        <tr>
          <th>Mã Sản Phẩm</th>
          <th>Tên Sản Phẩm</th>
          <th class="col-hide-md">Ảnh</th>
          <th>Số Lượng</th>
          <th class="col-hide-md">Đơn Giá</th>
          <th>Thành Tiền</th>
          <th>Hành Động</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $item)
          @php
            $img = trim((string)($item['HINHANH'] ?? ''));
            $imgUrl = $img !== '' ? asset('assets/images/' . $img)
                                  : asset('HinhAnh/LOGO/Logo.jpg');
            $price = (float)$item['GIABAN'];
            $qty   = (int)$item['SOLUONG'];
            $sub   = $price * $qty;
          @endphp
          <tr class="cart-row">
            <td class="fw-bold">{{ $item['MASANPHAM'] }}</td>
            <td>{{ $item['TENSANPHAM'] }}</td>

            <td class="col-hide-md">
              <img src="{{ $imgUrl }}" alt="{{ $item['TENSANPHAM'] }}" class="product-image">
            </td>

            <td>
              <div class="qty-box">
                <form method="POST" action="{{ route('cart.decrease', $item['MASANPHAM']) }}">
                  @csrf
                  <button type="submit" class="btn-qty" title="Giảm 1">−</button>
                </form>

                <span class="qty-number">{{ $qty }}</span>

                <form method="POST" action="{{ route('cart.increase', $item['MASANPHAM']) }}">
                  @csrf
                  <button type="submit" class="btn-qty" title="Tăng 1">+</button>
                </form>
              </div>
            </td>

            <td class="col-hide-md">
              <span class="price">{{ number_format($price, 0, ',', '.') }}</span>
              <span class="currency">VNĐ</span>
            </td>

            <td>
              <span class="subtotal">{{ number_format($sub, 0, ',', '.') }}</span>
              <span class="currency">VNĐ</span>
            </td>

            <td>
              <div class="row-actions">
                <a href="{{ route('sp.detail', $item['MASANPHAM']) }}" class="btn-soft">
                  <i class="fas fa-info-circle"></i>&nbsp;Chi Tiết
                </a>
                <form method="POST" action="{{ route('cart.remove', $item['MASANPHAM']) }}">
                  @csrf
                  <button type="submit" class="btn-soft btn-danger-soft">
                    <i class="fas fa-trash-alt"></i>&nbsp;Xóa
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="cart-empty">Giỏ hàng của bạn đang trống.</div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- Tổng kết --}}
    <div class="cart-totals">
      <h4 class="sum-qty">Tổng Số Lượng: {{ $totalQty }}</h4>
      <h4 class="sum-money">Tổng Thành Tiền: {{ number_format($totalPrice, 0, ',', '.') }} VNĐ</h4>
    </div>

    {{-- Nút chức năng --}}
    <div class="cart-actions">
      <a href="{{ route('all_product') }}" class="btn-outline">
        <i class="fas fa-arrow-left"></i>&nbsp;Trở lại danh sách
      </a>
      <a href="{{ route('checkout') }}" class="btn-gradient">
        <i class="fas fa-check-circle"></i>&nbsp;Đặt Hàng
      </a>
    </div>
  </section>
</main>
@endsection