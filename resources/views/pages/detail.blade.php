@extends('layouts.main')

@section('title', 'Chi Tiết Sản Phẩm')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
    <main>
        {{-- CSRF cho fetch POST --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $cartItemIds    = array_map('strval', array_keys(session('cart', [])));
            $isDetailInCart = in_array((string) ($p->MASANPHAM ?? ''), $cartItemIds, true);
            $orig           = (float) ($p->GIABAN ?? 0);
            $sale           = (float) ($p->gia_sau_km ?? $orig);
            $hasSale        = $sale < $orig;
            $percent        = $orig > 0 ? max(0, round(100 * max(0, $orig - $sale) / $orig)) : 0;
            $promo          = $p->activePromotions->first();
        @endphp

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
            <div class="col-md-5 product-image position-relative">
                @if($hasSale)
                    <div class="sale-sticker">
                        <span class="sale-sticker__percent">-{{ $percent }}%</span>
                        <small class="sale-sticker__label">Khuyến mãi</small>
                    </div>
                @endif
                <img src="{{ asset('assets/images/' . $p->HINHANH) }}"
                        alt="{{ $p->TENSANPHAM }}"
                        class="img-fluid" />
            </div>

            <!-- Thông tin sản phẩm -->
            <div class="col-md-7 product-info">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <h2 class="product-title mb-0">{{ $p->TENSANPHAM }}</h2>

                    <div class="d-flex align-items-center gap-2">
                        @if($promo)
                            <span class="badge promo-pill">Ưu tiên {{ $promo->UUTIEN ?? 1 }}</span>
                        @endif

                        @if((int) $p->SOLUONGTON <= 0)
                        <span class="badge text-bg-secondary" style="height:fit-content">Hết hàng</span>
                        @endif
                    </div>
                </div>

                <div class="price-stack">
                    @if($hasSale)
                        <div class="product-price product-price--sale">
                            <span class="product-price__old">{{ number_format($orig, 0, ',', '.') }} VNĐ</span>
                            <span class="product-price__new">{{ number_format($sale, 0, ',', '.') }} VNĐ</span>
                            <span class="product-price__badge">-{{ $percent }}%</span>
                        </div>
                        <div class="product-price__note">
                            Tiết kiệm {{ number_format(max(0, $orig - $sale), 0, ',', '.') }}đ
                            @if($promo)
                                · Ưu đãi: {{ $promo->TENKHUYENMAI }} (đến {{ optional($promo->NGAYKETTHUC)->format('d/m/Y') }})
                            @endif
                        </div>
                    @else
                        <p class="product-price">{{ number_format($orig, 0, ',', '.') }} VNĐ</p>
                    @endif
                </div>

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
                            <span class="meta-note">– {{ $sdtNCC }}</span>
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
                    <label class="qty-label" for="quantityInput">Số lượng</label>
                    <div class="qty-input-wrap">
                        <button class="btn-qty" type="button" data-action="qty-dec" title="Giảm 1">-</button>
                        <input
                        id="quantityInput"
                        type="number"
                        inputmode="numeric"
                        pattern="\d*"
                        min="1"
                        max="{{ (int) $p->SOLUONGTON ?: 9999 }}"
                        step="1"
                        value="1"
                        class="qty-input"
                        aria-label="Chọn số lượng"
                        />
                        <button class="btn-qty" type="button" data-action="qty-inc" title="Tăng 1">+</button>
                    </div>
                    </div>

                    <button
                    type="button"
                    id="btnAddToCart"
                    class="btn-gradient add-to-cart-btn {{ $isDetailInCart ? 'is-added' : '' }}"
                    data-product-id="{{ $p->MASANPHAM }}"
                    data-default-text="Chọn mua"
                    data-added-text="Đã trong giỏ hàng"
                    data-in-cart="{{ $isDetailInCart ? '1' : '0' }}"
                    data-action="add-to-cart"
                    @if((int) $p->SOLUONGTON <= 0) disabled @endif
                    >
                    {{ $isDetailInCart ? 'Đã trong giỏ hàng' : 'Chọn mua' }}
                    </button>
                </div>

                <div class="mt-4">
                    <a href="javascript:void(0);" onclick="window.history.back();" class="btn-outline back-btn">
                    <i class="fas fa-arrow-left"></i>&nbsp;Trở lại danh sách
                    </a>
                </div>
            </div>
        </div>

        {{-- ====== ĐÁNH GIÁ SẢN PHẨM ====== --}}
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
        @if (!empty($related) && count($related))
            <div class="row mt-5 align-items-stretch">
                <div class="col-md-12">
                    <h3 class="section-title">SẢN PHẨM LIÊN QUAN</h3>
                    <div class="row g-4 mt-2 align-items-stretch">
                        @foreach ($related as $item)
                            @include('partials.product-card', [
                                'item' => $item,
                                'cartItemIds' => $cartItemIds,
                                'compact' => true
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/add_product_detail.js') }}"></script>
@endpush
