<?php

use App\Http\Controllers\Public\ConfirmationController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\TicketDownloadController;
use App\Http\Controllers\Public\TicketResendController;
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

// Webhook
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');
