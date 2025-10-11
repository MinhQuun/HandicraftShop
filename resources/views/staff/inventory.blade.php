@extends('layouts.staff')
@section('title','Báo cáo Tồn kho hiện tại')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/staff-inventory.css') }}">
@endpush

@section('content')
<section class="page-header">
    <span class="kicker">Nhân viên</span>
    <h1 class="title">Báo cáo Tồn kho hiện tại</h1>
    <p class="muted">Xem danh sách sản phẩm đang còn trong kho, hiển thị số lượng tồn hiện tại.</p>
</section>

{{-- Form lọc --}}
<div class="card products-filter mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="{{ route('staff.reports.inventory') }}">
            <div class="col-md-4">
                <label class="form-label small mb-1">Tên / Loại / Nhà cung cấp</label>
                <input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary">Lọc</button>
                <a href="{{ route('staff.reports.inventory') }}" class="btn btn-outline-secondary">Xoá lọc</a>
            </div>
        </form>
    </div>
</div>

{{-- Bảng danh sách --}}
<div class="card">
    <div class="card-header">
        <h5 class="m-0">Danh sách sản phẩm</h5>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover products-table">
            <thead>
                <tr>
                    <th style="width:90px">Mã</th>
                    <th style="width:60px">Hình</th>
                    <th style="min-width:240px">Tên sản phẩm</th>
                    <th style="width:18%;">Loại</th>
                    <th style="width:15%">Tồn hiện tại</th>
                    <th style="width:18%;">Giá nhập</th>
                    <th style="width:18%;">Giá bán</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr class="product-row"
                    data-masp="{{ $p->MASANPHAM }}"
                    data-ten="{{ $p->TENSANPHAM }}"
                    data-hinh="{{ $p->HINHANH ? asset('assets/images/' . $p->HINHANH) : '' }}"
                    data-stock="{{ $p->SOLUONGTON }}"
                    data-price="{{ $p->GIABAN }}"
                    data-mota="{{ $p->MOTA ?? '' }}"
                    data-loai="{{ $p->TENLOAI ?? '' }}"
                    data-ncc="{{ $p->TENNHACUNGCAP }}"
                    data-cost="{{ $p->GIANHAP }}"
                >
                    <td>{{ $p->MASANPHAM }}</td>
                    <td>
                        @if($p->HINHANH)
                            <img src="{{ asset('assets/images/' . $p->HINHANH) }}" alt="{{ $p->TENSANPHAM }}" 
                                style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $p->TENSANPHAM }}</td>
                    <td>{{ $p->TENLOAI ?? '—' }}</td>
                    <td>
                        @if($p->SOLUONGTON < 10)
                            <span class="badge stock-bad">{{ $p->SOLUONGTON }}</span>
                        @else
                            <span class="badge stock-ok">{{ $p->SOLUONGTON }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($p->GIANHAP ?? 0,0,',','.') }} ₫</td>
                    <td>{{ number_format($p->GIABAN ?? 0,0,',','.') }} ₫</td>
                    
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
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

{{-- Modal chi tiết sản phẩm --}}
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
            <p><strong>Tồn hiện tại:</strong> <span id="modalProductStock"></span></p>
            <p><strong>Giá nhập:</strong> <span id="modalProductCost"></span> ₫</p>
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
<script src="{{ asset('js/staff-inventory.js') }}"></script>
@endpush
