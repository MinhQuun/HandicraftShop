@extends('layouts.admin')
@section('title','Quản lý người dùng')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
@endpush

@section('content')
    <div class="admin-users">
        <div class="au-header">
            <div>
            <h2 class="title">Quản lý tài khoản</h2>
            <p class="subtitle">Thêm / sửa / xoá & phân quyền người dùng</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">Thêm mới</button>
        </div>

        <form class="au-filter row g-2 align-items-center" method="get">
            <div class="col-md-6">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên, email, điện thoại...">
            </div>
            <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">— Tất cả quyền —</option>
                @foreach($roles as $r)
                <option value="{{ strtolower($r->TENQUYEN) }}" {{ $roleFilter===strtolower($r->TENQUYEN)?'selected':'' }}>
                    {{ $r->TENQUYEN }} ({{ $r->MAQUYEN }})
                </option>
                @endforeach
            </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-outline-primary">Lọc</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Xoá lọc</a>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
            <table class="table align-middle mb-0 table-fixed">
                <colgroup>
                    <col style="width:50px;">    {{-- ID --}}
                    <col style="width:16%;">     {{-- Họ tên  --}}
                    <col style="width:21%;">     {{-- Email  --}}
                    <col style="width:11%;">     {{-- Điện thoại  --}}
                    <col style="width:30%;">     {{-- Quyền  --}}
                    <col style="width:12%;">     {{-- Thao tác  --}}
                </colgroup>

                <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Quyền</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                @php
                    $firstRole = optional($u->roles->first());
                    $roleId    = $firstRole->MAQUYEN ?? null;
                    $roleName  = strtolower($firstRole->TENQUYEN ?? '');
                    $isAdmin   = $roleId === $adminId;
                    $isKhach   = $roleId === $khachhangId;
                @endphp
                <tr>
                    <td>{{ $u->id }}</td>
                    <td class="text-truncate">{{ $u->name }}</td>
                    <td class="text-truncate">{{ $u->email }}</td>
                    <td class="text-truncate">{{ $u->phone }}</td>

                    {{-- QUYỀN: bố cục cố định Badge | Select | Lưu --}}
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
                                {{ $isKhach ? 'disabled title=Khách hàng không được đổi quyền' : '' }}>
                            @foreach($roles as $r)
                            <option value="{{ $r->MAQUYEN }}" {{ $roleId===$r->MAQUYEN ? 'selected' : '' }}>
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

                    {{-- THAO TÁC: nút đồng cỡ, không nhảy --}}
                    <td class="td-actions text-end">
                    <button class="btn btn-sm btn-primary-soft action-btn"
                            data-bs-toggle="modal" data-bs-target="#modalEdit"
                            data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                            data-email="{{ $u->email }}" data-phone="{{ $u->phone }}">
                        <i class="bi bi-pencil-square me-1"></i>Sửa
                    </button>

                    <form action="{{ route('admin.users.destroy',$u) }}" method="post" class="d-inline"
                            onsubmit="return confirm('Xoá người dùng này?');">
                        @csrf @method('delete')
                        <button class="btn btn-sm btn-danger-soft action-btn"
                                {{ $isAdmin ? 'disabled title=Không thể xoá Admin' : '' }}>
                        <i class="bi bi-trash me-1"></i>Xoá
                        </button>
                    </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Không có dữ liệu</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
            <div class="p-3">{{ $users->links() }}</div>
        </div>
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
            <form id="formEdit" class="modal-content" method="post">
            @csrf @method('put')
            <div class="modal-header">
                <h5 class="modal-title">Sửa người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6"><label class="form-label">Họ tên</label><input id="e_name" name="name" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input id="e_email" type="email" name="email" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">SĐT</label><input id="e_phone" name="phone" class="form-control" placeholder="0xxxxxxxxx" pattern="0\d{9}"></div>
                <div class="col-md-6"><label class="form-label">Đổi mật khẩu (tuỳ chọn)</label><input type="password" name="password" class="form-control" minlength="6" placeholder="Để trống nếu không đổi"></div>
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