@extends('layouts.admin')

@section('title', 'Comps & Sponsor Allocations')

@section('content')
<div class="space-y-6">
    <div class="pb-2 border-b border-slate-800/60">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
            <span>Capacity Quota Reserves</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Complimentary &amp; Sponsor Allocations</h1>
        <p class="text-xs text-slate-400 mt-1">Deduct non-paid VIP, comp, and sponsor seats from the 450 capacity ceiling.</p>
    </div>

    <!-- Allocation Form -->
    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl space-y-4">
        <h2 class="text-sm font-bold text-slate-200">Issue New Seat Allocation</h2>

        <form action="{{ route('admin.allocations.store') }}" method="POST" class="grid sm:grid-cols-4 gap-4">
            @csrf
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Category Tier</label>
                <select name="ticket_category_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }} (₹{{ number_format($cat->price_paise / 100) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Allocation Type</label>
                <select name="allocation_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                    <option value="SPONSOR">Sponsor Allocation</option>
                    <option value="COMPLIMENTARY">Complimentary (VIP/Artist)</option>
                    <option value="ORGANISER_HOLD">Organiser Reserve Hold</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Number of Seats</label>
                <input type="number" name="quantity" min="1" max="50" value="1" required
                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Beneficiary / Sponsor Name</label>
                <input type="text" name="beneficiary_name" placeholder="e.g. Media Sponsor Hospitality" required
                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Beneficiary Email (Optional)</label>
                <input type="email" name="beneficiary_email" placeholder="sponsor@brand.com"
                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Beneficiary Phone (Optional)</label>
                <input type="text" name="beneficiary_phone" placeholder="7736606506"
                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
            </div>

            <div class="sm:col-span-2 flex items-center pt-5">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-amber-500/10">
                    Allocate Seats &amp; Lock Inventory
                </button>
            </div>
        </form>
    </div>

    <!-- Allocations History Table -->
    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl overflow-x-auto space-y-4">
        <h2 class="text-sm font-bold text-slate-200">Active Allocations Ledger</h2>
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                    <th class="py-3 px-3">Date</th>
                    <th class="py-3 px-3">Category</th>
                    <th class="py-3 px-3">Beneficiary</th>
                    <th class="py-3 px-3">Type</th>
                    <th class="py-3 px-3">Seats</th>
                    <th class="py-3 px-3 text-right">Allocated By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($allocations as $allocation)
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="py-3.5 px-3 font-mono text-slate-400">{{ $allocation->created_at->format('d M Y') }}</td>
                        <td class="py-3.5 px-3 font-bold text-amber-400">{{ $allocation->category->name }}</td>
                        <td class="py-3.5 px-3">
                            <div class="font-bold text-slate-100">{{ $allocation->beneficiary_name }}</div>
                            @if(!empty($allocation->notes))
                                <div class="text-[11px] text-slate-400">{{ $allocation->notes }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 text-[10px] font-bold">
                                {{ $allocation->allocation_type }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 font-mono font-bold text-slate-100">{{ $allocation->quantity }} seats</td>
                        <td class="py-3.5 px-3 text-right text-slate-400 font-sans">{{ $allocation->createdBy?->name ?? $allocation->allocatedBy?->name ?? 'Admin Team' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-500">No complimentary or sponsor allocations issued yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 pt-4 border-t border-slate-800/60">
            @if(method_exists($allocations, 'links'))
                {{ $allocations->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
