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
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Operations &amp; Financial Overview</h1>
            <p class="text-xs text-slate-400 mt-1">Raag-E-Ishq — Live Seat Inventory, 30-Min Holds, Cause Donations &amp; Gate Check-Ins</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-bold">Shared Pool: {{ $metrics['total_capacity'] }} Seats</span>
            </span>
            <a href="{{ route('admin.security') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-amber-500/30 text-amber-300 text-xs font-semibold transition">
                {{ auth()->user()->hasTwoFactorEnabled() ? '2FA Active ✓' : 'Enable 2FA →' }}
            </a>
        </div>
    </div>

    <!-- Primary 4 KPI Buckets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 1. Verified Ticket Collections vs ₹6,50,000 Full-Capacity Target -->
        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Confirmed Ticket Sales</div>
                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 text-[10px] font-bold">PAID</span>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-amber-400 font-mono tracking-tight">₹{{ $metrics['ticket_collections_rupees'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Gross Collected (incl. supporter passes): <strong class="text-slate-200 font-mono">₹{{ $metrics['gross_collections_rupees'] }}</strong>
                </div>
            </div>
            @php
                $targetPaise = max(1, $metrics['target_full_capacity_paise'] ?? 65000000);
                $targetPct = min(100, round((($metrics['ticket_collections_paise'] ?? 0) / $targetPaise) * 100, 1));
            @endphp
            <div class="text-[11px] text-slate-400 space-y-1.5 pt-2 border-t border-slate-800/60">
                <div class="flex justify-between items-center">
                    <span>Full-Capacity Target (450):</span>
                    <span class="text-slate-200 font-mono font-bold">₹{{ $metrics['target_full_capacity_rupees'] }}</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                    <div class="bg-amber-400 h-full rounded-full" style="width: {{ $targetPct }}%"></div>
                </div>
                <div class="flex justify-between text-[10px] text-slate-500">
                    <span>Realized: {{ $targetPct }}%</span>
                    <span>Offline: {{ $metrics['offline_bookings_count'] }} (₹{{ $metrics['offline_collections_rupees'] }})</span>
                </div>
            </div>
        </div>

        <!-- 2. Standalone Donations + Enquiries & Holds -->
        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Cause Donations (No Seat)</div>
                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">SEPARATE</span>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-emerald-400 font-mono">₹{{ $metrics['combined_donations_rupees'] }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Verified Cause Proceeds &bull; 0 hall seats deducted
                </div>
            </div>
            <div class="text-[11px] text-slate-400 space-y-1 pt-2 border-t border-slate-800/60">
                <div class="flex justify-between items-center">
                    <span>Standalone Donations ({{ $metrics['standalone_acknowledged_count'] }}ack / {{ $metrics['standalone_donations_count'] }}):</span>
                    <span class="text-slate-200 font-mono font-bold">₹{{ number_format($metrics['standalone_acknowledged_paise'] / 100, 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Pending / Submitted Proof:</span>
                    <span class="text-amber-300 font-mono">₹{{ number_format($metrics['standalone_pending_paise'] / 100, 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Non-Attending Passes ({{ $metrics['donation_bookings_count'] }}):</span>
                    <span class="text-slate-300 font-mono">₹{{ $metrics['donation_collections_rupees'] }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Active Holds, Submitted Proofs & Expirations -->
        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Enquiries &amp; Holds</div>
                <span class="px-2 py-0.5 rounded bg-sky-500/10 text-sky-400 text-[10px] font-bold">30m HOLD</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="text-2xl font-extrabold text-amber-300 font-mono">{{ $metrics['pending_bookings'] }}</div>
                    <div class="text-[10px] text-slate-400">Pending Hold ({{ $metrics['active_held_seats'] }} seats)</div>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-sky-400 font-mono">{{ $metrics['payment_submitted_bookings'] }}</div>
                    <div class="text-[10px] text-slate-400">UTR Submitted</div>
                </div>
            </div>
            <div class="text-[11px] text-slate-400 space-y-1 pt-2 border-t border-slate-800/60">
                <div class="flex justify-between items-center">
                    <span>Expired / Released Holds:</span>
                    <span class="text-rose-300 font-mono font-semibold">{{ $metrics['expired_bookings'] }} ({{ $metrics['expired_seats_released'] }} seats)</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Confirmed Paid Orders:</span>
                    <span class="text-emerald-400 font-mono font-bold">{{ $metrics['paid_bookings'] }} ({{ $metrics['conversion_rate'] }}%)</span>
                </div>
            </div>
        </div>

        <!-- 4. Seat Inventory, Comps/Sponsors & Gate Check-in -->
        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Seats &amp; Event Check-In</div>
                <a href="{{ route('staff.scan') }}" target="_blank" class="text-[10px] text-emerald-400 hover:underline font-bold">Gate Scanner &rarr;</a>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="text-2xl font-extrabold text-emerald-400 font-mono">{{ $metrics['global_remaining'] }} <span class="text-xs text-slate-500">/450</span></div>
                    <div class="text-[10px] text-slate-400">Available Seats</div>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-slate-100 font-mono">{{ $metrics['total_checked_in'] }}</div>
                    <div class="text-[10px] text-slate-400">Checked In ({{ $metrics['showup_rate'] }}%)</div>
                </div>
            </div>
            <div class="text-[11px] text-slate-400 space-y-1 pt-2 border-t border-slate-800/60">
                <div class="flex justify-between items-center">
                    <span>Paid Tickets Issued:</span>
                    <span class="text-slate-200 font-mono font-bold">{{ $metrics['paid_tickets_issued'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Sponsors / Comps / Holds:</span>
                    <span class="text-purple-300 font-mono">{{ $metrics['sponsor_seats_count'] }} / {{ $metrics['comp_seats_count'] }} / {{ $metrics['organiser_hold_seats_count'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Operations Dispatch Tiles -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.bookings') }}" class="p-5 rounded-xl bg-[#0d121f] border border-slate-800 hover:border-amber-500/50 transition group flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[10px] uppercase font-bold tracking-widest text-amber-400">Verification &amp; Offline Desk</div>
                <div class="text-sm font-bold text-slate-100 group-hover:text-amber-300 transition">Bookings, Offline Entry &amp; Causes</div>
                <div class="text-xs text-slate-400">Verify UTR proofs, log offline cash/VIP &amp; resend QR passes</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 group-hover:bg-amber-500 group-hover:text-slate-950 flex items-center justify-center transition flex-shrink-0">&rarr;</div>
        </a>

        <a href="{{ route('admin.dispatch') }}" class="p-5 rounded-xl bg-[#0d121f] border border-slate-800 hover:border-sky-500/50 transition group flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-[10px] uppercase font-bold tracking-widest text-sky-400">Pass Generation</div>
                <div class="text-sm font-bold text-slate-100 group-hover:text-sky-300 transition">Ticket Dispatch Desk</div>
                <div class="text-xs text-slate-400">Generate &amp; resend WhatsApp QR Entry Passes on demand</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 group-hover:bg-sky-500 group-hover:text-slate-950 flex items-center justify-center transition flex-shrink-0">&rarr;</div>
        </a>

        @if (auth()->user()->canManageFinancials())
            <a href="{{ route('admin.reports.export') }}" class="p-5 rounded-xl bg-[#0d121f] border border-slate-800 hover:border-emerald-500/50 transition group flex items-center justify-between">
                <div class="space-y-1">
                    <div class="text-[10px] uppercase font-bold tracking-widest text-emerald-400">Excel / CSV Reconciliation</div>
                    <div class="text-sm font-bold text-slate-100 group-hover:text-emerald-300 transition">Export Settlement &amp; UTR Ledger</div>
                    <div class="text-xs text-slate-400">Download full booking, offline &amp; donation CSV with UTRs</div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 group-hover:bg-emerald-500 group-hover:text-slate-950 flex items-center justify-center transition flex-shrink-0">&darr;</div>
            </a>
        @else
            <a href="{{ route('admin.allocations') }}" class="p-5 rounded-xl bg-[#0d121f] border border-slate-800 hover:border-purple-500/50 transition group flex items-center justify-between">
                <div class="space-y-1">
                    <div class="text-[10px] uppercase font-bold tracking-widest text-purple-400">Sponsor &amp; VIP Pool</div>
                    <div class="text-sm font-bold text-slate-100 group-hover:text-purple-300 transition">Comps &amp; Sponsor Allocations</div>
                    <div class="text-xs text-slate-400">Manage non-paid sponsor/VIP allocations in the 450-seat pool</div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-slate-800 text-slate-300 group-hover:bg-purple-500 group-hover:text-slate-950 flex items-center justify-center transition flex-shrink-0">&rarr;</div>
            </a>
        @endif
    </div>

    <!-- Category Quotas & Inventory Breakdown Table -->
    <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-800/60">
            <div>
                <h2 class="text-lg font-bold text-slate-100 font-serif-display">Seat Categories &amp; Revenue Breakdown (Gold / Silver / Bronze)</h2>
                <p class="text-xs text-slate-400">Live capacity, paid tickets, active 30m holds, comps/sponsors, and verified collections across 450 seats.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">Target Capacity Revenue: ₹{{ $metrics['target_full_capacity_rupees'] }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                        <th class="py-3 px-3">Category</th>
                        <th class="py-3 px-3">Unit Price</th>
                        <th class="py-3 px-3">Capacity</th>
                        <th class="py-3 px-3">Occupancy</th>
                        <th class="py-3 px-3">Paid Sold</th>
                        <th class="py-3 px-3">Holds / Comps</th>
                        <th class="py-3 px-3">Available</th>
                        <th class="py-3 px-3 text-right">Verified Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach ($metrics['category_breakdown'] as $cat)
                        @php
                            $catLower = strtolower($cat['name']);
                            $isGold = str_contains($catLower, 'gold') || str_contains($catLower, 'tier 1');
                            $isSilver = str_contains($catLower, 'silver') || str_contains($catLower, 'tier 2');
                            $soldPercent = $cat['capacity'] > 0 ? round(($cat['sold'] / $cat['capacity']) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition">
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-2">
                                    @if ($isGold)
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                        <span class="font-bold text-slate-100">{{ $cat['name'] }}</span>
                                        <span class="px-2 py-0.5 rounded bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[10px] font-bold">FRONT</span>
                                    @elseif ($isSilver)
                                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                                        <span class="font-bold text-slate-100">{{ $cat['name'] }}</span>
                                        <span class="px-2 py-0.5 rounded bg-slate-700/60 border border-slate-600 text-slate-200 text-[10px] font-bold">CENTRAL</span>
                                    @else
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-700"></span>
                                        <span class="font-bold text-slate-100">{{ $cat['name'] }}</span>
                                        <span class="px-2 py-0.5 rounded bg-amber-900/30 border border-amber-700/40 text-amber-200 text-[10px] font-bold">GENERAL</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-slate-300 font-mono font-semibold">
                                ₹{{ number_format($cat['price_paise'] / 100, 0) }}
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-300">
                                {{ $cat['capacity'] }} seats
                            </td>
                            <td class="py-3.5 px-3 min-w-[140px]">
                                <div class="space-y-1">
                                    <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full {{ $isGold ? 'bg-amber-400' : ($isSilver ? 'bg-slate-300' : 'bg-amber-700') }}" style="width: {{ $soldPercent }}%"></div>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $cat['sold'] }}/{{ $cat['capacity'] }} ({{ $soldPercent }}%)</div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 font-bold text-emerald-400 font-mono">
                                {{ $cat['sold'] }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-400 font-mono">
                                {{ $cat['held'] ?? 0 }} held &bull; {{ $cat['comps_only'] ?? $cat['comps'] }} comp
                            </td>
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

    <!-- Campaign & Attribution Intelligence -->
    @if (!empty($metrics['campaign_attribution']) && count($metrics['campaign_attribution']) > 0)
        <div class="p-6 rounded-2xl bg-[#0e1424] border border-slate-800/80 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-800/60">
                <div>
                    <h2 class="text-lg font-bold text-slate-100 font-serif-display">Campaign Attribution &amp; Traffic Channels</h2>
                    <p class="text-xs text-slate-400">Conversion and verified revenue across UTM traffic sources.</p>
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
                                <td class="py-3 px-3 font-mono font-semibold text-amber-400">{{ $camp->source }}</td>
                                <td class="py-3 px-3 font-semibold text-slate-200">{{ $camp->contacts_count }}</td>
                                <td class="py-3 px-3 text-slate-300">{{ $camp->bookings_count }}</td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-400">
                                    ₹{{ number_format($camp->verified_revenue_paise / 100, 0) }}
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
