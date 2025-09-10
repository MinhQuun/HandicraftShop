<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;

Route::get('/', [ProductController::class,'home'])->name('home');

Route::get('/tat-ca-san-pham', [ProductController::class,'allProducts'])->name('all_product');
Route::get('/danh-muc', [ProductController::class,'byCategory'])->name('category'); // nhận ?dm=...
Route::get('/loai/{maLoai}', [ProductController::class,'byType'])->name('sp.byType');
Route::get('/tim-kiem', [ProductController::class,'search'])->name('sp.search');
Route::get('/san-pham/{id}', [ProductController::class,'detail'])->name('sp.detail');

Route::view('/about-us', 'pages.about_us')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/services', 'pages.services')->name('services');   // tạo file nếu cần
Route::view('/cart', 'pages.cart')->name('cart');   
Route::view('/login', 'pages.auth')->name('login');   


Route::get('/lien-he', [ContactController::class, 'show'])->name('contact');
Route::post('/lien-he', [ContactController::class, 'submit'])->name('contact.submit');

// Xem giỏ
Route::get('/cart', [CartController::class, 'show'])->name('cart');

// Thêm vào giỏ (AJAX)
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

// Tăng/giảm/xoá
Route::post('/cart/increase/{id}', [CartController::class, 'increase'])->name('cart.increase');
Route::post('/cart/decrease/{id}', [CartController::class, 'decrease'])->name('cart.decrease');
Route::post('/cart/remove/{id}',   [CartController::class, 'remove'])->name('cart.remove');

// (Tuỳ chọn) Checkout
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');