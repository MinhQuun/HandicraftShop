@extends('layouts.main')

@section('title', 'Chi Tiết Sản Phẩm | Handicraft Shop')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endpush

@section('content')
  <main>
    {{-- CSRF cho fetch POST --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div
      class="container product-detail"
      data-stock="{{ (int) $p->SOLUONGTON }}"
      data-cart-add-url="{{ route('cart.add') }}"
      data-review-create-url="{{ route('reviews.store', $p->MASANPHAM) }}"
      data-is-logged-in="{{ Auth::check() ? '1' : '0' }}"
      data-login-url="{{ url('/login') }}"  
    >
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

          {{-- Loại & Nhà cung cấp --}}
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

          {{-- Qty + nút mua --}}
          <div class="detail-actions mt-2">
            <div class="qty-box">
              <button class="btn-qty" type="button" data-action="qty-dec" title="Giảm 1">−</button>
              <span id="qtyNumber" class="qty-number">1</span>
              <button class="btn-qty" type="button" data-action="qty-inc" title="Tăng 1">+</button>
            </div>

            <button
              type="button"
              id="btnAddToCart"
              class="btn-gradient add-to-cart-btn"
              data-action="add-to-cart"
              @if((int) $p->SOLUONGTON <= 0) disabled @endif
            >
              <i class="fas fa-cart-plus"></i>&nbsp;Chọn mua
            </button>
          </div>

          <div class="mt-4">
            <a href="javascript:void(0);" onclick="window.history.back();" class="btn-outline back-btn">
              <i class="fas fa-arrow-left"></i>&nbsp;Trở lại danh sách
            </a>
          </div>
        </div>
      </div>

      {{-- ====== ĐÁNH GIÁ SẢN PHẨM (chen giữa) ====== --}}
      <div id="reviews" class="mt-5">
        <h3 class="section-title">ĐÁNH GIÁ SẢN PHẨM</h3>

        {{-- Tổng quan rating --}}
        <div class="row g-3 align-items-center review-summary">
          <div class="col-12 col-md-3 text-center">
            <div class="review-score">{{ number_format($ratingAvg, 1) }}</div>
            <div class="review-stars">
              @for ($i=1; $i<=5; $i++)
                <i class="fas fa-star {{ $i <= round($ratingAvg) ? 'active' : '' }}"></i>
              @endfor
            </div>
            <div class="review-count text-muted">({{ $ratingCount }} đánh giá)</div>
          </div>

          <div class="col-12 col-md-6">
            @foreach([5,4,3,2,1] as $s)
              @php
                $pct = $ratingCount ? round(($breakdown[$s] ?? 0) * 100 / $ratingCount) : 0;
              @endphp
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="star-label">{{$s}} ★</span>
                <div class="progress flex-grow-1" style="height:8px;">
                  <div class="progress-bar" role="progressbar" style="width: {{$pct}}%"></div>
                </div>
                <span class="star-pct">{{$pct}}%</span>
              </div>
            @endforeach
          </div>

          <div class="col-12 col-md-3 text-center">
            @if (Auth::check())
              <button class="btn-gradient" data-bs-toggle="collapse" data-bs-target="#reviewForm">
                <i class="fas fa-pen"></i> Viết đánh giá
              </button>
            @else
              <a
                class="btn-outline"
                href="{{ route('login', ['redirect' => request()->fullUrl().'#reviews']) }}"
                data-action="open-login">
                  <i class="fas fa-sign-in-alt"></i> Đăng nhập để đánh giá
              </a>

            @endif
          </div>
        </div>

        {{-- Form đánh giá --}}
        @if (Auth::check())
        <div id="reviewForm" class="collapse mt-3">
          <form id="create-review-form" class="review-form" data-masp="{{ $p->MASANPHAM }}">
            <div class="rating-input">
              <label>Chấm điểm:</label>
              <div class="stars-input" data-value="5" id="starsInput">
                @for ($i=1; $i<=5; $i++)
                  <i class="far fa-star" data-star="{{$i}}"></i>
                @endfor
              </div>
              <input type="hidden" name="DIEMSO" id="score" value="5" />
            </div>

            <div class="mt-2">
              <label class="form-label">Nhận xét</label>
              <textarea class="form-control" name="NHANXET" id="comment" rows="3" maxlength="1000"
                placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
            </div>

            <div class="mt-3 d-flex gap-2">
              <button type="button" class="btn-gradient" data-action="submit-review">
                Gửi đánh giá
              </button>
              <button type="button" class="btn-outline" data-bs-toggle="collapse" data-bs-target="#reviewForm">
                Hủy
              </button>
            </div>
          </form>
        </div>
        @endif

        {{-- Danh sách đánh giá --}}
        <div class="review-list mt-4">
          @forelse ($reviews as $rv)
            <div class="review-item">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <div class="reviewer-name">
                    {{ $rv->khachHang?->HOTEN ?? 'Người dùng' }}
                  </div>
                  <div class="review-stars small">
                    @for ($i=1; $i<=5; $i++)
                      <i class="fas fa-star {{ $i <= (int)$rv->DIEMSO ? 'active' : '' }}"></i>
                    @endfor
                    <span class="text-muted ms-2">{{ \Carbon\Carbon::parse($rv->NGAYDANHGIA)->format('d/m/Y H:i') }}</span>
                  </div>
                </div>
              </div>
              @if(!empty($rv->NHANXET))
                <div class="review-content mt-2">
                  {!! nl2br(e($rv->NHANXET)) !!}
                </div>
              @endif
            </div>
          @empty
            <p class="text-muted">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
          @endforelse

          <div class="mt-3">
            {{ $reviews->fragment('reviews')->links() }}
          </div>
        </div>
      </div>
      {{-- ====== END: ĐÁNH GIÁ SẢN PHẨM ====== --}}

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
  {{-- Đổi sang file JS mới đã tách --}}
  <script src="{{ asset('js/add_product_detail.js') }}"></script>
@endpush
