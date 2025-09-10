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


  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>


  @include('partials.header')


  <main class="container-fluid p-0 @yield('main_class')">
    @yield('content')
  </main>


  @include('partials.footer')


  <script src="{{ asset('js/script.js') }}"></script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
