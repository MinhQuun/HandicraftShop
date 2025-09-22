<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Middleware\RoleMiddleware;

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
// ================== Admin ===================
Route::prefix('admin')
    ->middleware(['auth', RoleMiddleware::class . ':admin'])
    ->group(function () {
        // Dashboard -> tính thống kê
        Route::get('/dashboard', function () {
            $adminId     = DB::table('QUYEN')->whereRaw('LOWER(TENQUYEN)="admin"')->value('MAQUYEN');
            $nhanvienId  = DB::table('QUYEN')->whereRaw('LOWER(TENQUYEN)="nhanvien"')->value('MAQUYEN');
            $khachhangId = DB::table('QUYEN')->whereRaw('LOWER(TENQUYEN)="khachhang"')->value('MAQUYEN');

            $counts = [
                'admin'     => (int) DB::table('QUYEN_NGUOIDUNG')->where('MAQUYEN',$adminId)->count(),
                'nhanvien'  => (int) DB::table('QUYEN_NGUOIDUNG')->where('MAQUYEN',$nhanvienId)->count(),
                'khachhang' => (int) DB::table('QUYEN_NGUOIDUNG')->where('MAQUYEN',$khachhangId)->count(),
                'total'     => (int) \App\Models\User::count(),
            ];
            return view('admin.dashboard', compact('counts'));
        })->name('admin.dashboard');

        // Quản lý người dùng
        Route::get('/users',              [UserAdminController::class, 'index'])->name('admin.users.index');
        Route::post('/users',             [UserAdminController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}',       [UserAdminController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}',    [UserAdminController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/users/{user}/role', [UserAdminController::class, 'updateRole'])->name('admin.users.updateRole');
    });

// Nhân viên (sau này mới dùng)
Route::middleware(['auth', RoleMiddleware::class . ':nhanvien'])
    ->get('/nhanvien/dashboard', fn() => view('nhanvien.dashboard'))
    ->name('nhanvien.dashboard');
