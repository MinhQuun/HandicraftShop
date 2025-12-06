@php
    $compact      = $compact ?? false;
    $item         = $item ?? null;
    $id           = (string) ($item->MASANPHAM ?? $item->id ?? '');
    $name         = $item->TENSANPHAM ?? $item->name ?? '';
    $img          = trim((string) ($item->HINHANH ?? $item->image ?? ''));
    $imgUrl       = $item?->image_url ?? ($img !== '' ? asset('assets/images/' . urlencode($img)) : asset('HinhAnh/LOGO/Logo.jpg'));
    $isInCart     = in_array($id, $cartItemIds ?? [], true);
    $orig         = (float) ($item->GIABAN ?? $item->price ?? 0);
    $sale         = (float) ($item->gia_sau_km ?? $orig);
    $hasSale      = $sale < ($orig - 0.001);
    $percent      = (int) round($item->discount_percent ?? ($orig > 0 ? (100 * max(0, $orig - $sale) / $orig) : 0));
    $saving       = max(0, $orig - $sale);
    $stock        = $item->SOLUONGTON ?? $item->stock ?? null;
    $stockCount   = is_null($stock) ? null : (int) $stock;

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

<div class="col-lg-3 col-md-4 col-sm-6 d-flex align-items-stretch mt-4 mb-4">
    <article class="product-card {{ $compact ? 'product-card--compact' : '' }}">
        @if($hasSale)
            <div class="sale-ribbon">
                <span class="sale-ribbon__percent">-{{ $percent }}%</span>
                <span class="sale-ribbon__label">Ưu đãi</span>
            </div>
        @endif
        <a href="{{ route('sp.detail', $id) }}" class="product-card__link" aria-label="Xem chi tiết {{ $name }}">
            <div class="product-card__media">
                <img src="{{ $imgUrl }}" class="product-card__image" alt="{{ $name }}">
                @if($hasSale && !$compact)
                    <div class="price-chip">Tiết kiệm {{ number_format($saving, 0, ',', '.') }}đ</div>
                @endif
            </div>
            <div class="product-card__info">
                <h3 class="product-card__title">{{ $name }}</h3>
                @if ($hasSale)
                    <div class="price-block">
                        <div class="price-line">
                            <span class="price-old">{{ number_format($orig, 0, ',', '.') }}</span>
                            <span class="price-currency">VNĐ</span>
                        </div>
                        <div class="price-line price-line--new">
                            <span class="price-main">{{ number_format($sale, 0, ',', '.') }}</span>
                            <span class="price-currency price-currency--new">VNĐ</span>
                        </div>
                    </div>
                @else
                    <div class="price-block">
                        <div class="price-line price-line--new">
                            <span class="price-main">{{ number_format($orig, 0, ',', '.') }}</span>
                            <span class="price-currency price-currency--new">VNĐ</span>
                        </div>
                    </div>
                @endif
                <p class="product-card__availability {{ $availabilityClass }}">{{ $availabilityLabel }}</p>
            </div>
        </a>
        <div class="product-card__footer">
            <button
                type="button"
                class="btn product-card__add-btn {{ $isInCart ? 'is-added' : '' }}"
                data-product-id="{{ $id }}"
                data-default-text="Chọn mua"
                data-added-text="Đã trong giỏ hàng"
                data-in-cart="{{ $isInCart ? '1' : '0' }}"
                onclick="addToCart(this, '{{ $id }}')"
                @if (!is_null($stockCount) && $stockCount <= 0) disabled @endif
            >
                {{ $isInCart ? 'Đã trong giỏ hàng' : 'Chọn mua' }}
            </button>
        </div>
    </article>
</div>
