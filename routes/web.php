<?php

use App\Http\Controllers\Admin\AllocationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TicketDispatchController;
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
// Joygivers Foundation Main Landing Page (serves unified standalone + live-connected joygivers-website/)
Route::get('/joygivers', function (\App\Services\InventoryService $inventoryService) {
    $html = file_get_contents(base_path('joygivers-website/index.html'));
    $html = str_replace(
        ['src="./images/', 'content="./images/', 'href="./images/'],
        ['src="/joygivers/images/', 'content="/joygivers/images/', 'href="/joygivers/images/'],
        $html
    );

    $categoriesPayload = [];
    try {
        $event = \App\Models\Event::with(['categories' => function ($q) {
            $q->where('is_active', true)->orderBy('display_order');
        }])->where('slug', 'sagar-kinare-2026')->first();

        if ($event) {
            $categoriesPayload = $event->categories->map(function ($cat) use ($inventoryService) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'price_paise' => $cat->price_paise,
                    'available' => $inventoryService->getAvailableCapacity($cat),
                ];
            })->values()->all();
        }
    } catch (\Throwable $e) {
        // Fallback to static tier definitions inside joygivers-website/index.html
    }

    $runtimeInjection = '<meta name="csrf-token" content="' . csrf_token() . '" />' . "\n"
        . '  <script>window.JOYGIVERS_LIVE = ' . json_encode([
            'enabled' => true,
            'registerUrl' => route('register.store', [], false),
            'eventPageUrl' => app()->environment('production') ? 'https://raag-e-ishq.vercel.app' : route('home', [], false),
            'categories' => $categoriesPayload,
        ]) . ';</script>' . "\n</head>";

    $html = str_replace('</head>', $runtimeInjection, $html);

    return response($html, 200, [
        'Content-Type' => 'text/html; charset=UTF-8',
    ]);
})->name('joygivers.landing');

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
Route::get('/register', fn () => redirect()->route('home'))->name('register.show');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store')->middleware('throttle:30,1');

// Booking Payment & Polling
Route::get('/booking/{reference}/payment', [PaymentController::class, 'show'])->name('booking.payment');
Route::get('/booking/{reference}/status', [PaymentController::class, 'status'])->name('booking.status');
Route::get('/booking/{reference}/confirmed', [ConfirmationController::class, 'show'])->name('booking.confirmed');
Route::get('/pass/{reference}', [ConfirmationController::class, 'show'])->name('booking.pass');
Route::get('/p/{token}', [ConfirmationController::class, 'showShort'])->name('booking.pass.short');
Route::post('/booking/{reference}/simulate-payment', [PaymentController::class, 'simulatePayment'])->name('booking.simulate-payment')->middleware('throttle:30,1');

// Ticket Download (Signed URL) & Self-service Resend (Rate limited)
Route::get('/tickets/download/{uuid}', [TicketDownloadController::class, 'download'])->name('ticket.download')->middleware('signed');
Route::get('/tickets/{uuid}/qr', [TicketDownloadController::class, 'downloadQr'])->name('ticket.qr.download');
Route::get('/tickets/{uuid}/qr.png', [TicketDownloadController::class, 'downloadQrPng'])->name('ticket.qr.png');
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
    Route::post('/bookings/{id}/confirm-payment', [BookingController::class, 'confirmPayment'])->name('bookings.confirm-payment');
    Route::post('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');

    // Ticket Generator & Dispatch Desk
    Route::get('/dispatch', [TicketDispatchController::class, 'index'])->name('dispatch');
    Route::post('/dispatch/{id}/generate', [TicketDispatchController::class, 'generate'])->name('dispatch.generate');

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
