@extends('layouts.admin')

@section('title', 'Finance Reports & Reconciliation')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Audited Financial Settlement</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Finance &amp; Reconciliation Reports</h1>
            <p class="text-xs text-slate-400 mt-1">Verified ticket collections vs ₹{{ $metrics['target_full_capacity_rupees'] }} capacity target, UTR references &amp; standalone donations.</p>
        </div>
        <a href="{{ route('admin.reports.export') }}"
            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span>Export Settlement &amp; UTR CSV</span>
        </a>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-2">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Confirmed Ticket Revenue</div>
            <div class="text-3xl font-extrabold text-amber-400 font-mono">₹{{ $metrics['ticket_collections_rupees'] }}</div>
            <div class="text-[10px] text-slate-400">Target (450 seats): <span class="font-mono text-slate-200">₹{{ $metrics['target_full_capacity_rupees'] }}</span></div>
        </div>

        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-2">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Paid Admissions &amp; Offline</div>
            <div class="text-3xl font-extrabold text-slate-100 font-serif-display">{{ $metrics['paid_tickets_issued'] }} <span class="text-xs font-sans text-slate-400">tickets</span></div>
            <div class="text-[10px] text-slate-400">{{ $metrics['offline_bookings_count'] }} offline entries (₹{{ $metrics['offline_collections_rupees'] }})</div>
        </div>

        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-2">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cause Donations (No Seat)</div>
            <div class="text-3xl font-extrabold text-emerald-400 font-mono">₹{{ $metrics['combined_donations_rupees'] }}</div>
            <div class="text-[10px] text-slate-400">{{ $metrics['standalone_acknowledged_count'] }} standalone ack + {{ $metrics['donation_bookings_count'] }} supporter</div>
        </div>

        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-2">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Holds &amp; Expired</div>
            <div class="text-3xl font-extrabold text-sky-400 font-mono">{{ $metrics['conversion_rate'] }}%</div>
            <div class="text-[10px] text-slate-400">{{ $metrics['expired_bookings'] }} expired ({{ $metrics['expired_seats_released'] }} released)</div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-800/60">
            <div>
                <h2 class="text-lg font-bold text-slate-100 font-serif-display">Category &amp; Revenue Breakdown</h2>
                <p class="text-xs text-slate-400">Gold (₹2,500), Silver (₹1,500), and Bronze (₹1,000) collections vs capacity.</p>
            </div>
            <span class="text-xs font-mono text-slate-400">Capacity: {{ $metrics['total_capacity'] }} seats</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                        <th class="py-3 px-3">Category</th>
                        <th class="py-3 px-3">Unit Price</th>
                        <th class="py-3 px-3">Capacity</th>
                        <th class="py-3 px-3">Paid Sold</th>
                        <th class="py-3 px-3">Comps / Held</th>
                        <th class="py-3 px-3">Available</th>
                        <th class="py-3 px-3 text-right">Verified Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($metrics['category_breakdown'] as $cat)
                        <tr class="hover:bg-slate-900/40 transition">
                            <td class="py-3.5 px-3 font-bold text-slate-200">{{ $cat['name'] }}</td>
                            <td class="py-3.5 px-3 font-mono font-semibold text-slate-300">₹{{ number_format($cat['price_paise'] / 100, 0) }}</td>
                            <td class="py-3.5 px-3 font-semibold text-slate-300">{{ $cat['capacity'] }}</td>
                            <td class="py-3.5 px-3 font-bold text-emerald-400 font-mono">{{ $cat['sold'] }}</td>
                            <td class="py-3.5 px-3 text-slate-400 font-mono">{{ $cat['comps'] }}</td>
                            <td class="py-3.5 px-3 font-bold font-mono {{ $cat['available'] > 0 ? 'text-amber-400' : 'text-rose-400' }}">
                                {{ $cat['available'] }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono font-extrabold text-slate-100 text-sm">
                                ₹{{ number_format($cat['revenue_paise'] / 100, 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Verified Settlements & UTR References -->
    <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-4">
        <h2 class="text-lg font-bold text-slate-100 font-serif-display">Confirmed Settlement Ledger</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                        <th class="py-2.5 px-3">Booking Ref</th>
                        <th class="py-2.5 px-3">Category / Qty</th>
                        <th class="py-2.5 px-3">Channel</th>
                        <th class="py-2.5 px-3">UTR / Payment Ref</th>
                        <th class="py-2.5 px-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($settlements as $row)
                        @php $item = $row->items->first(); $payment = $row->paymentAttempts->where('status', 'CAPTURED')->first(); @endphp
                        <tr>
                            <td class="py-3 px-3 font-mono font-bold text-amber-400">
                                <a href="{{ route('admin.bookings.show', $row->id) }}" class="hover:underline">{{ $row->booking_reference }}</a>
                            </td>
                            <td class="py-3 px-3 text-slate-300">{{ $item?->category?->name ?? 'General' }} &times; {{ $item?->quantity ?? 1 }}</td>
                            <td class="py-3 px-3 text-slate-400 font-mono text-[11px]">{{ $row->payment_channel ?? ($row->is_offline_booking ? 'OFFLINE' : 'ONLINE') }}</td>
                            <td class="py-3 px-3 font-mono text-sky-300">{{ $row->utr_reference ?: ($payment?->provider_payment_id ?? 'N/A') }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-400">{{ $row->formatted_total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">No confirmed settlements yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $settlements->links() }}</div>
    </div>
</div>
@endsection
