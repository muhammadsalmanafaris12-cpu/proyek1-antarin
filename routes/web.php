<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\Driver\OrderController;
use App\Http\Controllers\Driver\HistoryController;
use App\Http\Controllers\Driver\ProfileController;
use App\Http\Controllers\Driver\WithdrawController;
use App\Http\Controllers\Admin\AdminController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

// Redirect authenticated users to their correct dashboard
Route::get('/dashboard-redirect', function () {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect()->route('login');
    }
    $user = \Illuminate\Support\Facades\Auth::user();
    return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'driver.dashboard');
})->middleware('auth')->name('dashboard.redirect');


Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login',   [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
    Route::post('/register',[LoginController::class, 'register'])->name('register.post');

    // Forgot & Reset Password (tanpa email — langsung redirect ke form ganti password)
    Route::get('/forgot-password',  [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',  [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Driver Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    // Dashboard
    Route::get('/dashboard',  [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/top-up',    [DashboardController::class, 'topUp'])->name('top-up');
    Route::post('/toggle-online', [DashboardController::class, 'toggleOnline'])->name('toggle-online');
    Route::post('/appeal',        [DashboardController::class, 'appeal'])->name('appeal');

    // Orders
    Route::get('/orders/{order}',           [OrderController::class, 'show'])->name('order.detail');
    Route::post('/orders/{order}/take',     [OrderController::class, 'take'])->name('order.take');
    Route::post('/orders/{order}/ignore',   [OrderController::class, 'ignore'])->name('order.ignore');
    Route::post('/orders/{order}/status',   [OrderController::class, 'updateStatus'])->name('order.status');

    // Active Order
    Route::get('/active-order', [OrderController::class, 'activeOrder'])->name('active-order');

    // History
    Route::get('/history', [HistoryController::class, 'index'])->name('history');

    // Withdrawals
    Route::get('/withdraw',   [WithdrawController::class, 'index'])->name('withdraw');
    Route::post('/withdraw',  [WithdrawController::class, 'store'])->name('withdraw.store');

    // Profile
    Route::get('/profile',            [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update',    [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password',  [ProfileController::class, 'changePassword'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',                        [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders',                           [AdminController::class, 'orders'])->name('orders');
    Route::get('/drivers',                          [AdminController::class, 'drivers'])->name('drivers');
    Route::post('/drivers/{user}/approve',          [AdminController::class, 'approveDriver'])->name('drivers.approve');
    Route::post('/drivers/{user}/reject',           [AdminController::class, 'rejectDriver'])->name('drivers.reject');
    Route::post('/drivers/{driver}/warn',           [AdminController::class, 'warnDriver'])->name('drivers.warn');
    Route::post('/drivers/{driver}/unwarn',         [AdminController::class, 'unwarnDriver'])->name('drivers.unwarn');
    Route::post('/drivers/{driver}/suspend',        [AdminController::class, 'suspendDriver'])->name('drivers.suspend');
    Route::post('/drivers/{driver}/reinstate',      [AdminController::class, 'reinstateDriver'])->name('drivers.reinstate');
    Route::post('/suspicion/{suspicion}/review',    [AdminController::class, 'reviewSuspicion'])->name('suspicion.review');

    // Admin Withdrawals
    Route::get('/withdrawals',                      [AdminController::class, 'withdrawals'])->name('withdrawals');
    Route::post('/withdrawals/{withdrawal}/approve', [AdminController::class, 'approveWithdrawal'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject',  [AdminController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
});
