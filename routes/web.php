<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\ContactController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ForgotPasswordController;

use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Middleware\RoleMiddleware;

use App\Http\Controllers\Staff\SupplierController;
use App\Http\Controllers\Staff\ProductController as StaffProductController;
use App\Http\Controllers\Staff\KhachHangController;
use App\Http\Controllers\Staff\ReviewController as StaffReviewController;
use App\Http\Controllers\Staff\ReceiptController;
use App\Http\Controllers\Staff\IssueController;
use App\Http\Controllers\Staff\OrderController as StaffOrderController;
use App\Http\Controllers\Staff\PromotionsController;

/*
|--------------------------------------------------------------------------
| PUBLIC (khách vãng lai)
|--------------------------------------------------------------------------
*/

// Trang chủ
Route::get('/', [ProductController::class, 'home'])->name('home');

// Trang tĩnh
Route::view('/about-us', 'pages.about_us')->name('about');
Route::view('/services', 'pages.services')->name('services');
Route::view('/contact',  'pages.contact')->name('contact');

// Sản phẩm / Danh mục / Loại / Tìm kiếm / Chi tiết
Route::prefix('/')
    ->group(function () {
        Route::get('/tat-ca-san-pham', [ProductController::class, 'allProducts'])->name('all_product');
        Route::get('/danh-muc',        [ProductController::class, 'byCategory'])->name('category'); // ?dm=...
        Route::get('/loai/{maLoai}',   [ProductController::class, 'byType'])->name('sp.byType');
        Route::get('/tim-kiem',        [ProductController::class, 'search'])->name('sp.search');
        Route::get('/san-pham/{id}',   [ProductController::class, 'detail'])->name('sp.detail');
    });

// Liên hệ
Route::get('/lien-he',  [ContactController::class, 'show'])->name('contact.form');
Route::post('/lien-he', [ContactController::class, 'submit'])->name('contact.submit');

// Đánh giá sản phẩm (yêu cầu đăng nhập)
Route::post('/sp/{id}/danh-gia', [ReviewController::class, 'store'])
    ->middleware('auth')->name('reviews.store');


/*
|--------------------------------------------------------------------------
| AUTH & USER (đăng nhập/đăng ký/đổi mật khẩu qua OTP)
|--------------------------------------------------------------------------
*/

// Điều hướng mở modal đăng nhập trên trang chủ (giữ nguyên name & hành vi)
Route::get('/login', function (Request $request) {
    $redir = $request->query('redirect', url()->previous());
    return redirect()->to(route('home') . '?open=login&redirect=' . urlencode($redir));
})->name('login');

// Đăng nhập / Đăng ký / Đăng xuất
Route::post('/login',    [UserController::class, 'login'])->name('users.login');
Route::get('/register',  [UserController::class, 'create'])->name('register');
Route::post('/register', [UserController::class, 'store'])->name('users.store');
Route::post('/logout',   [UserController::class, 'logout'])->name('logout');

// CRUD users (giữ nguyên ngoại lệ)
Route::resource('users', UserController::class)->except(['create', 'store']);

// Quên mật khẩu qua OTP (giữ nguyên URL & name)
Route::name('password.')->group(function () {
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('send');    // POST -> password.send
    Route::post('/verify-otp',      [ForgotPasswordController::class, 'verifyOtp'])->name('verify');      // POST -> password.verify
    Route::post('/reset-password',  [ForgotPasswordController::class, 'resetPassword'])->name('update');  // POST -> password.update
});


