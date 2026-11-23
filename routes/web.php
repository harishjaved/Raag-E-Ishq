<?php

use App\Http\Controllers\Public\ConfirmationController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'show'])->name('home');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');

Route::get('/booking/{reference}/payment', [PaymentController::class, 'show'])->name('booking.payment');
Route::get('/booking/{reference}/status', [PaymentController::class, 'status'])->name('booking.status');
Route::get('/booking/{reference}/confirmed', [ConfirmationController::class, 'show'])->name('booking.confirmed');

// Razorpay signature-verified raw webhook endpoint
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');
