@extends('layouts.admin')

@section('title', 'Payments & Gateway Ledger')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Payment Verification &amp; Gateway Audit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Payments &amp; Reconciliation</h1>
            <p class="text-xs text-slate-400 mt-1">Verified payment attempts, offline UPI transfers, settlement timestamps, and exception resolution.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.payments') }}" class="text-xs px-3.5 py-2 rounded-xl transition font-semibold {{ !$currentStatus ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-slate-200' }}">All</a>
            <a href="{{ route('admin.payments', ['status' => 'CAPTURED']) }}" class="text-xs px-3.5 py-2 rounded-xl transition font-semibold {{ $currentStatus === 'CAPTURED' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 border border-slate-800 text-emerald-400' }}">Captured</a>
            <a href="{{ route('admin.payments', ['status' => 'EXPIRED']) }}" class="text-xs px-3.5 py-2 rounded-xl transition font-semibold {{ $currentStatus === 'EXPIRED' ? 'bg-rose-500 text-slate-950 font-bold' : 'bg-slate-900 border border-slate-800 text-rose-400' }}">Expired</a>
            <a href="{{ route('admin.payments', ['status' => 'RECONCILIATION_REQUIRED']) }}" class="text-xs px-3.5 py-2 rounded-xl transition font-semibold {{ $currentStatus === 'RECONCILIATION_REQUIRED' ? 'bg-rose-500 text-slate-950 font-bold' : 'bg-rose-950/40 border border-rose-500/40 text-rose-400' }}">Exceptions</a>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                    <th class="py-3 px-3">Payment ID</th>
                    <th class="py-3 px-3">Booking Ref</th>
                    <th class="py-3 px-3">Method</th>
                    <th class="py-3 px-3">Amount (INR)</th>
                    <th class="py-3 px-3">Gateway Status</th>
                    <th class="py-3 px-3">Timestamp</th>
                    <th class="py-3 px-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="py-3.5 px-3 font-mono text-slate-300">{{ $payment->provider_payment_id ?? 'N/A' }}</td>
                        <td class="py-3.5 px-3 font-mono font-bold text-amber-400">
                            <a href="{{ route('admin.bookings.show', $payment->booking->id) }}" class="hover:underline">
                                {{ $payment->booking->booking_reference }}
                            </a>
                        </td>
                        <td class="py-3.5 px-3 text-slate-400 uppercase text-[10px] font-semibold">{{ $payment->method }}</td>
                        <td class="py-3.5 px-3 font-mono font-extrabold text-slate-100 text-sm">
                            ₹{{ number_format($payment->amount_paise / 100, 2) }}
                        </td>
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            @if ($payment->status === 'CAPTURED')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">CAPTURED</span>
                            @elseif ($payment->status === 'EXPIRED')
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-bold">EXPIRED</span>
                            @elseif ($payment->status === 'AWAITING_PAYMENT')
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-semibold">AWAITING PAYMENT</span>
                            @elseif ($payment->status === 'RECONCILIATION_REQUIRED')
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[10px] font-bold">RECONCILE REQUIRED</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px]">{{ $payment->status }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-slate-400 text-[11px] font-mono">{{ $payment->created_at->format('d M H:i') }}</td>
                        <td class="py-3.5 px-3 text-right">
                            @if ($payment->status === 'RECONCILIATION_REQUIRED')
                                <form action="{{ route('admin.payments.reconcile', ['id' => $payment->id]) }}" method="POST" class="inline-flex gap-1">
                                    @csrf
                                    <input type="hidden" name="notes" value="Resolved manually by Finance officer with bank settlement proof.">
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow">
                                        Reconcile
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-600 text-[11px]">Settled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-500">No payment attempts match the selected filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 pt-4 border-t border-slate-800/60">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
