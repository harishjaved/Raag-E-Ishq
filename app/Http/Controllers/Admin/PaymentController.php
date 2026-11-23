<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request, InventoryService $inventoryService): View
    {
        $inventoryService->releaseExpiredHolds();

        $query = PaymentAttempt::with(['booking.contact'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20);

        return view('admin.payments.index', [
            'payments' => $payments,
            'currentStatus' => $status,
        ]);
    }

    public function reconcile(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'resolution_action' => ['required', 'in:FORCE_CONFIRM,REFUND,DISMISS'],
            'notes' => ['required', 'string', 'min:5'],
        ]);

        $payment = PaymentAttempt::with('booking')->findOrFail($id);
        $booking = $payment->booking;

        $action = $validated['resolution_action'];
        $notes = $validated['notes'];

        if ($action === 'FORCE_CONFIRM') {
            $booking->update(['status' => Booking::STATUS_CONFIRMED, 'confirmed_at' => now()]);
            $payment->update(['status' => PaymentAttempt::STATUS_CAPTURED]);
            app(\App\Services\TicketService::class)->issueTicketsForBooking($booking);
        } elseif ($action === 'REFUND') {
            $booking->update(['status' => Booking::STATUS_REFUNDED, 'cancellation_reason' => $notes]);
            $payment->update(['status' => PaymentAttempt::STATUS_FAILED]);
        } else {
            $booking->update(['status' => Booking::STATUS_CANCELLED, 'cancellation_reason' => $notes]);
        }

        AuditLog::log(
            action: 'PAYMENT_MANUAL_RECONCILIATION',
            targetType: 'PaymentAttempt',
            targetId: $payment->id,
            newValues: [
                'action' => $action,
                'notes' => $notes,
                'resolved_by' => auth()->user()->name,
            ]
        );

        return back()->with('success', "Payment #{$payment->id} successfully resolved ({$action}).");
    }
}
