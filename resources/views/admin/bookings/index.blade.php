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
        <a href="{{ route('admin.bookings', $search ? ['search' => $search] : []) }}"
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
        <a href="{{ route('admin.bookings', array_filter(['status' => 'PENDING', 'search' => $search])) }}"
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
        <a href="{{ route('admin.bookings', array_filter(['status' => 'IN_PROCESS', 'search' => $search])) }}"
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
        <a href="{{ route('admin.bookings', array_filter(['status' => 'CONFIRMED', 'search' => $search])) }}"
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
        <a href="{{ route('admin.bookings', array_filter(['status' => 'CANCELLED', 'search' => $search])) }}"
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

    @if (isset($justApprovedBooking) && $justApprovedBooking && $justApprovedBooking->status === 'CONFIRMED')
        <!-- Just Approved Quick Dispatch Banner -->
        <div id="justApprovedBanner" class="p-5 rounded-3xl bg-gradient-to-r from-emerald-950/90 via-slate-900 to-emerald-950/70 border-2 border-emerald-500/60 shadow-2xl space-y-4 animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center font-bold text-xl flex-shrink-0 shadow-inner">
                        ✓
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/30">
                            <span>Ready For WhatsApp Dispatch</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-100 mt-0.5">
                            {{ $justApprovedBooking->contact->full_name }} &bull; <span class="font-mono text-amber-400">{{ $justApprovedBooking->booking_reference }}</span>
                        </h3>
                        <p class="text-xs text-slate-300">
                            {{ $justApprovedBooking->tickets->count() }} unique QR tickets issued. Send admission pass and entry QR directly to customer.
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button"
                        onclick="openWhatsAppModal({{ $justApprovedBooking->id }})"
                        class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 active:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-2 transition shadow-lg shadow-emerald-500/30">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>Open WhatsApp &amp; Send QR Ticket</span>
                    </button>
                    <button type="button"
                        onclick="quickSendWhatsAppWithQr({{ $justApprovedBooking->id }})"
                        class="px-3.5 py-2.5 rounded-xl bg-purple-600/30 hover:bg-purple-600/40 border border-purple-500/40 text-purple-300 font-bold text-xs flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 002 2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copy QR Image</span>
                    </button>
                    <button type="button"
                        onclick="document.getElementById('justApprovedBanner').remove()"
                        class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 text-xs">
                        &times;
                    </button>
                </div>
            </div>
            <div class="p-3 rounded-2xl bg-slate-950/70 border border-emerald-500/20 text-xs flex flex-wrap items-center justify-between gap-3 text-slate-300">
                <span class="flex items-center gap-2 text-emerald-300 font-medium">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Admission QR code generated and attached. Click to send directly to customer.</span>
                </span>
                <span class="font-mono text-[11px] text-emerald-400 font-bold">{{ $justApprovedBooking->contact->phone }}</span>
            </div>
        </div>
    @endif

    <!-- Search & Active Filters Bar -->
    <div class="p-4 rounded-2xl bg-[#0e1424] border border-slate-800/80 flex flex-col lg:flex-row lg:items-center justify-between gap-4 shadow-md">
        <form method="GET" action="{{ route('admin.bookings') }}" class="w-full lg:w-auto flex-grow flex flex-wrap items-center gap-3">
            <div class="relative flex-grow min-w-[220px] max-w-md">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search customer name, email, phone (+91), or reference..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
            </div>
            <select name="status" onchange="this.form.submit()" aria-label="Filter by workflow status"
                class="px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-amber-400 transition">
                <option value="">All Statuses ({{ $counts['all'] }})</option>
                <option value="PENDING" {{ in_array($currentStatus, ['PENDING', 'PAYMENT_PENDING']) ? 'selected' : '' }}>Pending Call ({{ $counts['pending'] }})</option>
                <option value="IN_PROCESS" {{ $currentStatus === 'IN_PROCESS' ? 'selected' : '' }}>In Process ({{ $counts['in_process'] }})</option>
                <option value="CONFIRMED" {{ $currentStatus === 'CONFIRMED' ? 'selected' : '' }}>Completed ({{ $counts['completed'] }})</option>
                <option value="CANCELLED" {{ $currentStatus === 'CANCELLED' ? 'selected' : '' }}>Cancelled ({{ $counts['cancelled'] }})</option>
            </select>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <span>Filter</span>
            </button>
            @if ($search || $currentStatus)
                <a href="{{ route('admin.bookings') }}" class="text-xs text-slate-400 hover:text-amber-400 underline font-medium">Reset</a>
            @endif
        </form>

        <div class="text-xs text-slate-400 font-mono flex-shrink-0">
            @if ($search || $currentStatus)
                @if ($bookings->total() > $bookings->count())
                    Showing <strong class="text-amber-300">{{ $bookings->firstItem() }}–{{ $bookings->lastItem() }}</strong> of <strong class="text-slate-200">{{ $bookings->total() }}</strong> matching ({{ $counts['all'] }} total)
                @else
                    Showing <strong class="text-amber-300">{{ $bookings->total() }}</strong> matching (of <strong class="text-slate-200">{{ $counts['all'] }}</strong> total enquiries)
                @endif
            @else
                Showing <strong class="text-slate-200">{{ $bookings->count() }}</strong> of <strong class="text-slate-200">{{ $bookings->total() }}</strong> enquiries
            @endif
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
                                    @php
                                        $emailOnlyMatch = $search !== ''
                                            && stripos($booking->contact->email, $search) !== false
                                            && stripos($booking->contact->full_name, $search) === false
                                            && stripos($booking->booking_reference, $search) === false
                                            && stripos($booking->contact->phone, $search) === false;
                                    @endphp
                                    <div class="font-bold text-slate-100 text-sm leading-tight">{{ $booking->contact->full_name }}</div>
                                    <div class="text-[11px] text-slate-400 flex flex-wrap items-center gap-1.5">
                                        <span>{{ $booking->contact->email }}</span>
                                        @if ($emailOnlyMatch)
                                            <span class="px-1.5 py-0.5 rounded bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[10px] font-semibold">matched email</span>
                                        @endif
                                    </div>

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
                                            <button type="button" onclick="openWhatsAppModal({{ $booking->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-950/60 hover:bg-emerald-900/80 border border-emerald-500/40 text-emerald-400 text-[11px] font-bold transition shadow-sm">
                                                <span>WhatsApp Pass</span>
                                            </button>
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
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" onclick="openWhatsAppModal({{ $booking->id }})"
                                            class="px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold text-xs transition border border-emerald-500/30 flex items-center gap-1.5 shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            <span>WhatsApp Pass</span>
                                        </button>
                                        <button type="button"
                                            onclick="quickSendWhatsAppWithQr({{ $booking->id }})"
                                            title="Copy QR Ticket Image to clipboard and open WhatsApp"
                                            class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 hover:text-amber-300 border border-slate-700 transition" aria-label="Copy QR and Open WhatsApp">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 002 2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        </button>
                                    </div>
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

