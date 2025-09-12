@php
  // Lấy menu DanhMuc -> Loai bằng Eloquent
  $menus = \App\Models\DanhMuc::orderBy('TENDANHMUC')
            ->with(['loais' => fn($q) => $q->orderBy('TENLOAI')])
            ->get();

  // Đếm số sản phẩm khác nhau trong giỏ (mỗi sản phẩm chỉ +1)
  $cartCount = session('cart') ? count(session('cart')) : 0;
@endphp

<header class="header">
  <div class="nav">
    <ul>
      {{-- Logo --}}
      <li>
        <a href="{{ route('home') }}" class="d-flex align-items-center mb-2 mb-lg-0 text-decoration-none">
          <img src="{{ asset('assets/images/LOGO/Logo.jpg') }}"
               alt="Handicraft Shop Logo"
               width="50"
               height="auto"
               class="bi me-2" />
        </a>
      </li>

      <li><a href="{{ route('home') }}">Trang chủ</a></li>

      {{-- Dropdown Sản phẩm (menu 2 tầng) --}}
      <li>
        <ul class="mega-menu">
          <li class="dropdown-root" style="width: 100px;">
            <a href="#">Sản phẩm <i class="fa-solid fa-angle-down"></i></a>

            <ul class="mega-sub">
              {{-- Tất cả sản phẩm --}}
              <li><a href="{{ route('all_product') }}">Tất cả sản phẩm</a></li>

              {{-- Danh mục cấp 1 --}}
              @foreach ($menus as $dm)
                @php
                  $madm  = (int) $dm->MADANHMUC;
                  $tendm = $dm->TENDANHMUC;
                  $loais = $dm->loais ?? collect();
                @endphp

                <li class="has-children">
                  <a href="{{ route('category', ['dm' => $madm]) }}">
                    {{ $tendm }}
                    @if ($loais->isNotEmpty())
                      <i class="fa-solid fa-angle-right"></i>
                    @endif
                  </a>

                  {{-- Loại cấp 2 --}}
                  @if ($loais->isNotEmpty())
                    <ul class="mega-sub">
                      @foreach ($loais as $loai)
                        <li>
                          <a href="{{ route('sp.byType', $loai->MALOAI) }}">
                            {{ $loai->TENLOAI }}
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </li>
              @endforeach
            </ul>
          </li>
        </ul>
      </li>

      {{-- Ô tìm kiếm --}}
      <form action="{{ route('sp.search') }}" method="get" style="display:inline;">
        <input type="text" name="q" placeholder="Tìm kiếm..."
               style="width:250px;height:30px;border-radius:10px;padding-left:10px"
               value="{{ request('q') }}">
      </form>

      {{-- Các link tĩnh --}}
      <li><a href="{{ route('services') }}">Dịch vụ</a></li>
      <li><a href="{{ route('contact') }}">Liên hệ</a></li>
      <li><a href="{{ route('about') }}">Về chúng tôi</a></li>

      {{-- Auth --}}
      @guest
          <li>
              <a href="#" data-bs-toggle="modal" data-bs-target="#authModal">
                  Đăng nhập / Đăng ký
              </a>
          </li>
      @else
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa-solid fa-user me-1"></i>
                  {{ auth()->user()->name }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                  <li><a class="dropdown-item" href="{{ route('users.show', auth()->user()->id) }}">Thông tin cá nhân</a></li>
                  <li>
                      <form action="{{ route('logout') }}" method="POST">
                          @csrf
                          <button type="submit" class="dropdown-item">Đăng xuất</button>
                      </form>
                  </li>
              </ul>
          </li>
      @endguest
            

      {{-- Giỏ hàng --}}
      <li>
        <a href="{{ route('cart') }}">
          <i class="fa-solid fa-cart-shopping"></i>
          <span id="cart-count">{{ $cartCount }}</span>
        </a>
      </li>
    </ul>
  </div>
</header>
