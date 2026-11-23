<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
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
}
