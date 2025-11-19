@extends('layouts.main')

@section('title','Liên hệ')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('content')
    <main class="page-contact">
        <!-- Hero -->
        <section class="contact-hero">
            <h1>Liên Hệ Với Chúng Tôi</h1>
            <p>Rất vui được nghe ý kiến của bạn. Điền form dưới đây, chúng tôi sẽ phản hồi sớm nhất!</p>
        </section>

        <!-- Content -->
        <section class="contact-wrap">
            <!-- LEFT: Form -->
            <div class="card contact-form-card">
                <h2 class="card-title"><i class="fa-regular fa-envelope"></i> Gửi tin nhắn</h2>


                <form method="post" action="{{ route('contact.submit') }}" class="contact-form" novalidate>
                    @csrf
                    <div class="field">
                        <input type="text" id="name" name="name" placeholder=" " value="{{ old('name') }}" required>
                        <label for="name">Họ và tên</label>
                        <i class="fa-regular fa-user field-icon"></i>
                    </div>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <div class="field">
                        <input type="email" id="email" name="email" placeholder=" " value="{{ old('email') }}" required>
                        <label for="email">Email</label>
                        <i class="fa-regular fa-at field-icon"></i>
                    </div>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <div class="field">
                        <textarea id="message" name="message" rows="5" placeholder=" " required>{{ old('message') }}</textarea>
                        <label for="message">Nội dung</label>
                        <i class="fa-regular fa-comment-dots field-icon"></i>
                    </div>
                    @error('message')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    <button type="submit" name="submit" class="btn-primary">
                        <i class="fa-regular fa-paper-plane"></i> Gửi
                    </button>
                </form>
            </div>

            <!-- RIGHT: Info -->
            <aside class="card contact-info-card">
                <h2 class="card-title"><i class="fa-solid fa-circle-info"></i> Thông tin liên hệ</h2>
                <ul class="info-list">
                    <li><i class="fa-solid fa-location-dot"></i> 140 Lê Trọng Tấn, Tây Thạnh, Tân Phú, Hồ Chí Minh</li>
                    <li><i class="fa-solid fa-phone"></i> 028 6270 6275</li>
                    <li><i class="fa-solid fa-envelope"></i> support@handicraftshop.vn</li>
                    <li><i class="fa-solid fa-clock"></i> 08:00 – 21:00 (T2–T7)</li>
                </ul>

                <div class="socials">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                </div>

                <div class="map-embed" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;">
                    <iframe
                    src="https://www.google.com/maps?q=Trường+Đại+học+Công+Thương+TP.HCM+140+Lê+Trọng+Tấn&output=embed"
                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </aside>
        </section>
    </main>
@endsection
