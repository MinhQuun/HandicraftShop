@extends('layouts.staff')
@section('title','Bảng điều khiển - Nhân viên')

@section('content')
<section class="page-header">
  <span class="kicker">Nhân viên</span>
  <h1 class="title">Bảng điều khiển</h1>
  <p class="muted">Tổng quan nhanh và lối tắt thao tác.</p>
</section>

<div class="row g-3 mb-4 stat-row">
  <div class="col-md-6 col-lg-3">
    <div class="s-card">
      <div class="icon"><i class="bi bi-box-seam"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['products'] ?? 0 }}</div>
        <div class="t">Sản phẩm</div>
      </div>
      <a href="{{ route('staff.products.index') }}" class="btn btn-ghost">Chi tiết</a>
    </div>
  </div>

  <div class="col-md-6 col-lg-3">
    <div class="s-card">
      <div class="icon"><i class="bi bi-collection"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['suppliers'] ?? 0 }}</div>
        <div class="t">Nhà cung cấp</div>
      </div>
      <a href="{{ route('staff.suppliers.index') }}" class="btn btn-ghost">Chi tiết</a>
    </div>
  </div>

  <div class="col-md-6 col-lg-3">
    <div class="s-card">
      <div class="icon"><i class="bi bi-receipt"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['orders_pending'] ?? 0 }}</div>
        <div class="t">Đơn chờ xử lý</div>
      </div>
      <a href="{{ route('staff.orders.index') }}" class="btn btn-ghost">Xem</a>
    </div>
  </div>

  <div class="col-md-6 col-lg-3">
    <div class="s-card">
      <div class="icon"><i class="bi bi-people"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['customers'] ?? 0 }}</div>
        <div class="t">Khách hàng</div>
      </div>
      <a href="{{ route('staff.customers.index') }}" class="btn btn-ghost">Xem</a>
    </div>
  </div>
</div>

<div class="card quick-links">
  <div class="card-body">
    <h5 class="mb-3">Tác vụ nhanh</h5>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('staff.products.index') }}" class="chip"><i class="bi bi-plus-circle me-1"></i>Thêm sản phẩm</a>
      <a href="{{ route('staff.orders.index') }}" class="chip"><i class="bi bi-clipboard2-check me-1"></i>Xử lý đơn hàng</a>
      <a href="{{ route('staff.promotions.index') }}" class="chip"><i class="bi bi-gift me-1"></i>Tạo khuyến mãi</a>
    </div>
  </div>
</div>
@endsection
