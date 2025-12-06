@extends('layouts.main')

@section('title', 'Sản phẩm khuyến mãi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
@endpush

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $cartItemIds = array_map('strval', array_keys(session('cart', [])));
    @endphp

    <main class="container product-section" id="Product" style="padding-top:16px;">
        <h2 class="section-title">Danh Sách Sản Phẩm Khuyến Mãi</h2>

        <div class="filter-card container mb-3">
            <form id="priceFilterForm" method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="col-6 col-md-3">
                    <label class="form-label">Giá thấp nhất (VNĐ)</label>
                    <input type="number" name="min_price" min="0" step="1000" class="form-control" value="{{ request('min_price') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Giá cao nhất (VNĐ)</label>
                    <input type="number" name="max_price" min="0" step="1000" class="form-control" value="{{ request('max_price') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Sắp xếp</label>
                    <select name="sort" class="form-select">
                        <option value="newest" {{ request('sort','newest')==='newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="price_asc" {{ request('sort')==='price_asc' ? 'selected' : '' }}>Giá tăng</option>
                        <option value="price_desc" {{ request('sort')==='price_desc' ? 'selected' : '' }}>Giá giảm</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-fill">Lọc</button>
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary flex-fill">Xóa lại</a>
                </div>
            </form>
        </div>

        <div class="row">
        @forelse ($sp as $item)
            @include('partials.product-card', ['item' => $item, 'cartItemIds' => $cartItemIds])
        @empty
            <div class="col-12">
                <p class="text-center text-muted">Chưa có sản phẩm khuyến mãi.</p>
            </div>
        @endforelse
        </div>

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
    </main>
@endsection

@push('scripts')
    <script>
        window.cartAddUrl = "{{ route('cart.add') }}";
        window.isLoggedIn = @json(Auth::check());
    </script>
    <script src="{{ asset('js/add_product.js') }}"></script>
@endpush
