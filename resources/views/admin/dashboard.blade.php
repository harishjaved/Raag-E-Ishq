@extends('layouts.admin')

@section('title', 'Operations Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Executive Command Center</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Operations Overview</h1>
            <p class="text-xs text-slate-400 mt-1">Raag-E-Ishq — Live Event Operations &amp; Inventory Management</p>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-2xl bg-slate-900 border border-slate-800 text-xs text-slate-300 shadow">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-bold">Active 450 Shared Pool</span>
            </span>
        </div>
    </div>

    <!-- KPI Cards Grid (4 Elevated Metric Containers) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 1. Gross Collections -->
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-3 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-xl group-hover:bg-amber-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Gross Collections</div>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-amber-400 font-mono tracking-tight">₹{{ $metrics['gross_collections_rupees'] }}</div>
            </div>
            <div class="text-[11px] text-slate-400 space-y-1 pt-1 border-t border-slate-800/60">
                <div class="flex justify-between items-center">
                    <span>Ticket Admissions:</span>
                    <span class="text-slate-200 font-mono font-bold">₹{{ $metrics['ticket_collections_rupees'] }}</span>
                </div>
                @if (($metrics['donation_collections_paise'] ?? 0) > 0)
                    <div class="flex justify-between items-center text-purple-300">
                        <span>Charity Donations ({{ $metrics['donation_bookings_count'] }}):</span>
                        <span class="font-mono font-bold">₹{{ $metrics['donation_collections_rupees'] }}</span>
                    </div>
                @else
                    <div class="text-[10px] text-slate-500">100% verified gateway receipts</div>
                @endif
            </div>
        </div>

        <!-- 2. Paid Admissions Issued -->
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-3 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-sky-500/5 rounded-full blur-xl group-hover:bg-sky-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Paid Admissions</div>
                <div class="w-8 h-8 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-100 font-serif-display">{{ $metrics['paid_tickets_issued'] }} <span class="text-xs font-sans font-medium text-slate-400">tickets</span></div>
            </div>
            <div class="text-[11px] text-slate-400 space-y-1 pt-1 border-t border-slate-800/60">
                <div class="flex justify-between items-center">
                    <span>Confirmed Orders:</span>
                    <span class="text-slate-200 font-semibold">{{ $metrics['paid_bookings'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Order Conversion:</span>
                    <span class="text-sky-400 font-bold font-mono">{{ $metrics['conversion_rate'] }}%</span>
                </div>
            </div>
        </div>

        <!-- 3. Capacity Remaining -->
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-3 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Auditorium Capacity</div>
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-emerald-400 font-mono tracking-tight">{{ $metrics['global_remaining'] }} <span class="text-xs font-sans text-slate-400">/ 450</span></div>
            </div>
            <div class="space-y-1.5 pt-1 border-t border-slate-800/60">
                @php
                    $percentAllocated = $metrics['total_capacity'] > 0 ? round(($metrics['total_allocated'] / $metrics['total_capacity']) * 100, 1) : 0;
                @endphp
                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden flex">
                    <div class="bg-gradient-to-r from-amber-500 to-rose-500 h-full rounded-full transition-all duration-500" style="width: {{ $percentAllocated }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] text-slate-400">
                    <span>Allocated / Held:</span>
                    <span class="text-slate-200 font-semibold">{{ $metrics['total_allocated'] }} seats ({{ $percentAllocated }}%)</span>
                </div>
            </div>
        </div>

        <!-- 4. Actual Gate Attendance -->
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-3 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-500/5 rounded-full blur-xl group-hover:bg-rose-500/10 transition"></div>
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Gate Check-in</div>
                <div class="w-8 h-8 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-slate-100 font-serif-display">{{ $metrics['total_checked_in'] }} <span class="text-xs font-sans font-medium text-slate-400">scanned</span></div>
            </div>
            <div class="text-[11px] text-slate-400 space-y-1 pt-1 border-t border-slate-800/60">
                <div class="flex justify-between items-center">
                    <span>Show-Up Rate:</span>
                    <span class="text-amber-400 font-bold font-mono">{{ $metrics['showup_rate'] }}%</span>
                </div>
                <div class="flex justify-between items-center text-slate-500">
                    <span>Gate Scanner:</span>
                    <a href="{{ route('staff.scan') }}" target="_blank" class="text-emerald-400 hover:underline font-semibold">Active &bull; Open &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Operations Dispatch Tiles (3 Actionable Lanes) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Lane 1: Enquiries Desk -->
        <a href="{{ route('admin.bookings') }}" class="p-5 rounded-2xl bg-[#0d121f] border border-slate-800 hover:border-amber-500/50 transition group shadow-md flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[10px] uppercase font-bold tracking-widest text-amber-400">Customer Follow-up</div>
                <div class="text-sm font-bold text-slate-100 group-hover:text-amber-300 transition">Enquiries &amp; Bookings Desk</div>
                <div class="text-xs text-slate-400">Call registrants &amp; share WhatsApp payment QR</div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-300 group-hover:bg-amber-500 group-hover:text-slate-950 flex items-center justify-center transition flex-shrink-0">
                &rarr;
            </div>
        </a>

        <!-- Lane 2: Ticket Dispatch Desk -->
        <a href="{{ route('admin.dispatch') }}" class="p-5 rounded-2xl bg-[#0d121f] border border-slate-800 hover:border-sky-500/50 transition group shadow-md flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[10px] uppercase font-bold tracking-widest text-sky-400">Pass Generation</div>
                <div class="text-sm font-bold text-slate-100 group-hover:text-sky-300 transition">Ticket Dispatch Desk</div>
                <div class="text-xs text-slate-400">Generate HMAC entry passes &amp; WhatsApp dispatch</div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-300 group-hover:bg-sky-500 group-hover:text-slate-950 flex items-center justify-center transition flex-shrink-0">
                &rarr;
            </div>
        </a>

        <!-- Lane 3: Financial Settlement CSV -->
        <a href="{{ route('admin.reports.export') }}" class="p-5 rounded-2xl bg-[#0d121f] border border-slate-800 hover:border-emerald-500/50 transition group shadow-md flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[10px] uppercase font-bold tracking-widest text-emerald-400">Financial Ledger</div>
                <div class="text-sm font-bold text-slate-100 group-hover:text-emerald-300 transition">Export Settlement CSV</div>
                <div class="text-xs text-slate-400">Download sanitized patron transaction ledger</div>
            </div>
            <div class="w-9 h-9 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-300 group-hover:bg-emerald-500 group-hover:text-slate-950 flex items-center justify-center transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            </div>
        </a>
    </div>

    <!-- Category Quotas & Inventory Breakdown Table -->
    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-800/60">
            <div>
                <h2 class="text-lg font-bold text-slate-100 font-serif-display">Category Quotas &amp; Sales (450 Total)</h2>
                <p class="text-xs text-slate-400">Real-time breakdown of allocation limits, sold admissions, holds, and collected revenues.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">Shared Pool: 450 Seats</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                        <th class="py-3 px-3">Category Tier</th>
                        <th class="py-3 px-3">Unit Price</th>
                        <th class="py-3 px-3">Capacity</th>
                        <th class="py-3 px-3">Occupancy Progress</th>
                        <th class="py-3 px-3">Paid Sold</th>
                        <th class="py-3 px-3">Comps / Holds</th>
                        <th class="py-3 px-3">Available</th>
                        <th class="py-3 px-3 text-right">Verified Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($metrics['category_breakdown'] as $cat)
                        @php
                            $isTier1 = str_contains(strtolower($cat['name']), 'tier 1');
                            $isTier2 = str_contains(strtolower($cat['name']), 'tier 2');
                            $isTier3 = str_contains(strtolower($cat['name']), 'tier 3');
                            $soldPercent = $cat['capacity'] > 0 ? round(($cat['sold'] / $cat['capacity']) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition">
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    @if ($isTier1)
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400/50"></span>
                                        <span class="font-bold text-slate-100">{{ $cat['name'] }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold">VIP</span>
                                    @elseif ($isTier2)
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500/50"></span>
                                        <span class="font-bold text-slate-100">{{ $cat['name'] }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold">PRIME</span>
                                    @else
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shadow-sm shadow-purple-500/50"></span>
                                        <span class="font-bold text-slate-100">{{ $cat['name'] }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold">CLASSIC</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-slate-300 font-mono font-semibold">
                                ₹{{ number_format($cat['price_paise'] / 100, 2) }}
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-300">
                                {{ $cat['capacity'] }} seats
                            </td>
                            <td class="py-3.5 px-3 min-w-[140px]">
                                <div class="space-y-1">
                                    <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300 {{ $isTier1 ? 'bg-amber-400' : ($isTier2 ? 'bg-rose-500' : 'bg-purple-500') }}" style="width: {{ $soldPercent }}%"></div>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $cat['sold'] }}/{{ $cat['capacity'] }} ({{ $soldPercent }}%)</div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 font-bold text-emerald-400 font-mono">
                                {{ $cat['sold'] }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-400 font-mono">
                                {{ $cat['comps'] }}
                            </td>
                            <td class="py-3.5 px-3 font-bold font-mono {{ $cat['available'] > 0 ? 'text-amber-400' : 'text-rose-400' }}">
                                {{ $cat['available'] }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono font-extrabold text-slate-100 text-sm">
                                ₹{{ number_format($cat['revenue_paise'] / 100, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Campaign & Attribution Intelligence -->
    @if (!empty($metrics['campaign_attribution']) && count($metrics['campaign_attribution']) > 0)
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-4 shadow-xl">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800/60">
                <div>
                    <h2 class="text-lg font-bold text-slate-100 font-serif-display">Campaign Attribution &amp; Traffic Channels</h2>
                    <p class="text-xs text-slate-400">Marketing channel conversion rates and verified revenue by UTM campaign source.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                            <th class="py-2.5 px-3">Channel / Source</th>
                            <th class="py-2.5 px-3">Unique Registrants</th>
                            <th class="py-2.5 px-3">Bookings Placed</th>
                            <th class="py-2.5 px-3 text-right">Verified Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach ($metrics['campaign_attribution'] as $camp)
                            <tr class="hover:bg-slate-900/40 transition">
                                <td class="py-3 px-3 font-mono font-semibold text-amber-400">
                                    {{ $camp->source }}
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-200">
                                    {{ $camp->contacts_count }}
                                </td>
                                <td class="py-3 px-3 text-slate-300">
                                    {{ $camp->bookings_count }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-400">
                                    ₹{{ number_format($camp->verified_revenue_paise / 100, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
