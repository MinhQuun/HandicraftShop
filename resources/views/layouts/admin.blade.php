<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Admin')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  {{-- CSS riêng --}}
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
  @stack('styles')
</head>
<body class="admin-body">

  {{-- TOPBAR --}}
  <nav class="admin-topbar navbar navbar-expand-lg">
    <div class="container-fluid">
      <button class="btn btn-outline-light d-lg-none me-2" id="btnSidebar">
        <i class="bi bi-list"></i>
      </button>

      <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
        <i class="bi bi-speedometer2 me-1"></i> Admin Panel
      </a>

      <div class="ms-auto d-flex align-items-center">
        {{-- user dropdown (chỉ Logout) --}}
        <div class="dropdown">
          <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i>
            {{ Auth::user()->name ?? 'Tài khoản' }}
          </button>
          <div class="dropdown-menu dropdown-menu-end">
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

  <div class="admin-wrapper">
    {{-- SIDEBAR --}}
    <aside id="adminSidebar" class="admin-sidebar">
      <div class="px-3 py-3">
        <div class="text-muted small mb-2">Điều hướng</div>
        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                href="{{ route('admin.dashboard') }}">
              <i class="bi bi-grid me-2"></i> Tổng quan
            </a>
          </li>
          <li class="nav-item mt-2 text-muted small">Quản trị</li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                href="{{ route('admin.users.index') }}">
              <i class="bi bi-people me-2"></i> Người dùng
            </a>
          </li>
        </ul>
      </div>
    </aside>

    {{-- MAIN --}}
    <main class="admin-main">
      @yield('content')
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('btnSidebar')?.addEventListener('click', () => {
      document.getElementById('adminSidebar')?.classList.toggle('open');
    });
  </script>
  @stack('scripts')
</body>
</html>
