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
Route::get('/cart', [CartController::class, 'show'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/increase/{id}', [CartController::class, 'increase'])->name('cart.increase');
Route::post('/cart/decrease/{id}', [CartController::class, 'decrease'])->name('cart.decrease');
Route::post('/cart/remove/{id}',   [CartController::class, 'remove'])->name('cart.remove');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');

// ================== USER AUTH ==================
// Hiển thị form login & register
Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register');

// Xử lý login/register/logout
Route::post('/login', [UserController::class, 'login'])->name('users.login');
Route::post('/register', [UserController::class, 'store'])->name('users.store');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// ================== USER CRUD ==================
Route::resource('users', UserController::class)->except(['create', 'store']);
