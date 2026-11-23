<?php

use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'show'])->name('home');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');

// Placeholders for payment and confirmation flows (Phase 3)
Route::get('/booking/{reference}/payment', function ($reference) {
    return response("Payment page for booking {$reference} will be implemented in Phase 3.", 200);
})->name('booking.payment');

Route::get('/booking/{reference}/status', function ($reference) {
    return response()->json(['status' => 'PENDING']);
})->name('booking.status');

Route::get('/booking/{reference}/confirmed', function ($reference) {
    return response("Booking confirmed for {$reference}", 200);
})->name('booking.confirmed');
