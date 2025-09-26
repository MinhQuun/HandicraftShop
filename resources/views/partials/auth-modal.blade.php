<div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered auth-modal-dialog">
    <div class="modal-content auth-modal-content">
      <div class="modal-body p-0 auth-modal-body">

        <div class="auth-container" id="authContainer">
          <!-- Sign Up Form -->
          <div class="auth-form-container sign-up-container">
            <form class="auth-form" action="{{ route('users.store') }}" method="post">
              @csrf
              <input type="hidden" name="redirect" value="{{ request('redirect', url()->full()) }}">
              <h1 class="auth-title">Đăng Ký</h1>

              <div class="auth-social-container">
                <a class="auth-social" href="#"><i class="fab fa-facebook-f"></i></a>
                <a class="auth-social" href="#"><i class="fab fa-google"></i></a>
                <a class="auth-social" href="#"><i class="fab fa-github"></i></a>
              </div>

              <span class="auth-subtitle">hoặc sử dụng email của bạn để đăng ký</span>

              {{-- Họ tên --}}
              <input
                class="auth-input @error('name') is-invalid @enderror"
                type="text" name="name" placeholder="Họ và Tên"
                value="{{ old('name') }}" required minlength="2" maxlength="255"
                autocomplete="name" />
              @error('name') <div class="auth-error">{{ $message }}</div> @enderror

              {{-- Email --}}
              <input
                class="auth-input @error('email') is-invalid @enderror"
                type="email" name="email" placeholder="Email"
                value="{{ old('email') }}" required maxlength="255"
                autocomplete="email" autocapitalize="off" />
              @error('email') <div class="auth-error">{{ $message }}</div> @enderror

              {{-- Số điện thoại (10 số, bắt đầu bằng 0) --}}
              <input
                class="auth-input @error('phone') is-invalid @enderror"
                type="text" name="phone" placeholder="Số Điện Thoại"
                value="{{ old('phone') }}" required inputmode="numeric" maxlength="10"
                pattern="^0\d{9}$" title="Số điện thoại phải gồm 10 số và bắt đầu bằng 0"
                autocomplete="tel"
                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" />
              @error('phone') <div class="auth-error">{{ $message }}</div> @enderror

              {{-- Mật khẩu (>= 6 ký tự) --}}
              <div class="auth-input-wrap">
                <input
                  class="auth-input @error('password') is-invalid @enderror"
                  type="password" name="password" placeholder="Mật Khẩu"
                  required minlength="6" autocomplete="new-password" />
                <button type="button" class="auth-toggle-pass" aria-label="Hiện/Ẩn mật khẩu">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              @error('password') <div class="auth-error">{{ $message }}</div> @enderror

              {{-- Xác nhận mật khẩu --}}
              <div class="auth-input-wrap">
                <input
                  class="auth-input @error('password_confirmation') is-invalid @enderror"
                  type="password" name="password_confirmation" placeholder="Xác Nhận Mật Khẩu"
                  required minlength="6" autocomplete="new-password" />
                <button type="button" class="auth-toggle-pass" aria-label="Hiện/Ẩn mật khẩu">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              @error('password_confirmation') <div class="auth-error">{{ $message }}</div> @enderror

              <button type="submit" class="auth-btn">Đăng Ký</button>
            </form>
          </div>

          <!-- Sign In Form -->
          <div class="auth-form-container sign-in-container">
            <form class="auth-form" action="{{ route('users.login') }}" method="post">
              @csrf
              <input type="hidden" name="redirect" value="{{ request('redirect', url()->full()) }}">
              <h1 class="auth-title">Đăng Nhập</h1>

              <div class="auth-social-container">
                <a class="auth-social" href="#"><i class="fab fa-facebook-f"></i></a>
                <a class="auth-social" href="#"><i class="fab fa-google"></i></a>
                <a class="auth-social" href="#"><i class="fab fa-github"></i></a>
              </div>

              <span class="auth-subtitle">hoặc sử dụng tài khoản của bạn</span>

              <input
                class="auth-input" type="email" name="email" placeholder="Email"
                value="{{ old('email') }}" required autofocus
                autocomplete="email" autocapitalize="off" />

              <div class="auth-input-wrap">
                <input class="auth-input" type="password" name="password" placeholder="Mật Khẩu"
                       required autocomplete="current-password" minlength="6" />
                <button type="button" class="auth-toggle-pass" aria-label="Hiện/Ẩn mật khẩu">
                  <i class="far fa-eye"></i>
                </button>
              </div>

              {{-- Lưu ý: phải là 'password' (chữ thường) --}}
              @error('password')
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


