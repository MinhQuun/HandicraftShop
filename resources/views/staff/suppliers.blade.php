@extends('layouts.staff')
@section('title','Quản lý Nhà cung cấp')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/staff-suppliers.css') }}">
@endpush

@section('content')
    <section class="page-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Nhà cung cấp</h1>
        <p class="muted">Thêm, sửa, xoá và tìm kiếm nhà cung cấp.</p>
    </section>

    {{-- FLASH data để JS đọc và hiện toast --}}
    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    <div class="card suppliers-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-center" method="get" action="{{ route('staff.suppliers.index') }}">
            <div class="col-md-6">
                <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control"
                    placeholder="Tìm theo tên, số điện thoại hoặc địa chỉ...">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary">Lọc</button>
                <a href="{{ route('staff.suppliers.index') }}" class="btn btn-outline-secondary">Xoá lọc</a>
            </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách nhà cung cấp</h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#modalCreate">
                    <i class="bi bi-plus-circle me-1"></i> Thêm mới
                </button>
                <a href="{{ route('staff.suppliers.exportCsv', request()->only('q')) }}"
                        class="btn-outline-success">
                    <i class="fa-solid fa-file-excel"></i> Xuất Excel
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover suppliers-table">
                <thead>
                    <tr>
                    <th style="width:80px;">ID</th>
                    <th style="width:28%;">Tên nhà cung cấp</th>
                    <th style="width:18%;">Điện thoại</th>
                    <th>Địa chỉ</th>
                    <th style="width:140px;" class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $s)
                    <tr>
                        <td>{{ $s->MANHACUNGCAP }}</td>
                        <td class="text-truncate" title="{{ $s->TENNHACUNGCAP }}">{{ $s->TENNHACUNGCAP }}</td>
                        <td class="text-truncate">{{ $s->DTHOAI }}</td>
                        <td class="text-truncate" title="{{ $s->DIACHI }}">{{ $s->DIACHI }}</td>
                        <td class="text-end">
                            <button
                                class="btn btn-sm btn-primary-soft me-1 btn-edit"
                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                data-id="{{ $s->MANHACUNGCAP }}"
                                data-name="{{ $s->TENNHACUNGCAP }}"
                                data-phone="{{ $s->DTHOAI }}"
                                data-address="{{ $s->DIACHI }}"
                                title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form
                                action="{{ route('staff.suppliers.destroy', $s->MANHACUNGCAP) }}"
                                method="post" class="d-inline form-delete">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-danger-soft" title="Xoá">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang custom --}}
        @php($sp = $suppliers) {{-- alias cho gọn --}}
        @if ($sp->lastPage() > 1)
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">

            {{-- Trước --}}
            @if ($sp->currentPage() > 1)
                <li class="page-item">
                <a class="page-link" href="{{ $sp->url($sp->currentPage() - 1) }}">Trước</a>
                </li>
            @endif

            {{-- Số trang --}}
            @for ($i = 1; $i <= $sp->lastPage(); $i++)
                <li class="page-item {{ $i === $sp->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $sp->url($i) }}">{{ $i }}</a>
                </li>
            @endfor

            {{-- Sau --}}
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
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" action="{{ route('staff.suppliers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Nhà cung cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">
                    <div class="col-md-6">
                    <label class="form-label">Tên nhà cung cấp</label>
                    <input type="text" name="TENNHACUNGCAP" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="DTHOAI" class="form-control" placeholder="0xxxxxxxxx" pattern="0\d{9}">
                    </div>
                    <div class="col-12">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="DIACHI" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Sửa (dùng chung) --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEdit" class="modal-content" method="post"
            data-action-template="{{ route('staff.suppliers.update', ':id') }}">
        @csrf @method('put')
        <div class="modal-header">
            <h5 class="modal-title">Sửa Nhà cung cấp</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body row g-3">
            <div class="col-md-6">
            <label class="form-label">Tên nhà cung cấp</label>
            <input id="e_name" type="text" name="TENNHACUNGCAP" class="form-control" required>
            </div>
            <div class="col-md-6">
            <label class="form-label">Số điện thoại</label>
            <input id="e_phone" type="text" name="DTHOAI" class="form-control" placeholder="0xxxxxxxxx" pattern="0\d{9}">
            </div>
            <div class="col-12">
            <label class="form-label">Địa chỉ</label>
            <textarea id="e_address" name="DIACHI" class="form-control" rows="2"></textarea>
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
    <script src="{{ asset('js/staff-suppliers.js') }}"></script>
@endpush
