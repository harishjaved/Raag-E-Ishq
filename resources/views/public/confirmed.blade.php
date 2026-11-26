@extends('layouts.app')

@section('title', 'Raag-E-Ishq QR Entry Pass — ' . $booking->contact->full_name)
@section('og_image', ($tickets->first() && isset($tickets->first()->og_qr_url)) ? $tickets->first()->og_qr_url : asset('images/hero-stage-desktop.jpg'))
@section('og_description', 'Official QR Entry Pass for Raag-E-Ishq Featuring Kuldeep Sagar at Chavara Hall, Kochi. Organized by Joygivers for girls’ education.')

@section('content')
<style>
@media print {
    @page { size: A4 portrait; margin: 12mm; }
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
    .printable-ticket {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        border: 2px dashed #3B0A14 !important;
        border-radius: 10px !important;
        padding: 20px !important;
        margin-bottom: 24px !important;
        background: #ffffff !important;
        color: #000000 !important;
        box-shadow: none !important;
    }
    .printable-ticket * { color: #000000 !important; }
    .printable-ticket .ticket-tag {
        background: #FAF6F0 !important;
        border: 1px solid #3B0A14 !important;
        color: #000000 !important;
        font-weight: 700 !important;
    }
    .printable-ticket .qr-box {
        border: 1px solid #94a3b8 !important;
        background: #ffffff !important;
    }
    .print-only-header { display: block !important; }
}
@media screen {
    .print-only-header { display: none !important; }
}
</style>

<div class="bg-[#FAF6F0] text-[#231715] py-8 sm:py-10 px-4">
    <div class="max-w-2xl mx-auto space-y-8">
        <!-- Success Banner (Screen Only) -->
        <div class="success-banner relative p-6 sm:p-8 rounded-xl bg-[#3B0A14] text-[#FAF6F0] border border-[#D4AF37]/50 text-center space-y-3">
            <a href="{{ route('home') }}"
                aria-label="Close ticket view and go to main page"
                title="Close &amp; go to main page"
                class="no-print absolute top-4 right-4 w-9 h-9 rounded-full bg-[#24050B] hover:bg-[#D4AF37] text-[#FAF6F0] hover:text-[#231715] border border-[#D4AF37]/40 flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </a>
            <div class="w-11 h-11 mx-auto rounded-full bg-[#D4AF37]/20 text-[#E5C158] flex items-center justify-center border border-[#D4AF37]/45">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-semibold text-[#FAF6F0] font-serif-display">
                @if ($booking->is_attending)
                    Payment Verified &amp; Tickets Confirmed
                @else
                    Payment Verified &amp; Contribution Acknowledged
                @endif
            </h1>
            <p class="text-xs text-[#E8DFD3] max-w-md mx-auto leading-relaxed">
                @if ($booking->is_attending)
                    Your individual QR Entry Passes are ready. Please present each QR code at Chavara Hall gate check-in (gates open at 6:15 PM).
                @else
                    Thank you for supporting Joygivers’ girls’ education initiatives! Your contribution has been verified.
                @endif
            </p>
            <div class="pt-1 text-xs text-[#D8CBB8]">
                Booking Reference: <span class="font-mono text-[#E5C158] font-semibold">{{ $booking->booking_reference }}</span>
            </div>
        </div>

        @if ($booking->is_attending)
            <!-- Ticket Passes Section -->
            <div class="space-y-5">
                <div class="no-print flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-[#3B0A14] font-serif-display">Your QR Entry Passes ({{ $tickets->count() }})</h2>
                        <p class="text-xs text-[#5A4640]">Save your QR Entry Pass to your phone or print for gate entry.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="window.print()" class="btn-antique-gold text-xs font-semibold px-4 py-2 rounded-lg transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print All Passes / Save as PDF</span>
                        </button>
                        <a href="{{ route('home') }}"
                            aria-label="Go to main page"
                            title="Go to main page"
                            class="btn-deep-burgundy inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Main Page</span>
                        </a>
                    </div>
                </div>

                @foreach ($tickets as $ticket)
                    <div class="printable-ticket p-6 rounded-xl bg-white border border-[#D4AF37]/45 space-y-4">
                        <!-- Print-Only Ticket Header -->
                        <div class="print-only-header text-center pb-3 border-b-2 border-[#3B0A14] space-y-0.5">
                            <div class="text-[11px] font-bold uppercase tracking-widest">Joygivers Presents</div>
                            <div class="text-xl font-bold font-serif-display">RAAG-E-ISHQ — OFFICIAL ENTRY PASS</div>
                            <div class="text-[10px]">17 January 2027 (Sunday) &bull; 7:00 PM &bull; Chavara Hall, Kochi</div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-6 justify-between">
                            <div class="space-y-2 text-center sm:text-left flex-grow">
                                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded bg-[#FAF6F0] border border-[#D4AF37]/50 text-[#3B0A14] text-[10px] font-semibold uppercase tracking-wider ticket-tag">
                                    {{ $ticket->category->name }} &bull; Admit 1
                                </div>
                                <div class="text-xl font-semibold text-[#3B0A14] font-serif-display">{{ $ticket->attendee_name }}</div>
                                <div class="text-xs text-[#5A4640] space-y-0.5">
                                    <div>Venue: <span class="text-[#231715] font-medium">Chavara Hall, Near South Rly Stn, Kochi</span></div>
                                    <div>Pass Number: <span class="font-mono text-[#3B0A14] font-semibold">{{ $ticket->ticket_number }}</span></div>
                                    <div>Booking Ref: <span class="font-mono text-[#7A5C2E] font-semibold">{{ $booking->booking_reference }}</span></div>
                                    <div>Status: <span class="text-[#3B0A14] font-semibold uppercase">{{ $ticket->status }}</span></div>
                                </div>
                            </div>

                            <!-- Unique QR Entry Pass -->
                            <div class="flex-shrink-0 text-center space-y-1">
                                <div class="qr-box p-2.5 bg-white rounded-lg border border-[#E3D8C8] inline-block max-w-[165px] [&>svg]:w-full [&>svg]:h-auto">
                                    {!! $ticket->qr_svg !!}
                                </div>
                                <div class="text-[9px] uppercase tracking-wider text-[#7A5C2E] font-semibold">QR Entry Pass</div>
                            </div>
                        </div>

                        <div class="no-print pt-3 border-t border-[#EBE0D0] flex flex-wrap items-center justify-between gap-2 text-xs">
                            <span class="text-[11px] text-[#7A5C2E]">Admit 1 &bull; Single gate check-in</span>
                            <div class="flex items-center gap-2">
                                <a href="{{ $ticket->qr_download_url ?? ($ticket->qr_data_uri ?? '#') }}" download="Raag-E-Ishq-{{ $ticket->ticket_number }}-QR.svg"
                                    class="btn-antique-gold px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                                    <span>Download QR</span>
                                </a>

                                @if (isset($ticket->download_url))
                                    <a href="{{ $ticket->download_url }}" target="_blank"
                                        class="btn-deep-burgundy px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
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
            <div class="printable-ticket p-6 sm:p-8 rounded-xl bg-white border border-[#D4AF37]/45 space-y-6 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-[#EBE0D0] gap-4">
                    <div>
                        <span class="px-2.5 py-1 rounded bg-[#FAF6F0] border border-[#D4AF37]/50 text-[#3B0A14] text-[11px] font-semibold uppercase tracking-wider ticket-tag">
                            Official Supporter Receipt
                        </span>
                        <h2 class="text-2xl font-semibold text-[#3B0A14] font-serif-display mt-2">{{ $booking->contact->full_name }}</h2>
                        <p class="text-xs text-[#5A4640]">Attendance: <span class="text-[#7A5C2E] font-semibold">Non-Attending Contribution</span></p>
                        <p class="text-xs text-[#5A4640]">Booking Reference: <span class="font-mono text-[#3B0A14] font-semibold">{{ $booking->booking_reference }}</span></p>
                    </div>
                    <button onclick="window.print()" class="no-print btn-deep-burgundy self-start sm:self-auto text-xs font-semibold px-4 py-2 rounded-lg transition">
                        Print Official Receipt
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="text-[#7A5C2E] font-semibold uppercase tracking-wider text-[10px]">Contribution Breakdown</div>
                    @foreach ($booking->items as $item)
                        <div class="flex justify-between text-[#42322D] py-1.5 border-b border-[#EBE0D0]">
                            <span>{{ $item->category->name }} Supporter &times; {{ $item->quantity }}</span>
                            <span class="font-mono text-[#3B0A14] font-semibold">₹{{ number_format($item->subtotal_paise / 100, 0) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-sm font-semibold text-[#3B0A14] pt-2">
                        <span>Total Contribution Received</span>
                        <span class="font-mono text-lg">{{ $booking->formatted_total }}</span>
                    </div>
                </div>

                <div class="p-4 rounded-lg bg-[#FAF6F0] border border-[#E3D8C8] text-xs text-[#42322D] space-y-1">
                    <div class="font-semibold text-[#3B0A14]">Joygivers Charitable Initiative Notice</div>
                    <p>
                        Your contribution has been recorded in our patron ledger with zero hall seating deducted. Proceeds directly support girls’ education initiatives.
                    </p>
                </div>
            </div>
        @endif

        <!-- Support Card (Screen Only) -->
        <div class="support-card no-print p-6 rounded-xl bg-white border border-[#D4AF37]/35 text-center space-y-3">
            @if (!empty($booking->contact->email))
                <p class="text-xs text-[#42322D]">A confirmation copy has been sent to <strong class="text-[#3B0A14]">{{ $booking->contact->email }}</strong>.</p>
            @endif
            <p class="text-[11px] text-[#5A4640] leading-relaxed">
                As this is a charitable fundraising event, all confirmed and paid bookings are non-refundable. Ticket transfers to another guest are permitted prior to the event; in the unlikely event of cancellation or postponement, full refunds or rescheduled dates will be offered.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                <a href="https://wa.me/917736606506?text={{ urlencode('Hi Joygivers, I have an enquiry regarding my confirmed booking ' . $booking->booking_reference) }}"
                    target="_blank" class="btn-antique-gold px-4 py-2 rounded-lg text-xs font-semibold transition">
                    WhatsApp Support
                </a>
                <a href="mailto:events@joygivers.org?subject={{ urlencode('Enquiry: Booking ' . $booking->booking_reference) }}"
                    class="btn-deep-burgundy px-4 py-2 rounded-lg text-xs font-semibold transition">
                    events@joygivers.org
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
