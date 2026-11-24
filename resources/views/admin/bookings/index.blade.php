@extends('layouts.admin')

@section('title', 'Enquiries & Bookings Desk')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Concierge Offline Payment Desk</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Enquiries &amp; Bookings Desk</h1>
            <p class="text-xs text-slate-400 mt-1">
                Call customer to verify details &rarr; Share payment details via WhatsApp &rarr; Approve payment &amp; dispatch cryptographic passes.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.dispatch') }}" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Ticket Dispatch Desk &rarr;</span>
            </a>
        </div>
    </div>

    <!-- 5-Segment Interactive Workflow Pipeline Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <!-- Tab 1: All -->
        <a href="{{ route('admin.bookings') }}"
            class="p-4 rounded-2xl border transition-all flex flex-col justify-between group shadow-sm {{ !$currentStatus ? 'bg-gradient-to-b from-slate-800 to-slate-900 border-amber-500/50 text-white shadow-amber-500/10' : 'bg-[#0d121f] border-slate-800/80 hover:border-slate-700 text-slate-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider">All Records</span>
                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
            </div>
            <div class="text-2xl font-extrabold font-mono mt-2 {{ !$currentStatus ? 'text-amber-400' : 'text-slate-200' }}">
                {{ $counts['all'] }}
            </div>
            <div class="text-[10px] text-slate-500 mt-1">Total registrations</div>
        </a>

        <!-- Tab 2: Pending Call -->
        <a href="{{ route('admin.bookings', ['status' => 'PENDING']) }}"
            class="p-4 rounded-2xl border transition-all flex flex-col justify-between group shadow-sm {{ in_array($currentStatus, ['PENDING', 'PAYMENT_PENDING']) ? 'bg-gradient-to-b from-amber-950/40 to-slate-900 border-amber-500 text-amber-300 shadow-amber-500/20' : 'bg-[#0d121f] border-slate-800/80 hover:border-amber-500/40 text-slate-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Pending Call</span>
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            </div>
            <div class="text-2xl font-extrabold font-mono mt-2 text-amber-400">
                {{ $counts['pending'] }}
            </div>
            <div class="text-[10px] text-amber-400/80 mt-1">Requires phone outreach</div>
        </a>

        <!-- Tab 3: In Process -->
        <a href="{{ route('admin.bookings', ['status' => 'IN_PROCESS']) }}"
            class="p-4 rounded-2xl border transition-all flex flex-col justify-between group shadow-sm {{ $currentStatus === 'IN_PROCESS' ? 'bg-gradient-to-b from-sky-950/40 to-slate-900 border-sky-500 text-sky-300 shadow-sky-500/20' : 'bg-[#0d121f] border-slate-800/80 hover:border-sky-500/40 text-slate-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-400">In Process</span>
                <span class="w-2 h-2 rounded-full bg-sky-400"></span>
            </div>
            <div class="text-2xl font-extrabold font-mono mt-2 text-sky-400">
                {{ $counts['in_process'] }}
            </div>
            <div class="text-[10px] text-sky-400/80 mt-1">QR / bank details sent</div>
        </a>

        <!-- Tab 4: Completed -->
        <a href="{{ route('admin.bookings', ['status' => 'CONFIRMED']) }}"
            class="p-4 rounded-2xl border transition-all flex flex-col justify-between group shadow-sm {{ $currentStatus === 'CONFIRMED' ? 'bg-gradient-to-b from-emerald-950/40 to-slate-900 border-emerald-500 text-emerald-300 shadow-emerald-500/20' : 'bg-[#0d121f] border-slate-800/80 hover:border-emerald-500/40 text-slate-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Completed</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-2xl font-extrabold font-mono mt-2 text-emerald-400">
                {{ $counts['completed'] }}
            </div>
            <div class="text-[10px] text-emerald-400/80 mt-1">Paid &amp; passes issued</div>
        </a>

        <!-- Tab 5: Cancelled -->
        <a href="{{ route('admin.bookings', ['status' => 'CANCELLED']) }}"
            class="p-4 rounded-2xl border transition-all flex flex-col justify-between group shadow-sm {{ $currentStatus === 'CANCELLED' ? 'bg-gradient-to-b from-rose-950/30 to-slate-900 border-rose-500 text-rose-300 shadow-rose-500/20' : 'bg-[#0d121f] border-slate-800/80 hover:border-rose-500/40 text-slate-400' }}">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-400">Cancelled</span>
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
            </div>
            <div class="text-2xl font-extrabold font-mono mt-2 text-rose-400">
                {{ $counts['cancelled'] }}
            </div>
            <div class="text-[10px] text-slate-500 mt-1">Released / expired</div>
        </a>
    </div>

    <!-- Search & Active Filters Bar -->
    <div class="p-4 rounded-2xl bg-[#0e1424] border border-slate-800/80 flex flex-col md:flex-row items-center justify-between gap-4 shadow-md">
        <form method="GET" action="{{ route('admin.bookings') }}" class="w-full md:w-auto flex-grow flex items-center gap-3">
            @if ($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif
            <div class="relative flex-grow max-w-md">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search customer name, email, phone (+91), or reference..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                <span>Filter</span>
            </button>
            @if ($search || $currentStatus)
                <a href="{{ route('admin.bookings') }}" class="text-xs text-slate-400 hover:text-amber-400 underline font-medium">Reset</a>
            @endif
        </form>

        <div class="text-xs text-slate-400 font-mono flex-shrink-0">
            Showing <strong class="text-slate-200">{{ $bookings->count() }}</strong> of <strong class="text-slate-200">{{ $bookings->total() }}</strong> enquiries
        </div>
    </div>

    <!-- Main Enquiries Table -->
    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                    <th class="py-3 px-3">Reference &amp; Date</th>
                    <th class="py-3 px-3">Customer &amp; Direct Contact</th>
                    <th class="py-3 px-3">Category &amp; Admissions</th>
                    <th class="py-3 px-3">Type</th>
                    <th class="py-3 px-3">Amount</th>
                    <th class="py-3 px-3">Workflow Status</th>
                    <th class="py-3 px-3 text-right">Team Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($bookings as $booking)
                    @php
                        $isPending = in_array($booking->status, ['PAYMENT_PENDING', 'PENDING']);
                        $isInProcess = $booking->status === 'IN_PROCESS';
                        $isConfirmed = $booking->status === 'CONFIRMED';
                        $isExpired = in_array($booking->status, ['CANCELLED', 'HOLD_EXPIRED']);
                    @endphp
                    <tr class="hover:bg-slate-900/40 transition">
                        <!-- 1. Reference & Date -->
                        <td class="py-4 px-3 whitespace-nowrap">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="font-mono font-bold text-amber-400 hover:underline block text-sm">
                                {{ $booking->booking_reference }}
                            </a>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                {{ $booking->created_at->format('d M, h:i A') }}
                            </div>
                        </td>

                        <!-- 2. Customer & Direct Contact (Spacious & Clean) -->
                        <td class="py-4 px-3">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">
                                    {{ strtoupper(substr($booking->contact->full_name, 0, 2)) }}
                                </div>
                                <div class="space-y-1">
                                    <div class="font-bold text-slate-100 text-sm leading-tight">{{ $booking->contact->full_name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $booking->contact->email }}</div>

                                    <!-- Action Pills for Call & WhatsApp -->
                                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                        <!-- Direct Phone Call -->
                                        <a href="tel:{{ $booking->contact->phone }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-amber-400 font-mono text-[11px] font-bold transition">
                                            <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ $booking->contact->phone }}</span>
                                        </a>

                                        <!-- WhatsApp Details / Payment Message -->
                                        @if ($isPending || $isInProcess)
                                            @if (isset($booking->whatsapp_payment_url))
                                                <a href="{{ $booking->whatsapp_payment_url }}" target="_blank"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-950/60 hover:bg-emerald-900/80 border border-emerald-500/40 text-emerald-400 text-[11px] font-bold transition shadow-sm">
                                                    <span>WhatsApp Details</span>
                                                </a>
                                            @endif
                                        @elseif ($isConfirmed)
                                            @if (isset($booking->whatsapp_ticket_url))
                                                <a href="{{ $booking->whatsapp_ticket_url }}" target="_blank"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-950/60 hover:bg-emerald-900/80 border border-emerald-500/40 text-emerald-400 text-[11px] font-bold transition shadow-sm">
                                                    <span>WhatsApp Pass</span>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- 3. Category & Admissions -->
                        <td class="py-4 px-3 whitespace-nowrap">
                            @foreach ($booking->items as $item)
                                @php
                                    $isTier1 = str_contains(strtolower($item->category->name), 'tier 1');
                                    $isTier2 = str_contains(strtolower($item->category->name), 'tier 2');
                                @endphp
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[11px] font-bold {{ $isTier1 ? 'bg-amber-500/10 border-amber-500/30 text-amber-400' : ($isTier2 ? 'bg-rose-500/10 border-rose-500/30 text-rose-400' : 'bg-purple-500/10 border-purple-500/30 text-purple-400') }}">
                                    <span>{{ $item->category->name }}</span>
                                    <span class="text-slate-500">&bull;</span>
                                    <span>{{ $item->quantity }} {{ $item->quantity === 1 ? 'Seat' : 'Seats' }}</span>
                                </div>
                            @endforeach
                        </td>

                        <!-- 4. Attendance Type -->
                        <td class="py-4 px-3 whitespace-nowrap">
                            @if ($booking->is_donation)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/30 text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                                    <span>CHARITY ONLY</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-300 border border-sky-500/30 text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                    <span>ATTENDING</span>
                                </span>
                            @endif
                        </td>

                        <!-- 5. Amount -->
                        <td class="py-4 px-3 font-mono font-extrabold text-slate-100 text-sm whitespace-nowrap">
                            ₹{{ number_format($booking->total_amount_paise / 100, 2) }}
                        </td>

                        <!-- 6. Workflow Status -->
                        <td class="py-4 px-3 whitespace-nowrap">
                            @if ($isConfirmed)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>COMPLETED</span>
                                </span>
                            @elseif ($isInProcess)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/15 text-sky-400 border border-sky-500/30 text-[11px] font-bold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                    <span>IN PROCESS</span>
                                </span>
                            @elseif ($isPending)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/30 text-[11px] font-bold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    <span>PENDING CALL</span>
                                </span>
                            @elseif ($booking->status === 'CANCELLED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-semibold">
                                    <span>CANCELLED</span>
                                </span>
                            @elseif ($booking->status === 'HOLD_EXPIRED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-800 text-slate-500 text-[10px] font-semibold">
                                    <span>EXPIRED</span>
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 text-[10px]">{{ $booking->status }}</span>
                            @endif
                        </td>

                        <!-- 7. Team Actions -->
                        <td class="py-4 px-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                @if ($isPending)
                                    <!-- Mark In Process -->
                                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="IN_PROCESS">
                                        <button type="submit" title="Mark In-Process (Called customer)" class="px-2.5 py-1.5 rounded-xl bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 font-bold text-xs transition border border-sky-500/30">
                                            Mark In Process
                                        </button>
                                    </form>

                                    <!-- Approve & Issue Tickets -->
                                    <form action="{{ route('admin.bookings.confirm-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm payment of ₹{{ number_format($booking->total_amount_paise / 100, 2) }} for {{ $booking->contact->full_name }} and issue tickets?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-md shadow-emerald-500/20">
                                            Approve &amp; Issue
                                        </button>
                                    </form>

                                    <!-- Cancel -->
                                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel enquiry {{ $booking->booking_reference }} and release seats?');">
                                        @csrf
                                        <input type="hidden" name="status" value="CANCELLED">
                                        <button type="submit" title="Cancel enquiry" class="px-2 py-1.5 rounded-xl bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-300 text-xs transition">
                                            Cancel
                                        </button>
                                    </form>
                                @elseif ($isInProcess)
                                    <!-- Approve & Issue Tickets -->
                                    <form action="{{ route('admin.bookings.confirm-payment', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Confirm payment of ₹{{ number_format($booking->total_amount_paise / 100, 2) }} for {{ $booking->contact->full_name }} and issue tickets?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-md shadow-emerald-500/20">
                                            Approve &amp; Issue
                                        </button>
                                    </form>

                                    <!-- Cancel -->
                                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel enquiry {{ $booking->booking_reference }} and release seats?');">
                                        @csrf
                                        <input type="hidden" name="status" value="CANCELLED">
                                        <button type="submit" title="Cancel enquiry" class="px-2 py-1.5 rounded-xl bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-300 text-xs transition">
                                            Cancel
                                        </button>
                                    </form>
                                @elseif ($isConfirmed)
                                    @if (isset($booking->whatsapp_ticket_url))
                                        <a href="{{ $booking->whatsapp_ticket_url }}" target="_blank"
                                            class="px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold text-xs transition border border-emerald-500/30 flex items-center gap-1">
                                            <span>WhatsApp Pass</span>
                                        </a>
                                    @endif
                                @endif

                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                                    Details &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            <div class="space-y-2">
                                <svg class="w-8 h-8 mx-auto text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <div class="text-sm font-semibold text-slate-400">No enquiries found in this view</div>
                                <div class="text-xs text-slate-500">Try changing the status tab or clearing your search query.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-6 pt-4 border-t border-slate-800/60">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
