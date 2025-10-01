@extends('layouts.staff')
@section('title','Quản lý Khuyến mãi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-promotions.css') }}">
@endpush

@section('content')
    <section class="page-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Khuyến mãi</h1>
        <p class="muted">Thêm, sửa, xoá và quản lý khuyến mãi, gán sản phẩm.</p>
    </section>

    {{-- FLASH data để JS đọc và hiện toast --}}
    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    {{-- Bộ lọc / tìm kiếm --}}
    <div class="card promotions-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-center" method="get" action="{{ route('staff.promotions.index') }}">
                <div class="col-lg-6">
                    <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control"
                            placeholder="Tìm theo mã, tên, loại khuyến mãi">
                </div>

                <div class="col-lg-4">
                    <select name="loai" class="form-select">
                        <option value="">-- Tất cả loại --</option>
                        @foreach($promotionTypes as $key => $value)
                            <option value="{{ $key }}" {{ ($loai ?? request('loai')) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 d-flex gap-2 justify-content-lg-end">
                    <button class="btn btn-outline-primary">Lọc</button>
                    <a href="{{ route('staff.promotions.index') }}" class="btn btn-outline-secondary">Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng dữ liệu --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách khuyến mãi</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                <i class="bi bi-plus-circle me-1"></i> Thêm mới
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover promotions-table">
                <thead>
                    <tr>
                        <th style="width:120px;">Mã KM</th>
                        <th style="min-width:220px;">Tên khuyến mãi</th>
                        <th style="width:12%;">Loại</th>
                        <th style="width:12%;">Giảm giá</th>
                        <th style="width:12%;">Bắt đầu</th>
                        <th style="width:12%;">Kết thúc</th>
                        <th style="width:16%;">Sản phẩm gán</th>
                        <th style="width:120px;" class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $p)
                    <tr>
                        <td>{{ $p->MAKHUYENMAI }}</td>
                        <td class="text-truncate" title="{{ $p->TENKHUYENMAI }}">{{ $p->TENKHUYENMAI }}</td>
                        <td class="text-truncate" title="{{ $p->LOAIKHUYENMAI }}">{{ $p->LOAIKHUYENMAI }}</td>
                        <td><span class="price">{{ $p->GIAMGIA }}</span></td>
                        <td>{{ $p->NGAYBATDAU->format('d/m/Y') }}</td>
                        <td>{{ $p->NGAYKETTHUC->format('d/m/Y') }}</td>
                        <td>{{ $p->sanphams->count() }}</td>
                        <td class="text-end">
                            <button
                                class="btn btn-sm btn-primary-soft me-1 btn-edit"
                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                data-id="{{ $p->MAKHUYENMAI }}"
                                data-name="{{ $p->TENKHUYENMAI }}"
                                data-type="{{ $p->LOAIKHUYENMAI }}"
                                data-discount="{{ $p->GIAMGIA }}"
                                data-start="{{ $p->NGAYBATDAU->format('Y-m-d') }}"
                                data-end="{{ $p->NGAYKETTHUC->format('Y-m-d') }}"
                                data-products="{{ $p->sanphams->pluck('MASANPHAM')->implode(',') }}"
                                title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form action="{{ route('staff.promotions.destroy', $p->MAKHUYENMAI) }}" method="post" class="d-inline form-delete">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-danger-soft" title="Xoá">
                                <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang custom --}}
        @if ($promotions->lastPage() > 1)
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
            @if ($promotions->currentPage() > 1)
                <li class="page-item"><a class="page-link" href="{{ $promotions->url($promotions->currentPage() - 1) }}">Trước</a></li>
            @endif

            @for ($i = 1; $i <= $promotions->lastPage(); $i++)
                <li class="page-item {{ $i === $promotions->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $promotions->url($i) }}">{{ $i }}</a>
                </li>
            @endfor

            @if ($promotions->currentPage() < $promotions->lastPage())
                <li class="page-item"><a class="page-link" href="{{ $promotions->url($promotions->currentPage() + 1) }}">Sau</a></li>
            @endif
            </ul>
        </nav>
        @endif
    </div>

    {{-- Modal: Thêm --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" method="post" action="{{ route('staff.promotions.store') }}">
            @csrf
            <div class="modal-header">
            <h5 class="modal-title">Thêm Khuyến mãi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Mã khuyến mãi</label>
                <input type="text" name="MAKHUYENMAI" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tên khuyến mãi</label>
                <input type="text" name="TENKHUYENMAI" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Loại</label>
                <select name="LOAIKHUYENMAI" class="form-select" required>
                    @foreach($promotionTypes as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Giảm giá</label>
                <input type="number" name="GIAMGIA" class="form-control" min="0" step="0.01" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ngày bắt đầu</label>
                <input type="date" name="NGAYBATDAU" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ngày kết thúc</label>
                <input type="date" name="NGAYKETTHUC" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Gán sản phẩm (tuỳ chọn)</label>
                <select name="sanphams[]" class="form-select" multiple>
                    @foreach($products as $product)
                    <option value="{{ $product->MASANPHAM }}">{{ $product->TENSANPHAM }}</option>
                    @endforeach
                </select>
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
        <form id="formEdit" class="modal-content" method="post" data-action-template="{{ route('staff.promotions.update', ':id') }}">
            @csrf @method('put')
            <div class="modal-header">
            <h5 class="modal-title">Sửa Khuyến mãi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Mã khuyến mãi (không sửa)</label>
                <input id="e_id" type="text" class="form-control" readonly disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tên khuyến mãi</label>
                <input id="e_name" type="text" name="TENKHUYENMAI" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Loại</label>
                <select id="e_type" name="LOAIKHUYENMAI" class="form-select" required>
                    @foreach($promotionTypes as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Giảm giá</label>
                <input id="e_discount" type="number" name="GIAMGIA" class="form-control" min="0" step="0.01" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ngày bắt đầu</label>
                <input id="e_start" type="date" name="NGAYBATDAU" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ngày kết thúc</label>
                <input id="e_end" type="date" name="NGAYKETTHUC" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Gán sản phẩm</label>
                <select id="e_products" name="sanphams[]" class="form-select" multiple>
                    @foreach($products as $product)
                    <option value="{{ $product->MASANPHAM }}">{{ $product->TENSANPHAM }}</option>
                    @endforeach
                </select>
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
    <script src="{{ asset('js/staff-promotions.js') }}"></script>
@endpush