@extends('layouts.main')

@section('title', 'Về chúng tôi | Handicraft Shop')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')
<main class="about-page">
  {{-- HERO --}}
  <section class="about-hero">
    <span class="kicker">Handicraft Shop • Nhóm 10</span>
    <h1>Về Chúng Tôi</h1>
    <p>
      Nền tảng mua sắm các sản phẩm <strong>mỹ nghệ thủ công Việt Nam</strong>,
      phát triển trong học phần <em>Lập trình mã nguồn mở (PHP &amp; MySQL)</em> – GVHD: ThS. Nguyễn Khắc Duy.
    </p>

    {{-- (Tuỳ chọn) Dãy số liệu nhanh --}}
    <div class="stats">
      <div class="stat"><span class="n">2</span><span class="t">Danh mục cấp</span></div>
      <div class="stat"><span class="n">50+</span><span class="t">Sản phẩm demo</span></div>
      <div class="stat"><span class="n">100%</span><span class="t">Responsive</span></div>
    </div>
  </section>

  {{-- GRID 2 cột --}}
  <section class="about-grid">
    {{-- Cột trái --}}
    <article class="about-card">
      <h2>Đề tài</h2>
      <div class="divider"></div>
      <p>
        <strong>Website bán hàng Mỹ Nghệ</strong>: xây dựng một cửa hàng trực tuyến cho các sản phẩm thủ công
        (mây, tre, cói, lục bình…), cho phép người dùng duyệt danh mục, xem chi tiết, thêm giỏ hàng và đặt mua.
      </p>

      <h2 style="margin-top:18px;">Mô tả</h2>
      <div class="divider"></div>
      <p>
        Hệ thống hướng đến trải nghiệm đơn giản, rõ ràng và phù hợp với đặc thù sản phẩm thủ công: trình bày đẹp,
        phân loại theo <em>Danh mục</em> và <em>Chất liệu</em>, tối ưu tìm kiếm và hiển thị hình ảnh.
        Bên quản trị có thể quản lý sản phẩm, đơn hàng và khách hàng.
      </p>

      <h2 style="margin-top:18px;">Chức năng chính</h2>
      <div class="divider"></div>
      <ul class="about-list">
        <li>Danh mục – Loại (2 cấp): duyệt theo Chất liệu, Lồng bàn, Giỏ…, tìm kiếm sản phẩm.</li>
        <li>Giỏ hàng, đặt hàng (COD/ghi nhận đơn), theo dõi trạng thái đơn.</li>
        <li>Trang quản trị: quản lý danh mục, loại, sản phẩm, đơn hàng, khách hàng.</li>
        <li>Giao diện responsive, icon Font Awesome, jQuery hỗ trợ tương tác cơ bản.</li>
      </ul>

      <h2 style="margin-top:18px;">Công nghệ sử dụng</h2>
      <div class="divider"></div>
      <ul class="tech-chips">
        <li>Laravel</li><li>PHP</li><li>MySQL</li><li>Blade</li><li>Bootstrap 5</li><li>Font Awesome</li>
      </ul>
    </article>

    {{-- Cột phải --}}
    <aside class="about-card">
      <h2>Thành viên nhóm</h2>
      <div class="divider"></div>
      <ul class="team-list">
        <li>Võ Nguyễn Minh Quân - 2001223952 (Nhóm trưởng)</li>
        <li>Trương Quang Như Đoan - 2001220985</li>
        <li>Lê Trần Ngọc Yến – 2001226134</li>
        <li>Phạm Hồ Thúy Vy – 2001225958</li>
      </ul>

      <h2 style="margin-top:18px;">Liên hệ</h2>
      <div class="divider"></div>
      <p class="contact-note">
        Mọi góp ý cho website xin gửi qua trang
        <a href="{{ route('contact') }}">Liên hệ</a>. Chúng mình luôn trân trọng ý kiến để hoàn thiện dự án tốt hơn.
      </p>
    </aside>
  </section>
</main>
@endsection
