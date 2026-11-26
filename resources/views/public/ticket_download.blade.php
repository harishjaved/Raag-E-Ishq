<!DOCTYPE html>
<html lang="en" class="bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Ticket — {{ $ticket->ticket_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-display { font-family: 'Playfair Display', serif; }
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: 2px dashed #000000 !important;
                box-shadow: none !important;
                color: #000000 !important;
                background: #ffffff !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                max-width: 100% !important;
                margin: 0 auto !important;
            }
            .print-card * {
                color: #000000 !important;
            }
            .print-card .qr-white-box {
                border: 1px solid #000000 !important;
                background: #ffffff !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-slate-950">
    <div class="max-w-md w-full space-y-4">
        <div class="no-print flex justify-between items-center text-xs">
            <a href="{{ route('event.show') }}" class="text-amber-400 font-semibold hover:underline">&larr; Back to Raag-E-Ishq</a>
            <div class="flex items-center gap-2">
                <a href="{{ route('ticket.qr.download', ['uuid' => $ticket->qr_uuid]) }}"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download QR</span>
                </a>
                <button onclick="window.print()" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Pass / PDF</span>
                </button>
            </div>
        </div>

        <div class="print-card rounded-3xl bg-slate-900 border border-slate-800 p-6 space-y-6 shadow-2xl">
            <!-- Header -->
            <div class="text-center space-y-1 pb-4 border-b border-slate-800">
                <div class="text-xs uppercase tracking-widest text-amber-400 font-bold">Joygivers Presents</div>
                <h1 class="text-2xl font-bold font-serif-display text-slate-100">Raag-E-Ishq</h1>
                <p class="text-xs text-slate-400">Official Event Admission Pass &bull; 17th January, 7:00 PM</p>
            </div>

            <!-- Admission Details (NO PRIVATE BILLING DATA) -->
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">Attendee</div>
                        <div class="text-base font-bold text-slate-100">{{ $ticket->attendee_name }}</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase">
                            {{ $ticket->category->name }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs pt-2">
                    <div>
                        <div class="text-slate-400 font-medium">Venue</div>
                        <div class="text-slate-200 font-semibold mt-0.5">Chavara Hall</div>
                        <div class="text-[10px] text-slate-400">Near South Rly Stn, Kochi</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-medium">Ticket Number</div>
                        <div class="text-slate-200 font-mono font-bold mt-0.5">{{ $ticket->ticket_number }}</div>
                        <div class="text-[10px] text-emerald-400">Status: {{ $ticket->status }}</div>
                    </div>
                </div>
            </div>

            <!-- Single Unique Admission QR -->
            <div class="text-center space-y-2 py-2">
                <div class="inline-block p-4 bg-white rounded-2xl shadow-md max-w-[220px] mx-auto [&>svg]:w-full [&>svg]:h-auto">
                    {!! $qrSvg !!}
                </div>
                <div class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Scan at Entrance Gate for Admission</div>
            </div>

            <!-- Instructions -->
            <div class="pt-4 border-t border-slate-800 text-center text-[11px] text-slate-400 space-y-1">
                <p>This pass admits one person only. Re-entry is not permitted once scanned.</p>
                <p class="text-[10px] text-slate-500">Joygivers &bull; events@joygivers.org</p>
            </div>
        </div>
    </div>
</body>
</html>
