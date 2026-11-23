<?php

use App\Http\Controllers\Admin\AllocationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController;
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
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store')->middleware('throttle:30,1');

// Booking Payment & Polling
Route::get('/booking/{reference}/payment', [PaymentController::class, 'show'])->name('booking.payment');
Route::get('/booking/{reference}/status', [PaymentController::class, 'status'])->name('booking.status');
Route::get('/booking/{reference}/confirmed', [ConfirmationController::class, 'show'])->name('booking.confirmed');
Route::post('/booking/{reference}/simulate-payment', [PaymentController::class, 'simulatePayment'])->name('booking.simulate-payment')->middleware('throttle:30,1');

// Ticket Download (Signed URL) & Self-service Resend (Rate limited)
Route::get('/tickets/download/{uuid}', [TicketDownloadController::class, 'download'])->name('ticket.download')->middleware('signed');
Route::get('/tickets/resend', [TicketResendController::class, 'show'])->name('tickets.resend.show');
Route::post('/tickets/resend', [TicketResendController::class, 'send'])->name('tickets.resend.send')->middleware('throttle:5,10');

// Razorpay raw webhook endpoint
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Staff Gate Operations (Restricted to Gate Staff, Event Manager, Super Admin)
Route::middleware(['auth', 'role:scan'])->group(function () {
    Route::get('/staff/scan', [ScanController::class, 'index'])->name('staff.scan');
    Route::post('/staff/scan/verify', [ScanController::class, 'verify'])->name('staff.scan.verify')->middleware('throttle:120,1');
    Route::post('/staff/lookup/manual', [ScanController::class, 'manualLookup'])->name('staff.lookup.manual')->middleware('throttle:30,1');
});

// Admin Back Office (Super Admin, Event Manager, Finance)
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRM Contacts & Bookings
    Route::get('/crm/contacts', [ContactController::class, 'index'])->name('crm.contacts');
    Route::get('/crm/contacts/{id}', [ContactController::class, 'show'])->name('crm.contacts.show');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/allocations', [AllocationController::class, 'index'])->name('allocations');
    Route::post('/allocations', [AllocationController::class, 'store'])->name('allocations.store');
    Route::post('/allocations/{allocation}/generate-tickets', [AllocationController::class, 'generateTickets'])->name('allocations.generate-tickets');

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
    });
});
