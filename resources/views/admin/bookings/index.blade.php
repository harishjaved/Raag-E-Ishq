@extends('layouts.admin')

@section('title', 'Bookings')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100">Bookings Ledger</h1>
            <p class="text-xs text-slate-400 mt-1">Real-time status of all public registrations and orders.</p>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.bookings') }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ !$currentStatus ? 'bg-slate-800 text-amber-400' : 'text-slate-400' }}">All</a>
            <a href="{{ route('admin.bookings', ['status' => 'CONFIRMED']) }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentStatus === 'CONFIRMED' ? 'bg-slate-800 text-emerald-400' : 'text-slate-400' }}">Confirmed</a>
            <a href="{{ route('admin.bookings', ['status' => 'PAYMENT_PENDING']) }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentStatus === 'PAYMENT_PENDING' ? 'bg-slate-800 text-amber-400' : 'text-slate-400' }}">Pending</a>
            <a href="{{ route('admin.bookings', ['status' => 'RECONCILIATION_REQUIRED']) }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentStatus === 'RECONCILIATION_REQUIRED' ? 'bg-rose-950/60 text-rose-400 border-rose-500/40' : 'text-slate-400' }}">Reconciliation</a>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <th class="py-2.5">Reference</th>
                    <th class="py-2.5">Purchaser</th>
                    <th class="py-2.5">Category &amp; Qty</th>
                    <th class="py-2.5">Amount</th>
                    <th class="py-2.5">Status</th>
                    <th class="py-2.5">Created At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($bookings as $booking)
                    <tr>
                        <td class="py-3 font-mono font-bold text-amber-400">{{ $booking->booking_reference }}</td>
                        <td class="py-3 font-semibold text-slate-200">
                            <div>{{ $booking->contact->full_name }}</div>
                            <div class="text-[11px] text-slate-400 font-normal">{{ $booking->contact->email }}</div>
                        </td>
                        <td class="py-3 text-slate-300">
                            @foreach ($booking->items as $item)
                                <span>{{ $item->category->name }} &times; {{ $item->quantity }}</span>
                            @endforeach
                        </td>
                        <td class="py-3 font-mono font-bold text-slate-100">
                            ₹{{ number_format($booking->total_amount_paise / 100, 2) }}
                        </td>
                        <td class="py-3">
                            @if ($booking->status === 'CONFIRMED')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">CONFIRMED</span>
                            @elseif ($booking->status === 'PAYMENT_PENDING')
                                <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold">PENDING</span>
                            @elseif ($booking->status === 'RECONCILIATION_REQUIRED')
                                <span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[10px] font-bold">RECONCILE</span>
                            @elseif ($booking->status === 'HOLD_EXPIRED')
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-500 text-[10px]">EXPIRED</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 text-[10px]">{{ $booking->status }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-slate-500 text-[11px]">{{ $booking->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">No bookings match the filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
