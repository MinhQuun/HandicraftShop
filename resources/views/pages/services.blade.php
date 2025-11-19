@extends('layouts.main')

@section('title','Dịch vụ')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}">
@endpush

@section('content')
    <main class="services-page">
        <!-- HERO -->
        <section class="sv-hero">
            <span class="kicker"><i class="fa-solid fa-leaf"></i> Dịch vụ thủ công</span>
            <h1>Giải pháp mỹ nghệ cho thương hiệu & không gian của bạn</h1>
            <p>Chúng tôi đồng hành từ ý tưởng đến sản phẩm hoàn thiện: quà tặng doanh nghiệp, trang trí nhà hàng – khách sạn, set-up cửa hàng, và thiết kế theo yêu cầu.</p>
            <div class="hero-actions">
                <a href="{{ route('contact') }}" class="btn-primary"><i class="fa-regular fa-paper-plane"></i> Liên hệ tư vấn</a>
                <a href="{{ route('all_product') }}" class="btn-outline"><i class="fa-regular fa-compass"></i> Xem sản phẩm</a>
            </div>
        </section>

        <!-- SERVICE CARDS -->
        <section class="sv-grid container">
            <article class="sv-card">
                <div class="sv-icon"><i class="fa-solid fa-gift"></i></div>
                <h3>Quà tặng doanh nghiệp</h3>
                <p>Thiết kế bộ quà tặng thủ công mang bản sắc Việt: giỏ cói, khay tre, set decor… có thể in logo & thông điệp.</p>
                <ul class="sv-feats">
                    <li><i class="fa-regular fa-circle-check"></i> Tùy biến theo ngân sách</li>
                    <li><i class="fa-regular fa-circle-check"></i> Đóng gói chuyên nghiệp</li>
                    <li><i class="fa-regular fa-circle-check"></i> Giao nhanh toàn quốc</li>
                </ul>
            </article>

            <article class="sv-card">
                <div class="sv-icon"><i class="fa-solid fa-shop"></i></div>
                <h3>Trang trí không gian</h3>
                <p>Setup decor tre – cói – lục bình cho quán cà phê, homestay, nhà hàng, showroom theo concept thương hiệu.</p>
                <ul class="sv-feats">
                    <li><i class="fa-regular fa-circle-check"></i> Tư vấn concept</li>
                    <li><i class="fa-regular fa-circle-check"></i> Sản xuất & thi công</li>
                    <li><i class="fa-regular fa-circle-check"></i> Bảo hành lắp đặt</li>
                </ul>
            </article>

            <article class="sv-card">
                <div class="sv-icon"><i class="fa-solid fa-pen-ruler"></i></div>
                <h3>Thiết kế theo yêu cầu</h3>
                <p>Nhận thiết kế – sản xuất mẫu mới theo bản vẽ/ảnh mô phỏng; tối ưu công năng & chất liệu thân thiện môi trường.</p>
                <ul class="sv-feats">
                    <li><i class="fa-regular fa-circle-check"></i> Prototyping nhanh</li>
                    <li><i class="fa-regular fa-circle-check"></i> Kiểm soát chất lượng</li>
                    <li><i class="fa-regular fa-circle-check"></i> MOQ linh hoạt</li>
                </ul>
            </article>
        </section>

        <!-- PROCESS -->
        <section class="sv-process container">
            <h2 class="sec-title">Quy trình làm việc</h2>
            <div class="steps">
            <div class="step">
                <span class="dot">1</span>
                <h4>Trao đổi yêu cầu</h4>
                <p>Tiếp nhận brief, ngân sách & timeline mong muốn.</p>
            </div>
            <div class="step">
                <span class="dot">2</span>
                <h4>Đề xuất concept</h4>
                <p>Đưa moodboard, chất liệu, mẫu thử & báo giá minh bạch.</p>
            </div>
            <div class="step">
                <span class="dot">3</span>
                <h4>Sản xuất & hoàn thiện</h4>
                <p>Gia công thủ công, QA và đóng gói theo tiêu chuẩn.</p>
            </div>
            <div class="step">
                <span class="dot">4</span>
                <h4>Bàn giao & hỗ trợ</h4>
                <p>Giao hàng tận nơi, hỗ trợ bảo quản và hậu mãi.</p>
            </div>
            </div>
        </section>

        <!-- USP STRIP -->
        <section class="sv-usp">
            <div class="usp">
                <i class="fa-regular fa-hand-peace"></i>
                <span>100% thủ công</span>
            </div>
            <div class="usp">
                <i class="fa-regular fa-lemon"></i>
                <span>Chất liệu tự nhiên</span>
            </div>
            <div class="usp">
                <i class="fa-regular fa-clock"></i>
                <span>Đúng tiến độ</span>
            </div>
            <div class="usp">
                <i class="fa-regular fa-face-smile"></i>
                <span>Bảo hành tận tâm</span>
            </div>
        </section>

        <!-- FAQ -->
        <section class="sv-faq container">
            <h2 class="sec-title">Câu hỏi thường gặp</h2>
            <div class="faq-list">
                <details class="faq">
                    <summary>Thời gian sản xuất một đơn hàng tuỳ biến là bao lâu?</summary>
                    <p>Thông thường 7–21 ngày tuỳ độ phức tạp & số lượng. Với quà tặng doanh nghiệp số lượng lớn, chúng tôi sẽ đề xuất lộ trình chi tiết.</p>
                </details>
                <details class="faq">
                    <summary>Có nhận in/khắc logo theo thương hiệu không?</summary>
                    <p>Có. Chúng tôi hỗ trợ in/khắc logo, tag, thiệp & đóng gói theo bộ nhận diện.</p>
                </details>
                <details class="faq">
                    <summary>Vận chuyển & bảo hành thế nào?</summary>
                    <p>Giao hàng toàn quốc, hỗ trợ đổi/trả nếu lỗi sản xuất. Hướng dẫn bảo quản cho từng chất liệu.</p>
                </details>
            </div>
        </section>

        <!-- CTA -->
        <section class="sv-cta">
            <h3>Đặt lịch tư vấn miễn phí</h3>
            <p>Cho chúng tôi biết nhu cầu của bạn—chúng tôi sẽ đề xuất giải pháp tối ưu nhất.</p>
            <a href="{{ route('contact') }}" class="btn-primary"><i class="fa-regular fa-calendar-check"></i> Liên hệ ngay</a>
        </section>
    </main>
@endsection
