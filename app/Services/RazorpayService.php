<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\GatewayWebhookEvent;
use App\Models\InventoryHold;
use App\Models\PaymentAttempt;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Throwable;

class RazorpayService
{
    protected ?Api $api = null;
    protected InventoryService $inventoryService;
    protected TicketService $ticketService;

    public function __construct(InventoryService $inventoryService, TicketService $ticketService)
    {
        $this->inventoryService = $inventoryService;
        $this->ticketService = $ticketService;

        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');
        if ($keyId && $keySecret && $keyId !== 'rzp_test_dummyKeyId') {
            try {
                $this->api = new Api($keyId, $keySecret);
            } catch (Throwable $e) {
                Log::warning('Razorpay API client initialization skipped: ' . $e->getMessage());
            }
        }
    }

    /**
     * Create or reuse an active payment attempt for a booking with UPI QR and mobile payment metadata.
     */
    public function createPaymentAttempt(Booking $booking, string $method = 'auto'): PaymentAttempt
    {
        $existing = $booking->paymentAttempts()
            ->whereIn('status', [PaymentAttempt::STATUS_CREATED, PaymentAttempt::STATUS_AWAITING_PAYMENT])
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($existing) {
            return $existing;
        }

        $rupees = number_format($booking->total_amount_paise / 100, 2, '.', '');
        $upiPayload = "upi://pay?pa=joygivers@razorpay&pn=Joygivers%20Sagar%20Kinare&am={$rupees}&cu=INR&tr={$booking->booking_reference}&tn=Sagar%20Kinare%20Tickets";

        $providerQrId = null;
        $providerOrderId = null;
        $qrImageUrl = null;

        // If Razorpay API client is active and network is enabled, attempt provider QR creation
        if ($this->api) {
            try {
                // Attempt Dynamic UPI QR Code creation
                $qrResponse = $this->api->qrCode->create([
                    'type' => 'upi_qr',
                    'name' => 'Joygivers - ' . $booking->booking_reference,
                    'usage' => 'single_use',
                    'fixed_amount' => true,
                    'payment_amount' => $booking->total_amount_paise,
                    'description' => "Admissions for Sagar Kinare ({$booking->booking_reference})",
                    'close_by' => $booking->hold_expires_at?->timestamp ?? Carbon::now()->addMinutes(10)->timestamp,
                    'notes' => [
                        'booking_reference' => $booking->booking_reference,
                    ],
                ]);

                $providerQrId = $qrResponse['id'] ?? null;
                $qrImageUrl = $qrResponse['image_url'] ?? null;
            } catch (Throwable $e) {
                // Feature not enabled or fallback required: create Razorpay Order for Checkout / Intent fallback
                try {
                    $orderResponse = $this->api->order->create([
                        'receipt' => $booking->booking_reference,
                        'amount' => $booking->total_amount_paise,
                        'currency' => 'INR',
                        'notes' => [
                            'booking_reference' => $booking->booking_reference,
                        ],
                    ]);
                    $providerOrderId = $orderResponse['id'] ?? null;
                } catch (Throwable $orderException) {
                    Log::warning('Razorpay Order API fallback failed: ' . $orderException->getMessage());
                }
            }
        }

        // Create local payment attempt record
        return PaymentAttempt::create([
            'booking_id' => $booking->id,
            'provider' => 'razorpay',
            'provider_qr_id' => $providerQrId,
            'provider_order_id' => $providerOrderId,
            'amount_paise' => $booking->total_amount_paise,
            'currency' => 'INR',
            'method' => $providerQrId ? 'upi_qr' : 'checkout',
            'qr_image_url' => $qrImageUrl,
            'qr_payload' => $upiPayload,
            'status' => PaymentAttempt::STATUS_AWAITING_PAYMENT,
            'expires_at' => $booking->hold_expires_at,
        ]);
    }

    /**
     * Generate SVG QR code for the UPI payment payload.
     */
    public function generatePaymentQrSvg(PaymentAttempt $paymentAttempt, int $size = 280): string
    {
        $payload = $paymentAttempt->qr_payload;
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        return $writer->writeString($payload);
    }

