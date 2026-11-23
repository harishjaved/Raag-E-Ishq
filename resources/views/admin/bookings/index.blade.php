@extends('layouts.admin')

@section('title', 'Enquiries & Bookings Desk')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Reservations &amp; Payment Desk</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-100 font-serif-display">Enquiries &amp; Bookings Desk</h1>
            <p class="text-xs text-slate-400 mt-1">Call customers to confirm bookings, send payment details via WhatsApp/Email, and approve tickets upon payment verification.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dispatch') }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition shadow-lg shadow-amber-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Ticket Generator &amp; Dispatch Desk &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Filter Tabs & Search Bar -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-3 shadow">
        <!-- Status Filter Tabs -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <a href="{{ route('admin.bookings') }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ !$currentStatus ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                All ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'PENDING']) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ in_array($currentStatus, ['PENDING', 'PAYMENT_PENDING']) ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-amber-400 hover:bg-slate-700' }}">
                Pending Call ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'IN_PROCESS']) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $currentStatus === 'IN_PROCESS' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-sky-400 hover:bg-slate-700' }}">
                In Process ({{ $counts['in_process'] }})
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'CONFIRMED']) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $currentStatus === 'CONFIRMED' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-emerald-400 hover:bg-slate-700' }}">
                Completed ({{ $counts['completed'] }})
            </a>
            <a href="{{ route('admin.bookings', ['status' => 'CANCELLED']) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $currentStatus === 'CANCELLED' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">
                Cancelled ({{ $counts['cancelled'] }})
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.bookings') }}" class="w-full md:w-auto flex items-center gap-2">
            @if ($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, phone, ref..."
                class="w-full md:w-64 bg-slate-950 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-amber-500">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium transition">
                Search
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <th class="py-2.5">Reference</th>
                    <th class="py-2.5">Customer &amp; Phone</th>
                    <th class="py-2.5">Category &amp; Qty</th>
                    <th class="py-2.5">Type</th>
                    <th class="py-2.5">Amount</th>
                    <th class="py-2.5">Status</th>
                    <th class="py-2.5">Created At</th>
                    <th class="py-2.5 text-right">Team Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($bookings as $booking)
                    <tr>
                        <!-- Reference -->
                        <td class="py-3 font-mono font-bold text-amber-400 whitespace-nowrap">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="hover:underline">
                                {{ $booking->booking_reference }}
                            </a>
                        </td>

                        <!-- Customer Details & Quick Contact -->
                        <td class="py-3 font-semibold text-slate-200">
                            <div class="font-bold text-slate-100">{{ $booking->contact->full_name }}</div>
                            <div class="text-[11px] text-slate-400 font-normal">{{ $booking->contact->email }}</div>
                            <div class="flex items-center gap-2 mt-1 text-[11px] font-mono">
                                <a href="tel:{{ $booking->contact->phone }}" class="text-amber-400 hover:underline flex items-center gap-1 font-semibold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>{{ $booking->contact->phone }}</span>
                                </a>
                                @if (isset($booking->whatsapp_payment_url))
                                    <span class="text-slate-600">&bull;</span>
                                    <a href="{{ $booking->whatsapp_payment_url }}" target="_blank" class="text-emerald-400 hover:underline flex items-center gap-0.5 text-[10px] font-sans">
                                        <span>WhatsApp Details</span>
                                    </a>
                                @endif
                            </div>
                        </td>

                        <!-- Items -->
                        <td class="py-3 text-slate-300">
                            @foreach ($booking->items as $item)
                                <span>{{ $item->category->name }} &times; {{ $item->quantity }}</span>
                            @endforeach
                        </td>

                        <!-- Attendance Type -->
                        <td class="py-3">
                            @if ($booking->is_donation)
                                <span class="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 text-[10px] font-bold">
                                    DONATION
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-300 border border-blue-500/20 text-[10px] font-bold">
                                    ATTENDING
                                </span>
                            @endif
                        </td>

                        <!-- Amount -->
                        <td class="py-3 font-mono font-bold text-slate-100 whitespace-nowrap">
                            ₹{{ number_format($booking->total_amount_paise / 100, 2) }}
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3">
                            @if ($booking->status === 'CONFIRMED')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">COMPLETED</span>
                            @elseif ($booking->status === 'IN_PROCESS')
                                <span class="px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20 text-[10px] font-bold">IN PROCESS</span>
                            @elseif (in_array($booking->status, ['PAYMENT_PENDING', 'PENDING']))
                                <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold">PENDING CALL</span>
                            @elseif ($booking->status === 'CANCELLED')
                                <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px]">CANCELLED</span>
                            @elseif ($booking->status === 'HOLD_EXPIRED')
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-500 text-[10px]">EXPIRED</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 text-[10px]">{{ $booking->status }}</span>
                            @endif
                        </td>

                        <!-- Created Timestamp -->
                        <td class="py-3 text-slate-500 text-[11px] whitespace-nowrap">{{ $booking->created_at->format('d M H:i') }}</td>

                        <!-- Action Buttons -->
                        <td class="py-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                @if (in_array($booking->status, ['PAYMENT_PENDING', 'PENDING']))
                                    <!-- Mark In Process -->
                                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="IN_PROCESS">
                                        <button type="submit" title="Mark In-Process (Called customer)" class="px-2 py-1 rounded-lg bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 font-bold text-[11px] transition border border-sky-500/30">
                                            Mark In Process
                                        </button>
                                    </form>

                                    <!-- Approve & Generate -->
                                    <form action="{{ route('admin.bookings.confirm-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm payment of ₹{{ number_format($booking->total_amount_paise / 100, 2) }} for {{ $booking->contact->full_name }} and issue tickets?');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-[11px] transition shadow">
                                            Approve &amp; Issue
                                        </button>
                                    </form>

                                    <!-- Cancel -->
                                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel enquiry {{ $booking->booking_reference }} and release seats?');">
                                        @csrf
                                        <input type="hidden" name="status" value="CANCELLED">
                                        <button type="submit" title="Cancel enquiry" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-rose-950/40 text-slate-400 hover:text-rose-300 text-[11px] transition">
                                            Cancel
                                        </button>
                                    </form>
                                @elseif ($booking->status === 'IN_PROCESS')
                                    <!-- Approve & Generate -->
                                    <form action="{{ route('admin.bookings.confirm-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm payment of ₹{{ number_format($booking->total_amount_paise / 100, 2) }} for {{ $booking->contact->full_name }} and issue tickets?');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-[11px] transition shadow">
                                            Approve &amp; Issue
                                        </button>
                                    </form>

                                    <!-- Cancel -->
                                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel enquiry {{ $booking->booking_reference }} and release seats?');">
                                        @csrf
                                        <input type="hidden" name="status" value="CANCELLED">
                                        <button type="submit" title="Cancel enquiry" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-rose-950/40 text-slate-400 hover:text-rose-300 text-[11px] transition">
                                            Cancel
                                        </button>
                                    </form>
                                @elseif ($booking->status === 'CONFIRMED')
                                    @if (isset($booking->whatsapp_ticket_url))
                                        <a href="{{ $booking->whatsapp_ticket_url }}" target="_blank"
                                            class="px-2.5 py-1 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold text-[11px] transition border border-emerald-500/30 flex items-center gap-1">
                                            <span>WhatsApp Pass</span>
                                        </a>
                                    @endif
                                @endif

                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-medium transition">
                                    Details &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">No bookings match the selected filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
