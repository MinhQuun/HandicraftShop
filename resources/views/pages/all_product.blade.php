@extends('layouts.main')

@section('title', 'All Products')

@push('styles')

  <link rel="stylesheet" href="{{ asset('css/index.css') }}">

@endpush

@section('content')
<main class="container product-section" id="Product" style="padding-top:16px;">
  <h2 class="section-title">Danh Sách Sản Phẩm</h2>

  <div class="row">
    @forelse ($sp as $item)
      @php
        // Lấy dữ liệu theo schema cũ (chữ in hoa)
        $id    = $item->MASANPHAM ?? $item->id ?? '';
        $name  = $item->TENSANPHAM ?? $item->name ?? '';
        $img   = trim((string)($item->HINHANH ?? $item->image ?? ''));
        $price = $item->GIABAN ?? $item->price ?? 0;

        $imgUrl = $img !== ''
          ? asset('assets/images/' . urlencode($img))
          : asset('HinhAnh/LOGO/Logo.jpg'); // fallback
      @endphp

      <div class="col-md-3 col-sm-6 text-center mt-4 mb-4">
        <div class="product-card">
          <img src="{{ $imgUrl }}" class="product-image" alt="{{ $name }}">

          <h5 class="product-title">{{ $name }}</h5>
          <p class="product-price">{{ number_format((float)$price, 0, ',', '.') }} VNĐ</p>

          <div class="button-group">
            <a href="{{ route('sp.detail', $id) }}" class="btn btn-outline-primary">Chi Tiết</a>
            <button type="button" class="btn btn-success" onclick="addToCart('{{ $id }}')">
              Chọn Mua
            </button>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <p class="text-center text-muted">Chưa có sản phẩm.</p>
      </div>
    @endforelse
  </div>


  {{-- Phân trang custom --}}
<nav aria-label="Page navigation" class="mt-4">
  <ul class="pagination justify-content-center">
    {{-- Nút Trước --}}
    @if ($sp->currentPage() > 1)
      <li class="page-item">
        <a class="page-link" href="{{ $sp->url($sp->currentPage() - 1) }}">Trước</a>
      </li>
    @endif

    {{-- Các số trang --}}
    @for ($i = 1; $i <= $sp->lastPage(); $i++)
      <li class="page-item {{ $i === $sp->currentPage() ? 'active' : '' }}">
        <a class="page-link" href="{{ $sp->url($i) }}">{{ $i }}</a>
      </li>
    @endfor

    {{-- Nút Sau --}}
    @if ($sp->currentPage() < $sp->lastPage())
      <li class="page-item">
        <a class="page-link" href="{{ $sp->url($sp->currentPage() + 1) }}">Sau</a>
      </li>
    @endif
  </ul>
</nav>

</main>

{{-- CSRF cho fetch POST --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
<script>
async function addToCart(productId) {
  try {
    const res = await fetch(`{{ route('cart.add') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ product_id: productId, qty: 1 })
    });

    if (!res.ok) throw new Error('Request failed');
    const data = await res.json();
    alert(data.message || 'Đã thêm vào giỏ!');
    // TODO: cập nhật badge .cart-count nếu có
  } catch (e) {
    console.error(e);
    alert('Thêm vào giỏ thất bại. Vui lòng thử lại.');
  }
}
</script>
@endpush
