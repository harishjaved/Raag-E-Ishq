<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Donation;
use App\Services\AnalyticsService;
use App\Services\InventoryService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function sales(AnalyticsService $analyticsService, InventoryService $inventoryService): View
    {
        $inventoryService->releaseExpiredHolds();

        $metrics = $analyticsService->getDashboardMetrics();

        $settlements = Booking::with(['items.category', 'contact', 'paymentAttempts'])
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_BOOKING_CONFIRMED, Booking::STATUS_TICKETS_ISSUED])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $donations = Donation::with('contact')
            ->latest('id')
            ->limit(25)
            ->get();

        return view('admin.reports.sales', [
            'metrics' => $metrics,
            'settlements' => $settlements,
            'donations' => $donations,
        ]);
    }

    public function export(): StreamedResponse
    {
        AuditLog::log(
            action: 'FINANCE_REPORT_EXPORTED',
            targetType: 'Report',
            newValues: ['type' => 'CSV_SETTLEMENT_RECONCILIATION'],
            userId: auth()->id()
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="raag_e_ishq_finance_reconciliation_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for clean Microsoft Excel rendering
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Record Type',
                'Booking Reference',
                'Booking Date',
                'Status',
                'Category',
                'Quantity',
                'Unit Price (INR)',
                'Total Amount (INR)',
                'Channel',
                'Payment Method',
                'UTR / Payment Reference',
            ]);

            Booking::with(['items.category', 'paymentAttempts'])
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_BOOKING_CONFIRMED, Booking::STATUS_TICKETS_ISSUED])
                ->chunk(100, function ($bookings) use ($handle) {
                    foreach ($bookings as $booking) {
                        $item = $booking->items->first();
                        $payment = $booking->paymentAttempts()->where('status', 'CAPTURED')->latest()->first();

                        $type = $booking->is_donation
                            ? 'Supporter Pass Donation'
                            : ($booking->is_offline_booking ? 'Offline Ticket Booking' : 'Ticket Booking');

                        fputcsv($handle, [
                            $type,
                            $booking->booking_reference,
                            $booking->confirmed_at?->format('Y-m-d H:i:s') ?? $booking->created_at->format('Y-m-d H:i:s'),
                            $booking->status,
                            $item?->category?->name ?? 'General',
                            $item?->quantity ?? 1,
                            number_format(($item?->unit_price_paise ?? 0) / 100, 2),
                            number_format($booking->total_amount_paise / 100, 2),
                            $booking->payment_channel ?? ($booking->is_offline_booking ? 'OFFLINE' : 'ONLINE'),
                            $payment?->method ?? 'UPI',
                            $booking->utr_reference ?: ($payment?->provider_payment_id ?? 'N/A'),
                        ]);
                    }
                });

            Donation::query()
                ->chunk(100, function ($donations) use ($handle) {
                    foreach ($donations as $donation) {
                        fputcsv($handle, [
                            'Cause Donation (No Seat)',
                            $donation->donation_reference,
                            ($donation->acknowledged_at ?? $donation->created_at)?->format('Y-m-d H:i:s'),
                            $donation->status,
                            'Cause Donation',
                            1,
                            number_format($donation->amount_paise / 100, 2),
                            number_format($donation->amount_paise / 100, 2),
                            'CAUSE_MODULE',
                            $donation->payment_method ?? 'UPI',
                            $donation->utr_reference ?? 'N/A',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
