@extends('layouts.main')

@section('title', 'Đơn Hàng Của Tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/order_history.css') }}">
<link rel="stylesheet" href="{{ asset('css/order_history2.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/order_history.js') }}"></script>
@endpush

@section('content')
    <main class="page-cart">
        <section class="cart-shell">
            <h2 class="cart-title">DANH SÁCH ĐƠN HÀNG</h2>

            <div class="filter-card mb-3">
                <form method="GET" class="filter-form d-flex flex-wrap align-items-end gap-2">
                    <div class="filter-item">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="from" class="form-label">Ngày đặt từ</label>
                        <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
                    </div>
                    <div class="filter-item">
                        <label for="to" class="form-label">Ngày đặt đến</label>
                        <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
                    </div>
                    <div class="filter-buttons d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-success">Lọc</button>
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary flex-fill">Xóa lọc</a>
                    </div>
                </form>
            </div>

            <table class="cart-table">
                <thead class="cart-thead">
                    <tr>
                        <th>Mã Đơn Hàng</th>
                        <th>Ngày Đặt</th>
                        <th class="col-hide-md">Ngày Giao</th>
                        <th>Tổng Số Lượng</th>
                        <th class="col-hide-md">Tổng Thành Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                    <tr class="cart-row">
                        <td>{{ $order->MADONHANG }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->NGAYDAT)->format('d/m/Y') }}</td>
                        <td class="col-hide-md">{{ $order->NGAYGIAO ? \Carbon\Carbon::parse($order->NGAYGIAO)->format('d/m/Y') : 'Chưa giao' }}</td>
                        <td>{{ $order->chiTiets->sum('SOLUONG') }}</td>
                        <td class="col-hide-md">{{ number_format($order->TONGTHANHTIEN,0,',','.') }} VNĐ</td>
                        <td>{{ $order->TRANGTHAI }}</td>
                        <td>
                        <div class="row-actions">
                            <button class="btn-soft btn-detail" data-id="{{ $order->MADONHANG }}">
                            <i class="fas fa-info-circle"></i> Chi Tiết
                            </button>
                            @if($order->TRANGTHAI === 'Chờ xử lý')
                            <form method="POST" action="{{ route('customer.orders.cancel', $order->MADONHANG) }}" class="form-cancel-order" data-order-id="{{ $order->MADONHANG }}">
                                @csrf
                                <button type="submit" class="btn-soft btn-danger-soft">
                                <i class="fas fa-times"></i> Hủy Đơn
                                </button>
                            </form>
                            @endif
                        </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">Bạn chưa có đơn hàng nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>



        </section>
    </main>

    {{--  Modal chi tiết --}}
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết đơn hàng <span id="md_id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Ngày đặt:</strong> <span id="md_date"></span></div>
                    <div class="mb-2"><strong>Ngày giao:</strong> <span id="md_delivery"></span></div>
                    <div class="mb-2"><strong>Trạng thái:</strong> <span id="md_status"></span></div>
                    <div class="mb-2"><strong>Tổng số lượng:</strong> <span id="md_total_qty"></span></div>
                    <div class="mb-2"><strong>Tổng thành tiền:</strong> <span id="md_total"></span></div>
                    <div class="mb-2"><strong>Địa chỉ giao hàng:</strong> <span id="md_address"></span></div>
                    <div class="mb-2"><strong>Hình thức thanh toán:</strong> <span id="md_payment"></span></div>

                    <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã SP</th>
                            <th>Tên SP</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection
