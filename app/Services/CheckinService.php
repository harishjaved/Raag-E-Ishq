<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Checkin;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckinService
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Atomically check in a ticket by its QR token.
     * Backed by database UNIQUE constraint on checkins.ticket_id and row-level locking.
     *
     * @return array{result: string, code: int, message: string, ticket: ?Ticket, checkin: ?Checkin}
     */
    public function checkinTicketByQr(string $uuid, string $signature, string $gate = 'GATE_1', ?User $staff = null): array
    {
        // 1. Initial signature and validity check
        $verification = $this->ticketService->verifyAdmissionToken($uuid, $signature);
        if (!$verification['valid']) {
            $ticket = $verification['ticket'];

            if ($verification['status'] === 'ALREADY_CHECKED_IN') {
                return [
                    'result' => 'ORANGE_ALREADY_USED',
                    'code' => 409,
                    'message' => $verification['message'],
                    'ticket' => $ticket,
                    'checkin' => $ticket?->checkin,
                ];
            }

            return [
                'result' => 'RED_INVALID',
                'code' => 422,
                'message' => $verification['message'],
                'ticket' => $ticket,
                'checkin' => null,
            ];
        }

        $ticket = $verification['ticket'];

        // 2. Atomic database transaction with row lock
        try {
            return DB::transaction(function () use ($ticket, $gate, $staff) {
                // Lock ticket row
                $lockedTicket = Ticket::where('id', $ticket->id)->lockForUpdate()->first();

                if ($lockedTicket->status === Ticket::STATUS_CHECKED_IN) {
                    $existingCheckin = Checkin::where('ticket_id', $lockedTicket->id)->first();
                    return [
                        'result' => 'ORANGE_ALREADY_USED',
                        'code' => 409,
                        'message' => "Already admitted at " . ($existingCheckin?->checked_in_at?->format('H:i:s d M') ?? 'earlier'),
                        'ticket' => $lockedTicket,
                        'checkin' => $existingCheckin,
                    ];
                }

                if ($lockedTicket->status !== Ticket::STATUS_ISSUED) {
                    return [
                        'result' => 'RED_INVALID',
                        'code' => 422,
                        'message' => "Ticket is not valid for admission (Status: {$lockedTicket->status})",
                        'ticket' => $lockedTicket,
                        'checkin' => null,
                    ];
                }

                // Insert into checkins with database UNIQUE constraint on ticket_id
                $checkin = Checkin::create([
                    'ticket_id' => $lockedTicket->id,
                    'scanned_by_user_id' => $staff?->id,
                    'checked_in_at' => Carbon::now(),
                    'gate_identifier' => $gate,
                    'verification_method' => Checkin::METHOD_QR_SCAN,
                ]);

                // Update ticket status
                $lockedTicket->update(['status' => Ticket::STATUS_CHECKED_IN]);

                AuditLog::log(
                    action: 'GATE_ADMISSION_CHECKIN',
                    targetType: 'Ticket',
                    targetId: $lockedTicket->id,
                    newValues: [
                        'gate' => $gate,
                        'scanned_by' => $staff?->name ?? 'System',
                        'checked_in_at' => $checkin->checked_in_at->toIso8601String(),
                    ],
                    userId: $staff?->id
                );

                return [
                    'result' => 'GREEN_VALID',
                    'code' => 200,
                    'message' => 'Admission Confirmed. Valid Entry.',
                    'ticket' => $lockedTicket,
                    'checkin' => $checkin,
                ];
            });
        } catch (Throwable $e) {
            // Concurrent race condition: another device scanned the same millisecond
            $existingCheckin = Checkin::where('ticket_id', $ticket->id)->first();
            return [
                'result' => 'ORANGE_ALREADY_USED',
                'code' => 409,
                'message' => "Concurrent entry detected: Already admitted at " . ($existingCheckin?->checked_in_at?->format('H:i:s') ?? 'just now'),
                'ticket' => $ticket,
                'checkin' => $existingCheckin,
            ];
        }
    }

    /**
     * Permission-gated, auditable manual ticket check-in for damaged QR codes.
     */
    public function manualLookupAndCheckin(string $ticketNumber, string $reason, string $gate = 'GATE_1', User $staff): array
    {
        $ticket = Ticket::with(['category', 'booking.contact', 'checkin.scannedBy'])
            ->where('ticket_number', trim($ticketNumber))
            ->first();

        if (!$ticket) {
            return [
                'result' => 'RED_INVALID',
                'code' => 404,
                'message' => 'Ticket number not found.',
                'ticket' => null,
                'checkin' => null,
            ];
        }

        if ($ticket->status === Ticket::STATUS_CHECKED_IN || $ticket->checkin !== null) {
            return [
                'result' => 'ORANGE_ALREADY_USED',
                'code' => 409,
                'message' => "Already checked in at " . ($ticket->checkin?->checked_in_at?->format('H:i:s d M') ?? 'earlier'),
                'ticket' => $ticket,
                'checkin' => $ticket->checkin,
            ];
        }

        if ($ticket->status !== Ticket::STATUS_ISSUED) {
            return [
                'result' => 'RED_INVALID',
                'code' => 422,
                'message' => "Ticket cannot be admitted (Status: {$ticket->status}, Reason: {$ticket->void_reason})",
                'ticket' => $ticket,
                'checkin' => null,
            ];
        }

        return DB::transaction(function () use ($ticket, $reason, $gate, $staff) {
            $lockedTicket = Ticket::where('id', $ticket->id)->lockForUpdate()->first();

            $checkin = Checkin::create([
                'ticket_id' => $lockedTicket->id,
                'scanned_by_user_id' => $staff->id,
                'checked_in_at' => Carbon::now(),
                'gate_identifier' => $gate,
                'verification_method' => Checkin::METHOD_MANUAL_LOOKUP,
                'notes' => $reason,
            ]);

            $lockedTicket->update(['status' => Ticket::STATUS_CHECKED_IN]);

            AuditLog::log(
                action: 'MANUAL_GATE_ADMISSION',
                targetType: 'Ticket',
                targetId: $lockedTicket->id,
                newValues: [
                    'ticket_number' => $lockedTicket->ticket_number,
                    'reason' => $reason,
                    'staff' => $staff->name,
                    'gate' => $gate,
                ],
                userId: $staff->id
            );

            return [
                'result' => 'GREEN_VALID',
                'code' => 200,
                'message' => 'Manual Admission Confirmed.',
                'ticket' => $lockedTicket,
                'checkin' => $checkin,
            ];
        });
    }
}
