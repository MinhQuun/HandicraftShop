@extends('layouts.main')

@section('title', 'Kết quả tìm kiếm')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
<main class="container product-section" style="padding-top:16px;">
  <h2 class="section-title">
    Kết quả tìm kiếm cho: "{{ e($q) }}"
  </h2>

  <div class="row">
    @forelse ($sp as $item)
      @php
        $id    = $item->MASANPHAM ?? $item->id ?? '';
        $name  = $item->TENSANPHAM ?? $item->name ?? '';
        $img   = trim((string)($item->HINHANH ?? $item->image ?? ''));
        $price = $item->GIABAN ?? $item->price ?? 0;

        $imgUrl = $img !== ''
          ? asset('assets/images/' . urlencode($img))
          : asset('HinhAnh/LOGO/Logo.jpg');
      @endphp

      <div class="col-md-3 col-sm-6 text-center mt-4 mb-4">
        <div class="product-card">
          <img src="{{ $imgUrl }}" class="product-image" alt="{{ $name }}">
          <h5 class="product-title">{{ $name }}</h5>
          <p class="product-price">{{ number_format((float)$price, 0, ',', '.') }} VNĐ</p>

          <div class="button-group">
            <a href="{{ route('sp.detail', $id) }}" class="btn btn-outline-primary">Chi Tiết</a>
            {{-- Gọi addToCart như trang All Products --}}
            <button type="button" class="btn btn-success" onclick="addToCart(this, '{{ $id }}')">
              Chọn Mua
            </button>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <p>Không tìm thấy sản phẩm nào.</p>
      </div>
    @endforelse
  </div>

  {{-- Phân trang --}}
  @if ($sp->lastPage() > 1)
    <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        @if ($sp->currentPage() > 1)
          <li class="page-item">
            <a class="page-link" href="{{ $sp->url($sp->currentPage() - 1) }}">Trước</a>
          </li>
        @endif

        @for ($i = 1; $i <= $sp->lastPage(); $i++)
          <li class="page-item {{ $i === $sp->currentPage() ? 'active' : '' }}">
            <a class="page-link" href="{{ $sp->url($i) }}">{{ $i }}</a>
          </li>
        @endfor

        @if ($sp->currentPage() < $sp->lastPage())
          <li class="page-item">
            <a class="page-link" href="{{ $sp->url($sp->currentPage() + 1) }}">Sau</a>
          </li>
        @endif
      </ul>
    </nav>
  @endif
</main>

{{-- CSRF --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
<script>
  // Truyền route sang JS
  window.cartAddUrl = "{{ route('cart.add') }}";
  window.isLoggedIn = @json(Auth::check()); 
</script>
{{-- Dùng lại file JS chung --}}
<script src="{{ asset('js/add_product.js') }}"></script>
@endpush
