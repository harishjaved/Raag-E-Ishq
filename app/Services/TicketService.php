<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\EventSetting;
use App\Models\Ticket;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    /**
     * Issue tickets idempotently for a confirmed booking.
     * Exactly one ticket per entitled admission.
     *
     * @return \Illuminate\Support\Collection<int, Ticket>
     */
    public function issueTicketsForBooking(Booking $booking): \Illuminate\Support\Collection
    {
        return DB::transaction(function () use ($booking) {
            // Check if tickets have already been issued
            $existingTickets = $booking->tickets()->get();
            if ($existingTickets->isNotEmpty()) {
                return $existingTickets;
            }

            $booking->loadMissing(['items.category', 'contact']);
            $tickets = collect();
            $ticketIndex = 1;

            foreach ($booking->items as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $attendeeName = $ticketIndex === 1
                        ? $booking->contact->full_name
                        : "{$booking->contact->full_name} (Guest #{$ticketIndex})";

                    $ticketNumber = $this->generateUniqueTicketNumber();
                    $qrUuid = (string) Str::uuid();
                    $qrNonce = Str::random(32);

                    $ticket = Ticket::create([
                        'booking_id' => $booking->id,
                        'booking_item_id' => $item->id,
                        'ticket_category_id' => $item->ticket_category_id,
                        'ticket_number' => $ticketNumber,
                        'qr_uuid' => $qrUuid,
                        'qr_nonce' => $qrNonce,
                        'attendee_name' => $attendeeName,
                        'status' => Ticket::STATUS_ISSUED,
                        'issued_at' => Carbon::now(),
                    ]);

                    $tickets->push($ticket);
                    $ticketIndex++;
                }
            }

            AuditLog::log(
                action: 'TICKETS_ISSUED',
                targetType: 'Booking',
                targetId: $booking->id,
                newValues: [
                    'tickets_count' => $tickets->count(),
                    'booking_reference' => $booking->booking_reference,
                ]
            );

            // Queue transactional confirmation email with retry protection
            \App\Jobs\SendTicketEmailJob::dispatch($booking);

            return $tickets;
        });
    }

    /**
     * Generate a tamper-proof signed URL for downloading an individual ticket.
     */
    public function getDownloadSignedUrl(Ticket $ticket): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'ticket.download',
            now()->addDays(7),
            ['uuid' => $ticket->qr_uuid]
        );
    }

    /**
     * Void a ticket with reason and audit logging.
     */
    public function voidTicket(Ticket $ticket, string $reason, ?\App\Models\User $actor = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $reason, $actor) {
            $ticket->update([
                'status' => Ticket::STATUS_VOID,
                'voided_at' => Carbon::now(),
                'void_reason' => $reason,
            ]);

            AuditLog::log(
                action: 'TICKET_VOIDED',
                targetType: 'Ticket',
                targetId: $ticket->id,
                oldValues: ['status' => Ticket::STATUS_ISSUED],
                newValues: ['status' => Ticket::STATUS_VOID, 'reason' => $reason],
                userId: $actor?->id
            );

            return $ticket;
        });
    }

    /**
     * Compute the cryptographic HMAC-SHA256 signature for a ticket.
     * Payload: ticket_id + qr_nonce signed with secret key.
     */
    public function generateHmacSignature(Ticket $ticket): string
    {
        $secret = config('app.ticket_secret') ?: EventSetting::get('ticket_hmac_secret', config('app.key'));
        return hash_hmac('sha256', $ticket->id . ':' . $ticket->qr_nonce, $secret);
    }

    /**
     * Get admission QR payload URL (No PII).
     */
    public function getAdmissionQrPayload(Ticket $ticket): string
    {
        $sig = $this->generateHmacSignature($ticket);
        return url("/gate/verify?u={$ticket->qr_uuid}&s={$sig}");
    }

    /**
     * Generate responsive SVG QR code for the admission ticket.
     */
    public function generateAdmissionQrSvg(Ticket $ticket, int $size = 250): string
    {
        $payload = $this->getAdmissionQrPayload($ticket);
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        return $writer->writeString($payload);
    }

    /**
     * Alias for generateAdmissionQrSvg for convenience.
     */
    public function generateQrSvg(Ticket $ticket, int $size = 250): string
    {
        return $this->generateAdmissionQrSvg($ticket, $size);
    }

    /**
     * Verify an admission token against database and HMAC signature.
     *
     * @return array{valid: bool, status: string, message: string, ticket: ?Ticket}
     */
    public function verifyAdmissionToken(string $uuid, string $signature): array
    {
        $ticket = Ticket::with(['category', 'booking.contact', 'checkin.scannedBy'])
            ->where('qr_uuid', $uuid)
            ->first();

        if (!$ticket) {
            return [
                'valid' => false,
                'status' => 'INVALID',
                'message' => 'Ticket not found or invalid QR code.',
                'ticket' => null,
            ];
        }

        $expectedSig = $this->generateHmacSignature($ticket);
        if (!hash_equals($expectedSig, $signature)) {
            return [
                'valid' => false,
                'status' => 'INVALID_SIGNATURE',
                'message' => 'Cryptographic signature mismatch. Possible counterfeit ticket.',
                'ticket' => null,
            ];
        }

        if ($ticket->status === Ticket::STATUS_VOID || $ticket->status === Ticket::STATUS_CANCELLED) {
            return [
                'valid' => false,
                'status' => 'VOID',
                'message' => "Ticket is void/cancelled: {$ticket->void_reason}",
                'ticket' => $ticket,
            ];
        }

        if ($ticket->status === Ticket::STATUS_CHECKED_IN || $ticket->checkin !== null) {
            return [
                'valid' => false,
                'status' => 'ALREADY_CHECKED_IN',
                'message' => "Already admitted at " . ($ticket->checkin?->checked_in_at?->format('H:i:s d M') ?? 'earlier'),
                'ticket' => $ticket,
            ];
        }

        if ($ticket->status !== Ticket::STATUS_ISSUED) {
            return [
                'valid' => false,
                'status' => 'UNPAID',
                'message' => 'Ticket has not been issued or payment is unverified.',
                'ticket' => $ticket,
            ];
        }

        return [
            'valid' => true,
            'status' => 'VALID',
            'message' => 'Valid admission ticket.',
            'ticket' => $ticket,
        ];
    }

    /**
     * Build structured WhatsApp text message for confirmed admission tickets.
     */
    public function buildWhatsAppTicketMessage(Booking $booking): string
    {
        $booking->loadMissing(['contact', 'items.category', 'tickets.category']);
        $amountFormatted = number_format($booking->total_amount_paise / 100, 2);
        $ticketsUrl = route('booking.confirmed', $booking->booking_reference);

        if ($booking->is_donation) {
            $lines = [
                "💖 *Joygivers Presents: Raag-E-Ishq*",
                "*Official Supporter & Donation Receipt*",
                "",
                "Dear *" . $booking->contact->full_name . "*,",
                "Thank you for your generous contribution of *₹" . $amountFormatted . "* supporting girl education! 🌟",
                "",
                "📋 *Booking Reference:* " . $booking->booking_reference,
                "📄 *Official Supporter Receipt:* " . $ticketsUrl,
                "",
                "100% of proceeds fund girl education and welfare programmes. Thank you for making a lasting difference! ✨",
            ];
            return implode("\n", $lines);
        }

        $lines = [
            "🎟️ *Joygivers Presents: Raag-E-Ishq*",
            "*Official Admission Pass & Gate QR Entry*",
            "",
            "Dear *" . $booking->contact->full_name . "*,",
            "Your tickets for *Raag-E-Ishq* have been approved and confirmed!",
            "",
            "📋 *Booking Reference:* " . $booking->booking_reference,
        ];

        $categorySummary = $booking->items->map(function ($item) {
            return ($item->category->name ?? 'Tier') . " (" . $item->quantity . " " . ($item->quantity === 1 ? 'Seat' : 'Seats') . ")";
        })->implode(', ');

        if ($categorySummary) {
            $lines[] = "💺 *Category:* " . $categorySummary;
        }

        $lines[] = "📅 *Event Date:* Tuesday, 17th December 2026";
        $lines[] = "⏰ *Time:* 7:00 PM IST (Gates open 6:15 PM)";
        $lines[] = "📍 *Venue:* Chavara Hall, Near South Railway Station, Kochi";
        $lines[] = "";
        $lines[] = "📲 *View & Download Full Digital Passes:*";
        $lines[] = $ticketsUrl;

        if ($booking->tickets->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "🎫 *Your Individual Admission Pass & QR Download Links:*";
            foreach ($booking->tickets as $idx => $ticket) {
                $num = $idx + 1;
                $catName = $ticket->category->name ?? 'Admission';
                $directUrl = \Illuminate\Support\Facades\URL::signedRoute('ticket.download', ['uuid' => $ticket->qr_uuid]);
                $lines[] = "• Pass #" . $num . " (" . $catName . " - " . $ticket->ticket_number . "): " . $directUrl;
            }
        }

        $lines[] = "";
        $lines[] = "⚠️ *Gate Entry Instructions:*";
        $lines[] = "• Present the QR code on your phone or printed pass at the entrance for instant scanning.";
        $lines[] = "• All admission bookings are final and non-refundable (all proceeds dedicated to girl education).";
        $lines[] = "";
        $lines[] = "We look forward to welcoming you to an unforgettable musical evening! 🎶✨";

        return implode("\n", $lines);
    }

    /**
     * Build payment instructions WhatsApp message for enquiry.
     */
    public function buildWhatsAppPaymentMessage(Booking $booking): string
    {
        $amountFormatted = number_format($booking->total_amount_paise / 100, 2);
        $lines = [
            "🎵 *Joygivers Presents: Raag-E-Ishq*",
            "*Ticket Booking Enquiry Confirmation*",
            "",
            "Hello *" . $booking->contact->full_name . "*!",
            "Thank you for registering for Raag-E-Ishq at Chavara Hall, Kochi.",
            "",
            "📋 *Enquiry Reference:* " . $booking->booking_reference,
            "💰 *Total Amount Payable:* ₹" . $amountFormatted,
            "",
            "💳 *UPI Payment Details:*",
            "• UPI ID: *mujammilibrahim007@okhdfcbank*",
            "• Payee: Joygivers Event Desk",
            "",
            "Please send your payment screenshot / UTR number here once done so our team can approve and issue your unique QR admission pass immediately. 🎟️",
        ];
        return implode("\n", $lines);
    }

    /**
     * Build clean wa.me URL with country code prefix.
     */
    public function buildWhatsAppUrl(string $phone, string $message): string
    {
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) === 10) {
            $phoneClean = '91' . $phoneClean;
        }
        return "https://wa.me/" . $phoneClean . "?text=" . urlencode($message);
    }

    protected function generateUniqueTicketNumber(): string
    {
        do {
            $num = 'TKT-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (Ticket::where('ticket_number', $num)->exists());

        return $num;
    }
}
