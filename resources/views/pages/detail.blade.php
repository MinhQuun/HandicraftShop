@extends('layouts.main')

@section('title', 'Chi Tiết Sản Phẩm | Handicraft Shop')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endpush

@section('content')
<main>
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
        <p class="product-description">
          <strong>Mô tả:</strong> {!! nl2br(e($p->MOTA)) !!}
        </p>
        <p class="product-description">
          <strong>Số lượng:</strong> {{ $p->SOLUONGTON }} sản phẩm có sẵn
        </p>

        <!-- Input cho số lượng và nút thêm vào giỏ -->
        <div class="form-inline mt-3">
          <input type="number" id="quantity"
                 placeholder="Số lượng"
                 value="1"
                 min="1"
                 max="{{ $p->SOLUONGTON }}"
                 class="form-control quantity-input" />
          <button class="btn btn-outline-success add-to-cart-btn"
                  onclick="addToCart('{{ $p->MASANPHAM }}')">
            Thêm vào giỏ hàng
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
  </div>
</main>
@endsection

@push('scripts')
  <script src="{{ asset('js/product_detail.js') }}"></script>
@endpush
