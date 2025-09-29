@extends('layouts.staff')
@section('title', 'Quản lý Phiếu nhập')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-receipts.css') }}">
@endpush

@section('content')
    <section class="page-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Phiếu nhập</h1>
        <p class="muted">Tạo, sửa, xác nhận và tra cứu phiếu nhập.</p>
    </section>

    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    <div class="card products-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="get" action="{{ route('staff.receipts.index') }}">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="Mã PN, Nhà cung cấp, Nhân viên">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Nhà cung cấp</label>
                    <select name="ncc" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->MANHACUNGCAP }}"
                                    {{ request('ncc') == $s->MANHACUNGCAP ? 'selected' : '' }}>
                                {{ $s->TENNHACUNGCAP }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="NHAP" {{ request('status') === 'NHAP' ? 'selected' : '' }}>NHAP</option>
                        <option value="DA_XAC_NHAN" {{ request('status') === 'DA_XAC_NHAN' ? 'selected' : '' }}>DA_XAC_NHAN</option>
                        <option value="HUY" {{ request('status') === 'HUY' ? 'selected' : '' }}>HUY</option>
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
                    <a href="{{ route('staff.receipts.index') }}" class="btn btn-outline-secondary">Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách phiếu nhập</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                <i class="bi bi-plus-circle me-1"></i> Thêm mới
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover products-table">
                <thead>
                    <tr>
                        <th style="width:70px;">STT</th>
                        <th style="width:140px;">Mã PN</th>
                        <th style="min-width:220px;">Nhà cung cấp</th>
                        <th style="min-width:160px;">Nhân viên</th>
                        <th style="width:160px;">Ngày nhập</th>
                        <th style="width:120px;" class="text-end">Tổng tiền</th>
                        <th style="width:140px;">Trạng thái</th>
                        <th style="width:160px;" class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $idx => $r)
                        @php
                            $st = $r->TRANGTHAI;
                            $rowNumber = ($receipts->currentPage()-1)*$receipts->perPage() + $idx + 1;
                        @endphp
                        <tr class="row-detail" data-id="{{ $r->MAPN }}" style="cursor:pointer">
                            <td>{{ $rowNumber }}</td>
                            <td class="text-primary fw-bold">
                                <a href="javascript:void(0)" class="link-detail" data-id="{{ $r->MAPN }}">{{ $r->MAPN }}</a>
                            </td>
                            <td class="text-truncate" title="{{ $r->TENNHACUNGCAP }}">{{ $r->TENNHACUNGCAP }}</td>
                            <td class="text-truncate" title="{{ $r->NHANVIEN }}">{{ $r->NHANVIEN }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->NGAYNHAP)->format('d/m/Y H:i') }}</td>
                            <td class="text-end"><span class="price">{{ number_format($r->TONGTIEN, 0, ',', '.') }} ₫</span></td>
                            <td>
                                @php
                                    $badge = $st === 'DA_XAC_NHAN' ? 'stock-ok' : ($st === 'HUY' ? 'stock-bad' : 'stock-warn');
                                @endphp
                                <span class="badge {{ $badge }}">{{ $st }}</span>
                            </td>
                            <td class="text-end actions" data-no-row-open>
                                @if($st === 'NHAP')
                                    <form data-no-row-open action="{{ route('staff.receipts.confirm', $r->MAPN) }}" method="post" class="d-inline form-confirm">
                                        @csrf @method('put')
                                        <button class="btn btn-sm btn-success-soft" title="Xác nhận phiếu"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                    <form data-no-row-open action="{{ route('staff.receipts.cancel', $r->MAPN) }}" method="post" class="d-inline form-cancel">
                                        @csrf @method('put')
                                        <button class="btn btn-sm btn-danger-soft" title="Huỷ phiếu"><i class="bi bi-x-octagon"></i></button>
                                    </form>
                                    <form data-no-row-open action="{{ route('staff.receipts.destroy', $r->MAPN) }}" method="post" class="d-inline form-delete">
                                        @csrf @method('delete')
                                        <button class="btn btn-sm btn-danger-soft" title="Xoá phiếu"><i class="bi bi-trash"></i></button>
                                    </form>
                                @elseif($st === 'DA_XAC_NHAN')
                                    <form data-no-row-open action="{{ route('staff.receipts.cancel', $r->MAPN) }}" method="post" class="d-inline form-cancel">
                                        @csrf @method('put')
                                        <button class="btn btn-sm btn-danger-soft" title="Huỷ phiếu"><i class="bi bi-x-octagon"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Chưa có phiếu nhập nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chi tiết Phiếu nhập <span id="md_id" class="text-muted"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><div><strong>Nhà cung cấp:</strong> <span id="md_ncc"></span></div></div>
                            <div class="col-md-4"><div><strong>Nhân viên:</strong> <span id="md_nv"></span></div></div>
                            <div class="col-md-4"><div><strong>Thời gian:</strong> <span id="md_time"></span></div></div>
                            <div class="col-md-12"><div><strong>Ghi chú:</strong> <span id="md_ghichu" class="text-muted"></span></div></div>
                        </div>
                        <div class="table-responsive border rounded p-2">
                            <table class="table table-sm align-middle mb-0" id="tblDetailLines">
                                <thead>
                                    <tr>
                                        <th style="width:70px;">STT</th>
                                        <th style="width:140px;">Mã SP</th>
                                        <th style="min-width:220px;">Tên sản phẩm</th>
                                        <th style="width:110px;" class="text-end">Số lượng</th>
                                        <th style="width:140px;" class="text-end">Đơn giá (₫)</th>
                                        <th style="width:160px;" class="text-end">Thành tiền (₫)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <div class="fs-6">
                                <span class="text-muted me-2">Tổng tiền:</span>
                                <strong id="md_tongtien">0</strong> ₫
                            </div>
                        </div>
                        <form id="md_form_confirm" action="#" method="post" class="mt-3 d-none">
                            @csrf @method('put')
                            <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Xác nhận phiếu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @php($sp = $receipts)
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

    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <form class="modal-content" method="post" action="{{ route('staff.receipts.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Phiếu nhập</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="MANHACUNGCAP" class="form-select" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->MANHACUNGCAP }}">{{ $sup->TENNHACUNGCAP }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Nhân viên nhập</label>
                            <select name="NHANVIEN_ID" class="form-select" required>
                                <option value="">-- Chọn nhân viên --</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <input type="text" name="GHICHU" class="form-control" placeholder="Ghi chú (không bắt buộc)">
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="tblCreateLines">
                                    <thead>
                                        <tr>
                                            <th style="min-width:280px;">Sản phẩm</th>
                                            <th style="width:120px;">Số lượng</th>
                                            <th style="width:140px;">Đơn giá (₫)</th>
                                            <th style="width:140px;">Thành tiền</th>
                                            <th style="width:60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="ITEM_MASP[]" class="form-select line-masp" required>
                                                    <option value="" selected disabled>-- Chọn sản phẩm --</option>
                                                    @foreach($products as $p)
                                                        <option value="{{ $p->MASANPHAM }}">{{ $p->TENSANPHAM }} ({{ $p->MASANPHAM }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" name="ITEM_SOLUONG[]" class="form-control line-qty" min="1" step="1" value="1" required></td>
                                            <td><input type="number" name="ITEM_DONGIA[]" class="form-control line-price" min="0" step="100" value="0" required></td>
                                            <td class="line-amount text-end">0</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger-soft btnDelLine" title="Xoá dòng"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" class="btn btn-outline-primary" id="btnAddLine">
                                                    <i class="bi bi-plus-lg me-1"></i> Thêm dòng
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
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
    <script>
        // dataset cho JS (kèm GIANHAP để auto điền đơn giá)
        window.products = @json($products);
        // route mẫu cho show; thay __ID__ bằng MAPN ở JS
        window.staff_receipt_show_url = @json(route('staff.receipts.show', ['id' => '__ID__']));
    </script>
    <script src="{{ asset('js/staff-receipts.js') }}"></script>
@endpush
