{{-- resources/views/partials/auth-modal.blade.php --}}
<div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered auth-modal-dialog">
    <div class="modal-content auth-modal-content">
      <div class="modal-body p-0 auth-modal-body">

        <div class="auth-container" id="authContainer">
          <!-- Sign Up Form -->
          <div class="auth-form-container sign-up-container">
            <form class="auth-form" action="{{ url('/Home/SignUp') }}" method="post">
              @csrf
              <h1 class="auth-title">Đăng Ký</h1>

              <div class="auth-social-container">
                <a href="#" class="auth-social"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="auth-social"><i class="fab fa-google"></i></a>
                <a href="#" class="auth-social"><i class="fab fa-linkedin-in"></i></a>
              </div>

              <span class="auth-subtitle">hoặc sử dụng email của bạn để đăng ký</span>

              <input class="auth-input" type="text" name="TENNGUOIDUNG" placeholder="Họ Tên" required />
              <input class="auth-input" type="text" name="TAIKHOAN"     placeholder="Tên Đăng Nhập" required />
              <input class="auth-input" type="email" name="EMAIL"       placeholder="Email" required />
              <input class="auth-input" type="password" name="MATKHAU"  placeholder="Mật Khẩu" required />
              <input class="auth-input" type="text" name="SODIENTHOAI"  placeholder="Số Điện Thoại"
                     required pattern="\d*" title="Chỉ được nhập số" />

              {{-- Hiển thị lỗi --}}
              @error('TAIKHOAN')    <div class="auth-error">{{ $message }}</div> @enderror
              @error('EMAIL')       <div class="auth-error">{{ $message }}</div> @enderror
              @error('SODIENTHOAI') <div class="auth-error">{{ $message }}</div> @enderror

              <button type="submit" class="auth-btn">Đăng Ký</button>
            </form>
          </div>

          <!-- Sign In Form -->
          <div class="auth-form-container sign-in-container">
            <form class="auth-form" action="{{ url('/Home/Login') }}" method="post">
              @csrf
              <h1 class="auth-title">Đăng Nhập</h1>

              <div class="auth-social-container">
                <a href="#" class="auth-social"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="auth-social"><i class="fab fa-google"></i></a>
                <a href="#" class="auth-social"><i class="fab fa-linkedin-in"></i></a>
              </div>

              <span class="auth-subtitle">hoặc sử dụng tài khoản của bạn</span>

              <input class="auth-input" type="text"     name="username" placeholder="Tên Đăng Nhập" required autofocus />
              <input class="auth-input" type="password" name="password" placeholder="Mật Khẩu" required />

              @error('Password') 
                <div class="auth-error">{{ $message }}</div>
              @enderror

              <a href="#" class="auth-link">Bạn quên mật khẩu?</a>
              <button type="submit" class="auth-btn">Đăng Nhập</button>
            </form>
          </div>

          <!-- Overlay -->
          <div class="auth-overlay-container">
            <div class="auth-overlay">
              <div class="auth-overlay-panel auth-overlay-left">
                <h1>Chào mừng trở lại!</h1>
                <p>Vui lòng đăng nhập để kết nối với chúng tôi</p>
                <button class="auth-btn ghost" id="signIn">Đăng Nhập</button>
              </div>
              <div class="auth-overlay-panel auth-overlay-right">
                <h1>Chào bạn!</h1>
                <p>Nhập thông tin để bắt đầu hành trình cùng chúng tôi</p>
                <button class="auth-btn ghost" id="signUp">Đăng Ký</button>
              </div>
            </div>
          </div>
        </div>

      </div> 
    </div>
  </div>
</div>

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@push('scripts')
<script>
  const signUpButton = document.getElementById('signUp');
  const signInButton = document.getElementById('signIn');
  const container = document.getElementById('authContainer');

  if(signUpButton && signInButton && container){
    signUpButton.addEventListener('click', () => {
      container.classList.add("right-panel-active");
    });
    signInButton.addEventListener('click', () => {
      container.classList.remove("right-panel-active");
    });
  }
</script>
@endpush
