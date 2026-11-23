@extends('layouts.admin')

@section('title', 'Comps & Sponsor Allocations')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-100">Complimentary &amp; Sponsor Allocations</h1>
        <p class="text-xs text-slate-400 mt-1">Deduct non-paid VIP, comp, and sponsor seats from the 450 capacity ceiling.</p>
    </div>

    <!-- Allocation Form -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <h2 class="text-sm font-bold text-slate-200">Issue New Seat Allocation</h2>

        <form action="{{ route('admin.allocations.store') }}" method="POST" class="grid sm:grid-cols-4 gap-4">
            @csrf
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Category</label>
                <select name="ticket_category_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }} (₹{{ number_format($cat->price_paise / 100) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Type</label>
                <select name="allocation_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100">
                    <option value="SPONSOR">Sponsor Allocation</option>
                    <option value="COMPLIMENTARY">Complimentary (VIP/Artist)</option>
                    <option value="ORGANISER_HOLD">Organiser Reserve Hold</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Quantity</label>
                <input type="number" name="quantity" min="1" max="50" value="1" required
                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100">
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-400">Beneficiary / Sponsor Name</label>
                <input type="text" name="beneficiary_name" placeholder="e.g. Title Sponsor Hospitality" required
                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-100">
            </div>

            <div class="sm:col-span-4 flex justify-end">
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition">
                    Deduct from 450 Pool &amp; Allocate
                </button>
            </div>
        </form>
    </div>

    <!-- Allocations History Table -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
        <h2 class="text-sm font-bold text-slate-200 mb-4">Existing Allocations</h2>
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <th class="py-2.5">Beneficiary</th>
                    <th class="py-2.5">Type</th>
                    <th class="py-2.5">Category</th>
                    <th class="py-2.5">Quantity</th>
                    <th class="py-2.5">Issued By</th>
                    <th class="py-2.5">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($allocations as $alloc)
                    <tr>
                        <td class="py-3 font-bold text-slate-200">{{ $alloc->beneficiary_name }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[10px] font-bold">
                                {{ $alloc->allocation_type }}
                            </span>
                        </td>
                        <td class="py-3 text-slate-300">{{ $alloc->category->name }}</td>
                        <td class="py-3 font-mono font-bold text-amber-400">{{ $alloc->quantity }} seats</td>
                        <td class="py-3 text-slate-400">{{ $alloc->createdBy?->name ?? 'System' }}</td>
                        <td class="py-3 text-slate-500 text-[11px]">{{ $alloc->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">No complimentary or sponsor allocations created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
