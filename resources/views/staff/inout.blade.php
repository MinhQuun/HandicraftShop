@extends('layouts.staff')
@section('title','Báo cáo Nhập – Xuất – Tồn')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/staff-inout.css') }}">
@endpush

@section('content')
<section class="page-header">
    <span class="kicker">Nhân viên</span>
    <h1 class="title">Báo cáo Nhập – Xuất – Tồn</h1>
    <p class="muted">Xem báo cáo theo khoảng thời gian, lọc và xuất dữ liệu.</p>
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
            <h5 class="m-0">Biểu đồ Top sản phẩm theo biến động (Nhập / Xuất / Tồn)</h5>
            <div class="text-muted small">Thời gian: <strong>{{ $filters['start_date'] }} → {{ $filters['end_date'] }}</strong></div>
        </div>
        <canvas id="inoutChart" height="120"></canvas>
    </div>
</div>

{{-- Form lọc --}}
<div class="card products-filter mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="{{ route('staff.reports.inout') }}">
            <div class="col-md-3">
                <label class="form-label small mb-1">Ngày bắt đầu</label>
                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Ngày kết thúc</label>
                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Chọn sản phẩm</label>
                <select name="q" class="form-select" id="productSelect" data-placeholder="Chọn sản phẩm">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach(DB::table('SANPHAM')->orderBy('TENSANPHAM')->get() as $p)
                        <option value="{{ $p->TENSANPHAM }}" {{ $filters['q'] == $p->TENSANPHAM ? 'selected' : '' }}>
                            {{ $p->TENSANPHAM }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary">Lọc</button>
                <a href="{{ route('staff.reports.inout') }}" class="btn btn-outline-secondary">Xoá lọc</a>
            </div>
        </form>
    </div>
</div>

{{-- Bảng danh sách --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0">Danh sách sản phẩm</h5>
        <div class="text-muted small">Hiển thị {{ $products->total() }} sản phẩm</div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover products-table">
            <thead>
                <tr>
                    <th style="width:90px">Mã</th>
                    <th style="width:60px">Hình</th>
                    <th style="min-width:240px">Tên sản phẩm</th>
                    <th style="width:18%;">Giá nhập</th>
                    <th style="width:12%">Tồn đầu</th>
                    <th style="width:12%">Nhập trong kỳ</th>
                    <th style="width:12%">Xuất trong kỳ</th>
                    <th style="width:12%">Tồn cuối</th>
                    <th style="width:18%;">Giá bán</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                <tr class="product-row"
                    data-masp="{{ $r->MASANPHAM }}"
                    data-ten="{{ $r->TENSANPHAM }}"
                    data-hinh="{{ $r->HINHANH ? asset('assets/images/' . $r->HINHANH) : '' }}"
                    data-loai="{{ $r->TENLOAI ?? '' }}"
                    data-ncc="{{ $r->NHACUNGCAP ?? '' }}"
                    data-opening="{{ $r->opening }}"
                    data-in="{{ $r->in }}"
                    data-out="{{ $r->out }}"
                    data-closing="{{ $r->closing }}"
                    data-gianhap="{{ $r->GIANHAP }}"
                    data-price="{{ $r->GIABAN }}"
                    data-mota="{{ $r->MOTA ?? '' }}"
                >
                    <td>{{ $r->MASANPHAM }}</td>
                    <td>
                        @if($r->HINHANH)
                            <img src="{{ asset('assets/images/' . $r->HINHANH) }}" alt="{{ $r->TENSANPHAM }}" 
                                style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-truncate" title="{{ $r->TENSANPHAM }}">{{ $r->TENSANPHAM }}</td>
                    <td>{{ number_format($r->GIANHAP ?? 0,0,',','.') }} ₫</td>
                    <td><span class="badge stock-ok">{{ $r->opening }}</span></td>
                    <td><span class="badge stock-warn">{{ $r->in }}</span></td>
                    <td><span class="badge stock-bad">{{ $r->out }}</span></td>
                    <td><strong>{{ $r->closing }}</strong></td>
                    <td>{{ number_format($r->GIABAN ?? 0,0,',','.') }} ₫</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
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
          <div class="col-md-4 text-center">
            <img id="modalProductImage" src="" alt="" class="img-fluid rounded mb-2">
          </div>
          <div class="col-md-8">
            <h5 id="modalProductName"></h5>
            <p><strong>Mã sản phẩm:</strong> <span id="modalProductCode"></span></p>
            <p><strong>Loại:</strong> <span id="modalProductType"></span></p>
            <p><strong>Nhà cung cấp:</strong> <span id="modalProductSupplier"></span></p>
            <p><strong>Tồn đầu:</strong> <span id="modalProductOpening"></span></p>
            <p><strong>Nhập trong kỳ:</strong> <span id="modalProductIn"></span></p>
            <p><strong>Xuất trong kỳ:</strong> <span id="modalProductOut"></span></p>
            <p><strong>Tồn cuối:</strong> <span id="modalProductClosing"></span></p>
            <p><strong>Giá nhập:</strong> <span id="modalProductGianhap"></span> ₫</p>
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
<script>
window.INOUT_CHART = {!! json_encode($chart) !!};
</script>
<script src="{{ asset('js/staff-inout.js') }}"></script>


@endpush
