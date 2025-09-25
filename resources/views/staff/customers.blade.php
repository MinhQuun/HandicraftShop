@extends('layouts.staff')
@section('title','Quản lý Khách hàng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-customers.css') }}">
@endpush

@section('content')
    @php
        $errCount = $errors->count();
        $formName = old('_form'); // 'create' hoặc 'edit'
        $editingId = old('editing_id'); // id đang sửa (nếu có)
    @endphp

    {{-- Cấu hình cho JS (tự mở lại modal khi có lỗi) --}}
    @push('scripts')
    <script>
        document.documentElement.dataset.laravelErrors = "{{ $errCount }}";
        document.documentElement.dataset.laravelForm   = "{{ $formName }}";
        document.documentElement.dataset.editId        = "{{ $editingId }}";
    </script>
    @endpush

    <section class="page-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Khách hàng</h1>
        <p class="muted">Thêm, sửa, xoá và tìm kiếm khách hàng.</p>
    </section>

    {{-- FLASH data để JS đọc và hiện toast --}}
    <div id="flash"
            data-success="{{ session('success') }}"
            data-error="{{ session('error') }}"
            data-info="{{ session('info') }}"
            data-warning="{{ session('warning') }}">
    </div>

    {{-- Bộ lọc / tìm kiếm --}}
    <div class="card customers-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-center" method="get" action="{{ route('staff.customers.index') }}">
                <div class="col-lg-6">
                    <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control"
                        placeholder="Tìm theo họ tên, email, số điện thoại">
                </div>

                <div class="col-lg-3">
                    @php $ha = $has_account ?? request('has_account'); @endphp
                    <select name="has_account" class="form-select">
                        <option value="">-- Tất cả tài khoản --</option>
                        <option value="1" {{ $ha==='1' ? 'selected' : '' }}>Có tài khoản</option>
                        <option value="0" {{ $ha==='0' ? 'selected' : '' }}>Guest (chưa có tài khoản)</option>
                    </select>
                </div>

                <div class="col-lg-3 d-flex gap-2 justify-content-lg-end">
                    <button class="btn btn-outline-primary">Lọc</button>
                    <a href="{{ route('staff.customers.index') }}" class="btn btn-outline-secondary">Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng dữ liệu --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách khách hàng</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                <i class="bi bi-plus-circle me-1"></i> Thêm mới
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover customers-table">
                <thead>
                <tr>
                    <th style="width:84px;">Mã KH</th>
                    <th style="min-width:240px;">Họ tên</th>
                    <th style="width:22%;">Email</th>
                    <th style="width:16%;">Số ĐT</th>
                    <th style="width:12%;">Tài khoản</th>
                    <th style="width:140px;" class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($customers as $c)
                    @php
                        $makh  = $c->MAKHACHHANG ?? $c->id;
                        $name  = $c->HOTEN ?? '';
                        $email = $c->EMAIL ?? '';
                        $phone = $c->SODIENTHOAI ?? '';
                        $hasUser = !empty($c->user) || (!empty($c->user_id));
                        $badge = $hasUser ? 'acc-yes' : 'acc-no';
                        $badgeText = $hasUser ? 'Có tài khoản' : 'Guest';
                    @endphp
                    <tr>
                        <td>{{ $makh }}</td>
                        <td class="text-truncate" title="{{ $name }}">{{ $name }}</td>
                        <td class="text-truncate" title="{{ $email }}">{{ $email }}</td>
                        <td class="text-truncate" title="{{ $phone }}">{{ $phone }}</td>
                        <td>
                            <span class="badge {{ $badge }}">{{ $badgeText }}</span>
                        </td>
                        <td class="text-end">
                            <button
                                class="btn btn-sm btn-primary-soft me-1 btn-edit"
                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                data-id="{{ $makh }}"
                                data-name="{{ $name }}"
                                data-email="{{ $email }}"
                                data-phone="{{ $phone }}"
                                data-hasuser="{{ $hasUser ? 1 : 0 }}"
                                title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form action="{{ route('staff.customers.destroy', $makh) }}"
                                method="post" class="d-inline form-delete">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-danger-soft" title="Xoá">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang custom --}}
        @php($pg = $customers)
        @if ($pg->lastPage() > 1)
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    @if ($pg->currentPage() > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $pg->url($pg->currentPage() - 1) }}">Trước</a>
                        </li>
                    @endif

                    @for ($i = 1; $i <= $pg->lastPage(); $i++)
                        <li class="page-item {{ $i === $pg->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $pg->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($pg->currentPage() < $pg->lastPage())
                        <li class="page-item">
                            <a class="page-link" href="{{ $pg->url($pg->currentPage() + 1) }}">Sau</a>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    {{-- Modal: Thêm --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" action="{{ route('staff.customers.store') }}">
                @csrf
                <input type="hidden" name="_form" value="create">

                <div class="modal-header">
                    <h5 class="modal-title">Thêm Khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="HOTEN"
                                value="{{ old('HOTEN') }}"
                                class="form-control @error('HOTEN') is-invalid @enderror"
                                required>
                        @error('HOTEN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email (đăng nhập)</label>
                        <input type="email" name="EMAIL"
                                value="{{ old('EMAIL') }}"
                                class="form-control @error('EMAIL') is-invalid @enderror"
                                required
                                title="Vui lòng nhập đúng định dạng email, ví dụ: ten@gmail.com">
                        @error('EMAIL')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" name="SODIENTHOAI"
                                value="{{ old('SODIENTHOAI') }}"
                                class="form-control @error('SODIENTHOAI') is-invalid @enderror"
                                required pattern="0\d{9}" minlength="10" maxlength="10" placeholder="0xxxxxxxxx"
                                title="Số điện thoại phải bắt đầu bằng số 0 và đủ 10 số">
                        @error('SODIENTHOAI')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Ít nhất 6 ký tự">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <div class="col-12">
                        <small class="text-muted">Nếu để trống mật khẩu, hệ thống sẽ tạo mật khẩu tạm và có thể yêu cầu khách đổi sau.</small>
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
            <form id="formEdit" class="modal-content" method="post"
                    data-action-template="{{ route('staff.customers.update', ':id') }}">
                @csrf @method('put')
                <input type="hidden" name="_form" value="edit">
                <input type="hidden" name="editing_id" value="{{ old('editing_id') }}">

                <div class="modal-header">
                    <h5 class="modal-title">Sửa Khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ tên</label>
                        <input id="e_name" type="text" name="HOTEN"
                                value="{{ old('HOTEN') }}"
                                class="form-control @error('HOTEN') is-invalid @enderror"
                                required>
                        @error('HOTEN')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input id="e_email" type="email" name="EMAIL"
                                value="{{ old('EMAIL') }}"
                                class="form-control @error('EMAIL') is-invalid @enderror"
                                title="Vui lòng nhập đúng định dạng email, ví dụ: ten@gmail.com">
                        @error('EMAIL')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input id="e_phone" type="tel" name="SODIENTHOAI"
                                value="{{ old('SODIENTHOAI') }}"
                                class="form-control @error('SODIENTHOAI') is-invalid @enderror"
                                required pattern="0\d{9}" minlength="10" maxlength="10" placeholder="0xxxxxxxxx"
                                title="Số điện thoại phải bắt đầu bằng số 0 và đủ 10 số">
                        @error('SODIENTHOAI')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Đặt lại mật khẩu (tuỳ chọn)</label>
                        <input id="e_password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Ít nhất 6 ký tự">
                        <input type="password" name="password_confirmation"
                                class="form-control mt-2" placeholder="Xác nhận mật khẩu">
                        <small class="text-muted d-block mt-1">Để trống nếu không đổi mật khẩu.</small>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
<script src="{{ asset('js/staff-customers.js') }}"></script>
@endpush
