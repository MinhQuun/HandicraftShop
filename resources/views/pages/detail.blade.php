@extends('layouts.main')

@section('title', 'Chi Tiết Sản Phẩm | Handicraft Shop')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
  {{-- Đã có .btn-qty / .btn-gradient / .btn-outline trong detail.css nên không cần cart.css --}}
  {{-- <link rel="stylesheet" href="{{ asset('css/cart.css') }}"> --}}
@endpush

@section('content')
  <main>
    {{-- CSRF cho fetch POST --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container product-detail" data-stock="{{ (int) $p->SOLUONGTON }}">
      <div class="row">
        <!-- Hình ảnh sản phẩm -->
        <div class="col-md-5 product-image">
          <img src="{{ asset('assets/images/' . $p->HINHANH) }}"
                alt="{{ $p->TENSANPHAM }}"
                class="img-fluid" />
        </div>

        <!-- Thông tin sản phẩm -->
        <div class="col-md-7 product-info">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <h2 class="product-title mb-0">{{ $p->TENSANPHAM }}</h2>

            @if((int) $p->SOLUONGTON <= 0)
              <span class="badge text-bg-secondary" style="height:fit-content">Hết hàng</span>
            @endif
          </div>

          <p class="product-price">{{ number_format($p->GIABAN, 0, ',', '.') }} VNĐ</p>

          {{-- Chỉ hiển thị thông tin khách hàng hiểu được: Loại (tên) & Nhà cung cấp --}}
          <div class="product-meta">
            @php
              $tenLoai = optional($p->loai)->TENLOAI;
              $tenNCC  = optional($p->nhaCungCap)->TENNHACUNGCAP;
              $sdtNCC  = optional($p->nhaCungCap)->SODIENTHOAI;
            @endphp

            @if(!empty($tenLoai))
              <div class="meta-item">
                <span class="meta-label">Loại:</span>
                <span class="meta-value">{{ $tenLoai }}</span>
              </div>
            @endif

            @if(!empty($tenNCC))
              <div class="meta-item">
                <span class="meta-label">Nhà cung cấp:</span>
                <span class="meta-value">
                  {{ $tenNCC }}
                  @if(!empty($sdtNCC))
                    <span class="meta-note">— {{ $sdtNCC }}</span>
                  @endif
                </span>
              </div>
            @endif
        </div>


          @if(!empty($p->MOTA))
            <p class="product-description">
              <strong>Mô tả:</strong> {!! nl2br(e($p->MOTA)) !!}
            </p>
          @endif

          <p class="product-description">
            <strong>Số lượng còn:</strong> {{ (int) $p->SOLUONGTON }}
          </p>

          {{-- Cụm số lượng + nút mua (giống phong cách trang giỏ hàng) --}}
          <div class="detail-actions mt-2">
            <div class="qty-box">
              <button class="btn-qty" type="button" onclick="changeQty(-1)" title="Giảm 1">−</button>
              <span id="qtyNumber" class="qty-number">1</span>
              <button class="btn-qty" type="button" onclick="changeQty(1)" title="Tăng 1">+</button>
            </div>

            <button
              type="button"
              id="btnAddToCart"
              class="btn-gradient add-to-cart-btn"
              onclick="addToCart('{{ $p->MASANPHAM }}')"
              @if((int) $p->SOLUONGTON <= 0) disabled @endif
            >
              <i class="fas fa-cart-plus"></i>&nbsp;Chọn mua
            </button>
          </div>

          {{-- Nút trở lại: đặt riêng ở dưới cùng --}}
          <div class="mt-4">
            <a href="javascript:void(0);" onclick="window.history.back();" class="btn-outline back-btn">
              <i class="fas fa-arrow-left"></i>&nbsp;Trở lại danh sách
            </a>
          </div>
        </div>
      </div>

      <!-- Sản phẩm liên quan -->
      @if(!empty($related) && count($related))
        <div class="row mt-5">
          <div class="col-md-12 text-center">
            <h3 class="section-title">SẢN PHẨM LIÊN QUAN</h3>
            <div class="row">
              @foreach ($related as $item)
                <div class="col-md-3 mb-3">
                  <div class="related-product text-center h-100">
                    <img src="{{ asset('assets/images/' . $item->HINHANH) }}"
                          alt="{{ $item->TENSANPHAM }}"
                          class="img-fluid related-product-img" />
                    <h4 class="related-product-title">{{ $item->TENSANPHAM }}</h4>
                    <p class="related-product-price">
                      {{ number_format($item->GIABAN, 0, ',', '.') }} VNĐ
                    </p>
                    <a href="{{ route('sp.detail', $item->MASANPHAM) }}" class="view-details-btn">
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
  // Config cho JS
  window.cartAddUrl  = "{{ route('cart.add') }}";
  window.isLoggedIn  = @json(Auth::check());
  window.STOCK_MAX   = {{ (int) $p->SOLUONGTON }};
</script>
<script src="{{ asset('js/add_product_detail.js') }}"></script>
@endpush
