<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;

// ================== TRANG CHỦ ==================
Route::get('/', [ProductController::class, 'home'])->name('home');

// ================== SẢN PHẨM ==================
Route::get('/tat-ca-san-pham', [ProductController::class, 'allProducts'])->name('all_product');
Route::get('/danh-muc', [ProductController::class, 'byCategory'])->name('category'); // nhận ?dm=...
Route::get('/loai/{maLoai}', [ProductController::class, 'byType'])->name('sp.byType');
Route::get('/tim-kiem', [ProductController::class, 'search'])->name('sp.search');
Route::get('/san-pham/{id}', [ProductController::class, 'detail'])->name('sp.detail');

// ================== TRANG TĨNH ==================
Route::view('/about-us', 'pages.about_us')->name('about');
Route::view('/services', 'pages.services')->name('services');
Route::view('/contact', 'pages.contact')->name('contact');

// ================== LIÊN HỆ ==================
Route::get('/lien-he', [ContactController::class, 'show'])->name('contact.form');
Route::post('/lien-he', [ContactController::class, 'submit'])->name('contact.submit');

// ================== GIỎ HÀNG ==================
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'show'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/increase/{id}', [CartController::class, 'increase'])->name('cart.increase');
    Route::post('/cart/decrease/{id}', [CartController::class, 'decrease'])->name('cart.decrease');
    Route::post('/cart/remove/{id}',   [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
});



// ================== USER AUTH ==================

// Xử lý login/register/logout
Route::post('/login', [UserController::class, 'login'])->name('users.login');
Route::post('/register', [UserController::class, 'store'])->name('users.store');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// ================== USER CRUD ==================
Route::resource('users', UserController::class)->except(['create', 'store']);

// ================== PHÂN QUYỀN ==================
// Admin quản lý tất cả
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Quản lý người dùng
    Route::resource('users', UserController::class)->only(['index','edit','update','destroy']);
});

// Nhân viên (chỉ được 1 số chức năng)
Route::middleware(['role:nhanvien'])->group(function () {
    Route::get('/nhanvien/dashboard', function () {
        return view('nhanvien.dashboard');
    })->name('nhanvien.dashboard');

    // Ví dụ: quản lý đơn hàng
    // Route::resource('orders', OrderController::class);
});

// Khách hàng (có thể thêm route riêng nếu cần)
Route::middleware(['role:khachhang'])->group(function () {
    Route::get('/khach/dashboard', function () {
        return view('khach.dashboard');
    })->name('khach.dashboard');
});