/*
|--------------------------------------------------------------------------
| PROTECTED (auth chung cho mọi user)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Giỏ hàng & đặt hàng
    Route::get('/cart',                [CartController::class, 'show'])->name('cart');
    Route::post('/cart/add',           [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/increase/{id}', [CartController::class, 'increase'])->name('cart.increase');
    Route::post('/cart/decrease/{id}', [CartController::class, 'decrease'])->name('cart.decrease');
    Route::post('/cart/remove/{id}',   [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [OrderController::class, 'create'])->name('checkout');
    Route::post('/orders',  [OrderController::class, 'store'])->name('orders.store');
    Route::post('/checkout/apply-promo', [OrderController::class, 'applyPromo'])->name('checkout.applyPromo');
    // (Nếu sau này bỏ confirm phía khách có thể xóa route này)
    Route::get('/orders/{id}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');

    // Hồ sơ cá nhân
    Route::get('/profile',               [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',               [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',      [ProfileController::class, 'changePassword'])->name('profile.changePassword');
});


/*
|--------------------------------------------------------------------------
| CUSTOMER AREA (role: khachhang)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', RoleMiddleware::class . ':khachhang'])
    ->prefix('my-orders')
    ->name('customer.orders.')
    ->group(function () {
        Route::get('/',           [CustomerOrderController::class, 'index'])->name('index');
        Route::get('/history',    [CustomerOrderController::class, 'history'])->name('history');
        Route::get('/{id}/json',  [CustomerOrderController::class, 'showJson'])->name('show.json');
        Route::post('/{id}/cancel', [CustomerOrderController::class, 'cancel'])->name('cancel');
    });


/*
|--------------------------------------------------------------------------
| ADMIN (role: admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', RoleMiddleware::class . ':admin'])
    ->group(function () {

        // Dashboard
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


/*
|--------------------------------------------------------------------------
| STAFF (role: nhanvien)
|--------------------------------------------------------------------------
*/
Route::prefix('staff')
    ->middleware(['auth', RoleMiddleware::class . ':nhanvien'])
    ->name('staff.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('staff.dashboard', [
                'stats' => [
                    'products'       => DB::table('SANPHAM')->count(),
                    'suppliers'      => DB::table('NHACUNGCAP')->count(),
                    'orders_pending' => DB::table('DONHANG')->where('TRANGTHAI','Chờ xử lý')->count(),
                    'customers'      => DB::table('KHACHHANG')->count(),
                ]
            ]);
        })->name('dashboard');

        // Nhà cung cấp
        Route::get('/suppliers',         [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers',        [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{id}',    [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::get('/suppliers/export-csv', [SupplierController::class, 'exportCsv'])->name('suppliers.exportCsv');

        // Sản phẩm
        Route::get('/products',          [StaffProductController::class, 'index'])->name('products.index');
        Route::post('/products',         [StaffProductController::class, 'store'])->name('products.store');
        Route::put('/products/{id}',     [StaffProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}',  [StaffProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/products/export-csv', [StaffProductController::class, 'exportCsv'])->name('products.exportCsv');
        
        // Khuyến mãi
        Route::get('/promotions',         [PromotionsController::class, 'index'])->name('promotions.index');
        Route::post('/promotions',        [PromotionsController::class, 'store'])->name('promotions.store');
        Route::put('/promotions/{id}',    [PromotionsController::class, 'update'])->name('promotions.update');
        Route::delete('/promotions/{id}', [PromotionsController::class, 'destroy'])->name('promotions.destroy');

        // Khách hàng
        Route::get('/customers',                [KhachHangController::class, 'index'])->name('customers.index');
        Route::post('/customers',               [KhachHangController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}',     [KhachHangController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}',  [KhachHangController::class, 'destroy'])->name('customers.destroy');

        // Hộp thư ý kiến
        Route::get('/reviews',          [StaffReviewController::class, 'index'])->name('reviews.index');
        Route::put('/reviews/{id}',     [StaffReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{id}',  [StaffReviewController::class, 'destroy'])->name('reviews.destroy');

        // Thanh toán (stub)
        Route::view('/payments', 'staff.stub')->name('payments.index');

        // Phiếu nhập
        Route::get('/receipts',                 [ReceiptController::class, 'index'])->name('receipts.index');
        Route::post('/receipts',                [ReceiptController::class, 'store'])->name('receipts.store');
        Route::get('/receipts/create', function () {
            return redirect()->route('staff.receipts.index', ['open' => 'create']);
        })->name('receipts.create');
        Route::get('/receipts/{id}',            [ReceiptController::class, 'show'])->name('receipts.show');
        Route::put('/receipts/{id}',            [ReceiptController::class, 'update'])->name('receipts.update');
        Route::put('/receipts/{id}/confirm',    [ReceiptController::class, 'confirm'])->name('receipts.confirm');
        Route::put('/receipts/{id}/cancel',     [ReceiptController::class, 'cancel'])->name('receipts.cancel');
        Route::delete('/receipts/{id}',         [ReceiptController::class, 'destroy'])->name('receipts.destroy');

        // Đơn hàng
        Route::get('/orders',             [StaffOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}',        [StaffOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{id}/status', [StaffOrderController::class, 'updateStatus'])->name('orders.updateStatus');

        // Phiếu xuất
        Route::get('/issues',              [IssueController::class, 'index'])->name('issues.index');
        Route::get('/issues/{id}',         [IssueController::class, 'show'])->name('issues.show');
        Route::put('/issues/{id}/confirm', [IssueController::class, 'confirm'])->name('issues.confirm');
        Route::put('/issues/{id}/cancel',  [IssueController::class, 'cancel'])->name('issues.cancel');
        Route::get('/issues/create', function () {
            return redirect()->route('staff.issues.index', ['open' => 'create']);
        })->name('issues.create');

        // Thống kê (stub)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::view('/inout',     'staff.stub')->name('inout');
            Route::view('/inventory', 'staff.stub')->name('inventory');
            Route::view('/sales',     'staff.stub')->name('sales');
            Route::view('/lowstock',  'staff.stub')->name('lowstock');
            Route::view('/top',       'staff.stub')->name('top');
        });
    });