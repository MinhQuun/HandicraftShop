<footer>
  <div class="footer-links">
    <!-- Cột 1 -->
    <div>
      <h3>Chăm sóc khách hàng</h3>
      <ul>
        <li><a href="#"><i class="fas fa-info-circle"></i> Trung Tâm Trợ Giúp</a></li>
        <li><a href="#"><i class="fas fa-envelope"></i> Handicraft Shop Mail</a></li>
        <li><a href="#"><i class="fas fa-question-circle"></i> Hướng Dẫn Mua Hàng</a></li>
      </ul>
    </div>

    <!-- Cột 2 -->
    <div>
      <h3>Hướng dẫn & hỗ trợ</h3>
      <ul>
        <li><a href="#">Hướng dẫn mua hàng</a></li>
        <li><a href="#">Đăng ký nhận tư vấn</a></li>
        <li><a href="#">Câu hỏi thường gặp (FAQ)</a></li>
        <li><a href="#">Liên hệ & đặt làm theo yêu cầu</a></li>
      </ul>
    </div>

    <!-- Cột 3 -->
    <div>
      <h3>Dịch vụ & chính sách</h3>
      <ul>
        <li><a href="#">Bảo hành & bảo quản</a></li>
        <li><a href="#">Đổi trả & hoàn tiền</a></li>
        <li><a href="#">Vận chuyển & thanh toán</a></li>
        <li><a href="#">Chính sách bảo mật</a></li>
      </ul>
    </div>

    <!-- Cột 4 -->
    <div>
      <h3>Về Handicraft Shop</h3>
      <ul>
        {{-- nếu đã có routes thì dùng route(), còn chưa có thì tạm để url() --}}
        <li><a href="{{ Route::has('about') ? route('about') : url('/about-us') }}">Về chúng tôi</a></li>
        <li><a href="#">Tuyển dụng</a></li>
        <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">Liên hệ</a></li>
        <li><a href="#">Blog / Tin tức</a></li>
      </ul>

      <div class="footer__social">
        <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
        <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
        <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
      </div>
    </div>
  </div>

  <div class="line-throught"></div>

  <div class="footer-links-2">
    <ul>
      <li><a href="#">&copy; {{ date('Y') }} HandicraftShop</a></li>
      <li><a href="#">Cài đặt</a></li>
      <li><a href="#">Quyền riêng tư</a></li>
      <li><a href="#">Điều khoản sử dụng</a></li>
    </ul>
    <ul>
      <li><a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a></li>
      <li><a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a></li>
      <li><a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a></li>
      <li><a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
    </ul>
  </div>
</footer>
