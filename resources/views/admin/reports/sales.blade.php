@extends('layouts.admin')

@section('title', 'Finance Reports & Reconciliation')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100">Finance &amp; Settlement Reports</h1>
            <p class="text-xs text-slate-400 mt-1">Verified gross collections, tier performance, and reconciliation ledger.</p>
        </div>
        <a href="{{ route('admin.reports.export') }}"
            class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span>Export Settlement CSV</span>
        </a>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Gross Verified Revenue</div>
            <div class="text-3xl font-extrabold text-amber-400 font-mono">₹{{ $metrics['gross_collections_rupees'] }}</div>
            <div class="text-[10px] text-slate-500">100% gateway verified receipts</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Paid Admissions Sold</div>
            <div class="text-3xl font-extrabold text-slate-100">{{ $metrics['paid_tickets_issued'] }} <span class="text-xs text-slate-500">tickets</span></div>
            <div class="text-[10px] text-slate-500">Across {{ count($metrics['category_breakdown']) }} tiers</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Pure Supporter Donations</div>
            <div class="text-3xl font-extrabold text-emerald-400 font-mono">₹{{ $metrics['donation_collections_rupees'] }}</div>
            <div class="text-[10px] text-slate-500">{{ $metrics['donation_bookings_count'] }} non-attending donor bookings</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Conversion Rate</div>
            <div class="text-3xl font-extrabold text-sky-400">{{ $metrics['conversion_rate'] }}%</div>
            <div class="text-[10px] text-slate-500">Registrations to paid conversion</div>
        </div>
    </div>

    <!-- Table 1: Category & Tier Revenue Breakdown -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-100">Category &amp; Revenue Breakdown</h2>
                <p class="text-[11px] text-slate-400">Inventory allocation, tickets sold, and revenue per category tier.</p>
            </div>
            <span class="text-[11px] font-mono text-slate-500">Total Capacity: {{ $metrics['total_capacity'] }} seats</span>
        </div>

        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                        <th class="py-3 px-4">Tier Name</th>
                        <th class="py-3 px-4">Unit Price</th>
                        <th class="py-3 px-4 text-center">Allocated Capacity</th>
                        <th class="py-3 px-4 text-center">Paid Tickets Sold</th>
                        <th class="py-3 px-4 text-center">Comps / Sponsors</th>
                        <th class="py-3 px-4 text-center">Available Remaining</th>
                        <th class="py-3 px-4 text-right">Gross Revenue (INR)</th>
                        <th class="py-3 px-4 text-right">Revenue Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @php $totalGrossPaise = $metrics['gross_collections_paise'] ?: 1; @endphp
                    @forelse ($metrics['category_breakdown'] as $cat)
                        @php
                            $sharePct = round(($cat['revenue_paise'] / $totalGrossPaise) * 100, 1);
                        @endphp
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-4 font-sans font-bold text-slate-200">{{ $cat['name'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-300">₹{{ number_format($cat['price_paise'] / 100, 2) }}</td>
                            <td class="py-3.5 px-4 text-center text-slate-400">{{ $cat['capacity'] }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-amber-400">{{ $cat['sold'] }}</td>
                            <td class="py-3.5 px-4 text-center text-purple-400">{{ $cat['comps'] }}</td>
                            <td class="py-3.5 px-4 text-center font-semibold text-emerald-400">{{ $cat['available'] }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-100">₹{{ number_format($cat['revenue_paise'] / 100, 2) }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[10px] font-semibold">{{ $sharePct }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-500 font-sans">No category performance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table 2: Verified Settlement & Payment Transactions Ledger -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-100">Verified Settlement Ledger</h2>
                <p class="text-[11px] text-slate-400">Chronological transaction record of all confirmed captured payments.</p>
            </div>
            <span class="text-[11px] font-mono text-emerald-400 font-semibold">{{ $settlements->total() }} confirmed settlements</span>
        </div>

        <div class="rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                        <th class="py-3 px-4">Booking Ref</th>
                        <th class="py-3 px-4">Customer Contact</th>
                        <th class="py-3 px-4">Category &amp; Qty</th>
                        <th class="py-3 px-4">Amount Paid</th>
                        <th class="py-3 px-4">Gateway Payment ID</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4 text-center">Settlement Status</th>
                        <th class="py-3 px-4 text-right">Verified At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono">
                    @forelse ($settlements as $booking)
                        @php
                            $item = $booking->items->first();
                            $payment = $booking->paymentAttempts()->where('status', 'CAPTURED')->latest('id')->first();
                        @endphp
                        <tr class="hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-4 font-bold text-amber-400">
                                <a href="{{ route('admin.bookings.show', ['id' => $booking->id]) }}" class="hover:underline">
                                    {{ $booking->booking_reference }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-sans text-slate-300">
                                <div class="font-bold text-slate-100">{{ $booking->contact?->full_name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $booking->contact?->email }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-sans">
                                <div class="text-slate-200 font-semibold">{{ $item?->category?->name ?? 'Tickets' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $booking->quantity }} ticket(s)</div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-400 text-sm">
                                ₹{{ number_format($booking->total_amount_paise / 100, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $payment?->provider_payment_id ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 uppercase text-[10px] text-slate-400">
                                {{ $payment?->method ?? 'UPI' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">CAPTURED</span>
                            </td>
                            <td class="py-3.5 px-4 text-right text-slate-400 text-[11px]">
                                {{ $booking->confirmed_at?->format('d M Y, H:i') ?? $booking->updated_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 font-sans">No confirmed payment transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4 border-t border-slate-800/80 font-sans">
                {{ $settlements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
