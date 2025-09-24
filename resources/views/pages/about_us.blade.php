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
      <strong>Handicraft Shop</strong> là website học phần <em>Lập trình mã nguồn mở (PHP &amp; MySQL)</em>,
      tập trung giới thiệu và bán các sản phẩm <strong>mỹ nghệ thủ công Việt Nam</strong>. Mục tiêu của dự án là
      thực hành quy trình phát triển web đầy đủ: thiết kế CSDL, xây dựng tính năng, tối ưu giao diện và trải nghiệm.
      (GVHD: ThS. Nguyễn Khắc Duy)
    </p>

    {{-- Số liệu nhanh (chỉ-dấu trạng thái, không dùng % hay đếm “50+”) --}}
    <div class="stats">
      <div class="stat"><span class="n">3</span><span class="t">Phân hệ: Khách • Nhân viên • Admin</span></div>
      <div class="stat"><span class="n">v1.0</span><span class="t">Bản phát hành lớp học</span></div>
      <div class="stat"><span class="n">Đang phát triển</span><span class="t">Hoàn thiện &amp; mở rộng</span></div>
    </div>
  </section>

  {{-- GRID 2 cột --}}
  <section class="about-grid">
    {{-- Cột trái --}}
    <article class="about-card">
      <h2>Đề tài</h2>
      <div class="divider"></div>
      <p>
        <strong>Website bán hàng Mỹ Nghệ</strong>: xây dựng cửa hàng trực tuyến cho sản phẩm thủ công
        (mây, tre, cói, lục bình…). Người dùng có thể duyệt theo danh mục, xem chi tiết, thêm vào giỏ
        và đặt mua; dữ liệu đơn hàng được lưu để theo dõi xử lý.
      </p>

      <h2 style="margin-top:18px;">Mô tả</h2>
      <div class="divider"></div>
      <p>
        Kiến trúc theo <em>2 cấp danh mục</em> (Danh mục &rarr; Loại/Chất liệu) giúp tìm sản phẩm nhanh.
        Giao diện nhấn mạnh hình ảnh, bố cục rõ ràng, phù hợp đặc thù hàng thủ công. Phía quản trị có màn hình
        quản lý sản phẩm, đơn hàng, khách hàng; phía nhân viên có công cụ tác nghiệp (lọc, tìm, phân trang, modal).
      </p>

      <h2 style="margin-top:18px;">Chức năng chính</h2>
      <div class="divider"></div>
      <ul class="about-list">
        <li>Duyệt theo <strong>Danh mục &rarr; Loại/Chất liệu</strong>, tìm kiếm sản phẩm.</li>
        <li><strong>Giỏ hàng &amp; Đặt hàng</strong> (ghi nhận thông tin đơn, trạng thái).</li>
        <li><strong>Quản trị &amp; Nhân viên</strong>: CRUD danh mục/loại/sản phẩm, quản lý đơn, khách hàng.</li>
        <li><strong>Giao diện responsive</strong>, dùng Bootstrap 5, Font Awesome; tương tác cơ bản bằng JS.</li>
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
        Mọi góp ý cho website xin gửi qua trang <a href="{{ route('contact') }}">Liên hệ</a>.
        Nhóm luôn trân trọng ý kiến để hoàn thiện dự án tốt hơn.
      </p>
    </aside>
  </section>
</main>
@endsection
