@extends('layouts.nhanvien')

@section('title', 'Nhan vien Dashboard')

@section('content')
  <h1 class="mb-3">Xin chào, nhan vien nha!</h1>
  <p class="text-muted">Đây là trang nhan vien.</p>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Người dùng</h5>
          <p class="card-text">Quản lý tài khoản, phân quyền.</p>
          <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">Đi tới</a>
        </div>
      </div>
    </div>
    {{-- thêm các card: Đơn hàng, Sản phẩm, Khuyến mãi... --}}
  </div>
@endsection
