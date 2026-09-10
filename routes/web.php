<?php

use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Guest Booking Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [BookingController::class, 'index'])->name('home');
Route::post('/pesan-tiket/draft', [BookingController::class, 'saveDraft'])->name('booking.draft');

// Portal Verifikasi Resmi (Scan QR Code)
Route::get('/tiket/verifikasi/{verificationCode}', [VerificationController::class, 'verify'])->name('ticket.verify');
Route::post('/tiket/verifikasi/{verificationCode}/checkin', [VerificationController::class, 'checkIn'])->name('ticket.checkin');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| User Booking & Transaction Routes (Requires Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [BookingController::class, 'checkoutPage'])->name('checkout.page');
    Route::post('/checkout', [BookingController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/riwayat-transaksi', [BookingController::class, 'ordersHistory'])->name('orders.history');
    Route::get('/pesanan/{invoiceNumber}', [BookingController::class, 'showOrder'])->name('orders.show');
    Route::post('/pesanan/{invoiceNumber}/bukti-bayar', [BookingController::class, 'updatePaymentProof'])->name('orders.uploadProof');
    Route::get('/pesanan/{invoiceNumber}/e-ticket', [BookingController::class, 'eTicket'])->name('orders.eticket');
});

/*
|--------------------------------------------------------------------------
| Admin Management Routes (Requires Auth & Admin Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminOrderController::class, 'dashboard'])->name('dashboard');

    // Manajemen Produk Tiket (CRUD oleh Admin)
    Route::resource('tickets', AdminTicketController::class)->except(['show']);
    Route::post('/tickets/{ticket}/toggle', [AdminTicketController::class, 'toggleStatus'])->name('tickets.toggle');

    // Manajemen Transaksi & Verifikasi Pembayaran Manual
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/approve', [AdminOrderController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [AdminOrderController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/mark-used', [AdminOrderController::class, 'markUsed'])->name('orders.markUsed');
});
