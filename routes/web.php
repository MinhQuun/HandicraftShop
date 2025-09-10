<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

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

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
