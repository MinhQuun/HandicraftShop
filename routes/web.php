<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Staff\SupplierController;

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

// ================== Nhân viên ==================
Route::prefix('staff')
    ->middleware(['auth', RoleMiddleware::class . ':nhanvien'])
    ->name('staff.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            // sau này sẽ tính thống kê đơn hàng, sản phẩm...
            return view('staff.dashboard', [
                'stats' => [
                    'products'       => DB::table('SANPHAM')->count(),
                    'suppliers'     => DB::table('NHACUNGCAP')->count(),
                    'orders_pending' => DB::table('DONHANG')->where('TRANGTHAI','Chờ xử lý')->count(),
                    'customers'      => DB::table('KHACHHANG')->count(),
                ]
            ]);
        })->name('dashboard');

        // Quản lý nhà cung cấp
        Route::get('/suppliers',        [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers',       [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{id}',   [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{id}',[SupplierController::class, 'destroy'])->name('suppliers.destroy');
        
        // Các module quản lý (stub trước)
        Route::view('/products', 'staff.stub')->name('products.index');
        Route::view('/promotions', 'staff.stub')->name('promotions.index');
        Route::view('/orders', 'staff.stub')->name('orders.index');
        Route::view('/customers', 'staff.stub')->name('customers.index');
        Route::view('/reviews', 'staff.stub')->name('reviews.index');
        Route::view('/payments', 'staff.stub')->name('payments.index');
    });

