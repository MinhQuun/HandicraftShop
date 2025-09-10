@extends('layouts.main')

@section('title', 'Xem Giỏ Hàng')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endpush

@section('content')
<div class="container mt-5 mb-5 shadow-lg p-4 rounded cart-page">
    <h2 class="text-center mb-4 text-uppercase font-weight-bold text-title">
        Thông Tin Giỏ Hàng
    </h2>

    {{-- Thông báo --}}
    @if (session('message'))
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Thông báo:</strong> {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- Bảng Giỏ Hàng --}}
    <table class="table table-hover text-center align-middle rounded shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>Mã Sản Phẩm</th>
                <th>Tên Sản Phẩm</th>
                <th>Ảnh</th>
                <th>Số Lượng</th>
                <th>Đơn Giá</th>
                <th>Thành Tiền</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td class="fw-bold">{{ $item['MASANPHAM'] }}</td>
                    <td>{{ $item['TENSANPHAM'] }}</td>
                    <td>
                        @php
                          $img = trim((string)($item['HINHANH'] ?? ''));
                          $imgUrl = $img !== '' ? asset('assets/images/' . $img)
                                                : asset('HinhAnh/LOGO/Logo.jpg');
                        @endphp
                        <img src="{{ $imgUrl }}" class="product-image img-thumbnail shadow-sm"
                             alt="{{ $item['TENSANPHAM'] }}">
                    </td>
                    <td>
                        <div class="d-flex justify-content-center align-items-center">
                            <form method="POST" action="{{ route('cart.decrease', $item['MASANPHAM']) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm me-2 rounded-circle shadow-sm">-</button>
                            </form>

                            <span class="fw-bold" style="min-width: 30px; display: inline-block;">
                                {{ $item['SOLUONG'] }}
                            </span>

                            <form method="POST" action="{{ route('cart.increase', $item['MASANPHAM']) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm ms-2 rounded-circle shadow-sm">+</button>
                            </form>
                        </div>
                    </td>
                    <td class="text-success fw-bold">
                        {{ number_format((float)$item['GIABAN'], 0, ',', '.') }} VNĐ
                    </td>
                    <td class="text-danger fw-bold">
                        {{ number_format((float)$item['GIABAN'] * (int)$item['SOLUONG'], 0, ',', '.') }} VNĐ
                    </td>
                    <td>
                        <a href="{{ route('sp.detail', $item['MASANPHAM']) }}" class="btn btn-info btn-sm me-2 shadow-sm">
                            <i class="fas fa-info-circle"></i> Chi Tiết
                        </a>
                        <form method="POST" action="{{ route('cart.remove', $item['MASANPHAM']) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm shadow-sm">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-muted">Giỏ hàng của bạn đang trống.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tổng kết giỏ hàng --}}
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h4 class="text-danger fw-bold">Tổng Số Lượng: {{ $totalQty }}</h4>
        <h4 class="text-danger fw-bold">Tổng Thành Tiền: {{ number_format($totalPrice, 0, ',', '.') }} VNĐ</h4>
    </div>

    {{-- Nút Chức Năng --}}
    <div class="text-end mt-4">
        <a href="{{ route('all_product') }}" class="btn btn-secondary btn-lg custom-back-btn me-3 shadow-sm">
            <i class="fas fa-arrow-left"></i> Trở lại danh sách
        </a>
        <a href="{{ route('checkout') }}" class="btn btn-success btn-lg custom-confirm-btn shadow-lg">
            <i class="fas fa-check-circle"></i> Đặt Hàng
        </a>
    </div>
</div>
@endsection