    /**
     * Verify the raw webhook signature using HMAC-SHA256 with constant-time equality check.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        if (empty($signature)) {
            return false;
        }

        $secret = config('services.razorpay.webhook_secret');
        if (empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Idempotently process incoming Razorpay webhooks with deduplication and state verification.
     */
    public function handleWebhook(
        string $providerEventId,
        string $eventType,
        array $payload,
        string $rawBody,
        ?string $signature
    ): array {
        // 1. Verify cryptographic signature
        if (!$this->verifyWebhookSignature($rawBody, $signature)) {
            AuditLog::log(
                action: 'WEBHOOK_SIGNATURE_FAILED',
                targetType: 'GatewayWebhookEvent',
                newValues: ['provider_event_id' => $providerEventId, 'event_type' => $eventType]
            );
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'Invalid webhook signature',
            ];
        }

        // 2. Deduplicate using gateway_webhook_events table
        $webhookRecord = GatewayWebhookEvent::firstOrCreate(
            ['provider_event_id' => $providerEventId],
            [
                'provider' => 'razorpay',
                'event_type' => $eventType,
                'raw_payload' => $rawBody,
                'signature' => $signature,
                'processing_status' => GatewayWebhookEvent::STATUS_PENDING,
            ]
        );

        if ($webhookRecord->processing_status === GatewayWebhookEvent::STATUS_PROCESSED) {
            return [
                'status' => 'duplicate',
                'code' => 200,
                'message' => 'Webhook event already processed',
            ];
        }

        // 3. Process capture / payment credit events
        if (in_array($eventType, ['payment.captured', 'qr_code.credited', 'order.paid'], true)) {
            return $this->processPaymentSuccess($webhookRecord, $payload);
        }

        if ($eventType === 'payment.failed') {
            return $this->processPaymentFailure($webhookRecord, $payload);
        }

        $webhookRecord->update([
            'processing_status' => GatewayWebhookEvent::STATUS_PROCESSED,
            'processed_at' => Carbon::now(),
        ]);

