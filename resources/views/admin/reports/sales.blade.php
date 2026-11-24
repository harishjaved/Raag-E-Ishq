@extends('layouts.admin')

@section('title', 'Finance Reports & Reconciliation')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Audited Financial Settlement</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Finance &amp; Settlement Reports</h1>
            <p class="text-xs text-slate-400 mt-1">Verified gross collections, tier performance, donation breakdown, and reconciliation ledger.</p>
        </div>
        <a href="{{ route('admin.reports.export') }}"
            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span>Export Settlement CSV</span>
        </a>
    </div>

    <!-- Financial KPI Summary Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-2 shadow-xl">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gross Verified Revenue</div>
            <div class="text-3xl font-extrabold text-amber-400 font-mono">₹{{ $metrics['gross_collections_rupees'] }}</div>
            <div class="text-[10px] text-slate-500">100% gateway verified receipts</div>
        </div>

        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-2 shadow-xl">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Paid Admissions Sold</div>
            <div class="text-3xl font-extrabold text-slate-100 font-serif-display">{{ $metrics['paid_tickets_issued'] }} <span class="text-xs font-sans text-slate-400">tickets</span></div>
            <div class="text-[10px] text-slate-500">Across {{ count($metrics['category_breakdown']) }} active admission tiers</div>
        </div>

        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-2 shadow-xl">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Charity Donations</div>
            <div class="text-3xl font-extrabold text-emerald-400 font-mono">₹{{ $metrics['donation_collections_rupees'] }}</div>
            <div class="text-[10px] text-slate-500">{{ $metrics['donation_bookings_count'] }} non-attending patron contributions</div>
        </div>

        <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-2 shadow-xl">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Conversion Rate</div>
            <div class="text-3xl font-extrabold text-sky-400 font-mono">{{ $metrics['conversion_rate'] }}%</div>
            <div class="text-[10px] text-slate-500">Registrations to paid patron conversion</div>
        </div>
    </div>

    <!-- Table 1: Category & Tier Revenue Breakdown -->
    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-2 border-b border-slate-800/60">
            <div>
                <h2 class="text-lg font-bold text-slate-100 font-serif-display">Category &amp; Revenue Breakdown</h2>
                <p class="text-xs text-slate-400">Inventory allocation, tickets sold, and revenue per category tier.</p>
            </div>
            <span class="text-xs font-mono text-slate-400">Capacity: {{ $metrics['total_capacity'] }} seats</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                        <th class="py-3 px-3">Tier Name</th>
                        <th class="py-3 px-3">Unit Price</th>
                        <th class="py-3 px-3">Allocated Seats</th>
                        <th class="py-3 px-3">Sold Tickets</th>
                        <th class="py-3 px-3">Comp Seats</th>
                        <th class="py-3 px-3">Remaining Available</th>
                        <th class="py-3 px-3 text-right">Verified Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($metrics['category_breakdown'] as $cat)
                        <tr class="hover:bg-slate-900/40 transition">
                            <td class="py-3.5 px-3 font-bold text-slate-200">{{ $cat['name'] }}</td>
                            <td class="py-3.5 px-3 font-mono font-semibold text-slate-300">₹{{ number_format($cat['price_paise'] / 100, 2) }}</td>
                            <td class="py-3.5 px-3 font-semibold text-slate-300">{{ $cat['capacity'] }}</td>
                            <td class="py-3.5 px-3 font-bold text-emerald-400 font-mono">{{ $cat['sold'] }}</td>
                            <td class="py-3.5 px-3 text-slate-400 font-mono">{{ $cat['comps'] }}</td>
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
</div>
@endsection
