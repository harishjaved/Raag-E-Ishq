@extends('layouts.app')

@section('title', 'Raag-E-Ishq Admission Pass — ' . $booking->contact->full_name)
@section('og_image', ($tickets->first() && isset($tickets->first()->og_qr_url)) ? $tickets->first()->og_qr_url : asset('images/music-hero-bg.jpg'))
@section('og_description', 'Official QR admission pass for Raag-E-Ishq at Chavara Hall, Kochi. Organized by Joygivers for girl education.')

@section('content')
<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 12mm;
    }
    /* Hide all web chrome, headers, footers, buttons and banners */
    header, footer, nav, .no-print, .success-banner, .support-card {
        display: none !important;
    }
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
        margin: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .max-w-2xl {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    /* Printable Ticket Pass */
    .printable-ticket {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        border: 2px dashed #0f172a !important;
        border-radius: 14px !important;
        padding: 20px !important;
        margin-bottom: 24px !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-shadow: none !important;
    }
    .printable-ticket * {
        color: #000000 !important;
    }
    .printable-ticket .ticket-tag {
        background: #f1f5f9 !important;
        border: 1px solid #000000 !important;
        color: #000000 !important;
        font-weight: 800 !important;
    }
    .printable-ticket .qr-box {
        border: 1px solid #94a3b8 !important;
        background: #ffffff !important;
    }
    .print-only-header {
        display: block !important;
    }
}
@media screen {
    .print-only-header {
        display: none !important;
    }
}
</style>