{{-- Embedded Meta & QR SVGs for Confirmed Bookings --}}
@php
    $confirmedList = $bookings->filter(fn($b) => $b->status === 'CONFIRMED')->values();
    if (isset($justApprovedBooking) && $justApprovedBooking && $justApprovedBooking->status === 'CONFIRMED' && !$confirmedList->contains('id', $justApprovedBooking->id)) {
        $confirmedList->push($justApprovedBooking);
    }
@endphp

@foreach ($confirmedList as $cb)
    <div id="booking-meta-{{ $cb->id }}" class="hidden"
        data-id="{{ $cb->id }}"
        data-reference="{{ $cb->booking_reference }}"
        data-customer="{{ $cb->contact->full_name }}"
        data-phone="{{ $cb->contact->phone }}"
        data-amount="₹{{ number_format($cb->total_amount_paise / 100, 2) }}"
        data-whatsapp-url="{{ $cb->whatsapp_ticket_url ?? '' }}"
        data-whatsapp-text="{{ $cb->whatsapp_ticket_text ?? '' }}"
        data-is-donation="{{ $cb->is_donation ? '1' : '0' }}"
        data-tickets-count="{{ $cb->tickets->count() }}">
        @foreach ($cb->tickets as $tIdx => $t)
            <div class="ticket-meta"
                data-ticket-id="{{ $t->id }}"
                data-ticket-number="{{ $t->ticket_number }}"
                data-attendee="{{ $t->attendee_name }}"
                data-category="{{ $t->category->name ?? 'Tier Pass' }}"
                data-qr-uuid="{{ $t->qr_uuid }}"
                data-download-url="{{ $t->download_url ?? '' }}"
                data-qr-download-url="{{ $t->qr_download_url ?? '' }}">
                <div id="svg-holder-{{ $cb->id }}-{{ $tIdx }}">
                    {!! $t->qr_svg ?? '' !!}
                </div>
            </div>
        @endforeach
    </div>
