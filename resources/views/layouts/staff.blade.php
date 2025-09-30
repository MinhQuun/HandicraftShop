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
          {{-- TỔNG QUAN --}}
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"
              href="{{ route('staff.dashboard') }}">
              <i class="bi bi-grid me-2"></i> Tổng quan
            </a>
          </li>

          {{-- SẢN PHẨM --}}
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
              @if(($badges['low_stock'] ?? 0) > 0)
                <span class="badge text-bg-warning ms-2">{{ $badges['low_stock'] }}</span>
              @endif
            </a>
          </li>

          {{-- KHUYẾN MÃI --}}
          <li class="nav-item mt-2 text-muted small">Khuyến mãi</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.promotions.*') ? 'active' : '' }}"
              href="{{ route('staff.promotions.index') }}">
              <i class="bi bi-gift me-2"></i> Khuyến mãi
            </a>
          </li>

          {{-- NHẬP HÀNG (PHIẾU NHẬP) --}}
          <li class="nav-item mt-2 text-muted small">Nhập hàng</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.receipts.*') && !request()->routeIs('staff.receipts.create') ? 'active' : '' }}"
              href="{{ route('staff.receipts.index') }}">
              <i class="bi bi-download me-2"></i> Danh sách phiếu nhập
              @if(($badges['pn_draft'] ?? 0) > 0)
                <span class="badge text-bg-secondary ms-2">{{ $badges['pn_draft'] }}</span>
              @endif
            </a>
          </li>

          {{-- BÁN HÀNG (ĐƠN + PHIẾU XUẤT) --}}
          <li class="nav-item mt-2 text-muted small">Bán hàng</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.orders.*') ? 'active' : '' }}"
              href="{{ route('staff.orders.index') }}">
              <i class="bi bi-receipt me-2"></i> Đơn hàng
              @if(($badges['orders_pending'] ?? 0) > 0)
                <span class="badge text-bg-danger ms-2">{{ $badges['orders_pending'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.issues.*') && !request()->routeIs('staff.issues.create') ? 'active' : '' }}"
              href="{{ route('staff.issues.index') }}">
              <i class="bi bi-upload me-2"></i> Danh sách phiếu xuất
              @if(($badges['px_draft'] ?? 0) > 0)
                <span class="badge text-bg-secondary ms-2">{{ $badges['px_draft'] }}</span>
              @endif
            </a>
          </li>

          {{-- KHÁCH HÀNG / Hộp thư ý kiến --}}
          <li class="nav-item mt-2 text-muted small">Khách hàng</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.customers.*') ? 'active' : '' }}"
              href="{{ route('staff.customers.index') }}">
              <i class="bi bi-people me-2"></i> Khách hàng
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.reply.*') ? 'active' : '' }}"
              href="{{ route('staff.reviews.index') }}">
              <i class="bi bi-chat-square-heart me-2"></i> Hộp thư ý kiến
            </a>
          </li>

          {{-- CẤU HÌNH --}}
          <li class="nav-item mt-2 text-muted small">Cấu hình</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.payments.*') ? 'active' : '' }}"
              href="{{ route('staff.payments.index') }}">
              <i class="bi bi-credit-card me-2"></i> Hình thức thanh toán
            </a>
          </li>

          {{-- THỐNG KÊ (để bạn code sau) --}}
          <li class="nav-item mt-2 text-muted small">Thống kê & Báo cáo</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.reports.inout') ? 'active' : '' }}"
              href="{{ route('staff.reports.inout') }}">
              <i class="bi bi-arrow-left-right me-2"></i> Nhập – Xuất – Tồn
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.reports.inventory') ? 'active' : '' }}"
              href="{{ route('staff.reports.inventory') }}">
              <i class="bi bi-clipboard-data me-2"></i> Tồn kho hiện tại
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.reports.sales') ? 'active' : '' }}"
              href="{{ route('staff.reports.sales') }}">
              <i class="bi bi-bar-chart me-2"></i> Doanh số bán hàng
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.reports.lowstock') ? 'active' : '' }}"
              href="{{ route('staff.reports.lowstock') }}">
              <i class="bi bi-exclamation-triangle me-2"></i> Hết hàng / Sắp hết
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('staff.reports.top') ? 'active' : '' }}"
              href="{{ route('staff.reports.top') }}">
              <i class="bi bi-trophy me-2"></i> Top sản phẩm / khách hàng
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

  <div id="sidebarOverlay" aria-hidden="true"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('btnSidebar')?.addEventListener('click', () => {
      document.getElementById('staffSidebar')?.classList.toggle('open');
    });
  </script>
  <script src="{{ asset('js/staff.js') }}"></script>
  @stack('scripts')
</body>
</html>
