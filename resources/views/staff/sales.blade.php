@extends('layouts.staff')
@section('title', 'Báo cáo Doanh thu')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/staff-sales.css') }}">
@endpush

@section('content')
<section class="page-header">
    <span class="kicker">Nhân viên</span>
    <h1 class="title">Báo cáo Doanh thu</h1>
    <p class="muted">Xem báo cáo doanh thu, vốn và lợi nhuận theo thời gian</p>
</section>

<div id="flash"
    data-success="{{ session('success') }}"
    data-error="{{ session('error') }}"
    data-info="{{ session('info') }}"
    data-warning="{{ session('warning') }}">
</div>

{{-- Biểu đồ tổng quan --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="m-0">Biểu đồ Doanh thu, Vốn và Lợi nhuận</h5>
            <div class="text-muted small">Thời gian: <strong>{{ $mode == 'year' ? "$start_year → $end_year" : ($mode == 'month' ? $year : ($mode == 'day' ? "$year-$month" : "$start_date → $end_date")) }}</strong></div>
        </div>
        <canvas id="revenueChart" height="120"></canvas>
    </div>
</div>

{{-- Form lọc --}}
<div class="card products-filter mb-3">
    <div class="card-body">
        <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $mode == 'year' ? 'active' : '' }}" id="year-tab" data-bs-toggle="tab" href="#year" role="tab">Thống kê theo năm</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $mode == 'month' ? 'active' : '' }}" id="month-tab" data-bs-toggle="tab" href="#month" role="tab">Thống kê trong năm</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $mode == 'day' ? 'active' : '' }}" id="day-tab" data-bs-toggle="tab" href="#day" role="tab">Thống kê trong tháng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $mode == 'custom' ? 'active' : '' }}" id="custom-tab" data-bs-toggle="tab" href="#custom" role="tab">Thống kê từ ngày đến ngày</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Thống kê theo năm -->
            <div class="tab-pane fade {{ $mode == 'year' ? 'show active' : '' }}" id="year" role="tabpanel">
                <form method="GET" class="row g-2 align-items-end" action="{{ route('staff.reports.sales') }}">
                    <input type="hidden" name="mode" value="year">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Năm bắt đầu</label>
                        <input type="number" name="start_year" class="form-control" value="{{ $start_year }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Năm kết thúc</label>
                        <input type="number" name="end_year" class="form-control" value="{{ $end_year }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-primary" type="submit">Lọc</button>
                        <a href="{{ route('staff.reports.sales', ['mode' => 'year']) }}" class="btn btn-outline-secondary">Xoá lọc</a>
                    </div>
                </form>
            </div>

            <!-- Thống kê trong năm (month) -->
            <div class="tab-pane fade {{ $mode == 'month' ? 'show active' : '' }}" id="month" role="tabpanel">
                <form method="GET" class="row g-2 align-items-end" action="{{ route('staff.reports.sales') }}">
                    <input type="hidden" name="mode" value="month">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Năm</label>
                        <input type="number" name="year" class="form-control" value="{{ $year }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-primary" type="submit">Lọc</button>
                        <a href="{{ route('staff.reports.sales', ['mode' => 'month']) }}" class="btn btn-outline-secondary">Xoá lọc</a>
                    </div>
                </form>
            </div>

            <!-- Thống kê trong tháng (day) -->
            <div class="tab-pane fade {{ $mode == 'day' ? 'show active' : '' }}" id="day" role="tabpanel">
                <form method="GET" class="row g-2 align-items-end" action="{{ route('staff.reports.sales') }}">
                    <input type="hidden" name="mode" value="day">
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Năm</label>
                        <input type="number" name="year" class="form-control" value="{{ $year }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Tháng</label>
                        <input type="number" name="month" class="form-control" value="{{ $month }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-primary" type="submit">Lọc</button>
                        <a href="{{ route('staff.reports.sales', ['mode' => 'day']) }}" class="btn btn-outline-secondary">Xoá lọc</a>
                    </div>
                </form>
            </div>

            <!-- Thống kê từ ngày đến ngày (custom) -->
            <div class="tab-pane fade {{ $mode == 'custom' ? 'show active' : '' }}" id="custom" role="tabpanel">
                <form method="GET" class="row g-2 align-items-end" action="{{ route('staff.reports.sales') }}">
                    <input type="hidden" name="mode" value="custom">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Ngày bắt đầu</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Ngày kết thúc</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-primary" type="submit">Lọc</button>
                        <a href="{{ route('staff.reports.sales', ['mode' => 'custom']) }}" class="btn btn-outline-secondary">Xoá lọc</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Bảng danh sách doanh thu --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0">Báo cáo Doanh thu</h5>
        <div class="text-muted small">Hiển thị {{ count($tableData) }} bản ghi</div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover products-table">
            <thead>
                <tr>
                    <th style="width:15%;">{{ $mode == 'year' ? 'Năm' : ($mode == 'month' ? 'Tháng' : ($mode == 'day' ? 'Ngày' : 'Ngày')) }}</th>
                    <th style="width:28%;">Vốn</th>
                    <th style="width:28%;">Doanh thu</th>
                    <th style="width:28%;">Lợi nhuận</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tableData as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ number_format($row['cost']) }}đ</td>
                    <td>{{ number_format($row['revenue']) }}đ</td>
                    <td>{{ number_format($row['profit']) }}đ</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Top sản phẩm và khách hàng --}}
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Top sản phẩm</h5>
                <div class="text-muted small">Hiển thị {{ $topProducts->count() }} sản phẩm</div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover products-table">
                    <thead>
                        <tr>
                            <th style="width:40%;">Sản phẩm</th>
                            <th style="width:30%;">Số lượng</th>
                            <th style="width:30%;">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $p)
                        <tr>
                            <td>{{ $p->TENSANPHAM }}</td>
                            <td>{{ $p->total_qty }}</td>
                            <td>{{ number_format($p->revenue) }}đ</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Top khách hàng</h5>
                <div class="text-muted small">Hiển thị {{ $topCustomers->count() }} khách hàng</div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover products-table">
                    <thead>
                        <tr>
                            <th style="width:40%;">Khách hàng</th>
                            <th style="width:30%;">Số lượng</th>
                            <th style="width:30%;">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $c)
                        <tr>
                            <td>{{ $c->HOTEN }}</td>
                            <td>{{ $c->total_qty }}</td>
                            <td>{{ number_format($c->revenue) }}đ</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.REVENUE_CHART = {
    labels: @json($chartLabels),
    revenues: @json($chartRevenues),
    costs: @json($chartCosts),
    profits: @json($chartProfits)
};
</script>
<script src="{{ asset('js/staff-sales.js') }}"></script>
@endpush