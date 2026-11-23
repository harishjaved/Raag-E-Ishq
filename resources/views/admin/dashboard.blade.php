@extends('layouts.admin')

@section('title', 'Operations Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100">Operations Overview</h1>
            <p class="text-xs text-slate-400 mt-1">Sagar Kinare — Live Event Operations &amp; Inventory Management</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-xs text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Active 450 Shared Pool</span>
            </span>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Gross Verified Collections -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2 shadow-xl">
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Gross Collections</div>
                @if (($metrics['donation_collections_paise'] ?? 0) > 0)
                    <span class="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 text-[9px] font-bold">
                        Inc. Donations
                    </span>
                @endif
            </div>
            <div class="text-2xl font-extrabold text-amber-400 font-mono">₹{{ $metrics['gross_collections_rupees'] }}</div>
            <div class="text-[10px] text-slate-400 space-y-0.5">
                <div>Tickets: <span class="text-slate-200 font-mono">₹{{ $metrics['ticket_collections_rupees'] }}</span></div>
                @if (($metrics['donation_collections_paise'] ?? 0) > 0)
                    <div>Charity Donations: <span class="text-purple-300 font-mono font-bold">₹{{ $metrics['donation_collections_rupees'] }}</span> ({{ $metrics['donation_bookings_count'] }})</div>
                @else
                    <div class="text-slate-500">Verified captured payments only</div>
                @endif
            </div>
        </div>

        <!-- Paid Admissions Issued -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2 shadow-xl">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Paid Admissions Issued</div>
            <div class="text-2xl font-extrabold text-slate-100">{{ $metrics['paid_tickets_issued'] }} <span class="text-xs font-normal text-slate-500">tickets</span></div>
            <div class="text-[10px] text-slate-500">From {{ $metrics['paid_bookings'] }} confirmed orders</div>
        </div>

        <!-- Capacity Remaining -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2 shadow-xl">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Capacity Remaining</div>
            <div class="text-2xl font-extrabold text-emerald-400">{{ $metrics['global_remaining'] }} <span class="text-xs font-normal text-slate-500">/ 450</span></div>
            <div class="text-[10px] text-slate-500">{{ $metrics['total_allocated'] }} total seats allocated/held</div>
        </div>

        <!-- Venue Attendance -->
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-2 shadow-xl">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Actual Attendance</div>
            <div class="text-2xl font-extrabold text-slate-100">{{ $metrics['total_checked_in'] }} <span class="text-xs font-normal text-slate-500">scanned</span></div>
            <div class="text-[10px] text-slate-500">Show-up rate: <strong class="text-amber-400">{{ $metrics['showup_rate'] }}%</strong></div>
        </div>
    </div>

    <!-- Category Breakdown Table -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
        <h2 class="text-base font-bold text-slate-100">Category Quotas &amp; Sales (450 Total)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-2.5">Category</th>
                        <th class="py-2.5">Unit Price</th>
                        <th class="py-2.5">Capacity</th>
                        <th class="py-2.5">Paid Sold</th>
                        <th class="py-2.5">Comps / Holds</th>
                        <th class="py-2.5">Available</th>
                        <th class="py-2.5 text-right">Verified Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($metrics['category_breakdown'] as $cat)
                        <tr>
                            <td class="py-3 font-bold text-slate-200">{{ $cat['name'] }}</td>
                            <td class="py-3 text-slate-400">₹{{ number_format($cat['price_paise'] / 100, 2) }}</td>
                            <td class="py-3 font-semibold">{{ $cat['capacity'] }}</td>
                            <td class="py-3 font-semibold text-emerald-400">{{ $cat['sold'] }}</td>
                            <td class="py-3 text-slate-400">{{ $cat['comps'] }}</td>
                            <td class="py-3 font-bold {{ $cat['available'] > 0 ? 'text-amber-400' : 'text-rose-400' }}">
                                {{ $cat['available'] }}
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-100">
                                ₹{{ number_format($cat['revenue_paise'] / 100, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Campaign Attribution Table -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
        <h2 class="text-base font-bold text-slate-100">Campaign Attribution (UTM Source)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <th class="py-2.5">Acquisition Source</th>
                        <th class="py-2.5">Contacts</th>
                        <th class="py-2.5">Bookings</th>
                        <th class="py-2.5 text-right">Verified Collections</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($metrics['campaign_attribution'] as $camp)
                        <tr>
                            <td class="py-2.5 font-semibold text-slate-200">{{ $camp->source }}</td>
                            <td class="py-2.5 text-slate-400">{{ $camp->contacts_count }}</td>
                            <td class="py-2.5 text-slate-400">{{ $camp->bookings_count }}</td>
                            <td class="py-2.5 text-right font-mono font-bold text-amber-400">
                                ₹{{ number_format($camp->verified_revenue_paise / 100, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
