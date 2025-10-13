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
```

---

## 🔐 Lấy OpenAI API Key cho Chatbot
1. Đăng nhập (hoặc tạo tài khoản) tại [https://platform.openai.com/](https://platform.openai.com/).
2. Mở menu người dùng góc trên bên phải → chọn **View API keys**.
3. Nhấn **Create new secret key**, đặt tên gợi nhớ rồi bấm **Create secret key**.
4. Sao chép chuỗi khóa ngay khi hiển thị (không xem lại được), lưu vào trình quản lý bí mật an toàn.
5. Cập nhật file `.env` của dự án:
   ```env
   OPENAI_API_KEY="sk-..."
   OPENAI_CHAT_MODEL="gpt-4o-mini"   # hoặc model bạn được phép sử dụng
   OPENAI_CHAT_ENDPOINT="https://api.openai.com/v1/chat/completions"
   ```
6. Khởi động lại ứng dụng (hoặc `php artisan config:clear`) để Laravel đọc khóa mới.

> ⚠️ Việc gọi API sẽ tính phí theo tài khoản OpenAI của bạn; hãy chắc chắn rằng bạn đã bật phương thức thanh toán hợp lệ.
