<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Contact;
use App\Models\TicketAllocation;
use App\Services\TicketService;
use Illuminate\Console\Command;

class GenerateAllocationTicketsCommand extends Command
{
    protected $signature = 'allocation:generate-tickets {id : The allocation ID}';
    protected $description = 'Generate admission tickets with QR codes for a sponsor or complimentary allocation';

    public function handle(TicketService $ticketService): int
    {
        $id = $this->argument('id');
        $allocation = TicketAllocation::with('category')->find($id);

        if (!$allocation) {
            $this->error("Allocation #{$id} not found.");
            return 1;
        }

        if ($allocation->booking_id) {
            $this->warn("Allocation #{$id} already has tickets issued (Booking ID: {$allocation->booking_id}).");
            $booking = Booking::find($allocation->booking_id);
            if ($booking) {
                $this->info("Admission URL: " . route('booking.confirmed', ['reference' => $booking->booking_reference]));
            }
            return 0;
        }

        $cleanEmail = 'sponsor.' . $allocation->id . '@joygivers.local';
        $cleanPhone = '9999999999';

        $contact = Contact::firstOrCreate(
            ['email' => $cleanEmail],
            [
                'full_name' => $allocation->beneficiary_name,
                'phone' => $cleanPhone,
                'country_code' => '+91',
                'notes' => "{$allocation->allocation_type} Beneficiary #{$allocation->id}",
            ]
        );

        $booking = Booking::create([
            'booking_reference' => Booking::generateUniqueReference(),
            'event_id' => $allocation->event_id,
            'contact_id' => $contact->id,
            'total_amount_paise' => 0,
            'currency' => 'INR',
            'status' => Booking::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'ticket_category_id' => $allocation->ticket_category_id,
            'quantity' => $allocation->quantity,
            'unit_price_paise' => 0,
            'subtotal_paise' => 0,
        ]);

        $allocation->update(['booking_id' => $booking->id]);

        $tickets = $ticketService->issueTicketsForBooking($booking);

        AuditLog::log(
            action: 'TICKET_ALLOCATION_TICKETS_ISSUED',
            targetType: 'TicketAllocation',
            targetId: $allocation->id,
            newValues: [
                'booking_reference' => $booking->booking_reference,
                'tickets_count' => $allocation->quantity,
                'beneficiary' => $allocation->beneficiary_name,
            ]
        );

        $this->info("=================================================");
        $this->info(" [SUCCESS] Tickets Issued for Allocation #{$id}");
        $this->info("=================================================");
        $this->line("Beneficiary:   {$allocation->beneficiary_name}");
        $this->line("Type:          {$allocation->allocation_type}");
        $this->line("Category:      {$allocation->category->name}");
        $this->line("Booking Ref:   {$booking->booking_reference}");
        $this->line("Tickets Count: " . $tickets->count());
        $this->newLine();
        $this->info("View Admission Tickets & QRs at:");
        $this->comment(route('booking.confirmed', ['reference' => $booking->booking_reference]));

        return 0;
    }
}
