<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Staff')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- SweetAlert2 + Bootstrap --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- CSS riêng cho nhân viên --}}
  <link rel="stylesheet" href="{{ asset('css/staff.css') }}">
  @stack('styles')
</head>
<body class="staff-body">
  {{-- TOPBAR --}}
  <nav class="staff-topbar navbar navbar-expand-lg">
    <div class="container-fluid">
      <button class="btn btn-outline-light d-lg-none me-2" id="btnSidebar">
        <i class="bi bi-list"></i>
      </button>

      <a class="navbar-brand fw-bold" href="{{ route('staff.dashboard') }}">
        <i class="bi bi-person-badge me-1"></i> Staff Panel
      </a>

      <div class="ms-auto d-flex align-items-center">
        <div class="dropdown">
          <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i>
            {{ Auth::user()->name ?? 'Tài khoản' }}
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            @can('view-admin')
              <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2 me-1"></i> Admin
              </a>
              <div class="dropdown-divider"></div>
            @endcan
            <form action="{{ route('logout') }}" method="post" class="px-3 py-1">
              @csrf
              <button class="btn btn-danger w-100">
                <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="staff-wrapper">
    {{-- SIDEBAR --}}
    <aside id="staffSidebar" class="staff-sidebar">
      <div class="px-3 py-3">
        <div class="text-muted small mb-2">Điều hướng</div>
        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"
                href="{{ route('staff.dashboard') }}">
              <i class="bi bi-grid me-2"></i> Tổng quan
            </a>
          </li>

          <li class="nav-item mt-2 text-muted small">Sản phẩm</li>
          
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.suppliers.*') ? 'active' : '' }}"
                href="{{ route('staff.suppliers.index') }}">
              <i class="bi bi-truck me-2"></i> Nhà cung cấp
            </a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.products.*') ? 'active' : '' }}"
                href="{{ route('staff.products.index') }}">
              <i class="bi bi-box-seam me-2"></i> Sản phẩm
            </a>
          </li>
          

          <li class="nav-item mt-2 text-muted small">Khuyến mãi</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.promotions.*') ? 'active' : '' }}"
                href="{{ route('staff.promotions.index') }}">
              <i class="bi bi-gift me-2"></i> Khuyến mãi
            </a>
          </li>

          <li class="nav-item mt-2 text-muted small">Bán hàng</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.orders.*') ? 'active' : '' }}"
                href="{{ route('staff.orders.index') }}">
              <i class="bi bi-receipt me-2"></i> Đơn hàng
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.customers.*') ? 'active' : '' }}"
                href="{{ route('staff.customers.index') }}">
              <i class="bi bi-people me-2"></i> Khách hàng
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.reviews.*') ? 'active' : '' }}"
                href="{{ route('staff.reviews.index') }}">
              <i class="bi bi-chat-square-heart me-2"></i> Đánh giá
            </a>
          </li>

          <li class="nav-item mt-2 text-muted small">Cấu hình</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.payments.*') ? 'active' : '' }}"
                href="{{ route('staff.payments.index') }}">
              <i class="bi bi-credit-card me-2"></i> Hình thức thanh toán
            </a>
          </li>
        </ul>
      </div>
    </aside>

    {{-- MAIN --}}
    <main class="staff-main">
      {{-- Flash cho SweetAlert2 --}}
      <div id="flash-data"
            data-success="{{ session('success') }}"
            data-error="{{ session('error') }}"></div>

      @yield('content')
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('btnSidebar')?.addEventListener('click', () => {
      document.getElementById('staffSidebar')?.classList.toggle('open');
    });
  </script>
  @stack('scripts')
</body>
</html>
