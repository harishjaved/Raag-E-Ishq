<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Services\AnalyticsService;
use App\Services\InventoryService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function sales(AnalyticsService $analyticsService, InventoryService $inventoryService): View
    {
        $inventoryService->releaseExpiredHolds();

        $metrics = $analyticsService->getDashboardMetrics();

        $settlements = Booking::with(['items.category', 'contact', 'paymentAttempts'])
            ->where('status', Booking::STATUS_CONFIRMED)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.sales', [
            'metrics' => $metrics,
            'settlements' => $settlements,
        ]);
    }

    public function export(): StreamedResponse
    {
        AuditLog::log(
            action: 'FINANCE_REPORT_EXPORTED',
            targetType: 'Report',
            newValues: ['type' => 'CSV_SETTLEMENT_SALES']
        );

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sagar_kinare_finance_export_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Booking Reference',
                'Booking Date',
                'Status',
                'Category',
                'Quantity',
                'Unit Price (INR)',
                'Total Amount (INR)',
                'Payment Method',
                'Gateway Payment ID',
            ]);

            Booking::with(['items.category', 'paymentAttempts'])
                ->where('status', Booking::STATUS_CONFIRMED)
                ->chunk(100, function ($bookings) use ($handle) {
                    foreach ($bookings as $booking) {
                        $item = $booking->items->first();
                        $payment = $booking->paymentAttempts()->where('status', 'CAPTURED')->latest()->first();

                        fputcsv($handle, [
                            $booking->booking_reference,
                            $booking->confirmed_at?->format('Y-m-d H:i:s') ?? $booking->created_at->format('Y-m-d H:i:s'),
                            $booking->status,
                            $item?->category?->name ?? 'General',
                            $item?->quantity ?? 1,
                            number_format(($item?->unit_price_paise ?? 0) / 100, 2),
                            number_format($booking->total_amount_paise / 100, 2),
                            $payment?->method ?? 'UPI',
                            $payment?->provider_payment_id ?? 'N/A',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
