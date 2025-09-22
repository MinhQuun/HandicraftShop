@extends('layouts.admin')
@section('title','Bảng điều khiển')

@section('content')
<section class="page-header">
  <span class="kicker">Quản trị hệ thống</span>
  <h1 class="title">Bảng điều khiển</h1>
  <p class="muted">Tổng quan nhanh & lối tắt tới các khu vực chính.</p>
</section>

<div class="row g-3 mb-4 stat-row">
  {{-- Tổng người dùng --}}
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-people"></i></div>
      <div>
        <div class="n">{{ $counts['total'] ?? 0 }}</div>
        <div class="t">Tổng người dùng</div>
      </div>
      <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Chi tiết</a>
    </div>
  </div>

  {{-- Admin --}}
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-shield-lock"></i></div>
      <div>
        <div class="n">{{ $counts['admin'] ?? 0 }}</div>
        <div class="t">Admin</div>
      </div>
      <a href="{{ route('admin.users.index',['role'=>'admin']) }}" class="btn btn-ghost">Xem</a>
    </div>
  </div>

  {{-- Nhân viên --}}
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-person-badge"></i></div>
      <div>
        <div class="n">{{ $counts['nhanvien'] ?? 0 }}</div>
        <div class="t">Nhân viên</div>
      </div>
      <a href="{{ route('admin.users.index',['role'=>'nhanvien']) }}" class="btn btn-ghost">Xem</a>
    </div>
  </div>

  {{-- Khách hàng --}}
  <div class="col-md-6 col-lg-3">
    <div class="stat-card">
      <div class="icon"><i class="bi bi-person"></i></div>
      <div>
        <div class="n">{{ $counts['khachhang'] ?? 0 }}</div>
        <div class="t">Khách hàng</div>
      </div>
      <a href="{{ route('admin.users.index',['role'=>'khachhang']) }}" class="btn btn-ghost">Xem</a>
    </div>
  </div>
</div>

<div class="card quick-links">
  <div class="card-body">
    <h5 class="mb-3">Tác vụ nhanh</h5>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.users.index') }}" class="chip"><i class="bi bi-person-plus me-1"></i>Thêm người dùng</a>
      <a href="{{ route('admin.users.index') }}" class="chip"><i class="bi bi-shield-check me-1"></i>Phân quyền</a>
      <a href="{{ route('home') }}" class="chip"><i class="bi bi-window-sidebar me-1"></i>Trang khách</a>
    </div>
  </div>
</div>
@endsection
