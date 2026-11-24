@extends('layouts.admin')

@section('title', 'Enquiry Details — ' . $booking->booking_reference)

@section('content')
<div class="space-y-6 max-w-5xl">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-slate-800/60">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.bookings') }}" class="text-xs text-slate-400 hover:text-amber-400 transition flex items-center gap-1 font-semibold">
                    &larr; Back to Enquiries Desk
                </a>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">{{ $booking->booking_reference }}</h1>
                @if ($booking->status === 'CONFIRMED')
                    <span class="px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-xs font-bold">
                        COMPLETED &bull; PAID
                    </span>
                @elseif ($booking->status === 'IN_PROCESS')
                    <span class="px-3 py-1 rounded-full bg-sky-500/15 text-sky-400 border border-sky-500/30 text-xs font-bold">
                        IN PROCESS &bull; CONTACTED
                    </span>
                @elseif (in_array($booking->status, ['PAYMENT_PENDING', 'PENDING']))
                    <span class="px-3 py-1 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/30 text-xs font-bold animate-pulse">
                        PENDING CALL
                    </span>
                @elseif ($booking->status === 'CANCELLED')
                    <span class="px-3 py-1 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 text-xs font-bold">
                        CANCELLED
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-slate-800 text-slate-400 text-xs font-bold">
                        {{ $booking->status }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-400 mt-1 font-mono">Enquiry submitted: {{ $booking->created_at->format('d M Y, h:i A') }}</p>
        </div>

        <!-- Top Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if (in_array($booking->status, ['PAYMENT_PENDING', 'PENDING']))
                @if (isset($booking->whatsapp_payment_url))
                    <a href="{{ $booking->whatsapp_payment_url }}" target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/40 text-emerald-300 font-bold text-xs flex items-center gap-1.5 transition">
                        <span>Send WhatsApp Details</span>
                    </a>
                @endif
                <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="status" value="IN_PROCESS">
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 border border-sky-500/30 font-bold text-xs transition">
                        Mark In Process
                    </button>
                </form>
                <form action="{{ route('admin.bookings.confirm-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm payment received and issue admission tickets?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition shadow-lg shadow-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve &amp; Issue Tickets</span>
                    </button>
                </form>
                <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel enquiry and release reserved seats?');">
                    @csrf
                    <input type="hidden" name="status" value="CANCELLED">
                    <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-300 text-xs transition">
                        Cancel Enquiry
                    </button>
                </form>
            @elseif ($booking->status === 'IN_PROCESS')
                @if (isset($booking->whatsapp_payment_url))
                    <a href="{{ $booking->whatsapp_payment_url }}" target="_blank"
                        class="px-3.5 py-2 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 border border-sky-500/40 text-sky-300 font-bold text-xs flex items-center gap-1.5 transition">
                        <span>Send WhatsApp Payment Details</span>
                    </a>
                @endif
                <form action="{{ route('admin.bookings.confirm-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm payment received and issue admission tickets?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition shadow-lg shadow-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve &amp; Issue Tickets</span>
                    </button>
                </form>
                <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel enquiry and release reserved seats?');">
                    @csrf
                    <input type="hidden" name="status" value="CANCELLED">
                    <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-300 text-xs transition">
                        Cancel Enquiry
                    </button>
                </form>
            @elseif ($booking->status === 'CONFIRMED')
                @if (isset($booking->whatsapp_ticket_url))
                    <a href="{{ $booking->whatsapp_ticket_url }}" target="_blank"
                        class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-300 font-bold text-xs flex items-center gap-1.5 transition">
                        <span>Send Passes via WhatsApp</span>
                    </a>
                @endif
                <a href="{{ route('booking.confirmed', $booking->booking_reference) }}" target="_blank"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                    <span>Public Ticket View &rarr;</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 3-Step Operations Progress Stepper -->
    <div class="p-5 rounded-2xl bg-[#0e1424] border border-slate-800/80">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Step 1 -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                    ✓
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-200">1. Details Registered</div>
                    <div class="text-[11px] text-slate-400">Seats held in system</div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full {{ in_array($booking->status, ['IN_PROCESS', 'CONFIRMED']) ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400' : 'bg-amber-500/20 border-amber-500/50 text-amber-400 animate-pulse' }} border flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ in_array($booking->status, ['IN_PROCESS', 'CONFIRMED']) ? '✓' : '2' }}
                </div>
                <div>
                    <div class="text-xs font-bold {{ in_array($booking->status, ['IN_PROCESS', 'CONFIRMED']) ? 'text-slate-200' : 'text-amber-300' }}">2. Phone Call &amp; WhatsApp</div>
                    <div class="text-[11px] text-slate-400">{{ in_array($booking->status, ['IN_PROCESS', 'CONFIRMED']) ? 'Details communicated' : 'Call customer now' }}</div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full {{ $booking->status === 'CONFIRMED' ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400' : 'bg-slate-800 border-slate-700 text-slate-500' }} border flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ $booking->status === 'CONFIRMED' ? '✓' : '3' }}
                </div>
                <div>
                    <div class="text-xs font-bold {{ $booking->status === 'CONFIRMED' ? 'text-emerald-400' : 'text-slate-400' }}">3. Payment &amp; Passes</div>
                    <div class="text-[11px] text-slate-400">{{ $booking->status === 'CONFIRMED' ? 'Tickets active & issued' : 'Awaiting confirmation' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Summary Grid (3 Modern Cards) -->
    <div class="grid md:grid-cols-3 gap-5">
        <!-- 1. Customer Contact Profile Card -->
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Customer Profile</span>
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            </div>
            <div class="space-y-1">
                <div class="text-lg font-bold text-slate-100">{{ $booking->contact->full_name }}</div>
                <div class="text-xs text-slate-300">{{ $booking->contact->email }}</div>
                <div class="text-sm text-amber-400 font-mono font-bold">{{ $booking->contact->phone }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800/60">
                <a href="tel:{{ $booking->contact->phone }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>Direct Call</span>
                </a>
                @if (isset($booking->whatsapp_payment_url))
                    <a href="{{ $booking->whatsapp_payment_url }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-950/60 hover:bg-emerald-900 border border-emerald-500/40 text-emerald-400 text-xs font-bold transition flex items-center gap-1">
                        <span>WhatsApp</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 2. Financial Amount & Method Card -->
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Total Payable</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-amber-400 font-mono">
                    ₹{{ number_format($booking->total_amount_paise / 100, 2) }}
                </div>
                <div class="text-xs text-slate-400 mt-1">
                    @if ($booking->is_donation)
                        <span class="text-purple-400 font-bold">Pure Supporter Donation</span> &bull; 0 seats held
                    @else
                        <span>Verified offline UPI transfer</span>
                    @endif
                </div>
            </div>
            <div class="pt-2 border-t border-slate-800/60 text-[11px] text-slate-400">
                Payment UPI ID: <strong class="text-slate-200 font-mono">mujammilibrahim007@okhdfcbank</strong>
            </div>
        </div>

        <!-- 3. Admission Items & Seating Card -->
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Reserved Category</span>
                <span class="w-2 h-2 rounded-full bg-sky-400"></span>
            </div>
            <div class="space-y-2 text-xs">
                @foreach ($booking->items as $item)
                    <div class="flex justify-between items-center text-slate-200 font-semibold p-2 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span>{{ $item->category->name }} &times; {{ $item->quantity }}</span>
                        <span class="font-mono font-bold text-amber-400">₹{{ number_format($item->subtotal_paise / 100, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="pt-1 text-[11px] text-slate-400">
                Type: <strong class="text-slate-200">{{ $booking->is_attending ? 'In-Person Attendance' : 'Charity Donation Only' }}</strong>
            </div>
        </div>
    </div>

    <!-- Admission Tickets Section -->
    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800/60">
            <div>
                <h2 class="text-lg font-bold text-slate-100 font-serif-display">Issued Admission Tickets ({{ $booking->tickets->count() }})</h2>
                <p class="text-xs text-slate-400">Each admission ticket features a tamper-proof HMAC signature for gate verification.</p>
            </div>
            @if ($booking->status === 'CONFIRMED' && $booking->tickets->isNotEmpty())
                <div class="flex items-center gap-2">
                    <a href="{{ route('booking.confirmed', $booking->booking_reference) }}" target="_blank"
                        class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition flex items-center gap-1.5 shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print All Passes / PDF</span>
                    </a>
                </div>
            @endif
        </div>

        @if ($booking->tickets->isEmpty())
            <div class="py-12 text-center text-slate-500 space-y-2">
                <svg class="w-8 h-8 mx-auto text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <div class="text-xs">No tickets issued yet. Click "Approve &amp; Issue Tickets" above to generate unique entry passes.</div>
            </div>
        @else
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($booking->tickets as $ticket)
                    @php
                        $qrSvg = $ticket->qr_svg ?? app(\App\Services\TicketService::class)->generateAdmissionQrSvg($ticket);
                    @endphp
                    <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 flex items-center gap-4 hover:border-slate-700 transition">
                        <div class="w-24 h-24 bg-white p-2 rounded-xl flex-shrink-0 flex items-center justify-center [&>svg]:w-full [&>svg]:h-auto shadow-md">
                            {!! $qrSvg !!}
                        </div>
                        <div class="space-y-1.5 text-xs flex-grow">
                            <div class="inline-block px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold text-[10px] uppercase">
                                {{ $ticket->category->name }} &bull; Admit 1
                            </div>
                            <div class="font-bold text-slate-100 text-sm">{{ $ticket->attendee_name }}</div>
                            <div class="font-mono text-[11px] text-slate-400 font-bold">{{ $ticket->ticket_number }}</div>

                            <div class="pt-1 flex items-center gap-2">
                                @if ($ticket->status === 'CHECKED_IN')
                                    <span class="px-2 py-0.5 rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/30 text-[10px] font-bold">
                                        CHECKED IN ({{ $ticket->checkin?->checked_in_at?->format('h:i A') }})
                                    </span>
                                @elseif ($ticket->status === 'ISSUED')
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">
                                        ADMIT READY (VALID)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30 text-[10px] font-bold">
                                        {{ $ticket->status }}
                                    </span>
                                @endif
                            </div>

                            <div class="pt-2 flex flex-wrap items-center gap-2">
                                @if (isset($ticket->qr_download_url))
                                    <a href="{{ $ticket->qr_download_url }}" download="Raag-E-Ishq-{{ $ticket->ticket_number }}-QR.svg"
                                        class="text-[11px] text-amber-400 hover:underline flex items-center gap-1 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download QR</span>
                                    </a>
                                @endif
                                @if (isset($ticket->download_url))
                                    <span class="text-slate-600">&bull;</span>
                                    <a href="{{ $ticket->download_url }}" target="_blank"
                                        class="text-[11px] text-slate-300 hover:underline flex items-center gap-1">
                                        <span>Single Pass &rarr;</span>
                                    </a>
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