        return [
            'status' => 'ignored',
            'code' => 200,
            'message' => "Event type {$eventType} ignored",
        ];
    }

    protected function processPaymentSuccess(GatewayWebhookEvent $webhookRecord, array $payload): array
    {
        $entity = $payload['payload']['payment']['entity']
            ?? $payload['payload']['qr_code']['entity']
            ?? [];

        $bookingRef = $entity['notes']['booking_reference']
            ?? $entity['description']
            ?? null;

        // Attempt lookup by notes or provider IDs
        $booking = null;
        if ($bookingRef) {
            $booking = Booking::where('booking_reference', $bookingRef)->first();
        }

        if (!$booking && !empty($entity['id'])) {
            $paymentAttempt = PaymentAttempt::where('provider_payment_id', $entity['id'])
                ->orWhere('provider_order_id', $entity['order_id'] ?? null)
                ->orWhere('provider_qr_id', $entity['qr_code_id'] ?? null)
                ->first();
            $booking = $paymentAttempt?->booking;
        }

        if (!$booking) {
            $webhookRecord->update([
                'processing_status' => GatewayWebhookEvent::STATUS_FAILED,
                'error_message' => 'Associated booking reference could not be resolved from payload.',
            ]);

            return [
                'status' => 'error',
                'code' => 422,
                'message' => 'Unresolvable booking reference',
            ];
        }

        return DB::transaction(function () use ($webhookRecord, $booking, $entity) {
            // Lock booking for update
            $booking = Booking::where('id', $booking->id)->lockForUpdate()->first();

            // Idempotent: If already confirmed, mark webhook processed and exit
            if ($booking->status === Booking::STATUS_CONFIRMED) {
                $webhookRecord->update([
                    'processing_status' => GatewayWebhookEvent::STATUS_PROCESSED,
                    'processed_at' => Carbon::now(),
                ]);

                return [
                    'status' => 'already_confirmed',
                    'code' => 200,
                    'message' => 'Booking was already confirmed',
                ];
            }

            $amountReceived = (int) ($entity['amount'] ?? 0);
            $currency = $entity['currency'] ?? 'INR';

            // Amount and Currency Integrity Check
            if ($amountReceived !== $booking->total_amount_paise || $currency !== 'INR') {
                $booking->update([
                    'status' => Booking::STATUS_RECONCILIATION_REQUIRED,
                ]);

                $webhookRecord->update([
                    'processing_status' => GatewayWebhookEvent::STATUS_FAILED,
                    'error_message' => "Amount mismatch: expected {$booking->total_amount_paise}, received {$amountReceived}",
                ]);

                AuditLog::log(
                    action: 'PAYMENT_AMOUNT_MISMATCH',
                    targetType: 'Booking',
                    targetId: $booking->id,
                    newValues: ['expected' => $booking->total_amount_paise, 'received' => $amountReceived]
                );

                return [
                    'status' => 'reconciliation_required',
                    'code' => 200,
                    'message' => 'Amount mismatch routed to reconciliation',
                ];
            }

            // Check if hold has expired (Late payment arrival)
            if ($booking->status === Booking::STATUS_HOLD_EXPIRED || ($booking->hold_expires_at && $booking->hold_expires_at->isPast())) {
                // Check if capacity is still available
                $bookingItem = $booking->items()->first();
                $category = $bookingItem->category()->lockForUpdate()->first();
                $available = $this->inventoryService->getAvailableCapacity($category);

                if ($available < $bookingItem->quantity) {
                    // CAPACITY EXHAUSTED: ROUTE TO RECONCILIATION - NEVER OVERSELL 451ST SEAT!
                    $booking->update([
                        'status' => Booking::STATUS_RECONCILIATION_REQUIRED,
                    ]);

                    $paymentAttempt = $booking->paymentAttempts()->latest()->first();
                    $paymentAttempt?->update([
                        'status' => PaymentAttempt::STATUS_RECONCILIATION_REQUIRED,
                        'provider_payment_id' => $entity['id'] ?? null,
                    ]);

                    $webhookRecord->update([
                        'processing_status' => GatewayWebhookEvent::STATUS_PROCESSED,
                        'processed_at' => Carbon::now(),
                        'error_message' => 'Payment received after hold expiry and category is full. Requires manual refund/reassignment.',
                    ]);

                    AuditLog::log(
                        action: 'LATE_PAYMENT_CAPACITY_EXHAUSTED',
                        targetType: 'Booking',
                        targetId: $booking->id,
                        newValues: ['reason' => 'Late payment arrived after stock was reallocated. Reconcile required.']
                    );

                    return [
                        'status' => 'reconciliation_required',
                        'code' => 200,
                        'message' => 'Late payment with exhausted capacity routed to reconciliation',
                    ];
                }
            }

            // Valid payment: Confirm booking and issue tickets
            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => Carbon::now(),
            ]);

            // Update Payment Attempt
            $paymentAttempt = $booking->paymentAttempts()->latest()->first();
            if ($paymentAttempt) {
                $paymentAttempt->update([
                    'status' => PaymentAttempt::STATUS_CAPTURED,
                    'provider_payment_id' => $entity['id'] ?? $paymentAttempt->provider_payment_id,
                    'captured_at' => Carbon::now(),
                ]);
            }

            // Convert hold to permanent allocation
            InventoryHold::where('booking_id', $booking->id)->update([
                'status' => InventoryHold::STATUS_CONVERTED,
            ]);

            // Issue Tickets via TicketService
            $this->ticketService->issueTicketsForBooking($booking);

            $webhookRecord->update([
                'processing_status' => GatewayWebhookEvent::STATUS_PROCESSED,
                'processed_at' => Carbon::now(),
            ]);

            AuditLog::log(
                action: 'PAYMENT_VERIFIED_CONFIRMED',
                targetType: 'Booking',
                targetId: $booking->id,
                newValues: [
                    'payment_id' => $entity['id'] ?? null,
                    'amount_paise' => $amountReceived,
                ]
            );

            return [
                'status' => 'confirmed',
                'code' => 200,
                'message' => 'Payment verified and tickets issued',
            ];
        });
    }

    protected function processPaymentFailure(GatewayWebhookEvent $webhookRecord, array $payload): array
    {
        $entity = $payload['payload']['payment']['entity'] ?? [];
        $bookingRef = $entity['notes']['booking_reference'] ?? null;

        if ($bookingRef) {
            $booking = Booking::where('booking_reference', $bookingRef)->first();
            if ($booking && $booking->status === Booking::STATUS_PAYMENT_PENDING) {
                $booking->update([
                    'status' => Booking::STATUS_PAYMENT_FAILED,
                    'cancellation_reason' => $entity['error_description'] ?? 'Payment declined by gateway/bank',
                ]);
            }
        }

        $webhookRecord->update([
            'processing_status' => GatewayWebhookEvent::STATUS_PROCESSED,
            'processed_at' => Carbon::now(),
        ]);

        return [
            'status' => 'failed_recorded',
            'code' => 200,
            'message' => 'Payment failure recorded',
        ];
    }
}
