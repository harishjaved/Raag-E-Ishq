@extends('layouts.admin')

@section('title', 'Booking Details — ' . $booking->booking_reference)

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.bookings') }}" class="text-xs text-slate-400 hover:text-slate-200 transition">&larr; Back to Bookings</a>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-100 mt-1">Booking {{ $booking->booking_reference }}</h1>
            <p class="text-xs text-slate-400">Created {{ $booking->created_at->format('d M Y, h:i A') }}</p>
        </div>

        <div class="flex items-center gap-2">
            @if ($booking->is_donation)
                <span class="px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300 text-xs font-bold">
                    DONATION &bull; NON-ATTENDING
                </span>
            @else
                <span class="px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs font-bold">
                    IN-PERSON ATTENDEE
                </span>
            @endif

            @if ($booking->status === 'CONFIRMED')
                <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold">
                    CONFIRMED &bull; PAID
                </span>
            @elseif ($booking->status === 'PAYMENT_PENDING')
                <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold">
                    PAYMENT PENDING
                </span>
                <form action="{{ route('admin.bookings.confirm-payment', $booking->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition shadow-lg shadow-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve &amp; Issue Tickets</span>
                    </button>
                </form>
            @else
                <span class="px-3 py-1 rounded-full bg-slate-800 text-slate-300 text-xs font-bold">
                    {{ $booking->status }}
                </span>
            @endif
        </div>
    </div>

    <!-- Booking Summary Grid -->
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Contact Card -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-3">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400">Contact / Beneficiary</h2>
            <div class="space-y-1">
                <div class="text-base font-bold text-slate-100">{{ $booking->contact->full_name }}</div>
                <div class="text-xs text-slate-300">{{ $booking->contact->email }}</div>
                <div class="text-xs text-slate-400 font-mono">{{ $booking->contact->phone }}</div>
            </div>
        </div>

        <!-- Financial Summary Card -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-3">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400">Payment &amp; Financials</h2>
            <div class="space-y-1">
                <div class="text-2xl font-bold font-mono text-amber-400">
                    ₹{{ number_format($booking->total_amount_paise / 100, 2) }}
                </div>
                <div class="text-xs text-slate-400">
                    @if ($booking->is_donation)
                        Charitable Contribution (0 Hall Seats Deducted)
                    @elseif ($booking->total_amount_paise == 0)
                        Complimentary / Sponsor Allocation
                    @else
                        Paid via Razorpay UPI
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Summary Card -->
        <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-3">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400">Reserved Items</h2>
            <div class="space-y-1 text-xs">
                @foreach ($booking->items as $item)
                    <div class="flex justify-between text-slate-300">
                        <span>{{ $item->category->name }} &times; {{ $item->quantity }}</span>
                        <span class="font-mono text-slate-100">₹{{ number_format($item->subtotal_paise / 100, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Admission Tickets Section -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-100">Issued Admission Tickets ({{ $booking->tickets->count() }})</h2>
                <p class="text-xs text-slate-400">Each admission ticket has a unique cryptographic QR code for gate admission.</p>
            </div>
            @if ($booking->status === 'CONFIRMED' && $booking->tickets->isNotEmpty())
                <a href="{{ route('booking.confirmed', $booking->booking_reference) }}" target="_blank"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Public Ticket View &amp; Print</span>
                </a>
            @endif
        </div>

        @if ($booking->tickets->isEmpty())
            <div class="p-8 text-center text-xs text-slate-500">
                No tickets issued yet. Tickets are issued automatically once payment is verified or complimentary allocation is confirmed.
            </div>
        @else
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($booking->tickets as $ticket)
                    @php
                        $qrSvg = $ticket->qr_svg ?? app(\App\Services\TicketService::class)->generateAdmissionQrSvg($ticket);
                    @endphp
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center gap-4">
                        <div class="w-24 h-24 bg-white p-2 rounded-xl flex-shrink-0 flex items-center justify-center [&>svg]:w-full [&>svg]:h-auto shadow-inner">
                            {!! $qrSvg !!}
                        </div>
                        <div class="space-y-1 text-xs flex-grow">
                            <div class="inline-block px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold text-[10px]">
                                {{ $ticket->category->name }}
                            </div>
                            <div class="font-bold text-slate-100 text-sm">{{ $ticket->attendee_name }}</div>
                            <div class="font-mono text-[11px] text-slate-400">{{ $ticket->ticket_number }}</div>

                            <div class="pt-1 flex items-center gap-2">
                                @if ($ticket->status === 'CHECKED_IN')
                                    <span class="px-2 py-0.5 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-bold">
                                        CHECKED IN ({{ $ticket->checkin?->checked_in_at?->format('h:i A') }})
                                    </span>
                                @elseif ($ticket->status === 'ISSUED')
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">
                                        ADMIT READY (VALID)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 text-[10px] font-bold">
                                        {{ $ticket->status }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
