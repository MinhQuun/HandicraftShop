<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>@yield('title','HandicraftShop')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Roboto+Slab&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/cdbcf8b89b.js" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  @stack('styles')
 
</head>
<body>


  @include('partials.header')
  @include('partials.auth-modal')


  <main class="container-fluid p-0 @yield('main_class')">
    @yield('content')
  </main>

  
  @include('partials.footer')


  <script src="{{ asset('js/script.js') }}"></script>
  <script src="{{ asset('js/auth.js') }}"></script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Toast container ở góc trên bên phải -->
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
    @if(session('success'))
      <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            {{ session('success') }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    @endif

    @if(session('error'))
      <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            {{ session('error') }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    @endif
  </div>

  @stack('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Toast thành công
      var successToastEl = document.getElementById('successToast');
      if (successToastEl) {
        var toast = new bootstrap.Toast(successToastEl, { delay: 3000 }); // 3000ms = 3s
        toast.show();
      }

      // Toast lỗi
      var errorToastEl = document.getElementById('errorToast');
      if (errorToastEl) {
        var toastError = new bootstrap.Toast(errorToastEl, { delay: 3000 });
        toastError.show();
      }
    });
  </script>
</body>
</html>
