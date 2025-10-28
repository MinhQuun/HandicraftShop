<div align="center">

# 🧶 HandicraftShop

Hệ thống bán hàng thủ công mỹ nghệ xây dựng bằng Laravel 12 + Blade. Dự án đồ án đã hoàn thành, tối ưu cho trải nghiệm duyệt sản phẩm, giỏ hàng và trang quản trị nội bộ (nhân viên/admin).

[![PHP 8.2+](https://img.shields.io/badge/PHP-%5E8.2-777BB4?logo=php)](https://www.php.net/)
[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-ff2d20?logo=laravel)](https://laravel.com/)
[![Vite](https://img.shields.io/badge/Vite-7-646CFF?logo=vite)](https://vitejs.dev/)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-06B6D4?logo=tailwindcss)](https://tailwindcss.com/)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap)](https://getbootstrap.com/)

</div>

---

## Mục Lục

- Giới thiệu
- Tính năng chính
- Kiến trúc & công nghệ
- Yêu cầu hệ thống
- Cài đặt nhanh (Quick Start)
- Cấu hình môi trường (.env)
- Tài khoản demo (Seed dữ liệu)
- Lệnh thường dùng
- Gợi ý tối ưu

---

## Giới Thiệu

HandicraftShop là website bán hàng thủ công mỹ nghệ với giao diện thân thiện, tối ưu tìm kiếm và duyệt sản phẩm; đi kèm khu vực quản trị cho nhân viên và admin. Dự án sử dụng Laravel 12, Vite, TailwindCSS/Bootstrap và tích hợp xuất PDF, CSV, cùng chatbot AI (Gemini) hỗ trợ khách hàng.

Trạng thái: Hoàn thành (Finalized for coursework).

---

## Tính Năng Chính

- Khách hàng:
  - Duyệt tất cả sản phẩm, theo danh mục/loại, khuyến mãi, tìm kiếm, chi tiết sản phẩm.
  - Giỏ hàng theo session, tăng/giảm/xóa, trang thanh toán (mock/giả lập).
  - Đánh giá sản phẩm (yêu cầu đăng nhập), liên hệ (contact form).
  - Đăng nhập/đăng ký (auth modal), quên mật khẩu qua OTP.
  - Chatbot AI (Gemini) trả lời tự nhiên, hỗ trợ tra cứu giá, tồn kho, danh mục, khuyến mãi, đánh giá.

- Nhân viên (Staff):
  - Bảng điều khiển và các module: Sản phẩm, Khuyến mãi, Khách hàng, Đánh giá, Đơn hàng.
  - Phiếu nhập, phiếu xuất: xem/duyệt/hủy, xuất PDF, xuất CSV.
  - Báo cáo: bán hàng, tồn kho, nhập/xuất, sắp hết hàng.

- Quản trị (Admin):
  - Quản lý người dùng và phân quyền (admin/nhân viên/khách hàng).

---

## Kiến Trúc & Công Nghệ

- Backend: Laravel 12.x (PHP 8.2+), Eloquent ORM, Validator, Middleware phân quyền.
- Frontend: Blade, Vite, TailwindCSS 4, Bootstrap 5, Axios.
- Tích hợp:
  - PDF: barryvdh/laravel-dompdf (xuất phiếu nhập/xuất).
  - Chatbot AI: Google Gemini (Generative Language API) với RAG (truy xuất dữ liệu nội bộ + sinh câu trả lời).
  - CSV export cho các danh sách chính.
- Cơ sở dữ liệu: MySQL/MariaDB (hỗ trợ SQLite cho môi trường local/dev).
- Công cụ dev: Laravel Pint, PHPUnit, Laravel Pail, concurrently.

---

## Yêu Cầu Hệ Thống

- PHP 8.2 trở lên (khuyến nghị bật: mbstring, openssl, intl, bcmath, fileinfo, zip).
- Composer 2.x.
- Node.js 18+ và npm.
- MySQL 8+ hoặc MariaDB (hoặc dùng SQLite mặc định trong `.env.example`).

Gợi ý Windows: nếu dùng XAMPP, bật các extension trên trong `php.ini` để tránh lỗi khi cài package.

---

## Cài Đặt Nhanh (Quick Start)

```bash
git clone <repo-url>
cd HandicraftShop

composer install
cp .env.example .env
php artisan key:generate

# Cấu hình DB trong .env (MySQL hoặc dùng SQLite mặc định)
php artisan migrate --seed

# Cách 1: chạy tất cả (server + queue + logs + Vite)
composer run dev

# Cách 2: chạy thủ công
php artisan serve          # http://127.0.0.1:8000
npm install && npm run dev # Vite dev
```

Build production assets:

```bash
npm run build
```

Chạy test:

```bash
composer test
```

---

## Cấu Hình Môi Trường (.env)

Các biến quan trọng (tham khảo `config/services.php`):

```env
# Database
DB_CONNECTION=mysql        # hoặc sqlite (mặc định trong .env.example)
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=handicraftshop
DB_USERNAME=root
DB_PASSWORD=

# Session/Queue/Cache
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Chatbot AI (Gemini)
GEMINI_API_KEY=your_api_key
GEMINI_MODEL=gemini-2.0-flash
GEMINI_ENDPOINT=https://generativelanguage.googleapis.com/v1beta/models

# (Tùy chọn) OpenAI – nếu bạn muốn chuyển provider
OPENAI_API_KEY=
OPENAI_CHAT_MODEL=gpt-4o-mini
OPENAI_CHAT_ENDPOINT=https://api.openai.com/v1/chat/completions
```

Lưu ý: route chatbot được throttle chống spam: `POST /chatbot/query` (20 yêu cầu/phút).

---

## Tài Khoản Demo (Seed)

Sau khi `php artisan migrate --seed`, hệ thống tạo sẵn một số tài khoản để demo:

- Admin: `quan@gmail.com` / `123456`
- Nhân viên: `doan@gmail.com`, `vy@gmail.com`, `yen@gmail.com` / `123456`
- Khách hàng: `khachhang@example.com` / `123456`

Khuyến nghị thay đổi mật khẩu khi triển khai môi trường thật.

---

## Lệnh Thường Dùng

- Dọn cache cấu hình/route/view khi thay đổi môi trường:
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```
- Xuất PDF/CSV: thao tác ngay từ giao diện nhân viên ở các trang phiếu nhập/xuất, đơn hàng.

---

## Gợi Ý Tối Ưu

- Full‑text search cho bảng sản phẩm để cải thiện tìm kiếm/chatbot:
  ```sql
  ALTER TABLE SANPHAM ADD FULLTEXT(TENSANPHAM, MOTA);
  ```
- Cân nhắc Redis cho cache/queue trong môi trường production.
- Bật HTTPS và thiết lập rate‑limit/ngăn chặn brute force đăng nhập.

---

## Bản Quyền

Dự án phục vụ mục đích học tập/đồ án. Vui lòng giữ phần giới thiệu tác giả/nguồn khi tái sử dụng.
