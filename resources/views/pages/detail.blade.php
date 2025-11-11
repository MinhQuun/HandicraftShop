@extends('layouts.main')

@section('title', 'Chi Tiết Sản Phẩm | Handicraft Shop')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
    <main>
        {{-- CSRF cho fetch POST --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $cartItemIds = array_map('strval', array_keys(session('cart', [])));
            $isDetailInCart = in_array((string) ($p->MASANPHAM ?? ''), $cartItemIds, true);
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

            @php
                $orig = (float)($p->GIABAN ?? 0);
                $sale = (float)($p->gia_sau_km ?? $orig);
                $hasSale = $sale < $orig;
            @endphp
            @if($hasSale)
                <p class="product-price">
                <span style="text-decoration: line-through; color:#a17a44; font-weight:600; margin-right:8px;">
                    {{ number_format($orig, 0, ',', '.') }} VNĐ
                </span>
                <span style="color:#6a8f55; font-weight:800;">
                    {{ number_format($sale, 0, ',', '.') }} VNĐ
                </span>
                </p>
            @else
                <p class="product-price">{{ number_format($orig, 0, ',', '.') }} VNĐ</p>
            @endif

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
        @if (!empty($related) && count($related))
            <div class="row mt-5">
                <div class="col-md-12">
                    <h3 class="section-title">SẢN PHẨM LIÊN QUAN</h3>
                    <div class="row g-4 mt-2">
                        @foreach ($related as $item)
                            @php
                                $relatedId       = $item->MASANPHAM ?? $item->id ?? '';
                                $relatedName     = $item->TENSANPHAM ?? $item->name ?? '';
                                $img             = trim((string)($item->HINHANH ?? $item->image ?? ''));
                                $origR           = (float) ($item->GIABAN ?? 0);
                                $saleR           = (float) ($item->gia_sau_km ?? $origR);
                                $hasSale         = $saleR < $origR;
                                $stockRelated    = $item->SOLUONGTON ?? null;
                                $stockCount      = is_null($stockRelated) ? null : (int) $stockRelated;
                                $formattedOrigR  = number_format($origR, 0, ',', '.');
                                $formattedSaleR  = number_format($saleR, 0, ',', '.');
                                $imgUrl          = $img !== '' ? asset('assets/images/' . urlencode($img)) : asset('HinhAnh/LOGO/Logo.jpg');
                                $isInCartRelated = in_array((string) $relatedId, $cartItemIds, true);

                                if ($stockCount === null) {
                                    $availabilityLabel = 'Liên hệ để biết hàng';
                                    $availabilityClass = 'is-muted';
                                } elseif ($stockCount <= 0) {
                                    $availabilityLabel = 'Hết hàng';
                                    $availabilityClass = 'is-out';
                                } elseif ($stockCount <= 5) {
                                    $availabilityLabel = 'Còn ' . $stockCount . ' sản phẩm';
                                    $availabilityClass = 'is-low';
                                } else {
                                    $availabilityLabel = 'Sẵn sàng';
                                    $availabilityClass = 'is-available';
                                }
                            @endphp

                            <div class="col-lg-3 col-md-4 col-sm-6 d-flex">
                                <article class="product-card product-card--compact">
                                    @if ($hasSale)
                                        <div class="sale-ribbon">-{{ max(0, min(100, round(100 * ($origR - $saleR) / max($origR, 1)))) }}%</div>
                                    @endif
                                    <a href="{{ route('sp.detail', $relatedId) }}" class="product-card__link" aria-label="Xem chi tiết {{ $relatedName }}">
                                        <div class="product-card__media">
                                            <img src="{{ $imgUrl }}" class="product-card__image" alt="{{ $relatedName }}">
                                        </div>
                                        <div class="product-card__info">
                                            <h3 class="product-card__title">{{ $relatedName }}</h3>
                                            <div class="product-card__price-group">
                                                @if ($hasSale)
                                                    <span class="product-card__price-old">{{ $formattedOrigR }} VNĐ</span>
                                                    <span class="product-card__price-new">{{ $formattedSaleR }} VNĐ</span>
                                                @else
                                                    <span class="product-card__price-new">{{ $formattedOrigR }} VNĐ</span>
                                                @endif
                                            </div>
                                            <p class="product-card__availability {{ $availabilityClass }}">{{ $availabilityLabel }}</p>
                                        </div>
                                    </a>
                                    <div class="product-card__footer">
                                        <button
                                            type="button"
                                            class="btn product-card__add-btn {{ $isInCartRelated ? 'is-added' : '' }}"
                                            data-product-id="{{ $relatedId }}"
                                            data-default-text="Chọn mua"
                                            data-added-text="Đã trong giỏ hàng"
                                            data-in-cart="{{ $isInCartRelated ? '1' : '0' }}"
                                            onclick="addToCart(this, '{{ $relatedId }}')"
                                            @if (!is_null($stockCount) && $stockCount <= 0) disabled @endif
                                        >
                                            {{ $isInCartRelated ? 'Đã trong giỏ hàng' : 'Chọn mua' }}
                                        </button>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
                        </p>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/add_product_detail.js') }}"></script>
@endpush
