@extends('layouts.staff')
@section('title','Quản lý Sản phẩm')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-products.css') }}">
@endpush

@section('content')
    <section class="page-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Sản phẩm</h1>
        <p class="muted">Thêm, sửa, xoá và tìm kiếm sản phẩm.</p>
    </section>

    {{-- FLASH data để JS đọc và hiện toast --}}
    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    {{-- Bộ lọc / tìm kiếm --}}
    <div class="card products-filter mb-3">
        <div class="card-body">
        <form class="row g-2 align-items-center" method="get" action="{{ route('staff.products.index') }}">
            <div class="col-lg-5">
            <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control"
                    placeholder="Tìm theo tên, loại sản phẩm">
            </div>

            <div class="col-lg-3">
            @isset($categories)
                <select name="loai" class="form-select">
                <option value="">-- Tất cả loại --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->MALOAI ?? $cat->id }}"
                    {{ ($loai ?? request('loai')) == ($cat->MALOAI ?? $cat->id) ? 'selected' : '' }}>
                    {{ $cat->TENLOAI ?? $cat->name }}
                    </option>
                @endforeach
                </select>
            @endisset
            </div>

            <div class="col-lg-2">
            <select name="status" class="form-select">
                @php $st = $status ?? request('status'); @endphp
                <option value="">-- Tất cả trạng thái --</option>
                <option value="active" {{ $st=='active' ? 'selected' : '' }}>Đang bán</option>
                <option value="out"    {{ $st=='out'    ? 'selected' : '' }}>Hết hàng</option>
                <option value="hidden" {{ $st=='hidden' ? 'selected' : '' }}>Ẩn</option>
            </select>
            </div>

            <div class="col-lg-2 d-flex gap-2 justify-content-lg-end">
            <button class="btn btn-outline-primary">Lọc</button>
            <a href="{{ route('staff.products.index') }}" class="btn btn-outline-secondary">Xoá lọc</a>
            </div>
        </form>
        </div>
    </div>

    {{-- Bảng dữ liệu --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách sản phẩm</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                <i class="bi bi-plus-circle me-1"></i> Thêm mới
            </button>
        </div>

        <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover products-table">
            <thead>
            <tr>
                <th style="width:84px;">Mã</th>
                <th style="width:72px;">Hình</th>
                <th style="min-width:240px;">Tên sản phẩm</th>
                <th style="width:16%;">Loại</th>
                <th style="width:14%;">Đơn giá</th>
                <th style="width:10%;">Tồn kho</th>
                <th style="width:140px;" class="text-end">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                @php
                $id   = $p->MASANPHAM ?? $p->MASP ?? $p->id;
                $name = $p->TENSANPHAM ?? $p->TENSP ?? $p->name ?? '';
                $price= $p->GIABAN ?? $p->GIA ?? 0;
                $stock= $p->SOLUONGTON ?? $p->TONKHO ?? 0;
                $loai = $p->TENLOAI ?? ($p->loai->TENLOAI ?? $p->category->name ?? '-');
                $img  = $p->HINHANH ?? $p->image ?? null;
                @endphp
                <tr>
                <td>{{ $id }}</td>
                <td>
                    @if($img)
                    <img class="thumb" src="{{ asset('assets/images/'.$p->HINHANH) }}" alt="{{ $p->TENSANPHAM }}">
                    @else
                    <div class="thumb placeholder">No<br>Img</div>
                    @endif
                </td>
                <td class="text-truncate" title="{{ $name }}">{{ $name }}</td>
                <td class="text-truncate" title="{{ $loai }}">{{ $loai }}</td>
                <td><span class="price">{{ number_format($price, 0, ',', '.') }} ₫</span></td>
                <td>
                    @php
                    $badge = $stock <= 0 ? 'stock-bad' : ($stock < 5 ? 'stock-warn' : 'stock-ok');
                    @endphp
                    <span class="badge {{ $badge }}">{{ $stock }}</span>
                </td>
                <td class="text-end">
                    <button
                        class="btn btn-sm btn-primary-soft me-1 btn-edit"
                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                        data-id="{{ $id }}"
                        data-name="{{ $name }}"
                        data-price="{{ $price }}"
                        data-stock="{{ $stock }}"
                        data-category="{{ $p->MALOAI ?? $p->loai->MALOAI ?? $p->category_id ?? '' }}"
                        data-image="{{ $img }}"
                        title="Sửa">
                    <i class="bi bi-pencil"></i>
                    </button>

                    <form action="{{ route('staff.products.destroy', $id) }}"
                        method="post" class="d-inline form-delete">
                    @csrf @method('delete')
                    <button class="btn btn-sm btn-danger-soft" title="Xoá">
                        <i class="bi bi-trash"></i>
                    </button>
                    </form>
                </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>

        {{-- Phân trang custom --}}
        @php($sp = $products)
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
    </div>

    {{-- Modal: Thêm --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" method="post" enctype="multipart/form-data"
                action="{{ route('staff.products.store') }}">
            @csrf
            <div class="modal-header">
            <h5 class="modal-title">Thêm Sản phẩm</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Tên sản phẩm</label>
                <input type="text" name="TENSANPHAM" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Đơn giá (₫)</label>
                <input type="number" name="GIABAN" class="form-control" min="0" step="1000" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tồn kho</label>
                <input type="number" name="SOLUONGTON" class="form-control" min="0" step="1" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Loại</label>
                <select name="MALOAI" class="form-select" required>
                    @isset($categories)
                        @foreach($categories as $cat)
                        <option value="{{ $cat->MALOAI ?? $cat->id }}">
                            {{ $cat->TENLOAI ?? $cat->name }}
                        </option>
                        @endforeach
                    @endisset
                </select>

            </div>
            <div class="col-md-6">
                <label class="form-label">Hình ảnh</label>
                <input type="file" name="HINHANH" class="form-control" accept="image/*">
            </div>
            <div class="col-12">
                <label class="form-label">Mô tả</label>
                <textarea name="MOTA" class="form-control" rows="2" placeholder="Mô tả ngắn..."></textarea>
            </div>
            </div>

            <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
            <button class="btn btn-primary">Lưu</button>
            </div>
        </form>
        </div>
    </div>

    {{-- Modal: Sửa --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEdit" class="modal-content" method="post" enctype="multipart/form-data"
                data-action-template="{{ route('staff.products.update', ':id') }}">
            @csrf @method('put')
            <div class="modal-header">
            <h5 class="modal-title">Sửa Sản phẩm</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Tên sản phẩm</label>
                <input id="e_name" type="text" name="TENSANPHAM" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Đơn giá (₫)</label>
                <input id="e_price" type="number" name="GIABAN" class="form-control" min="0" step="1000" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tồn kho</label>
                <input id="e_stock" type="number" name="SOLUONGTON" class="form-control" min="0" step="1" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Loại</label>
                <select id="e_category" name="MALOAI" class="form-select" required>
                @isset($categories)
                    @foreach($categories as $cat)
                    <option value="{{ $cat->MALOAI ?? $cat->id }}">{{ $cat->TENLOAI ?? $cat->name }}</option>
                    @endforeach
                @endisset
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Hình ảnh (tuỳ chọn)</label>
                <input id="e_image" type="file" name="HINHANH" class="form-control" accept="image/*">
                <div class="mt-2 d-flex align-items-center gap-2">
                    {{-- <img class="thumb" src="{{ asset('assets/images/'.$p->HINHANH) }}" alt="{{ $p->TENSANPHAM }}"> --}}
                    <img id="e_preview" class="thumb" style="display:none;" alt="preview">
                    <span id="e_imgname" class="text-muted small"></span>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Mô tả</label>
                <textarea id="e_desc" name="MOTA" class="form-control" rows="2"></textarea>
            </div>
            </div>

            <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button class="btn btn-primary">Lưu</button>
            </div>
        </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff-products.js') }}"></script>
@endpush