<div class="max-w-2xl mx-auto px-4 py-8 space-y-8">
    <!-- Success Banner (Screen Only) -->
    <div class="success-banner relative p-6 rounded-3xl bg-emerald-950/80 border border-emerald-500/40 text-center space-y-3 shadow-2xl">
        <a href="{{ route('home') }}"
            aria-label="Close ticket view and go to main page"
            title="Close &amp; go to main page"
            class="no-print absolute top-4 right-4 w-9 h-9 rounded-full bg-slate-900/80 hover:bg-amber-500 text-slate-200 hover:text-slate-950 border border-emerald-500/40 hover:border-amber-400 flex items-center justify-center transition shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </a>
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
                Your admission tickets have been generated. Present each unique QR code at Chavara Hall gate check-in.
            @else
                Thank you for your generous support of Joygivers' initiative for girl education! Your contribution has been verified.
            @endif
        </p>
        <div class="pt-2 text-xs text-slate-400">
            Booking Reference: <span class="font-mono text-amber-400 font-bold">{{ $booking->booking_reference }}</span>
        </div>
    </div>

    @if ($booking->is_attending)
    <!-- Ticket Passes Section -->
    <div class="space-y-6">
        <div class="no-print flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-100">Your Admission Tickets ({{ $tickets->count() }})</h2>
                <p class="text-xs text-slate-400">Save QR to your gallery or print tickets for gate check-in.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="window.print()" class="text-xs font-bold px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 transition flex items-center gap-2 shadow-lg shadow-amber-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print All Passes / Save as PDF</span>
                </button>
                <a href="{{ route('home') }}"
                    aria-label="Go to main page"
                    title="Go to main page"
                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Main Page</span>
                </a>
            </div>
        </div>

        @foreach ($tickets as $ticket)
            <div class="printable-ticket p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
                <!-- Print-Only Ticket Header -->
                <div class="print-only-header text-center pb-3 border-b-2 border-slate-900 space-y-0.5">
                    <div class="text-[11px] font-bold uppercase tracking-widest text-slate-800">Joygivers Presents</div>
                    <div class="text-xl font-extrabold text-slate-950 font-serif-display">RAAG-E-ISHQ — OFFICIAL ADMISSION PASS</div>
                    <div class="text-[10px] text-slate-700">A Musical Evening For Girl Education &bull; 17th January, 7:00 PM &bull; Chavara Hall, Kochi</div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-6 justify-between">
                    <div class="space-y-2 text-center sm:text-left flex-grow">
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold uppercase tracking-wider ticket-tag">
                            {{ $ticket->category->name }} &bull; Admit 1
                        </div>
                        <div class="text-xl font-bold text-slate-100">{{ $ticket->attendee_name }}</div>
                        <div class="text-xs text-slate-400 space-y-0.5">
                            <div>Venue: <span class="text-slate-200 font-medium">Chavara Hall, Near South Rly Stn, Kochi</span></div>
                            <div>Ticket Number: <span class="font-mono text-slate-300 font-bold">{{ $ticket->ticket_number }}</span></div>
                            <div>Booking Ref: <span class="font-mono text-amber-400 font-bold">{{ $booking->booking_reference }}</span></div>
                            <div>Status: <span class="text-emerald-400 font-semibold uppercase">{{ $ticket->status }}</span></div>
                        </div>
                    </div>

                    <!-- Unique Admission QR -->
                    <div class="flex-shrink-0 text-center space-y-1">
                        <div class="qr-box p-3 bg-white rounded-2xl inline-block shadow-md max-w-[170px] [&>svg]:w-full [&>svg]:h-auto">
                            {!! $ticket->qr_svg !!}
                        </div>
                        <div class="text-[9px] uppercase tracking-wider text-slate-500 font-bold">Entrance Scan QR</div>
                    </div>
                </div>

                <!-- Ticket Actions (Screen Only) -->
                <div class="no-print pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="text-[11px] text-slate-500">Admit One &bull; Valid for single gate scan</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ $ticket->qr_download_url ?? ($ticket->qr_data_uri ?? '#') }}" download="Raag-E-Ishq-{{ $ticket->ticket_number }}-QR.svg"
                            class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-400 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download QR</span>
                        </a>

                        @if (isset($ticket->download_url))
                            <a href="{{ $ticket->download_url }}" target="_blank"
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>View / Print Pass</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <!-- Non-Attending Official Contribution & Supporter Receipt -->
    <div class="printable-ticket p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6 shadow-xl text-center sm:text-left">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-800 gap-4">
            <div>
                <span class="px-2.5 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300 text-[11px] font-bold uppercase tracking-wider ticket-tag">
                    Official Supporter Receipt
                </span>
                <h2 class="text-xl font-bold text-slate-100 mt-2">{{ $booking->contact->full_name }}</h2>
                <p class="text-xs text-slate-400">Attendance: <span class="text-amber-400 font-semibold">Non-Attending / In Absentia</span></p>
                <p class="text-xs text-slate-400">Booking Reference: <span class="font-mono text-amber-400 font-bold">{{ $booking->booking_reference }}</span></p>
            </div>
            <button onclick="window.print()" class="no-print self-start sm:self-auto text-xs font-semibold px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 transition flex items-center gap-1.5 shadow">
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
                Your registration has been recorded in our official patron ledger. Since you indicated that you will not be attending in person, no hall seating has been withheld, and your entire contribution directly fuels our initiatives for girl education.
            </p>
        </div>
    </div>
    @endif

    <!-- Support Card (Screen Only) -->
    <div class="support-card no-print p-6 rounded-3xl bg-slate-900 border border-slate-800 text-center space-y-4 shadow-xl">
        <div class="space-y-1">
            @if (!empty($booking->contact->email))
                <p class="text-xs text-slate-300">A confirmation receipt has been emailed to <strong class="text-amber-400">{{ $booking->contact->email }}</strong>.</p>
            @else
                <p class="text-xs text-slate-300">Save or print this official confirmation pass for smooth venue entry at Chavara Hall.</p>
            @endif
            <p class="text-[11px] text-slate-500">
                <strong>Cancellation Policy:</strong> All ticket bookings are final, non-cancellable, and non-refundable. All proceeds support Joygivers educational initiatives.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a href="https://wa.me/917736606506?text={{ urlencode('Hi Joygivers, I have an enquiry regarding my confirmed booking ' . $booking->booking_reference) }}"
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
