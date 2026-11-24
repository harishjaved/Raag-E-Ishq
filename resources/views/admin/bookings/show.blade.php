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
                    <button type="button" onclick="openWhatsAppModal()"
                        class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-300 font-bold text-xs flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>Send Passes via WhatsApp</span>
                    </button>
                    <button type="button" onclick="quickSendWhatsAppWithQr()"
                        class="px-3.5 py-2 rounded-xl bg-purple-600/30 hover:bg-purple-600/40 border border-purple-500/40 text-purple-300 font-bold text-xs flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 002 2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copy QR &amp; Open WhatsApp</span>
                    </button>
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
                                @if (isset($ticket->qr_svg))
                                    <span class="text-slate-600">&bull;</span>
                                    <button type="button"
                                        onclick="copyTicketCardQr({{ $loop->index }})"
                                        class="text-[11px] text-purple-400 hover:underline flex items-center gap-1 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 002 2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        <span>Copy QR Image</span>
                                    </button>
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

{{-- Embedded Meta & QR SVGs for this booking --}}
<div id="booking-meta-{{ $booking->id }}" class="hidden"
    data-id="{{ $booking->id }}"
    data-reference="{{ $booking->booking_reference }}"
    data-customer="{{ $booking->contact->full_name }}"
    data-phone="{{ $booking->contact->phone }}"
    data-amount="₹{{ number_format($booking->total_amount_paise / 100, 2) }}"
    data-whatsapp-url="{{ $booking->whatsapp_ticket_url ?? '' }}"
    data-whatsapp-text="{{ $booking->whatsapp_ticket_text ?? '' }}"
    data-is-donation="{{ $booking->is_donation ? '1' : '0' }}"
    data-tickets-count="{{ $booking->tickets->count() }}">
    @foreach ($booking->tickets as $tIdx => $t)
        <div class="ticket-meta"
            data-ticket-id="{{ $t->id }}"
            data-ticket-number="{{ $t->ticket_number }}"
            data-attendee="{{ $t->attendee_name }}"
            data-category="{{ $t->category->name ?? 'Tier Pass' }}"
            data-qr-uuid="{{ $t->qr_uuid }}"
            data-download-url="{{ $t->download_url ?? '' }}"
            data-qr-download-url="{{ $t->qr_download_url ?? '' }}">
            <div id="svg-holder-{{ $booking->id }}-{{ $tIdx }}">
                {!! $t->qr_svg ?? '' !!}
            </div>
        </div>
    @endforeach
</div>

<!-- WhatsApp Ticket Dispatch Modal -->
<div id="whatsappDispatchModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-[#0e1424] border border-slate-700 rounded-3xl max-w-xl w-full p-6 shadow-2xl relative space-y-5 animate-scale-up">
        <!-- Modal Header -->
        <div class="flex items-start justify-between border-b border-slate-800 pb-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>WhatsApp QR Ticket Dispatch</span>
                </div>
                <h3 id="modalCustomerTitle" class="text-lg font-bold text-slate-100 font-serif-display">{{ $booking->contact->full_name }} ({{ $booking->booking_reference }})</h3>
                <p id="modalSubtitle" class="text-xs text-slate-400">Total: ₹{{ number_format($booking->total_amount_paise / 100, 2) }} &bull; Phone: {{ $booking->contact->phone }}</p>
            </div>
            <button type="button" onclick="closeWhatsAppModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-100 flex items-center justify-center text-sm font-bold transition">
                &times;
            </button>
        </div>

        <!-- Ticket Switcher Tabs (if booking has multiple tickets) -->
        <div id="modalTicketTabs" class="flex flex-wrap gap-2 hidden">
            <!-- Dynamically populated -->
        </div>

        <!-- Active Ticket QR Preview Badge -->
        <div class="p-5 rounded-2xl bg-white text-slate-900 shadow-xl border border-slate-200 text-center space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2 text-left">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-amber-600">Joygivers Presents</div>
                    <div class="text-base font-black text-slate-950 font-serif-display">Raag-E-Ishq</div>
                </div>
                <div id="modalCategoryBadge" class="px-2.5 py-1 rounded-full bg-slate-900 text-white font-bold text-[11px]">
                    Tier Pass
                </div>
            </div>

            <div class="py-1">
                <div id="modalAttendeeName" class="font-extrabold text-slate-900 text-base leading-tight">Customer Name</div>
                <div id="modalTicketNumber" class="font-mono text-xs text-slate-500 font-bold mt-0.5">TKT-XXXX-XXXX</div>
            </div>

            <!-- Crisp QR Display -->
            <div class="flex justify-center my-2">
                <div id="modalQrDisplay" class="w-52 h-52 bg-white p-2 rounded-2xl border-2 border-slate-200 shadow-inner flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                    <!-- SVG injected here -->
                </div>
            </div>

            <div class="text-[11px] font-semibold text-slate-600">
                Scan at Chavara Hall gate entrance for instant check-in
            </div>
            <div class="text-[10px] text-amber-800 font-bold italic">
                100% Proceeds Dedicated to Girl Education
            </div>
        </div>

        <!-- Primary Action Buttons -->
        <div class="space-y-2.5 pt-1">
            <!-- 1. Send WhatsApp Pass with QR Code (Primary prominent action) -->
            <button type="button" id="modalOpenWhatsAppBtn"
                class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 active:from-emerald-600 active:to-emerald-700 text-slate-950 font-extrabold text-sm flex items-center justify-center gap-2.5 transition shadow-lg shadow-emerald-500/25">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                <span>Send WhatsApp Pass (with QR Code)</span>
            </button>

            <!-- Secondary Actions Row -->
            <div class="grid grid-cols-2 gap-2.5">
                <!-- 2. Download QR Image (PNG) -->
                <button type="button" id="modalDownloadPngBtn"
                    class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download QR (PNG)</span>
                </button>

                <!-- 3. Copy Text Message -->
                <button type="button" id="modalCopyTextBtn"
                    class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs flex items-center justify-center gap-2 transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Copy Message Text</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Toast Notification -->
