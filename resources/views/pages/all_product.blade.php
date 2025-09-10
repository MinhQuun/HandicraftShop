@extends('layouts.main')

@section('title', 'All Products')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
  {{-- CSRF (nếu layout đã có thì thẻ này cũng không gây lỗi) --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <main class="container product-section" id="Product" style="padding-top:16px;">
    <h2 class="section-title">Danh Sách Sản Phẩm</h2>

    <div class="row">
      @forelse ($sp as $item)
        @php
          // Chuẩn hóa dữ liệu (schema cũ IN HOA vẫn OK)
          $id     = $item->MASANPHAM ?? $item->id ?? '';
          $name   = $item->TENSANPHAM ?? $item->name ?? '';
          $img    = trim((string)($item->HINHANH ?? $item->image ?? ''));
          $price  = $item->GIABAN ?? $item->price ?? 0;
          $stock  = $item->SOLUONGTON ?? $item->stock ?? null;

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

              <button
                type="button"
                class="btn btn-success"
                onclick="addToCart(this, '{{ $id }}')"
                @if(!is_null($stock) && (int)$stock <= 0) disabled @endif
              >
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
        {{-- Trước --}}
        @if ($sp->currentPage() > 1)
          <li class="page-item">
            <a class="page-link" href="{{ $sp->url($sp->currentPage() - 1) }}">Trước</a>
          </li>
        @endif

        {{-- Số trang --}}
        @for ($i = 1; $i <= $sp->lastPage(); $i++)
          <li class="page-item {{ $i === $sp->currentPage() ? 'active' : '' }}">
            <a class="page-link" href="{{ $sp->url($i) }}">{{ $i }}</a>
          </li>
        @endfor

        {{-- Sau --}}
        @if ($sp->currentPage() < $sp->lastPage())
          <li class="page-item">
            <a class="page-link" href="{{ $sp->url($sp->currentPage() + 1) }}">Sau</a>
          </li>
        @endif
      </ul>
    </nav>
  </main>
@endsection

@push('scripts')
<script>
function getCsrf() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

// Thêm vào giỏ từ danh sách: mặc định qty = 1
async function addToCart(btn, productId) {
  const original = btn.innerHTML;

  try {
    btn.disabled = true;
    btn.innerHTML = 'Đang thêm...';

    const res = await fetch(`{{ route('cart.add') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ product_id: productId, qty: 1 })
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Yêu cầu thất bại');

    alert(data.message || 'Đã thêm vào giỏ!');

    // Nếu backend trả số lượng giỏ hiện tại -> cập nhật badge (nếu có)
    if (typeof data.cart_count !== 'undefined') {
      const badge = document.getElementById('cart-count');
      if (badge) badge.textContent = data.cart_count;
    }
  } catch (err) {
    console.error(err);
    alert(err.message || 'Thêm vào giỏ thất bại. Vui lòng thử lại.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = original;
  }
}
</script>
@endpush
