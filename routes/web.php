<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\ConfirmationController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\TicketDownloadController;
use App\Http\Controllers\Public\TicketResendController;
use App\Http\Controllers\Staff\ScanController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

// Public Registration & Event
Route::get('/', [EventController::class, 'show'])->name('home');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');

// Booking Payment & Polling
Route::get('/booking/{reference}/payment', [PaymentController::class, 'show'])->name('booking.payment');
Route::get('/booking/{reference}/status', [PaymentController::class, 'status'])->name('booking.status');
Route::get('/booking/{reference}/confirmed', [ConfirmationController::class, 'show'])->name('booking.confirmed');

// Ticket Download (Signed URL) & Self-service Resend (Rate limited)
Route::get('/tickets/download/{uuid}', [TicketDownloadController::class, 'download'])->name('ticket.download')->middleware('signed');
Route::get('/tickets/resend', [TicketResendController::class, 'show'])->name('tickets.resend.show');
Route::post('/tickets/resend', [TicketResendController::class, 'send'])->name('tickets.resend.send')->middleware('throttle:5,10');

// Razorpay raw webhook endpoint
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Staff Gate Operations (Restricted to Gate Staff, Event Manager, Super Admin)
Route::middleware(['auth', 'role:scan'])->group(function () {
    Route::get('/staff/scan', [ScanController::class, 'index'])->name('staff.scan');
    Route::post('/staff/scan/verify', [ScanController::class, 'verify'])->name('staff.scan.verify');
    Route::post('/staff/lookup/manual', [ScanController::class, 'manualLookup'])->name('staff.lookup.manual');
});
