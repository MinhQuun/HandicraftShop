@extends('layouts.main')

@section('title', 'Chi Tiết Sản Phẩm | Handicraft Shop')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endpush

@section('content')
<main>
  {{-- CSRF cho fetch POST --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div class="container product-detail">
    <div class="row">
      <!-- Hình ảnh sản phẩm -->
      <div class="col-md-5 product-image">
        <img src="{{ asset('assets/images/' . $p->HINHANH) }}"
             alt="{{ $p->TENSANPHAM }}"
             class="img-fluid" />
      </div>

      <!-- Thông tin sản phẩm -->
      <div class="col-md-7 product-info">
        <h2 class="product-title">{{ $p->TENSANPHAM }}</h2>
        <p class="product-price">{{ number_format($p->GIABAN, 0, ',', '.') }} VNĐ</p>

        @if(!empty($p->MOTA))
          <p class="product-description">
            <strong>Mô tả:</strong> {!! nl2br(e($p->MOTA)) !!}
          </p>
        @endif

        <p class="product-description">
          <strong>Số lượng còn:</strong> {{ (int) $p->SOLUONGTON }}
        </p>

        <!-- Số lượng + nút thêm -->
        <div class="form-inline mt-3 d-flex gap-2 align-items-center">
          <input
            type="number"
            id="quantity"
            class="form-control quantity-input"
            placeholder="Số lượng"
            value="1"
            min="1"
            max="{{ (int) $p->SOLUONGTON }}"
            style="max-width:120px"
          />

          <button
            type="button"
            id="btnAddToCart"
            class="btn btn-outline-success add-to-cart-btn"
            onclick="addToCart('{{ $p->MASANPHAM }}')"
            @if((int)$p->SOLUONGTON <= 0) disabled @endif
          >
            Chọn mua
          </button>
        </div>

        <div>
          <a href="javascript:void(0);" onclick="window.history.back();"
             class="btn btn-outline-secondary back-btn mt-3">
            Trở lại danh sách
          </a>
        </div>
      </div>
    </div>

    <!-- Sản phẩm liên quan -->
    @if(!empty($related) && count($related))
    <div class="row mt-5">
      <div class="col-md-12">
        <h3 class="related-products-title">SẢN PHẨM LIÊN QUAN</h3>
        <div class="row">
          @foreach ($related as $item)
            <div class="col-md-3">
              <div class="related-product text-center">
                <img src="{{ asset('assets/images/' . $item->HINHANH) }}"
                     alt="{{ $item->TENSANPHAM }}"
                     class="img-fluid related-product-img" />
                <h4 class="related-product-title">{{ $item->TENSANPHAM }}</h4>
                <p class="related-product-price">
                  {{ number_format($item->GIABAN, 0, ',', '.') }} VNĐ
                </p>
                <a href="{{ route('sp.detail', $item->MASANPHAM) }}"
                   class="btn btn-warning view-details-btn">
                  Xem chi tiết
                </a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  </div>
</main>
@endsection

@push('scripts')
<script>
function clamp(n, min, max) {
  n = Number(n);
  if (Number.isNaN(n)) n = 1;
  return Math.max(min, Math.min(max, n));
}

async function addToCart(productId) {
  const btn = document.getElementById('btnAddToCart');
  const qtyInput = document.getElementById('quantity');
  const maxQty = Number(qtyInput.getAttribute('max')) || 9999;
  const qty = clamp(qtyInput.value, 1, maxQty);

  try {
    btn.disabled = true;
    btn.dataset._text = btn.innerHTML;
    btn.innerHTML = 'Đang thêm...';

    const res = await fetch(`{{ route('cart.add') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ product_id: productId, qty })
    });

    // backend nên trả { ok: true, message, cart_count }
    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
      const msg = (data && data.message) || 'Yêu cầu thất bại';
      throw new Error(msg);
    }

    alert(data.message || 'Đã thêm vào giỏ!');
    // Nếu có badge giỏ hàng (ví dụ <span id="cart-count">0</span>) thì cập nhật:
    if (typeof data.cart_count !== 'undefined') {
      const badge = document.getElementById('cart-count');
      if (badge) badge.textContent = data.cart_count;
    }
  } catch (err) {
    console.error(err);
    alert(err.message || 'Thêm vào giỏ thất bại. Vui lòng thử lại.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = btn.dataset._text || 'Thêm vào giỏ hàng';
  }
}
</script>
@endpush
