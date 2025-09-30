@extends('layouts.staff')
@section('title', 'Quản lý Phiếu xuất')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-issues.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <section class="issues-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Phiếu xuất</h1>
        <p class="muted">Xác nhận, hủy và tra cứu phiếu xuất.</p>
    </section>

    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    <div class="card issues-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="get" action="{{ route('staff.issues.index') }}">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="Mã PX, Khách hàng">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Khách hàng</label>
                    <select name="customer" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->MAKHACHHANG }}"
                                    {{ request('customer') == $c->MAKHACHHANG ? 'selected' : '' }}>
                                {{ $c->HOTEN }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="NHAP" {{ request('status') === 'NHAP' ? 'selected' : '' }}>Nháp</option>
                        <option value="DA_XAC_NHAN" {{ request('status') === 'DA_XAC_NHAN' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="HUY" {{ request('status') === 'HUY' ? 'selected' : '' }}>Hủy</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Khoảng thời gian</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-lg-end mt-2">
                    <button class="btn btn-outline-primary">Lọc</button>
                    <a href="{{ route('staff.issues.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách phiếu xuất</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover issues-table">
                <thead>
                    <tr>
                        <th style="width:70px;">STT</th>
                        <th style="width:140px;">Mã PX</th>
                        <th style="min-width:220px;">Khách hàng</th>
                        <th style="min-width:160px;">Địa chỉ</th>
                        <th style="width:120px;" class="text-end">Tổng SL</th>
                        <th style="width:140px;">Trạng thái</th>
                        <th style="width:160px;" class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($issues as $idx => $issue)
                        @php
                            $st = $issue->TRANGTHAI;
                            $rowNumber = ($issues->currentPage()-1)*$issues->perPage() + $idx + 1;
                        @endphp
                        <tr class="row-detail" data-id="{{ $issue->MAPHIEUXUAT }}" style="cursor:pointer">
                            <td>{{ $rowNumber }}</td>
                            <td>{{ $issue->MAPHIEUXUAT }}</td>
                            <td class="text-truncate" title="{{ $issue->khachHang->HOTEN ?? '—' }}">{{ $issue->khachHang->HOTEN ?? '—' }}</td>
                            <td class="text-truncate" title="{{ $issue->diaChi->DIACHI ?? '—' }}">{{ $issue->diaChi->DIACHI ?? '—' }}</td>
                            <td class="text-end"><span class="price">{{ number_format($issue->TONGSL, 0, ',', '.') }}</span></td>
                            <td>
                                @php
                                    $badge = $st === 'DA_XAC_NHAN' ? 'stock-ok' : ($st === 'HUY' ? 'stock-bad' : 'stock-warn');
                                @endphp
                                <span class="badge {{ $badge }}">{{ $st }}</span>
                            </td>
                            <td class="text-end actions" data-no-row-open>
                                @if($st === 'NHAP')
                                    <form data-no-row-open action="{{ route('staff.issues.confirm', $issue->MAPHIEUXUAT) }}" method="post" class="d-inline form-confirm">
                                        @csrf @method('put')
                                        <button class="btn btn-sm btn-success-soft" title="Xác nhận phiếu"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                    <form data-no-row-open action="{{ route('staff.issues.cancel', $issue->MAPHIEUXUAT) }}" method="post" class="d-inline form-cancel">
                                        @csrf @method('put')
                                        <button class="btn btn-sm btn-danger-soft" title="Hủy phiếu"><i class="bi bi-x-octagon"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Không có phiếu xuất.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($issues->lastPage() > 1)
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    @if ($issues->currentPage() > 1)
                        <li class="page-item"><a class="page-link" href="{{ $issues->url($issues->currentPage() - 1) }}">Trước</a></li>
                    @endif
                    @for ($i = 1; $i <= $issues->lastPage(); $i++)
                        <li class="page-item {{ $i === $issues->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $issues->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    @if ($issues->currentPage() < $issues->lastPage())
                        <li class="page-item"><a class="page-link" href="{{ $issues->url($issues->currentPage() + 1) }}">Sau</a></li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết phiếu xuất</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã phiếu</label>
                            <p class="form-control-plaintext" id="md_id"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Khách hàng</label>
                            <p class="form-control-plaintext" id="md_customer"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <p class="form-control-plaintext" id="md_address"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày xuất</label>
                            <p class="form-control-plaintext" id="md_time"></p>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="tblDetailLines">
                                    <thead>
                                        <tr>
                                            <th style="width:70px;">STT</th>
                                            <th style="width:140px;">Mã SP</th>
                                            <th style="min-width:200px;">Tên sản phẩm</th>
                                            <th style="width:120px;" class="text-end">Số lượng</th>
                                            <th style="width:140px;" class="text-end">Đơn giá (₫)</th>
                                            <th style="width:140px;" class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <label class="form-label">Tổng số lượng</label>
                            <p class="form-control-plaintext fw-bold" id="md_tongsl"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="md_form_confirm" action="" method="post" class="d-inline form-confirm">
                        @csrf @method('put')
                        <button type="submit" class="btn btn-success">Xác nhận phiếu</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        window.staff_issue_show_url = @json(route('staff.issues.show', ['id' => '__ID__']));
    </script>
    <script src="{{ asset('js/staff-issues.js') }}"></script>
@endpush