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
    <a href="{{ route('staff.suppliers.index') }}" class="s-card text-decoration-none">
      <div class="icon"><i class="bi bi-collection"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['suppliers'] ?? 0 }}</div>
        <div class="t">Nhà cung cấp</div>
      </div>
    </a>
  </div>

  <div class="col-md-6 col-lg-3">
    <a href="{{ route('staff.products.index') }}" class="s-card text-decoration-none">
      <div class="icon"><i class="bi bi-box-seam"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['products'] ?? 0 }}</div>
        <div class="t">Sản phẩm</div>
      </div>
    </a>
  </div>

  <div class="col-md-6 col-lg-3">
    <a href="{{ route('staff.orders.index') }}" class="s-card text-decoration-none">
      <div class="icon"><i class="bi bi-receipt"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['orders_pending'] ?? 0 }}</div>
        <div class="t">Đơn chờ xử lý</div>
      </div>
    </a>
  </div>

  <div class="col-md-6 col-lg-3">
    <a href="{{ route('staff.customers.index') }}" class="s-card text-decoration-none">
      <div class="icon"><i class="bi bi-people"></i></div>
      <div class="meta">
        <div class="n">{{ $stats['customers'] ?? 0 }}</div>
        <div class="t">Khách hàng</div>
      </div>
    </a>
  </div>
</div>

@php
  $lowStock = $badges['low_stock'] ?? 0;
  $ordersPending = $stats['orders_pending'] ?? 0;
@endphp

<div class="card quick-links">
  <div class="card-body">
    <h5 class="mb-3">Tác vụ nhanh</h5>
    <div class="d-flex flex-wrap gap-2">
      {{-- Tạo nhanh / hành động chủ động --}}
      <a href="{{ route('staff.products.index') }}?action=create" class="chip">
        <i class="bi bi-plus-circle me-1"></i> Thêm sản phẩm
      </a>
      <a href="{{ route('staff.receipts.create') }}" class="chip">
        <i class="bi bi-download me-1"></i> Tạo phiếu nhập
      </a>
      <a href="{{ route('staff.issues.create') }}" class="chip">
        <i class="bi bi-upload me-1"></i> Tạo phiếu xuất
      </a>
      <a href="{{ route('staff.promotions.index') }}?action=create" class="chip">
        <i class="bi bi-gift me-1"></i> Tạo khuyến mãi
      </a>
      <a href="{{ route('staff.customers.index') }}?action=create" class="chip">
        <i class="bi bi-person-plus me-1"></i> Thêm khách hàng
      </a>

      {{-- Hàng đợi / cảnh báo có ngữ cảnh (chỉ hiện khi > 0) --}}
      @if($ordersPending > 0)
        <a href="{{ route('staff.orders.index') }}?status=Chờ xử lý" class="chip">
          <i class="bi bi-clipboard2-check me-1"></i>
          Đơn chờ xử lý
          <span class="badge text-bg-danger ms-1">{{ $ordersPending }}</span>
        </a>
      @endif

      @if($lowStock > 0)
        <a href="{{ route('staff.reports.lowstock') }}" class="chip">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Sắp hết hàng
          <span class="badge text-bg-warning ms-1">{{ $lowStock }}</span>
        </a>
      @endif
    </div>
  </div>
</div>
@endsection
