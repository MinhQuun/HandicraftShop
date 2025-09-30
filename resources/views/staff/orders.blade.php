@extends('layouts.staff')
@section('title', 'Quản lý Đơn hàng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-orders.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <section class="orders-header">
        <span class="kicker">Nhân viên</span>
        <h1 class="title">Quản lý Đơn hàng</h1>
        <p class="muted">Xác nhận, hủy và tra cứu đơn hàng.</p>
    </section>

    <div id="flash"
        data-success="{{ session('success') }}"
        data-error="{{ session('error') }}"
        data-info="{{ session('info') }}"
        data-warning="{{ session('warning') }}">
    </div>

    <div class="card orders-filter mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="get" action="{{ route('staff.orders.index') }}">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="Mã đơn, Khách hàng">
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
                        <option value="Chờ xử lý" {{ request('status') === 'Chờ xử lý' ? 'selected' : '' }}>Chờ xử lý</option>
                        <option value="Chờ thanh toán" {{ request('status') === 'Chờ thanh toán' ? 'selected' : '' }}>Chờ thanh toán</option>
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
                    <a href="{{ route('staff.orders.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="m-0">Danh sách đơn hàng</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover orders-table">
                <thead>
                    <tr>
                        <th style="width:70px;">STT</th>
                        <th style="width:140px;">Mã đơn</th>
                        <th style="min-width:220px;">Khách hàng</th>
                        <th style="min-width:160px;">Địa chỉ</th>
                        <th style="width:120px;" class="text-end">Tổng tiền</th>
                        <th style="width:140px;">Trạng thái</th>
                        <th style="width:160px;" class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $idx => $order)
                        @php
                            $st = $order->TRANGTHAI;
                            $rowNumber = ($orders->currentPage()-1)*$orders->perPage() + $idx + 1;
                        @endphp
                        <tr class="row-detail" data-id="{{ $order->MADONHANG }}" style="cursor:pointer">
                            <td>{{ $rowNumber }}</td>
                            <td>{{ $order->MADONHANG }}</td>
                            <td class="text-truncate" title="{{ $order->khachHang->HOTEN ?? '—' }}">{{ $order->khachHang->HOTEN ?? '—' }}</td>
                            <td class="text-truncate" title="{{ $order->diaChi->DIACHI ?? '—' }}">{{ $order->diaChi->DIACHI ?? '—' }}</td>
                            <td class="text-end"><span class="price">{{ number_format($order->TONGTHANHTIEN, 0, ',', '.') }} ₫</span></td>
                            <td>
                                @php
                                    $badge = $st === 'DA_XAC_NHAN' ? 'stock-ok' : ($st === 'HUY' ? 'stock-bad' : 'stock-warn');
                                @endphp
                                <span class="badge {{ $badge }}">{{ $st }}</span>
                            </td>
                            <td class="text-end actions" data-no-row-open>
                                @if(in_array($st, ['Chờ xử lý', 'Chờ thanh toán']))
                                    <form data-no-row-open action="{{ route('staff.orders.confirm', $order->MADONHANG) }}" method="post" class="d-inline form-confirm">
                                        @csrf @method('put')
                                        <button class="btn btn-sm btn-success-soft" title="Xác nhận đơn"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                    <form data-no-row-open action="{{ route('staff.orders.cancel', $order->MADONHANG) }}" method="post" class="d-inline form-cancel">
                                        @csrf @method('put')
                                        <button class="btn btn-sm btn-danger-soft" title="Hủy đơn"><i class="bi bi-x-octagon"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Không có đơn hàng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->lastPage() > 1)
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    @if ($orders->currentPage() > 1)
                        <li class="page-item"><a class="page-link" href="{{ $orders->url($orders->currentPage() - 1) }}">Trước</a></li>
                    @endif
                    @for ($i = 1; $i <= $orders->lastPage(); $i++)
                        <li class="page-item {{ $i === $orders->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $orders->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor
                    @if ($orders->currentPage() < $orders->lastPage())
                        <li class="page-item"><a class="page-link" href="{{ $orders->url($orders->currentPage() + 1) }}">Sau</a></li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã đơn</label>
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
                            <label class="form-label">Ngày đặt</label>
                            <p class="form-control-plaintext" id="md_time"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phương thức thanh toán</label>
                            <p class="form-control-plaintext" id="md_payment"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ghi chú</label>
                            <p class="form-control-plaintext" id="md_note"></p>
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
                            <label class="form-label">Tổng tiền</label>
                            <p class="form-control-plaintext fw-bold" id="md_tongtien"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="md_form_confirm" action="" method="post" class="d-inline form-confirm">
                        @csrf @method('put')
                        <button type="submit" class="btn btn-success">Xác nhận đơn</button>
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
        window.staff_order_show_url = @json(route('staff.orders.show', ['id' => '__ID__']));
    </script>
    <script src="{{ asset('js/staff-orders.js') }}"></script>
@endpush