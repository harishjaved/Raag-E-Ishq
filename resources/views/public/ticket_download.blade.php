@extends('layouts.app')

@section('title', 'QR Entry Pass — ' . $ticket->ticket_number)

@section('content')
<style>
@media print {
    @page { size: A4 portrait; margin: 12mm; }
    header, footer, nav, .no-print { display: none !important; }
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .printable-ticket {
        border: 2px dashed #3B0A14 !important;
        box-shadow: none !important;
        background: #ffffff !important;
        color: #000000 !important;
    }
}
</style>

<div class="bg-[#FAF6F0] text-[#231715] py-10 px-4">
    <div class="max-w-md mx-auto space-y-4">
        <div class="no-print flex flex-wrap justify-between items-center gap-2 text-xs">
            <a href="{{ route('event.show') }}" class="text-[#7A5C2E] font-semibold hover:underline">&larr; Back to Raag-E-Ishq</a>
            <div class="flex items-center gap-2">
                <a href="{{ route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]) }}"
                    download="Raag-E-Ishq-{{ $ticket->ticket_number }}-QR.svg"
                    class="btn-deep-burgundy px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5">
                    <span>Download QR</span>
                </a>
                <button onclick="window.print()" class="btn-antique-gold px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5">
                    <span>Print Pass / PDF</span>
                </button>
                <a href="{{ route('home') }}"
                    aria-label="Close ticket and go to main page"
                    title="Close &amp; go to main page"
                    class="w-8 h-8 rounded-full bg-[#3B0A14] hover:bg-[#D4AF37] text-[#FAF6F0] hover:text-[#231715] border border-[#D4AF37]/50 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="printable-ticket p-7 rounded-xl bg-white border border-[#D4AF37]/50 space-y-5 text-center">
            <div class="space-y-1 border-b border-[#EBE0D0] pb-4">
                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#7A5C2E]">Joygivers Presents</div>
                <h1 class="text-3xl font-semibold text-[#3B0A14] font-serif-display">Raag-E-Ishq</h1>
                <div class="text-xs text-[#5A4640]">17 January 2027 (Sunday) &bull; 7:00 PM &bull; Chavara Hall, Kochi</div>
            </div>

            <div class="space-y-1.5">
                <div class="inline-block px-3 py-0.5 rounded bg-[#FAF6F0] border border-[#D4AF37]/50 text-[#3B0A14] text-xs font-semibold uppercase tracking-wider">
                    {{ $ticket->category->name }} &bull; Admit 1
                </div>
                <div class="text-xl font-semibold text-[#3B0A14] font-serif-display pt-1">{{ $ticket->attendee_name }}</div>
                <div class="text-xs font-mono text-[#7A5C2E] font-semibold">{{ $ticket->ticket_number }}</div>
            </div>

            <div class="p-3 bg-[#FAF6F0] rounded-lg border border-[#E3D8C8] inline-block mx-auto max-w-[200px] [&>svg]:w-full [&>svg]:h-auto">
                {!! $qrSvg !!}
            </div>

            <div class="text-[11px] text-[#5A4640] space-y-1 border-t border-[#EBE0D0] pt-4">
                <div>Booking Reference: <span class="font-mono text-[#3B0A14] font-semibold">{{ ($ticket->booking ?? $booking)->booking_reference }}</span></div>
                <div>Individual QR Entry Pass &bull; Gates open at 6:15 PM</div>
                <div>Single-entry verification &bull; Chavara Cultural Centre, Kochi</div>
            </div>
        </div>
    </div>
</div>
@endsection
