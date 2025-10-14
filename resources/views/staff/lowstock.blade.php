@extends('layouts.staff')
@section('title', 'Báo cáo Hết hàng / Sắp hết')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/staff-lowstock.css') }}">
<link rel="stylesheet" href="{{ asset('css/staff-reports.css') }}">
@endpush

@section('content')
<section class="page-header">
    <span class="kicker">Nhân viên</span>
    <h1 class="title">Báo cáo Hết hàng / Sắp hết</h1>
    <p class="muted">Xem báo cáo sản phẩm tồn kho thấp tại thời điểm hiện tại.</p>
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
            <h5 class="m-0">Biểu đồ Sản phẩm tồn kho</h5>
            <div class="text-muted small">Dữ liệu tại thời điểm hiện tại</div>
        </div>
        <canvas id="lowstockChart" height="120"></canvas>
    </div>
</div>

{{-- Thông báo thay vì form lọc --}}
<div class="card products-filter mb-3">
    <div class="card-body text-center py-4">
        <p class="text-muted">Danh sách và biểu đồ hiển thị tình trạng tồn kho tại thời điểm hiện tại.</p>
    </div>
</div>

{{-- Bảng danh sách --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0">Danh sách sản phẩm hết hàng / sắp hết</h5>
        <div class="text-muted small">Hiển thị {{ $products->total() }} sản phẩm</div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover products-table">
            <thead>
                <tr>
                    <th style="width:90px">Mã</th>
                    <th style="min-width:240px">Tên sản phẩm</th>
                    <th style="width:12%">Tồn kho</th>
                    <th style="width:12%">Tình trạng</th>
                    <th style="width:18%;">Giá bán</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $r)
                <tr class="product-row"
                    data-masp="{{ $r->MASANPHAM }}"
                    data-ten="{{ $r->TENSANPHAM }}"
                    data-stock="{{ $r->stock }}"
                    data-price="{{ $r->GIABAN }}"
                    data-mota="{{ $r->MOTA ?? '' }}"
                >
                    <td>{{ $r->MASANPHAM }}</td>
                    <td class="text-truncate" title="{{ $r->TENSANPHAM }}">{{ $r->TENSANPHAM }}</td>
                    <td><span class="badge stock-ok">{{ $r->stock }}</span></td>
                    <td><strong>{{ $r->stock <= 0 ? 'Hết hàng' : 'Sắp hết' }}</strong></td>
                    <td>{{ number_format($r->GIABAN ?? 0, 0, ',', '.') }} ₫</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Không có sản phẩm nào sắp hết hoặc hết hàng</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}
    @if ($products->lastPage() > 1)
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
        @if ($products->currentPage() > 1)
            <li class="page-item"><a class="page-link" href="{{ $products->url($products->currentPage() - 1) }}">Trước</a></li>
        @endif
        @for ($i = 1; $i <= $products->lastPage(); $i++)
            <li class="page-item {{ $i === $products->currentPage() ? 'active' : '' }}">
            <a class="page-link" href="{{ $products->url($i) }}">{{ $i }}</a>
            </li>
        @endfor
        @if ($products->currentPage() < $products->lastPage())
            <li class="page-item"><a class="page-link" href="{{ $products->url($products->currentPage() + 1) }}">Sau</a></li>
        @endif
        </ul>
    </nav>
    @endif
</div>

<!-- Modal chi tiết sản phẩm -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productDetailLabel">Chi tiết sản phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <h5 id="modalProductName"></h5>
            <p><strong>Mã sản phẩm:</strong> <span id="modalProductCode"></span></p>
            <p><strong>Tồn kho:</strong> <span id="modalProductStock"></span></p>
            <p><strong>Tình trạng:</strong> <span id="modalProductStatus"></span></p>
            <p><strong>Giá bán:</strong> <span id="modalProductPrice"></span> ₫</p>
            <p><strong>Mô tả:</strong> <span id="modalProductDesc"></span></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.LOWSTOCK_CHART = {!! json_encode($chart) !!};
</script>
<script src="{{ asset('js/staff-lowstock.js') }}"></script>
@endpush
