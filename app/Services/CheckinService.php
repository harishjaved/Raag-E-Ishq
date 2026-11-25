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

        return $this->admitLockedTicket(
            ticket: $ticket,
            method: Checkin::METHOD_QR_SCAN,
            gate: $gate,
            staff: $staff,
            notes: null,
            auditAction: 'GATE_ADMISSION_CHECKIN',
            successMessage: 'Admission Confirmed. Valid Entry.'
        );
    }

    /**
     * Permission-gated, auditable manual ticket check-in for damaged QR codes.
     */
    public function manualLookupAndCheckin(string $ticketNumber, string $reason, string $gate = 'GATE_1', User $staff): array
    {
        $normalized = strtoupper(preg_replace('/\s+/', '', trim($ticketNumber)));
        $candidates = [$normalized];
        if (!str_starts_with($normalized, 'TKT-')) {
            if (preg_match('/^TKT([A-Z0-9]{4})([A-Z0-9]{4})$/', $normalized, $m)) {
                $candidates[] = "TKT-{$m[1]}-{$m[2]}";
            } elseif (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $normalized)) {
                $candidates[] = 'TKT-' . $normalized;
            }
        }

        $ticket = Ticket::with(['category', 'booking.contact', 'checkin.scannedBy'])
            ->where(function ($q) use ($candidates) {
                foreach ($candidates as $idx => $candidate) {
                    $method = $idx === 0 ? 'whereRaw' : 'orWhereRaw';
                    $q->{$method}('UPPER(ticket_number) = ?', [$candidate]);
                }
            })
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
            $reasonSuffix = $ticket->void_reason ? ", Reason: {$ticket->void_reason}" : '';
            return [
                'result' => 'RED_INVALID',
                'code' => 422,
                'message' => "Ticket cannot be admitted (Status: {$ticket->status}{$reasonSuffix})",
                'ticket' => $ticket,
                'checkin' => null,
            ];
        }

        return $this->admitLockedTicket(
            ticket: $ticket,
            method: Checkin::METHOD_MANUAL_LOOKUP,
            gate: $gate,
            staff: $staff,
            notes: trim($reason),
            auditAction: 'MANUAL_GATE_ADMISSION',
            successMessage: 'Manual Admission Confirmed.'
        );
    }

    /**
     * Atomically lock and admit an eligible ticket inside a transaction.
     *
     * @return array{result: string, code: int, message: string, ticket: Ticket, checkin: ?Checkin}
     */
    protected function admitLockedTicket(
        Ticket $ticket,
        string $method,
        string $gate,
        ?User $staff,
        ?string $notes,
        string $auditAction,
        string $successMessage
    ): array {
        try {
            return DB::transaction(function () use ($ticket, $method, $gate, $staff, $notes, $auditAction, $successMessage) {
                $lockedTicket = Ticket::with('category')->where('id', $ticket->id)->lockForUpdate()->first();

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

                $checkin = Checkin::create([
                    'ticket_id' => $lockedTicket->id,
                    'scanned_by_user_id' => $staff?->id,
                    'checked_in_at' => Carbon::now(),
                    'gate_identifier' => $gate,
                    'verification_method' => $method,
                    'notes' => $notes,
                ]);

                $lockedTicket->update(['status' => Ticket::STATUS_CHECKED_IN]);

                $auditPayload = [
                    'ticket_number' => $lockedTicket->ticket_number,
                    'gate' => $gate,
                    'scanned_by' => $staff?->name ?? 'System',
                    'checked_in_at' => $checkin->checked_in_at->toIso8601String(),
                ];
                if ($notes !== null && $notes !== '') {
                    $auditPayload['reason'] = $notes;
                }

                AuditLog::log(
                    action: $auditAction,
                    targetType: 'Ticket',
                    targetId: $lockedTicket->id,
                    newValues: $auditPayload,
                    userId: $staff?->id
                );

                return [
                    'result' => 'GREEN_VALID',
                    'code' => 200,
                    'message' => $successMessage,
                    'ticket' => $lockedTicket,
                    'checkin' => $checkin,
                ];
            });
        } catch (Throwable $e) {
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
}