<div id="qrToast" class="fixed bottom-6 right-6 z-50 hidden p-4 rounded-2xl bg-slate-900 border-2 border-emerald-500 text-slate-100 text-xs font-bold shadow-2xl flex items-center gap-3 transition transform max-w-sm">
    <div class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">✓</div>
    <div id="qrToastMessage">Action completed!</div>
</div>

<script>
let currentActiveBooking = null;
let currentActiveTicketIdx = 0;

function showToast(message, duration = 3500) {
    const toast = document.getElementById('qrToast');
    const msg = document.getElementById('qrToastMessage');
    if (!toast || !msg) return;
    msg.textContent = message;
    toast.classList.remove('hidden');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => {
        toast.classList.add('hidden');
    }, duration);
}

function getBookingMetadata(bookingId) {
    const el = document.getElementById('booking-meta-' + bookingId);
    if (!el) return null;

    const tickets = [];
    el.querySelectorAll('.ticket-meta').forEach((tEl, idx) => {
        const svgHolder = document.getElementById(`svg-holder-${bookingId}-${idx}`);
        tickets.push({
            id: tEl.dataset.ticketId,
            number: tEl.dataset.ticketNumber,
            attendee: tEl.dataset.attendee,
            category: tEl.dataset.category,
            qrUuid: tEl.dataset.qrUuid,
            downloadUrl: tEl.dataset.downloadUrl,
            qrDownloadUrl: tEl.dataset.qrDownloadUrl,
            svg: svgHolder ? svgHolder.querySelector('svg') : null,
        });
    });

    return {
        id: bookingId,
        reference: el.dataset.reference,
        customer: el.dataset.customer,
        phone: el.dataset.phone,
        amount: el.dataset.amount,
        whatsappUrl: el.dataset.whatsappUrl,
        whatsappText: el.dataset.whatsappText,
        isDonation: el.dataset.isDonation === '1',
        tickets: tickets,
    };
}

