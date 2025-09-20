<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Admin')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- CSS riêng cho admin --}}
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
  {{-- Bootstrap (nếu cần) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Nhan vien Panel</a>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-light btn-sm" href="{{ route('home') }}">Về trang khách</a>
        <form action="{{ route('logout') }}" method="post" class="d-inline">
          @csrf
          <button class="btn btn-danger btn-sm">Đăng xuất</button>
        </form>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <aside class="col-12 col-md-3 col-lg-2 p-3 border-end bg-white">
        {{-- Menu trái cho admin --}}
        <ul class="nav flex-column small">
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Tổng quan</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Người dùng</a></li>
          {{-- thêm các mục: đơn hàng, sản phẩm, khuyến mãi... --}}
        </ul>
      </aside>
      <main class="col p-4">
        @yield('content')
      </main>
    </div>
  </div>

  {{-- JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
