@extends('layouts.staff')
@section('title','Quản lý Khuyến mãi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-promotions.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
@endpush

@section('content')
    <section class="page-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Khuyến mãi</h1>
        <p class="muted">Voucher toàn đơn & khuyến mãi theo sản phẩm với điều kiện linh hoạt.</p>
    </section>

    {{-- Flash messages --}}
    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    {{-- Bộ lọc --}}
    <div class="card promotions-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-center" method="get" action="{{ route('staff.promotions.index') }}">
                <div class="col-lg-4">
                    <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control" placeholder="Tìm theo mã, tên, loại...">
                </div>
                <div class="col-lg-2">
                    <select name="loai" class="form-select">
                        <option value="">-- Tất cả loại --</option>
                        @foreach($promotionTypes as $key => $value)
                            <option value="{{ $key }}" {{ ($loai ?? request('loai')) == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="phamvi" class="form-select">
                        <option value="">-- Tất cả phạm vi --</option>
                        @foreach($scopeOptions as $k => $v)
                            <option value="{{ $k }}" {{ ($phamvi ?? request('phamvi')) == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="state" class="form-select">
                        <option value="">-- Trạng thái --</option>
                        <option value="upcoming" {{ request('state')=='upcoming'?'selected':'' }}>Sắp diễn ra</option>
                        <option value="active"   {{ request('state')=='active'  ?'selected':'' }}>Đang diễn ra</option>
                        <option value="expired"  {{ request('state')=='expired' ?'selected':'' }}>Hết hạn</option>
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
                        <th>Mã KM</th>
                        <th>Tên khuyến mãi</th>
                        <th>Phạm vi</th>
                        <th>Loại</th>
                        <th>Giảm</th>
                        <th>Hiệu lực</th>
                        <th>#SP</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $p)
                    @php
                        $state = $p->NGAYBATDAU && $p->NGAYKETTHUC
                        ? (now()->lt($p->NGAYBATDAU) ? 'upcoming' : (now()->gt($p->NGAYKETTHUC) ? 'expired' : 'active'))
                        : 'upcoming';
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $p->MAKHUYENMAI }}</td>
                        <td class="text-truncate" title="{{ $p->TENKHUYENMAI }}">{{ $p->TENKHUYENMAI }}</td>
                        <td>
                        <span class="badge-scope {{ $p->PHAMVI==='ORDER'?'order':'product' }}">
                            {{ $p->PHAMVI==='ORDER'?'Voucher':'Sản phẩm' }}
                        </span>
                        </td>
                        <td>{{ $p->LOAIKHUYENMAI }}</td>
                        <td>
                        @if ($p->LOAIKHUYENMAI === 'Giảm %')
                            {{ rtrim(rtrim(number_format($p->GIAMGIA,2,'.',''), '0'), '.') }}%
                        @elseif ($p->LOAIKHUYENMAI === 'Giảm fixed')
                            {{ number_format($p->GIAMGIA,0,',','.') }}₫
                        @else
                            {{ $p->GIAMGIA }}
                        @endif
                        </td>
                        <td>
                            <div>{{ optional($p->NGAYBATDAU)->format('d/m/Y') }} - {{ optional($p->NGAYKETTHUC)->format('d/m/Y') }}</div>
                            <span class="badge-state badge-{{ $state }}">
                                {{ $state==='active'?'Đang diễn ra':($state==='upcoming'?'Sắp diễn ra':'Hết hạn') }}
                            </span>
                        </td>
                        <td>{{ $p->sanphams_count }}</td>
                        <td class="text-end">
                        <button class="btn btn-sm btn-primary-soft me-1 btn-edit"
                            data-bs-toggle="modal" data-bs-target="#modalEdit"
                            data-id="{{ $p->MAKHUYENMAI }}"
                            data-name="{{ $p->TENKHUYENMAI }}"
                            data-type="{{ $p->LOAIKHUYENMAI }}"
                            data-discount="{{ $p->GIAMGIA }}"
                            data-start="{{ optional($p->NGAYBATDAU)->format('Y-m-d') }}"
                            data-end="{{ optional($p->NGAYKETTHUC)->format('Y-m-d') }}"
                            data-scope="{{ $p->PHAMVI }}"
                            data-priority="{{ $p->UUTIEN }}"
                            data-json='{{ $p->DIEUKIEN_JSON }}'
                            data-products="{{ $p->sanphams->pluck('MASANPHAM')->implode(',') }}"
                            title="Sửa"><i class="bi bi-pencil"></i></button>

                        <form action="{{ route('staff.promotions.destroy', $p->MAKHUYENMAI) }}" method="post" class="d-inline form-delete">
                            @csrf @method('delete')
                            <button class="btn btn-sm btn-danger-soft" title="Xoá"><i class="bi bi-trash"></i></button>
                        </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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

    {{-- Modal Thêm --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="post" action="{{ route('staff.promotions.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">
                    {{-- Thông tin chung --}}
                    <div class="col-md-3">
                        <label class="form-label">Phạm vi</label>
                        <select name="PHAMVI" id="c_scope" class="form-select" required>
                            @foreach($scopeOptions as $k=>$v)
                            <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ưu tiên</label>
                        <input type="number" name="UUTIEN" class="form-control" value="10" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mã khuyến mãi</label>
                        <input type="text" name="MAKHUYENMAI" id="c_code" class="form-control" placeholder="XUAN2025">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tên khuyến mãi</label>
                        <input type="text" name="TENKHUYENMAI" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Loại</label>
                        <select name="LOAIKHUYENMAI" id="c_type" class="form-select" required>
                            @foreach($promotionTypes as $k=>$v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mức giảm</label>
                        <input type="number" name="GIAMGIA" id="c_value" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ngày bắt đầu</label>
                        <input type="date" name="NGAYBATDAU" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ngày kết thúc</label>
                        <input type="date" name="NGAYKETTHUC" class="form-control" required>
                    </div>

                    {{-- ORDER only --}}
                    <div class="col-12 c-order-only d-none">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tổng tối thiểu (đ)</label>
                                <input type="number" name="min_order_total" class="form-control" min="0" step="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giảm tối đa (đ)</label>
                                <input type="number" name="max_discount" class="form-control" min="0" step="1000">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="non_stackable" id="c_non_stackable" value="1">
                                    <label class="form-check-label" for="c_non_stackable">Không cộng dồn KM khác</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PRODUCT only --}}
                    <div class="col-12 c-product-only d-none">
                        <label class="form-label">Gán trực tiếp sản phẩm</label>

                        <div class="d-flex gap-2 mb-2">
                            <input type="text" id="filterName" class="form-control" placeholder="Tìm theo tên sản phẩm...">
                            <select id="filterType" class="form-select" style="max-width:200px">
                                <option value="">-- Loại sản phẩm --</option>
                                @foreach($loais as $l)
                                    <option value="{{ $l->MALOAI }}">{{ $l->TENLOAI }}</option>
                                @endforeach
                            </select>
                            <select id="filterSupplier" class="form-select" style="max-width:200px">
                                <option value="">-- Nhà cung cấp --</option>
                                @foreach($nccs as $n)
                                    <option value="{{ $n->MANHACUNGCAP }}">{{ $n->TENNHACUNGCAP }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dual-listbox d-flex">
                            <select id="c_available_products" class="form-select" multiple size="10">
                                @foreach($products as $product)
                                    <option
                                        data-type="{{ $product->MALOAI ?? '' }}"
                                        data-supplier="{{ $product->MANHACUNGCAP ?? '' }}"
                                        data-name="{{ $product->TENSANPHAM }}"
                                        data-image="{{ trim((string)($product->HINHANH ?? '')) }}"
                                        data-price="{{ (float)($product->GIABAN ?? 0) }}"
                                        value="{{ $product->MASANPHAM }}"
                                    >{{ $product->TENSANPHAM }}</option>
                                @endforeach
                            </select>
                            <div class="buttons d-flex flex-column justify-content-center px-2">
                                <button type="button" id="c_move_right" class="btn btn-sm btn-outline-primary mb-2">&gt;&gt;</button>
                                <button type="button" id="c_move_left"  class="btn btn-sm btn-outline-primary">&lt;&lt;</button>
                            </div>
                            <select name="sanphams[]" id="c_selected_products" class="form-select" multiple size="10"></select>
                        </div>

                        <div id="c_selected_preview" class="selected-preview mt-2"></div>
                        <div class="small-note mt-1">Chọn sản phẩm từ trái và di chuyển sang phải.</div>
                    </div>

                    <div class="col-12 c-product-only d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Theo Loại</label>
                                <select name="maloais[]" id="c_loais" class="form-select select2" multiple>
                                    @foreach($loais as $l)
                                        <option value="{{ $l->MALOAI }}">{{ $l->TENLOAI }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Theo Nhà cung cấp</label>
                                <select name="manccs[]" id="c_nccs" class="form-select select2" multiple>
                                    @foreach($nccs as $n)
                                        <option value="{{ $n->MANHACUNGCAP }}">{{ $n->TENNHACUNGCAP }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Sửa --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="formEdit" class="modal-content" method="post" data-action-template="{{ route('staff.promotions.update', ':id') }}">
                @csrf @method('put')
                <div class="modal-header">
                    <h5 class="modal-title">Sửa Khuyến mãi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Phạm vi</label>
                        <select id="e_scope" name="PHAMVI" class="form-select" required>
                            @foreach($scopeOptions as $k=>$v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ưu tiên</label>
                        <input id="e_priority" type="number" name="UUTIEN" class="form-control" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mã khuyến mãi</label>
                        <input id="e_id" type="text" class="form-control" readonly disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tên khuyến mãi</label>
                        <input id="e_name" type="text" name="TENKHUYENMAI" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Loại</label>
                        <select id="e_type" name="LOAIKHUYENMAI" class="form-select" required>
                            @foreach($promotionTypes as $k=>$v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mức giảm</label>
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

                    {{-- ORDER only --}}
                    <div class="col-12 e-order-only d-none">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tổng tối thiểu (đ)</label>
                                <input id="e_min" type="number" name="min_order_total" class="form-control" min="0" step="1000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giảm tối đa (đ)</label>
                                <input id="e_max" type="number" name="max_discount" class="form-control" min="0" step="1000">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="non_stackable" id="e_non_stackable" value="1">
                                    <label class="form-check-label" for="e_non_stackable">Không cộng dồn KM khác</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PRODUCT only --}}
                    <div class="col-12 e-product-only d-none">
                        <label class="form-label">Gán trực tiếp sản phẩm</label>

                        <div class="dual-listbox d-flex">
                            <select id="e_available_products" class="form-select" multiple size="10">
                                @foreach($products as $product)
                                    <option
                                        data-name="{{ $product->TENSANPHAM }}"
                                        data-image="{{ trim((string)($product->HINHANH ?? '')) }}"
                                        data-price="{{ (float)($product->GIABAN ?? 0) }}"
                                        value="{{ $product->MASANPHAM }}"
                                    >{{ $product->TENSANPHAM }}</option>
                                @endforeach
                            </select>
                            <div class="buttons d-flex flex-column justify-content-center px-2">
                                <button type="button" id="e_move_right" class="btn btn-sm btn-outline-primary mb-2">&gt;&gt;</button>
                                <button type="button" id="e_move_left"  class="btn btn-sm btn-outline-primary">&lt;&lt;</button>
                            </div>
                            <select name="sanphams[]" id="e_selected_products" class="form-select" multiple size="10"></select>
                        </div>

                        <div id="e_selected_preview" class="selected-preview mt-2"></div>
                    </div>

                    <div class="col-12 e-product-only d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Theo Loại</label>
                                <select id="e_loais" name="maloais[]" class="form-select select2" multiple>
                                    @foreach($loais as $l)
                                        <option value="{{ $l->MALOAI }}">{{ $l->TENLOAI }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Theo Nhà cung cấp</label>
                                <select id="e_nccs" name="manccs[]" class="form-select select2" multiple>
                                    @foreach($nccs as $n)
                                        <option value="{{ $n->MANHACUNGCAP }}">{{ $n->TENNHACUNGCAP }}</option>
                                    @endforeach
                                </select>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/staff-promotions.js') }}"></script>
@endpush