@endforeach

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
                <h3 id="modalCustomerTitle" class="text-lg font-bold text-slate-100 font-serif-display">Send Passes to Customer</h3>
                <p id="modalSubtitle" class="text-xs text-slate-400">Raag-E-Ishq Admission Ticket &amp; Gate QR Pass</p>
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
            <!-- 1. Send WhatsApp Pass to Customer (Direct to designated number) -->
            <button type="button" id="modalOpenWhatsAppBtn"
                class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 active:from-emerald-600 active:to-emerald-700 text-slate-950 font-extrabold text-sm flex items-center justify-center gap-2.5 transition shadow-lg shadow-emerald-500/25">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                <span id="modalOpenWhatsAppText">Send WhatsApp Pass (to Customer)</span>
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

            // 1. Solid White Base
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // 2. Obsidian / Navy Header
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
            ctx.fillText('17 Jan 2027 • 7:00 PM • Chavara Hall, Kochi', 32, 96);

            // 3. Attendee & Details Card
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

            // 4. Center QR Code
            if (!svgEl) {
                return resolve(canvas);
            }
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

                // 5. Footer Details
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

    // Direct WhatsApp Web / App target with designated customer phone number
    const phoneClean = String(booking.phone || '').replace(/[^0-9]/g, '');
    const fullPhone = phoneClean.length === 10 ? '91' + phoneClean : phoneClean;
    const targetUrl = booking.whatsappUrl || `https://api.whatsapp.com/send?phone=${fullPhone}&text=${encodeURIComponent(booking.whatsappText || '')}`;

    // Silently copy QR image canvas to clipboard in background (for quick paste if desired)
    if (ticket && ticket.svg) {
        try {
            const canvas = await renderTicketCanvas(ticket.svg, ticket.attendee, booking.reference, ticket.category, ticket.number);
            await copyCanvasToClipboard(canvas);
        } catch (e) {}
    }

    showToast(`Opening WhatsApp for +${fullPhone}...`);
    window.open(targetUrl, '_blank');
}

async function quickSendWhatsAppWithQr(bookingId) {
    const data = getBookingMetadata(bookingId);
    if (!data) return;
    const ticket = (data.tickets && data.tickets.length > 0) ? data.tickets[0] : null;
    await dispatchWhatsAppPass(data, ticket);
}

function openWhatsAppModal(bookingId) {
    const data = getBookingMetadata(bookingId);
    if (!data) return;

    currentActiveBooking = data;
    currentActiveTicketIdx = 0;

    const modal = document.getElementById('whatsappDispatchModal');
    document.getElementById('modalCustomerTitle').textContent = `${data.customer} (${data.reference})`;
    document.getElementById('modalSubtitle').textContent = `Total: ${data.amount} • Phone: ${data.phone}`;

    const phoneClean = String(data.phone || '').replace(/[^0-9]/g, '');
    const formattedPhone = phoneClean.length === 10 ? '+91 ' + phoneClean : '+' + phoneClean;
    const openBtnText = document.getElementById('modalOpenWhatsAppText');
    if (openBtnText) {
        openBtnText.textContent = `Send WhatsApp Pass (${formattedPhone})`;
    }

    // Setup Ticket Tabs if multiple tickets
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

    // Update tab styling
    const tabsContainer = document.getElementById('modalTicketTabs');
    tabsContainer.querySelectorAll('button').forEach((b, i) => {
        if (i === idx) {
            b.className = 'px-3 py-1 rounded-xl text-xs font-bold transition bg-amber-500 text-slate-950 shadow';
        } else {
            b.className = 'px-3 py-1 rounded-xl text-xs font-bold transition bg-slate-800 text-slate-300 hover:bg-slate-700';
        }
    });

    // Populate preview card
    document.getElementById('modalAttendeeName').textContent = ticket.attendee || currentActiveBooking.customer;
    document.getElementById('modalTicketNumber').textContent = ticket.number || currentActiveBooking.reference;
    document.getElementById('modalCategoryBadge').textContent = ticket.category || 'Admission';

    const qrDisplay = document.getElementById('modalQrDisplay');
    qrDisplay.innerHTML = '';
    if (ticket.svg) {
        qrDisplay.appendChild(ticket.svg.cloneNode(true));
    } else {
        qrDisplay.innerHTML = '<div class="text-xs text-slate-400">QR Ready for verification</div>';
    }

    // Attach actions
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

// Auto-trigger if a booking was just approved and issued
document.addEventListener('DOMContentLoaded', () => {
    @if (session('just_approved_booking_id'))
        try {
            setTimeout(() => {
                openWhatsAppModal({{ session('just_approved_booking_id') }});
            }, 300);
        } catch (e) {}
    @endif
});
</script>
@endsection
