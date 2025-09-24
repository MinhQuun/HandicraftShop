@extends('layouts.admin')
@section('title','Quản lý người dùng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
@endpush

@section('content')
    {{-- FLASH data (giống staff) --}}
    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    {{-- Header đồng bộ staff --}}
    <section class="page-header">
        <span class="kicker">Admin</span>
        <h1 class="title">Quản lý người dùng</h1>
        <p class="muted">Thêm, sửa, xoá & phân quyền tài khoản.</p>
    </section>

    {{-- Bộ lọc / tìm kiếm (đồng bộ staff-products) --}}
    <div class="card users-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-center" method="get" action="{{ route('admin.users.index') }}">
                <div class="col-lg-5">
                    <input class="form-control" name="q" value="{{ $q ?? request('q') }}"
                        placeholder="Tìm theo tên, email, điện thoại...">
                </div>
                <div class="col-lg-3">
                    <select name="role" class="form-select">
                        @php $rf = $roleFilter ?? request('role'); @endphp
                        <option value="">— Tất cả quyền —</option>
                        @foreach($roles as $r)
                        @php $val = strtolower($r->TENQUYEN); @endphp
                        <option value="{{ $val }}" {{ $rf===$val ? 'selected' : '' }}>
                            {{ $r->TENQUYEN }} ({{ $r->MAQUYEN }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2 justify-content-lg-end">
                    <button class="btn btn-outline-primary">Lọc</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Xoá lọc</a>
                </div>
                {{-- <div class="col-lg-2 text-lg-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                        <i class="bi bi-plus-circle me-1"></i> Thêm mới
                    </button>
                </div> --}}
            </form>
        </div>
    </div>

    {{-- Bảng dữ liệu (đồng bộ staff: card, thead, hover, soft buttons) --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách người dùng</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                <i class="bi bi-plus-circle me-1"></i> Thêm mới
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover users-table table-fixed">
                <colgroup>
                    <col style="width:60px;">
                    <col style="width:16%;">
                    <col style="width:20%;">
                    <col style="width:12%;">
                    <col style="width:32%;">
                    <col style="width:12%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Quyền</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        @php
                        $firstRole = optional($u->roles->first());
                        $roleId    = $firstRole->MAQUYEN ?? null;
                        $isAdmin   = $roleId === ($adminId ?? null);
                        $isKhach   = $roleId === ($khachhangId ?? null);
                        @endphp
                        <tr>
                        <td>{{ $u->id }}</td>
                        <td class="text-truncate" title="{{ $u->name }}">{{ $u->name }}</td>
                        <td class="text-truncate" title="{{ $u->email }}">{{ $u->email }}</td>
                        <td class="text-truncate" title="{{ $u->phone }}">{{ $u->phone }}</td>

                        {{-- Cột Quyền: Badge | Select | Lưu (giữ bố cục cố định) --}}
                        <td class="role-cell">
                            <form action="{{ route('admin.users.updateRole',$u) }}" method="post">
                            @csrf
                            <div class="role-wrap">
                                <span class="badge rounded-pill
                                {{ $isAdmin ? 'text-bg-danger' : ($isKhach ? 'text-bg-secondary' : 'text-bg-success') }}">
                                {{ $firstRole->TENQUYEN ?? '—' }}
                                </span>

                                <select name="MAQUYEN"
                                        class="form-select form-select-sm role-select"
                                        data-lock="{{ $isAdmin ? 'admin' : '' }}"
                                        data-staff="{{ $nhanvienId ?? '' }}"
                                        data-khach="{{ $khachhangId ?? '' }}"
                                        {{ $isKhach ? 'disabled title=Khách hàng không được đổi quyền' : '' }}>
                                @foreach($roles as $r)
                                    <option value="{{ $r->MAQUYEN }}"
                                            {{ $roleId===$r->MAQUYEN ? 'selected' : '' }}
                                            @if(($roleId ?? null) === ($nhanvienId ?? null) && $r->MAQUYEN === ($khachhangId ?? null))
                                            disabled title="Nhân viên không thể hạ xuống Khách hàng"
                                            @endif>
                                    {{ $r->TENQUYEN }}
                                    </option>
                                @endforeach
                                </select>

                                <button class="btn btn-sm btn-success-soft role-save" {{ $isKhach ? 'disabled' : '' }}>
                                <i class="bi bi-check2-circle me-1"></i>Lưu
                                </button>
                            </div>
                            </form>
                        </td>

                        {{-- Thao tác: soft buttons, cố định kích thước --}}
                        <td class="td-actions text-end">
                            <button class="btn btn-sm btn-primary-soft action-btn"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit"
                                    data-id="{{ $u->id }}"
                                    data-name="{{ $u->name }}"
                                    data-email="{{ $u->email }}"
                                    data-phone="{{ $u->phone }}">
                            <i class="bi bi-pencil me-1"></i>
                            </button>

                            <form action="{{ route('admin.users.destroy',$u) }}" method="post" class="d-inline form-delete">
                            @csrf @method('delete')
                            <button class="btn btn-sm btn-danger-soft action-btn"
                                    {{ $isAdmin ? 'disabled title=Không thể xoá Admin' : '' }}>
                                <i class="bi bi-trash me-1"></i>
                            </button>
                            </form>
                        </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang custom (đồng phong cách staff) --}}
        @php($sp = $users)
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

    {{-- Modal tạo --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" action="{{ route('admin.users.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ tên</label>
                        <input name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SĐT</label>
                        <input name="phone" class="form-control" placeholder="0xxxxxxxxx" pattern="0\d{9}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="6" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quyền</label>
                        <select name="MAQUYEN" class="form-select" required>
                        @foreach($roles as $r)
                            <option value="{{ $r->MAQUYEN }}">{{ $r->TENQUYEN }} ({{ $r->MAQUYEN }})</option>
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

    {{-- Modal sửa --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="formEdit" class="modal-content" method="post"
                    data-action-template="{{ route('admin.users.update', ':id') }}">
                @csrf @method('put')
                <div class="modal-header">
                    <h5 class="modal-title">Sửa người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ tên</label>
                        <input id="e_name" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input id="e_email" type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SĐT</label>
                        <input id="e_phone" name="phone" class="form-control" placeholder="0xxxxxxxxx" pattern="0\d{9}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Đổi mật khẩu (tuỳ chọn)</label>
                        <input type="password" name="password" class="form-control" minlength="6" placeholder="Để trống nếu không đổi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Xác nhận mật khẩu (tuỳ chọn)</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="6" placeholder="Để trống nếu không đổi">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-users.js') }}"></script>
@endpush
