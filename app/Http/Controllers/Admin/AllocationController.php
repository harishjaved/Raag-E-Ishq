<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Contact;
use App\Models\Event;
use App\Models\TicketAllocation;
use App\Models\TicketCategory;
use App\Services\InventoryService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AllocationController extends Controller
{
    public function index(): View
    {
        $event = Event::with('categories')->where('slug', 'sagar-kinare-2026')->first() ?? Event::with('categories')->firstOrFail();
        $allocations = TicketAllocation::with(['category', 'createdBy', 'booking.tickets'])->latest()->paginate(20);

        return view('admin.allocations.index', [
            'event' => $event,
            'allocations' => $allocations,
            'categories' => $event->categories,
        ]);
    }

    public function store(Request $request, InventoryService $inventoryService, TicketService $ticketService): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_category_id' => ['required', 'exists:ticket_categories,id'],
            'allocation_type' => ['required', 'in:COMPLIMENTARY,SPONSOR,ORGANISER_HOLD'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'beneficiary_name' => ['required', 'string', 'max:255'],
            'beneficiary_email' => ['nullable', 'email', 'max:255'],
            'beneficiary_phone' => ['nullable', 'string', 'max:20'],
            'issue_tickets' => ['nullable'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $category = TicketCategory::findOrFail($validated['ticket_category_id']);
        $event = $category->event;

        $available = $inventoryService->getAvailableCapacity($category);
        if ($available < $validated['quantity']) {
            return back()->withInput()->withErrors([
                'quantity' => "Cannot allocate {$validated['quantity']} seats. Only {$available} available in {$category->name}."
            ]);
        }

        $globalAvailable = $inventoryService->getGlobalRemainingCapacity($event);
        if ($globalAvailable < $validated['quantity']) {
            return back()->withInput()->withErrors([
                'quantity' => "Cannot exceed total event capacity of 450. Only {$globalAvailable} seats remain unallocated."
            ]);
        }

        $allocation = TicketAllocation::create([
            'event_id' => $event->id,
            'ticket_category_id' => $category->id,
            'allocation_type' => $validated['allocation_type'],
            'quantity' => $validated['quantity'],
            'beneficiary_name' => $validated['beneficiary_name'],
            'notes' => $validated['notes'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        AuditLog::log(
            action: 'TICKET_ALLOCATION_CREATED',
            targetType: 'TicketAllocation',
            targetId: $allocation->id,
            newValues: [
                'type' => $allocation->allocation_type,
                'category' => $category->name,
                'quantity' => $allocation->quantity,
                'beneficiary' => $allocation->beneficiary_name,
            ]
        );

        $shouldIssueTickets = $request->boolean('issue_tickets', true);
        if ($shouldIssueTickets && in_array($allocation->allocation_type, [TicketAllocation::TYPE_SPONSOR, TicketAllocation::TYPE_COMPLIMENTARY])) {
            $this->issueTicketsForAllocation($allocation, $ticketService, $validated['beneficiary_email'] ?? null, $validated['beneficiary_phone'] ?? null);
            return back()->with('success', "Allocated {$allocation->quantity} {$allocation->allocation_type} seat(s) and issued admission tickets for {$allocation->beneficiary_name}.");
        }

        return back()->with('success', "Allocated {$allocation->quantity} {$allocation->allocation_type} seat(s) for {$allocation->beneficiary_name}.");
    }

    public function generateTickets(TicketAllocation $allocation, TicketService $ticketService): RedirectResponse
    {
        if ($allocation->booking_id) {
            return back()->with('info', 'Admission tickets have already been generated for this allocation.');
        }

        $this->issueTicketsForAllocation($allocation, $ticketService);

        return back()->with('success', "Successfully generated {$allocation->quantity} admission ticket(s) with QR codes for {$allocation->beneficiary_name}.");
    }

    protected function issueTicketsForAllocation(TicketAllocation $allocation, TicketService $ticketService, ?string $email = null, ?string $phone = null): Booking
    {
        $cleanEmail = $email ?: ('sponsor.' . $allocation->id . '@joygivers.local');
        $cleanPhone = $phone ?: '9999999999';

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

        $ticketService->issueTicketsForBooking($booking);

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

        return $booking;
    }
}

