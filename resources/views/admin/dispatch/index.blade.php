@extends('layouts.admin')

@section('title', 'Ticket Generator & Dispatch Desk')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Gate Admission Desk</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Ticket Generator &amp; Dispatch Desk</h1>
            <p class="text-xs text-slate-400 mt-1">Generate unique cryptographic QR tickets for verified customers and dispatch via WhatsApp, Email, or Print.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.bookings') }}" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Enquiries Desk</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-1 shadow">
            <div class="text-xs text-slate-400 font-medium">Total Registered Enquiries</div>
            <div class="text-2xl font-bold font-mono text-slate-100">{{ $counts['total'] }}</div>
        </div>
        <div class="p-5 rounded-2xl bg-[#0e1424] border border-amber-500/30 space-y-1 shadow">
            <div class="text-xs text-amber-300 font-medium">Ready for Ticket Generation</div>
            <div class="text-2xl font-bold font-mono text-amber-400">{{ $counts['needs_generation'] }}</div>
        </div>
        <div class="p-5 rounded-2xl bg-[#0e1424] border border-emerald-500/30 space-y-1 shadow">
            <div class="text-xs text-emerald-300 font-medium">Tickets Generated &amp; Active</div>
            <div class="text-2xl font-bold font-mono text-emerald-400">{{ $counts['dispatched'] }}</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl bg-[#0e1424] border border-slate-800/80 flex flex-col md:flex-row items-center justify-between gap-3 shadow">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.dispatch') }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ $filter === 'all' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                All ({{ $counts['total'] }})
            </a>
            <a href="{{ route('admin.dispatch', ['filter' => 'needs_generation']) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ $filter === 'needs_generation' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Ready to Generate ({{ $counts['needs_generation'] }})
            </a>
            <a href="{{ route('admin.dispatch', ['filter' => 'dispatched']) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ $filter === 'dispatched' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                Generated / Dispatched ({{ $counts['dispatched'] }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.dispatch') }}" class="w-full md:w-auto flex items-center gap-2">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, phone, ref..."
                class="w-full md:w-64 bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-amber-500">
            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition">
                Search
            </button>
        </form>
    </div>

    <!-- Bookings & Generated Tickets Cards -->
    <div class="space-y-5">
        @forelse ($bookings as $booking)
            <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-5 shadow-xl">
                <!-- Booking Header Row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-800/70 gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-100">{{ $booking->contact->full_name }}</span>
                            @if ($booking->status === 'CONFIRMED')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">COMPLETED</span>
                            @elseif ($booking->status === 'IN_PROCESS')
                                <span class="px-2.5 py-0.5 rounded-full bg-sky-500/15 text-sky-400 border border-sky-500/30 text-[10px] font-bold">IN PROCESS</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/30 text-[10px] font-bold">{{ $booking->status }}</span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[10px]">
                                {{ $booking->is_attending ? 'In-Person Attendee' : 'Charity Donation Only' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 font-mono">
                            <span>Ref: <strong class="text-amber-400 font-bold">{{ $booking->booking_reference }}</strong></span>
                            <span>Phone: <a href="tel:{{ $booking->contact->phone }}" class="text-slate-300 hover:underline">{{ $booking->contact->phone }}</a></span>
                            <span class="font-sans">Email: <span class="text-slate-300">{{ $booking->contact->email }}</span></span>
                            <span>Amount: <strong class="text-slate-200">₹{{ number_format($booking->total_amount_paise / 100, 2) }}</strong></span>
                        </div>
                    </div>

                    <!-- Top Action / WhatsApp Dispatch -->
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($booking->tickets->isEmpty())
                            <form action="{{ route('admin.dispatch.generate', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition shadow-lg shadow-amber-500/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>⚡ Generate Unique QR Tickets</span>
                                </button>
                            </form>
                        @else
                            @if (isset($booking->whatsapp_dispatch_url))
                                <a href="{{ $booking->whatsapp_dispatch_url }}" target="_blank"
                                    class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-300 font-bold text-xs flex items-center gap-1.5 transition">
                                    <span>Send Passes via WhatsApp</span>
                                </a>
                            @endif
                            <a href="{{ route('booking.confirmed', $booking->booking_reference) }}" target="_blank"
                                class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Public Passes Page</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Generated Tickets Grid -->
                @if ($booking->tickets->isEmpty())
                    <div class="p-6 rounded-2xl bg-slate-950/60 border border-dashed border-slate-800 text-center space-y-2">
                        <div class="text-xs text-slate-400">Tickets have not been generated yet for this booking enquiry.</div>
                        <p class="text-[11px] text-slate-500">Confirm payment and click <strong>"Generate Unique QR Tickets"</strong> to produce individual gate entry passes with cryptographic QR codes.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Generated Gate Admission Passes ({{ $booking->tickets->count() }})
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($booking->tickets as $ticket)
                                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center gap-3">
                                    <!-- QR Preview -->
                                    <div class="w-20 h-20 bg-white p-1.5 rounded-xl flex-shrink-0 flex items-center justify-center [&>svg]:w-full [&>svg]:h-auto shadow">
                                        {!! $ticket->qr_svg !!}
                                    </div>

                                    <div class="space-y-1 text-xs flex-grow overflow-hidden">
                                        <div class="inline-block px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold text-[10px]">
                                            {{ $ticket->category->name }}
                                        </div>
                                        <div class="font-bold text-slate-100 truncate">{{ $ticket->attendee_name }}</div>
                                        <div class="font-mono text-[10px] text-slate-400 font-bold">{{ $ticket->ticket_number }}</div>

                                        <div class="pt-1 flex items-center gap-2">
                                            <a href="{{ $ticket->qr_download_url }}" download="Raag-E-Ishq-{{ $ticket->ticket_number }}-QR.svg"
                                                class="text-[11px] text-amber-400 hover:underline flex items-center gap-1 font-semibold">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>QR SVG</span>
                                            </a>
                                            <span class="text-slate-600">&bull;</span>
                                            <a href="{{ $ticket->download_url }}" target="_blank"
                                                class="text-[11px] text-slate-300 hover:underline flex items-center gap-1">
                                                <span>Print Pass &rarr;</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-12 rounded-3xl bg-[#0e1424] border border-slate-800/80 text-center text-xs text-slate-500">
                No bookings match the selected filter.
            </div>
        @endforelse

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