function renderTicketCanvas(svgEl, attendeeName, ref, categoryName, ticketNum) {
    return new Promise((resolve, reject) => {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 640;
            canvas.height = 720;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, canvas.width, 110);

            ctx.fillStyle = '#f59e0b';
            ctx.font = 'bold 12px sans-serif';
            ctx.fillText('JOYGIVERS PRESENTS', 32, 38);

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 28px sans-serif';
            ctx.fillText('Raag-E-Ishq', 32, 74);

            ctx.fillStyle = '#94a3b8';
            ctx.font = '12px sans-serif';
            ctx.fillText('17 Dec 2026 • 7:00 PM • Chavara Hall, Kochi', 32, 96);

            ctx.fillStyle = '#f8fafc';
            ctx.fillRect(24, 126, canvas.width - 48, 70);
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 1;
            ctx.strokeRect(24, 126, canvas.width - 48, 70);

            ctx.fillStyle = '#64748b';
            ctx.font = 'bold 10px sans-serif';
            ctx.fillText('ADMITTEE / CATEGORY', 38, 148);

            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 17px sans-serif';
            ctx.fillText(attendeeName || 'Guest Admission', 38, 175);

            ctx.fillStyle = '#f59e0b';
            ctx.font = 'bold 13px sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(categoryName || 'Admit 1', canvas.width - 38, 175);
            ctx.textAlign = 'left';

            if (!svgEl) return resolve(canvas);

            const svgString = new XMLSerializer().serializeToString(svgEl);
            const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            const URL = window.URL || window.webkitURL || window;
            const blobUrl = URL.createObjectURL(svgBlob);
            const img = new Image();

            img.onload = () => {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(110, 210, 420, 420);
                ctx.strokeStyle = '#cbd5e1';
                ctx.strokeRect(110, 210, 420, 420);

                ctx.drawImage(img, 130, 230, 380, 380);
                URL.revokeObjectURL(blobUrl);

                ctx.fillStyle = '#0f172a';
                ctx.font = 'bold 13px monospace';
                ctx.textAlign = 'center';
                ctx.fillText((ticketNum || ref || ''), canvas.width / 2, 652);

                ctx.fillStyle = '#64748b';
                ctx.font = '11px sans-serif';
                ctx.fillText('Present this QR at Chavara Hall entrance for instant scan check-in', canvas.width / 2, 676);

                ctx.fillStyle = '#94a3b8';
                ctx.font = 'italic 10px sans-serif';
                ctx.fillText('100% Proceeds Dedicated to Girl Education & Welfare', canvas.width / 2, 696);

                ctx.textAlign = 'left';
                resolve(canvas);
            };
            img.onerror = () => resolve(canvas);
            img.src = blobUrl;
        } catch (e) {
            reject(e);
        }
    });
}

function copyCanvasToClipboard(canvas) {
    return new Promise((resolve, reject) => {
        canvas.toBlob(async (blob) => {
            if (!blob) return reject(new Error('No blob'));
            try {
                if (navigator.clipboard && navigator.clipboard.write) {
                    await navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]);
                    resolve(true);
                } else {
                    reject(new Error('Clipboard API unavailable'));
                }
            } catch (err) {
                reject(err);
            }
        }, 'image/png');
    });
}

async function dispatchWhatsAppPass(booking, ticket) {
    if (!booking) return;
    let canvas = null;
    let file = null;

    if (ticket && ticket.svg) {
        try {
            canvas = await renderTicketCanvas(ticket.svg, ticket.attendee, booking.reference, ticket.category, ticket.number);
            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
            if (blob) {
                file = new File([blob], `Raag-E-Ishq-${ticket.number || 'Pass'}-QR.png`, { type: 'image/png' });
            }
        } catch (e) {
            console.warn('Canvas render error', e);
        }
    }

    // Try Web Share API with file (shares image and text directly into WhatsApp on supported mobile/desktop)
    if (file && navigator.canShare && navigator.canShare({ files: [file] })) {
        try {
            await navigator.share({
                title: `Raag-E-Ishq Admission Pass - ${ticket ? ticket.number : booking.reference}`,
                text: booking.whatsappText,
                files: [file]
            });
            showToast('✓ WhatsApp Pass with QR Code shared successfully!');
            return;
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.warn('Navigator share error, falling back', err);
        }
    }

    // Direct Web Fallback: copy image to clipboard and open WhatsApp link (which includes direct QR image link and digital pass)
    if (canvas) {
        try {
            await copyCanvasToClipboard(canvas);
        } catch (e) {}
    }

    showToast('Opening WhatsApp with ticket details & QR pass link!');
    if (booking.whatsappUrl) {
        window.open(booking.whatsappUrl, '_blank');
    }
}

async function quickSendWhatsAppWithQr() {
    const bookingId = {{ $booking->id }};
    const data = getBookingMetadata(bookingId);
    if (!data) return;
    const ticket = (data.tickets && data.tickets.length > 0) ? data.tickets[0] : null;
    await dispatchWhatsAppPass(data, ticket);
}

