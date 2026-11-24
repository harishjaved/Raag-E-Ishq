<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TicketDownloadController extends Controller
{
    public function download(Request $request, string $uuid, TicketService $ticketService): View
    {
        // Enforce valid signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired ticket download link.');
        }

        $ticket = Ticket::with(['category', 'booking'])
            ->where('qr_uuid', $uuid)
            ->firstOrFail();

        $qrSvg = $ticketService->generateAdmissionQrSvg($ticket, 280);

        return view('public.ticket_download', [
            'ticket' => $ticket,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function downloadQr(string $uuid, TicketService $ticketService): Response
    {
        $ticket = Ticket::with(['category', 'booking'])
            ->where('qr_uuid', $uuid)
            ->firstOrFail();

        $qrSvg = $ticketService->generateAdmissionQrSvg($ticket, 400);

        return response($qrSvg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="Raag-E-Ishq-' . $ticket->ticket_number . '-QR.svg"',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    public function downloadQrPng(string $uuid, TicketService $ticketService): Response
    {
        $ticket = Ticket::with(['category', 'booking'])
            ->where('qr_uuid', $uuid)
            ->firstOrFail();

        $pngData = $ticketService->generateAdmissionQrPng($ticket, 10, 4);

        return response($pngData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="Raag-E-Ishq-' . $ticket->ticket_number . '-QR.png"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
