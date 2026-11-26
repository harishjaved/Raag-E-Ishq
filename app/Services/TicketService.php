<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Contact;
use App\Models\EventSetting;
use App\Models\Ticket;
use App\Models\TicketCategory;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
     * Generate responsive PNG QR code for admission ticket using GD.
     */
    public function generateAdmissionQrPng(Ticket $ticket, int $scale = 10, int $margin = 4): string
    {
        $payload = $this->getAdmissionQrPayload($ticket);
        $qr = \BaconQrCode\Encoder\Encoder::encode($payload, \BaconQrCode\Common\ErrorCorrectionLevel::M());
        $matrix = $qr->getMatrix();
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();

        $imgWidth = ($width + $margin * 2) * $scale;
        $imgHeight = ($height + $margin * 2) * $scale;

        $im = imagecreatetruecolor($imgWidth, $imgHeight);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 15, 23, 42);

        imagefill($im, 0, 0, $white);

        $bytes = $matrix->getArray();
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($bytes[$y][$x] === 1) {
                    imagefilledrectangle(
                        $im,
                        ($x + $margin) * $scale,
                        ($y + $margin) * $scale,
                        ($x + $margin + 1) * $scale - 1,
                        ($y + $margin + 1) * $scale - 1,
                        $black
                    );
                }
            }
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        return $png;
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

    protected function getPassTokenSecret(): string
    {
        return (string) (config('app.key') ?: 'joygivers-pass-token-fallback');
    }

    /**
     * Build a compact, tamper-proof HMAC-signed /p/{token} URL so customer ticket links
     * stay short in WhatsApp/email while remaining viewable across serverless resets.
     */
    public function buildSignedPassUrl(Booking $booking): string
    {
        $booking->loadMissing(['contact', 'items.category', 'tickets.category']);

        $holderName = $booking->contact?->full_name ?? 'Guest';
        $defaultCategory = $booking->items->first()?->category?->name ?? 'Admission';

        $itemsPayload = $booking->items->map(fn ($item) => [
            $item->category?->name ?? 'Tier',
            (int) $item->quantity,
            (int) $item->subtotal_paise,
        ])->values()->all();

        $ticketsPayload = $booking->tickets->values()->map(function ($ticket, int $idx) use ($holderName, $defaultCategory) {
            $defaultAttendee = $idx === 0 ? $holderName : "{$holderName} (Guest #" . ($idx + 1) . ")";
            $ticketCat = $ticket->category?->name ?? 'Admission';

            if ($ticket->attendee_name === $defaultAttendee && $ticketCat === $defaultCategory) {
                return $ticket->ticket_number;
            }

            return [$ticket->ticket_number, $ticketCat, $ticket->attendee_name];
        })->all();

        $compactPayload = [
            $booking->booking_reference,
            $holderName,
            ($booking->is_attending ? 1 : 0) | ($booking->is_donation ? 2 : 0),
            (int) $booking->total_amount_paise,
            $itemsPayload,
            $ticketsPayload,
        ];

        $deflated = gzdeflate(json_encode($compactPayload, JSON_UNESCAPED_UNICODE), 9);
        $mac = substr(hash_hmac('sha256', "\x01" . $deflated, $this->getPassTokenSecret(), true), 0, 12);
        $token = rtrim(strtr(base64_encode("\x01" . $mac . $deflated), '+/', '-_'), '=');

        return route('booking.pass.short', ['token' => $token]);
    }

    /**
     * Verify and reconstruct booking + tickets from the compact /p/{token} pass format.
     *
     * @return array{booking: Booking, tickets: Collection<int, Ticket>}|null
     */
    public function restoreConfirmedBookingFromShortToken(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $padded = strtr($token, '-_', '+/');
        $mod = strlen($padded) % 4;
        if ($mod > 0) {
            $padded .= str_repeat('=', 4 - $mod);
        }

        $raw = base64_decode($padded, true);
        if ($raw === false || strlen($raw) < 14 || $raw[0] !== "\x01") {
            return null;
        }

        $providedMac = substr($raw, 1, 12);
        $deflated = substr($raw, 13);
        $expectedMac = substr(hash_hmac('sha256', "\x01" . $deflated, $this->getPassTokenSecret(), true), 0, 12);

        if (!hash_equals($expectedMac, $providedMac)) {
            return null;
        }

        $json = @gzinflate($deflated);
        if ($json === false) {
            return null;
        }

        $arr = json_decode($json, true);
        if (!is_array($arr) || count($arr) < 6 || empty($arr[0])) {
            return null;
        }

        $reference = (string) $arr[0];
        $holderName = (string) ($arr[1] ?? 'Guest');
        $flags = (int) ($arr[2] ?? 1);
        $totalPaise = (int) ($arr[3] ?? 0);
        $rawItems = is_array($arr[4]) ? $arr[4] : [];
        $rawTickets = is_array($arr[5]) ? $arr[5] : [];
        $defaultCategory = (string) ($rawItems[0][0] ?? 'Admission');

        return $this->hydrateRestoredBooking([
            'r' => $reference,
            'n' => $holderName,
            'e' => '',
            'a' => (bool) ($flags & 1),
            'd' => (bool) ($flags & 2),
            'm' => $totalPaise,
            'i' => array_map(fn ($row) => [
                'c' => (string) ($row[0] ?? 'Tier'),
                'q' => (int) ($row[1] ?? 1),
                's' => (int) ($row[2] ?? 0),
            ], $rawItems),
            't' => collect($rawTickets)->values()->map(function ($t, int $idx) use ($reference, $holderName, $defaultCategory) {
                $num = is_array($t) ? (string) ($t[0] ?? '') : (string) $t;
                $cat = is_array($t) ? (string) ($t[1] ?? $defaultCategory) : $defaultCategory;
                $defaultAtt = $idx === 0 ? $holderName : "{$holderName} (Guest #" . ($idx + 1) . ")";
                $att = is_array($t) ? (string) ($t[2] ?? $defaultAtt) : $defaultAtt;

                return [
                    'id' => $idx + 1,
                    'num' => $num,
                    'u' => $this->deriveDeterministicUuid($reference, $num),
                    'nc' => $this->deriveDeterministicNonce($reference, $num),
                    'cat' => $cat,
                    'att' => $att,
                ];
            })->all(),
        ]);
    }

    /**
     * Verify and reconstruct booking + tickets from legacy ?p=...&s=... pass URL tokens.
     *
     * @return array{booking: Booking, tickets: Collection<int, Ticket>}|null
     */
    public function restoreConfirmedBookingFromToken(string $reference, string $encoded, string $signature): ?array
    {
        if ($encoded === '' || $signature === '') {
            return null;
        }

        $expectedSig = rtrim(strtr(base64_encode(hash_hmac('sha256', $encoded, $this->getPassTokenSecret(), true)), '+/', '-_'), '=');
        if (!hash_equals($expectedSig, $signature)) {
            return null;
        }

        $padded = strtr($encoded, '-_', '+/');
        $mod = strlen($padded) % 4;
        if ($mod > 0) {
            $padded .= str_repeat('=', 4 - $mod);
        }

        $rawCompressed = base64_decode($padded, true);
        if ($rawCompressed === false) {
            return null;
        }

        $rawJson = @gzuncompress($rawCompressed);
        if ($rawJson === false) {
            return null;
        }

        $data = json_decode($rawJson, true);
        if (!is_array($data) || ($data['r'] ?? null) !== $reference) {
            return null;
        }

        return $this->hydrateRestoredBooking($data);
    }

    /**
     * @return array{booking: Booking, tickets: Collection<int, Ticket>}
     */
    protected function hydrateRestoredBooking(array $data): array
    {
        $booking = new Booking([
            'booking_reference' => $data['r'],
            'is_attending' => (bool) ($data['a'] ?? true),
            'is_donation' => (bool) ($data['d'] ?? false),
            'total_amount_paise' => (int) ($data['m'] ?? 0),
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $contact = new Contact([
            'full_name' => (string) ($data['n'] ?? 'Guest'),
            'email' => (string) ($data['e'] ?? ''),
        ]);
        $booking->setRelation('contact', $contact);

        $items = collect($data['i'] ?? [])->map(function (array $row) {
            $item = new BookingItem([
                'quantity' => (int) ($row['q'] ?? 1),
                'subtotal_paise' => (int) ($row['s'] ?? 0),
            ]);
            $item->setRelation('category', new TicketCategory(['name' => (string) ($row['c'] ?? 'Tier')]));
            return $item;
        });
        $booking->setRelation('items', $items);

        $tickets = collect($data['t'] ?? [])->map(function (array $t) {
            $ticket = new Ticket([
                'ticket_number' => (string) ($t['num'] ?? ''),
                'qr_uuid' => (string) ($t['u'] ?? ''),
                'qr_nonce' => (string) ($t['nc'] ?? ''),
                'attendee_name' => (string) ($t['att'] ?? 'Guest'),
                'status' => Ticket::STATUS_ISSUED,
            ]);
            $ticket->id = (int) ($t['id'] ?? 0);
            $ticket->setRelation('category', new TicketCategory(['name' => (string) ($t['cat'] ?? 'Admission')]));

            $ticket->qr_svg = $this->generateAdmissionQrSvg($ticket);
            $ticket->verification_url = $this->getAdmissionQrPayload($ticket);
            $ticket->qr_data_uri = 'data:image/svg+xml;base64,' . base64_encode($ticket->qr_svg);
            return $ticket;
        });
        $booking->setRelation('tickets', $tickets);

        return [
            'booking' => $booking,
            'tickets' => $tickets,
        ];
    }

    protected function deriveDeterministicUuid(string $reference, string $ticketNumber): string
    {
        $hex = substr(hash_hmac('sha256', "uuid:{$reference}:{$ticketNumber}", $this->getPassTokenSecret()), 0, 32);
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20, 12));
    }

    protected function deriveDeterministicNonce(string $reference, string $ticketNumber): string
    {
        return substr(hash_hmac('sha256', "nonce:{$reference}:{$ticketNumber}", $this->getPassTokenSecret()), 0, 32);
    }

    /**
     * Build structured WhatsApp text message for confirmed admission tickets.
     */
    public function buildWhatsAppTicketMessage(Booking $booking): string
    {
        $booking->loadMissing(['contact', 'items.category', 'tickets.category']);
        $amountFormatted = $booking->total_amount_paise % 100 === 0
            ? number_format($booking->total_amount_paise / 100, 0)
            : number_format($booking->total_amount_paise / 100, 2);
        $ticketsUrl = $this->buildSignedPassUrl($booking);

        if ($booking->is_donation) {
            $lines = [
                "*Joygivers Presents: Raag-E-Ishq*",
                "*Official Supporter & Donation Receipt*",
                "",
                "Dear *" . $booking->contact->full_name . "*,",
                "Thank you for your generous contribution of *₹" . $amountFormatted . "* supporting girl education!",
                "",
                "• *Booking Reference:* " . $booking->booking_reference,
                "• *Official Supporter Receipt:*",
                $ticketsUrl,
                "",
                "100% of proceeds fund girl education and welfare programmes. Thank you for making a lasting difference!",
            ];
            return implode("\n", $lines);
        }

        $categorySummary = $booking->items->map(function ($item) {
            return ($item->category->name ?? 'Tier') . " (" . $item->quantity . " " . ($item->quantity === 1 ? 'Seat' : 'Seats') . ")";
        })->implode(', ');

        $ticketSummary = '';
        if ($booking->tickets->isNotEmpty()) {
            $ticketSummary = $booking->tickets->map(function ($ticket, $idx) {
                return "Pass #" . ($idx + 1) . " (" . ($ticket->category->name ?? 'Admission') . " - " . $ticket->ticket_number . ")";
            })->implode(', ');
        } else {
            $ticketSummary = $categorySummary ?: 'Admission Pass';
        }

        $lines = [
            "*Raag-E-Ishq — Official Admission Pass*",
            "",
            "Dear *" . $booking->contact->full_name . "*,",
            "Your ticket booking is confirmed! Here is your official entry pass:",
            "",
            "• *Booking Ref:* " . $booking->booking_reference,
            "• *Tickets:* " . $ticketSummary,
            "• *Date & Time:* Sunday, 17th January 2027 • 7:00 PM",
            "• *Venue:* Chavara Hall, Near South Railway Station, Kochi",
            "",
            "*Open Your QR Ticket Pass:*",
            $ticketsUrl,
            "",
            "Present this QR pass at the entrance for instant check-in.",
            "(100% proceeds dedicated to girl education)",
        ];

        return implode("\n", $lines);
    }

    /**
     * Build payment instructions WhatsApp message for reservation request.
     */
    public function buildWhatsAppPaymentMessage(Booking $booking): string
    {
        $amountFormatted = $booking->total_amount_paise % 100 === 0
            ? number_format($booking->total_amount_paise / 100, 0)
            : number_format($booking->total_amount_paise / 100, 2);
        $vpa = config('services.razorpay.merchant_vpa', 'mujammilibrahim007@okhdfcbank');
        $lines = [
            "*Joygivers Presents: Raag-E-Ishq*",
            "*Reservation Request & Payment Instructions*",
            "",
            "Hello *" . $booking->contact->full_name . "*!",
            "Thank you for reserving your seats for Raag-E-Ishq at Chavara Hall, Kochi.",
            "",
            "• *Booking Reference:* " . $booking->booking_reference,
            "• *Total Amount Payable:* ₹" . $amountFormatted,
            "",
            "*UPI Payment Details:*",
            "• UPI ID: *" . $vpa . "*",
            "• Payee: Joygivers Event Desk",
            "",
            "Please submit your UTR / payment screenshot reference on your booking page within 30 minutes so our team can verify payment and send your QR Entry Passes immediately.",
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
        return "https://wa.me/" . $phoneClean . "?text=" . rawurlencode($message);
    }

    protected function generateUniqueTicketNumber(): string
    {
        do {
            $num = 'TKT-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (Ticket::where('ticket_number', $num)->exists());

        return $num;
    }
}
