<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\TicketAllocation;
use App\Models\TicketCategory;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AllocationController extends Controller
{
    public function index(): View
    {
        $event = Event::with('categories')->where('slug', 'sagar-kinare-2026')->firstOrFail();
        $allocations = TicketAllocation::with(['category', 'createdBy'])->latest()->get();

        return view('admin.allocations.index', [
            'event' => $event,
            'allocations' => $allocations,
            'categories' => $event->categories,
        ]);
    }

    public function store(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_category_id' => ['required', 'exists:ticket_categories,id'],
            'allocation_type' => ['required', 'in:COMPLIMENTARY,SPONSOR,ORGANISER_HOLD'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'beneficiary_name' => ['required', 'string', 'max:255'],
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

        return back()->with('success', "Allocated {$allocation->quantity} {$allocation->allocation_type} seat(s) for {$allocation->beneficiary_name}.");
    }
}
