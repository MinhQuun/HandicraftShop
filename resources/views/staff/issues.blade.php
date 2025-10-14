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
    <p class="muted">Xem, xác nhận và hủy phiếu xuất.</p>
</section>

<div id="flash"
    data-success="{{ session('success') }}"
    data-error="{{ session('error') }}"
    data-info="{{ session('info') }}"
    data-warning="{{ session('warning') }}">
</div>

{{-- Bộ lọc phiếu xuất --}}
<div class="card issues-filter mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="{{ route('staff.issues.index') }}">
            <div class="col-lg-4 col-md-6">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Mã PX, Khách hàng">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Khách hàng</label>
                <select name="customer" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->MAKHACHHANG }}" {{ $customer == $c->MAKHACHHANG ? 'selected' : '' }}>
                            {{ $c->HOTEN }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="NHAP" {{ $status === 'NHAP' ? 'selected' : '' }}>Nháp</option>
                    <option value="DA_XAC_NHAN" {{ $status === 'DA_XAC_NHAN' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="HUY" {{ $status === 'HUY' ? 'selected' : '' }}>Hủy</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Khoảng thời gian</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="date" name="from" value="{{ $from }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <input type="date" name="to" value="{{ $to }}" class="form-control">
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

{{-- Danh sách phiếu xuất --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="m-0">Danh sách phiếu xuất</h5>
        <div class="d-flex gap-2">
                <a href="{{ route('staff.products.exportCsv', request()->only('q','loai')) }}"
                            class="btn-outline-success">
                            <i class="fa-solid fa-file-excel"></i>  Xuất Excel
                </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã PX</th>
                    <th>Khách hàng</th>
                    <th>Nhân viên</th>
                    <th>Ngày xuất</th>
                    <th class="text-end">Tổng tiền</th>
                    <th>Khuyến mãi</th>
                    <th class="text-end">Tổng tiền đã áp dụng KM</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issues as $idx => $issue)
                    @php
                        $rowNumber = ($issues->currentPage()-1)*$issues->perPage() + $idx + 1;
                        $badgeClass = $issue->TRANGTHAI === 'DA_XAC_NHAN' ? 'stock-ok' : ($issue->TRANGTHAI === 'HUY' ? 'stock-bad' : 'stock-warn');
                        $promotionText = $issue->MAKHUYENMAI ? ($issue->LOAIKHUYENMAI ?? 'Khuyến mãi') . ' (' . $issue->GIAMGIA . '%)' : '—';
                    @endphp
                    <tr data-id="{{ $issue->MAPX }}" class="row-detail" style="cursor:pointer">
                        <td>{{ $rowNumber }}</td>
                        <td>{{ $issue->MAPX }}</td>
                        <td>{{ $issue->KHACHHANG ?? '—' }}</td>
                        <td>{{ $issue->NHANVIEN }}</td>
                        <td>{{ \Carbon\Carbon::parse($issue->NGAYXUAT)->format('d/m/Y H:i') }}</td>
                        <td class="text-end">{{ number_format($issue->TONGTIEN + ($issue->GIAMGIA ? ($issue->TONGTIEN * $issue->GIAMGIA / 100) : 0), 0, ',', '.') }}</td>
                        <td>{{ $promotionText }}</td>
                        <td class="text-end">{{ number_format($issue->TONGTIEN, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $issue->TRANGTHAI }}</span></td>
                        <td class="text-end">
                            @if($issue->TRANGTHAI === 'NHAP')
                                <form class="d-inline form-confirm" action="{{ route('staff.issues.confirm', $issue->MAPX) }}" method="post">
                                    @csrf @method('put')
                                    <button class="btn btn-sm btn-success-soft" title="Xác nhận"><i class="bi bi-check2-circle"></i></button>
                                </form>
                                <form class="d-inline form-cancel" action="{{ route('staff.issues.cancel', $issue->MAPX) }}" method="post">
                                    @csrf @method('put')
                                    <button class="btn btn-sm btn-danger-soft" title="Hủy"><i class="bi bi-x-octagon"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center">Không có phiếu xuất.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}
    @php($sp = $issues)
        @if ($sp->lastPage() > 1)
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    @if ($sp->currentPage() > 1)
                        <li class="page-item"><a class="page-link" href="{{ $sp->url($sp->currentPage() - 1) }}">Trước</a></li>
                    @endif
                    @for ($i = 1; $i <= $sp->lastPage(); $i++)
                        <li class="page-item {{ $i === $sp->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $sp->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    @if ($sp->currentPage() < $sp->lastPage())
                        <li class="page-item"><a class="page-link" href="{{ $sp->url($sp->currentPage() + 1) }}">Sau</a></li>
                    @endif
                </ul>
            </nav>
        @endif
</div>

{{-- Modal chi tiết phiếu xuất --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết phiếu xuất</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label>Mã phiếu</label>
                        <p class="form-control-plaintext" id="md_id">—</p>
                    </div>
                    <div class="col-md-4">
                        <label>Khách hàng</label>
                        <p class="form-control-plaintext" id="md_customer">—</p>
                    </div>
                    <div class="col-md-4">
                        <label>Nhân viên</label>
                        <p class="form-control-plaintext" id="md_staff">—</p>
                    </div>
                    <div class="col-md-6">
                        <label>Địa chỉ giao hàng</label>
                        <p class="form-control-plaintext" id="md_address">—</p>
                    </div>
                    <div class="col-md-6">
                        <label>Ngày xuất</label>
                        <p class="form-control-plaintext" id="md_time">—</p>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle" id="tblDetailLines">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row text-end">
                    <div class="col-6 offset-6">
                        <div class="mb-1">
                            <label class="fw-bold">Tổng số lượng</label>
                            <p class="form-control-plaintext fw-bold" id="md_tongsl">0</p>
                        </div>
                        <div class="mb-1">
                            <label class="fw-bold">Khuyến mãi</label>
                            <p class="form-control-plaintext fw-bold" id="md_promotion">—</p>
                        </div>
                        <div class="mb-1">
                            <label class="fw-bold">Tiền giảm</label>
                            <p class="form-control-plaintext fw-bold" id="md_tiengiam">0 ₫</p>
                        </div>
                        <div>
                            <label class="fw-bold">Tổng tiền</label>
                            <p class="form-control-plaintext fw-bold" id="md_tongtien">0 ₫</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <form id="md_form_confirm" method="post" class="d-inline form-confirm">
                    @csrf @method('put')
                    <button type="submit" class="btn btn-success">Xác nhận phiếu</button>
                </form>
                <a id="btnExportPdf" href="#" target="_blank" class="btn btn-outline-danger">
                    <i class="fa fa-file-pdf"></i> Xuất PDF
                </a>
                
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