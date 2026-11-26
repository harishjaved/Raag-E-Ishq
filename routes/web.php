<?php

use App\Http\Controllers\Admin\AllocationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\TicketDispatchController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\ConfirmationController;
use App\Http\Controllers\Public\DonationController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\TicketDownloadController;
use App\Http\Controllers\Public\TicketResendController;
use App\Http\Controllers\Staff\ScanController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

// Unified Joygivers Presents: Raag-E-Ishq Public Experience
Route::get('/', [EventController::class, 'show'])->name('home');
Route::get('/event', [EventController::class, 'show'])->name('event.show');
Route::get('/joygivers', fn () => redirect()->route('home', [], 301))->name('joygivers.landing');
Route::get('/calendar/raag-e-ishq.ics', [EventController::class, 'calendarIcs'])->name('event.calendar');

Route::get('/joygivers/images/{file}', function (string $file) {
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
        abort(404);
    }
    $path = base_path('joygivers-website/images/' . $file);
    if (!is_file($path)) {
        abort(404);
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
    ];

    return response(file_get_contents($path), 200, [
        'Content-Type' => $mimeTypes[$ext] ?? 'application/octet-stream',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('joygivers.asset');

// Ticket Reservation & Standalone Donations
Route::get('/register', fn () => redirect()->route('event.show'))->name('register.show');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store')->middleware('throttle:30,1');
Route::post('/donate', [DonationController::class, 'store'])->name('donate.store')->middleware('throttle:30,1');
Route::get('/donations/{reference}', [DonationController::class, 'show'])->name('donation.show');
Route::post('/donations/{reference}/submit-payment', [DonationController::class, 'submitPaymentProof'])->name('donation.submit-payment')->middleware('throttle:20,1');

// Booking Hold Timer, UTR Proof Submission, Status & Confirmed Passes
Route::get('/booking/{reference}/payment', [PaymentController::class, 'show'])->name('booking.payment');
Route::post('/booking/{reference}/submit-payment', [PaymentController::class, 'submitPaymentProof'])->name('booking.submit-payment')->middleware('throttle:20,1');
Route::get('/booking/{reference}/status', [PaymentController::class, 'status'])->name('booking.status');
Route::get('/booking/{reference}/confirmed', [ConfirmationController::class, 'show'])->name('booking.confirmed');
Route::get('/pass/{reference}', [ConfirmationController::class, 'show'])->name('booking.pass');
Route::get('/p/{token}', [ConfirmationController::class, 'showShort'])->name('booking.pass.short');
Route::post('/booking/{reference}/simulate-payment', [PaymentController::class, 'simulatePayment'])->name('booking.simulate-payment')->middleware('throttle:30,1');

// Ticket Download (Signed URL) & Self-service Resend
Route::get('/tickets/download/{uuid}', [TicketDownloadController::class, 'download'])->name('ticket.download')->middleware('signed');
Route::get('/tickets/{uuid}/qr', [TicketDownloadController::class, 'downloadQr'])->name('ticket.qr.download');
Route::get('/tickets/{uuid}/qr.png', [TicketDownloadController::class, 'downloadQrPng'])->name('ticket.qr.png');
Route::get('/tickets/resend', [TicketResendController::class, 'show'])->name('tickets.resend.show');
Route::post('/tickets/resend', [TicketResendController::class, 'send'])->name('tickets.resend.send')->middleware('throttle:5,10');

// Razorpay Raw Webhook Endpoint
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');

// Authentication & Two-Factor Challenge Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactorChallenge'])->name('two-factor.verify')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Staff Gate Operations (Restricted to Gate Staff, Event Manager, Super Admin)
Route::middleware(['auth', 'role:scan'])->group(function () {
    Route::get('/staff/scan', [ScanController::class, 'index'])->name('staff.scan');
    Route::post('/staff/scan/verify', [ScanController::class, 'verify'])->name('staff.scan.verify')->middleware('throttle:120,1');
    Route::post('/staff/lookup/manual', [ScanController::class, 'manualLookup'])->name('staff.lookup.manual')->middleware('throttle:30,1');
});

// Admin Back Office (Super Admin, Event Manager, Finance Officer - strictly blocks GATE_STAFF)
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRM Contacts & Bookings + Offline Bookings & Resend
    Route::get('/crm/contacts', [ContactController::class, 'index'])->name('crm.contacts');
    Route::get('/crm/contacts/{id}', [ContactController::class, 'show'])->name('crm.contacts.show');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings');
    Route::post('/bookings/offline', [BookingController::class, 'storeOffline'])->name('bookings.offline');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/confirm-payment', [BookingController::class, 'confirmPayment'])->name('bookings.confirm-payment');
    Route::post('/bookings/{id}/resend', [BookingController::class, 'resendPasses'])->name('bookings.resend');
    Route::post('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::post('/donations/{id}/acknowledge', [BookingController::class, 'acknowledgeDonation'])->name('donations.acknowledge');

    // Ticket Generator & Dispatch Desk
    Route::get('/dispatch', [TicketDispatchController::class, 'index'])->name('dispatch');
    Route::post('/dispatch/{id}/generate', [TicketDispatchController::class, 'generate'])->name('dispatch.generate');

    Route::get('/allocations', [AllocationController::class, 'index'])->name('allocations');
    Route::post('/allocations', [AllocationController::class, 'store'])->name('allocations.store');
    Route::post('/allocations/{allocation}/generate-tickets', [AllocationController::class, 'generateTickets'])->name('allocations.generate-tickets');

    // Account Security & Two-Factor Authentication
    Route::get('/security', [SecurityController::class, 'index'])->name('security');
    Route::post('/security/2fa/enable', [SecurityController::class, 'enableTwoFactor'])->name('security.2fa.enable');
    Route::post('/security/2fa/disable', [SecurityController::class, 'disableTwoFactor'])->name('security.2fa.disable');
    Route::post('/security/password', [SecurityController::class, 'updatePassword'])->name('security.password');

    // Finance Restricted (Super Admin & Finance only)
    Route::middleware('role:finance')->group(function () {
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments');
        Route::post('/payments/{id}/reconcile', [AdminPaymentController::class, 'reconcile'])->name('payments.reconcile');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // Super Admin Only
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
        Route::post('/security/staff/{id}/toggle', [SecurityController::class, 'toggleStaffStatus'])->name('security.staff.toggle');
    });
});
