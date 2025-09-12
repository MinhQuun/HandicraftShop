# HandicraftShop

Website bán hàng thủ công mỹ nghệ xây bằng **Laravel + Blade** với mega menu 2 tầng, modal Đăng nhập/Đăng ký hiện đại, và hệ thống flash message đẹp, dễ dùng.

---

## ✨ Tính năng chính
- **Mega menu 2 tầng** (DanhMuc → Loai) lấy dữ liệu bằng **Eloquent** (eager loading).
- **Auth dropdown** (Tên người dùng/Thông tin cá nhân/Đăng xuất) đồng bộ style với mega menu, **căn giữa** dưới trigger (desktop).
- **Auth modal** (Đăng nhập/Đăng ký) dạng **panel switch**, **mở rộng chiều ngang**, responsive.
- **Validation**:
  - **Server** (Laravel Validator / Eloquent Rules).
  - **Client** (HTML attributes: `required`, `minlength`, `pattern`, `autocomplete`…).
- **Flash messages**: 1 partial duy nhất, UI toast-card đẹp, auto-hide.
- **Phân trang** custom gọn: “Trước/Sau” + số trang.

---

## 🧰 Công nghệ & Yêu cầu
- PHP **8.2+**
- Laravel **12.x** (ví dụ: 12.28.1)
- MySQL/MariaDB
- Bootstrap 5, Font Awesome

---

## ⚙️ Cài đặt
```bash
git clone <repo-url>
cd HandicraftShop

composer install
cp .env.example .env
php artisan key:generate
# Cấu hình DB trong .env
php artisan migrate
php artisan serve
# http://127.0.0.1:8000
