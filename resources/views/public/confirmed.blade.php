@extends('layouts.app')

@section('title', 'Tickets Confirmed — Sagar Kinare')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8 space-y-8">
    <!-- Success Banner -->
    <div class="p-6 rounded-3xl bg-emerald-950/80 border border-emerald-500/40 text-center space-y-3 shadow-2xl">
        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">
            @if ($booking->is_attending)
                Payment Verified &amp; Tickets Confirmed!
            @else
                Payment Verified &amp; Contribution Acknowledged!
            @endif
        </h1>
        <p class="text-xs text-emerald-300/90 max-w-md mx-auto">
            @if ($booking->is_attending)
                Your admission tickets have been successfully generated. Present each admission QR code at Chavara Hall gate check-in.
            @else
                Thank you for your generous support of Joygivers' education initiative for underprivileged students! Your contribution has been verified.
            @endif
        </p>
        <div class="pt-2 text-xs text-slate-400">
            Booking Reference: <span class="font-mono text-amber-400 font-bold">{{ $booking->booking_reference }}</span>
        </div>
    </div>

    @if ($booking->is_attending)
    <!-- Ticket Cards List -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-100">Your Event Admission Tickets ({{ $tickets->count() }})</h2>
            <button onclick="window.print()" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / Save</span>
            </button>
        </div>

        @foreach ($tickets as $ticket)
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
                <div class="flex flex-col sm:flex-row items-center gap-6 justify-between">
                    <div class="space-y-2 text-center sm:text-left flex-grow">
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold uppercase tracking-wider">
                            {{ $ticket->category->name }} &bull; Admit 1
                        </div>
                        <div class="text-xl font-bold text-slate-100">{{ $ticket->attendee_name }}</div>
                        <div class="text-xs text-slate-400 space-y-0.5">
                            <div>Venue: <span class="text-slate-200 font-medium">Chavara Hall, Kochi</span></div>
                            <div>Ticket Number: <span class="font-mono text-slate-300">{{ $ticket->ticket_number }}</span></div>
                            <div>Status: <span class="text-emerald-400 font-semibold">{{ $ticket->status }}</span></div>
                        </div>
                    </div>

                    <!-- Unique Admission QR -->
                    <div class="flex-shrink-0 text-center space-y-1">
                        <div class="p-3 bg-white rounded-2xl inline-block shadow-md max-w-[170px] [&>svg]:w-full [&>svg]:h-auto">
                            {!! $ticket->qr_svg !!}
                        </div>
                        <div class="text-[9px] uppercase tracking-wider text-slate-500 font-bold">Admission QR</div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800/80 text-[11px] text-slate-500 flex justify-between items-center">
                    <span>Valid for single entry only</span>
                    <span class="font-mono text-[10px] text-slate-600">ID: {{ substr($ticket->qr_uuid, 0, 8) }}...</span>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <!-- Non-Attending Official Contribution & Supporter Receipt -->
    <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6 shadow-xl text-center sm:text-left">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-800 gap-4">
            <div>
                <span class="px-2.5 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300 text-[11px] font-bold uppercase tracking-wider">
                    Official Supporter Receipt
                </span>
                <h2 class="text-xl font-bold text-slate-100 mt-2">{{ $booking->contact->full_name }}</h2>
                <p class="text-xs text-slate-400">Attendance: <span class="text-amber-400 font-semibold">Non-Attending / In Absentia</span></p>
            </div>
            <button onclick="window.print()" class="self-start sm:self-auto text-xs font-semibold px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 transition flex items-center gap-1.5 shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Official Receipt</span>
            </button>
        </div>

        <div class="space-y-3 text-xs">
            <div class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Contribution Breakdown</div>
            @foreach ($booking->items as $item)
                <div class="flex justify-between text-slate-300 py-1.5 border-b border-slate-800/40">
                    <span>{{ $item->category->name }} Tier Support &times; {{ $item->quantity }}</span>
                    <span class="font-mono text-slate-100 font-bold">₹{{ number_format($item->subtotal_paise / 100, 2) }}</span>
                </div>
            @endforeach
            <div class="flex justify-between text-sm font-bold text-slate-100 pt-2">
                <span>Total Contribution Received</span>
                <span class="font-mono text-amber-400 text-lg">₹{{ number_format($booking->total_amount_paise / 100, 2) }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 text-xs text-slate-400 space-y-1">
            <div class="font-semibold text-slate-200">Joygivers Charitable Initiative Notice</div>
            <p>
                Your registration has been recorded in our official patron ledger. Since you indicated that you will not be attending in person, no hall seating has been withheld, and your entire payment directly fuels our educational programs for underprivileged children.
            </p>
        </div>
    </div>
    @endif
    </div>

    <!-- Help, Support & Policy Notice -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 text-center space-y-4 shadow-xl">
        <div class="space-y-1">
            <p class="text-xs text-slate-300">A confirmation receipt with your ticket passes has been queued for delivery to <strong class="text-amber-400">{{ $booking->contact->email }}</strong>.</p>
            <p class="text-[11px] text-slate-500">
                <strong>Cancellation Policy:</strong> All ticket bookings are final, non-cancellable, and non-refundable. All proceeds support Joygivers educational initiatives.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a href="https://wa.me/919876543210?text={{ urlencode('Hi Joygivers, I have a query regarding my booking ' . $booking->booking_reference) }}"
                target="_blank"
                class="px-4 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                <span>WhatsApp Support</span>
            </a>

            <a href="mailto:events@joygivers.org?subject={{ urlencode('Enquiry: Booking ' . $booking->booking_reference) }}"
                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-bold flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>events@joygivers.org</span>
            </a>
        </div>
    </div>
</div>
@endsection