async function copyTicketCardQr(idx) {
    const bookingId = {{ $booking->id }};
    const data = getBookingMetadata(bookingId);
    if (!data || !data.tickets[idx] || !data.tickets[idx].svg) return;

    try {
        const ticket = data.tickets[idx];
        const canvas = await renderTicketCanvas(ticket.svg, ticket.attendee, data.reference, ticket.category, ticket.number);
        await copyCanvasToClipboard(canvas);
        showToast(`✓ QR Ticket Image #${idx + 1} copied to clipboard!`);
    } catch (e) {
        showToast('Clipboard copy failed. Please use "Download QR".');
    }
}

function openWhatsAppModal() {
    const bookingId = {{ $booking->id }};
    const data = getBookingMetadata(bookingId);
    if (!data) return;

    currentActiveBooking = data;
    currentActiveTicketIdx = 0;

    const modal = document.getElementById('whatsappDispatchModal');

    const tabsContainer = document.getElementById('modalTicketTabs');
    tabsContainer.innerHTML = '';
    if (data.tickets.length > 1) {
        tabsContainer.classList.remove('hidden');
        data.tickets.forEach((t, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `px-3 py-1 rounded-xl text-xs font-bold transition ${idx === 0 ? 'bg-amber-500 text-slate-950 shadow' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'}`;
            btn.textContent = `Pass #${idx + 1} (${t.category})`;
            btn.onclick = () => selectModalTicket(idx);
            tabsContainer.appendChild(btn);
        });
    } else {
        tabsContainer.classList.add('hidden');
    }

    selectModalTicket(0);
    modal.classList.remove('hidden');
}

function selectModalTicket(idx) {
    if (!currentActiveBooking || !currentActiveBooking.tickets[idx]) return;
    currentActiveTicketIdx = idx;
    const ticket = currentActiveBooking.tickets[idx];

    const tabsContainer = document.getElementById('modalTicketTabs');
    tabsContainer.querySelectorAll('button').forEach((b, i) => {
        if (i === idx) {
            b.className = 'px-3 py-1 rounded-xl text-xs font-bold transition bg-amber-500 text-slate-950 shadow';
        } else {
            b.className = 'px-3 py-1 rounded-xl text-xs font-bold transition bg-slate-800 text-slate-300 hover:bg-slate-700';
        }
    });

    document.getElementById('modalAttendeeName').textContent = ticket.attendee || currentActiveBooking.customer;
    document.getElementById('modalTicketNumber').textContent = ticket.number || currentActiveBooking.reference;
    document.getElementById('modalCategoryBadge').textContent = ticket.category || 'Admission';

    const qrDisplay = document.getElementById('modalQrDisplay');
    qrDisplay.innerHTML = '';
    if (ticket.svg) {
        qrDisplay.appendChild(ticket.svg.cloneNode(true));
    }

    const openBtn = document.getElementById('modalOpenWhatsAppBtn');
    openBtn.onclick = async () => {
        await dispatchWhatsAppPass(currentActiveBooking, ticket);
    };

    const downloadBtn = document.getElementById('modalDownloadPngBtn');
    downloadBtn.onclick = async () => {
        if (!ticket.svg) return;
        try {
            const canvas = await renderTicketCanvas(ticket.svg, ticket.attendee, currentActiveBooking.reference, ticket.category, ticket.number);
            const a = document.createElement('a');
            a.download = `Raag-E-Ishq-${ticket.number || 'Pass'}-QR.png`;
            a.href = canvas.toDataURL('image/png');
            a.click();
            showToast('✓ High-resolution QR Ticket downloaded as PNG!');
        } catch (e) {
            showToast('Download failed.');
        }
    };

    const copyTextBtn = document.getElementById('modalCopyTextBtn');
    copyTextBtn.onclick = async () => {
        try {
            await navigator.clipboard.writeText(currentActiveBooking.whatsappText || '');
            showToast('✓ WhatsApp message text copied to clipboard!');
        } catch (e) {
            showToast('Text copy failed.');
        }
    };
}

function closeWhatsAppModal() {
    const modal = document.getElementById('whatsappDispatchModal');
    if (modal) modal.classList.add('hidden');
}
</script>
@endsection
