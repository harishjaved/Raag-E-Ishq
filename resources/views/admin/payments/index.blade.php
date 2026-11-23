@extends('layouts.admin')

@section('title', 'Payments & Gateway Ledger')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100">Payments &amp; Reconciliation</h1>
            <p class="text-xs text-slate-400 mt-1">Verified Razorpay payment attempts and exception handling.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.payments') }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ !$currentStatus ? 'bg-slate-800 text-amber-400' : 'text-slate-400' }}">All</a>
            <a href="{{ route('admin.payments', ['status' => 'CAPTURED']) }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentStatus === 'CAPTURED' ? 'bg-slate-800 text-emerald-400' : 'text-slate-400' }}">Captured</a>
            <a href="{{ route('admin.payments', ['status' => 'EXPIRED']) }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentStatus === 'EXPIRED' ? 'bg-slate-800 text-rose-400' : 'text-slate-400' }}">Expired</a>
            <a href="{{ route('admin.payments', ['status' => 'RECONCILIATION_REQUIRED']) }}" class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentStatus === 'RECONCILIATION_REQUIRED' ? 'bg-rose-950/60 text-rose-400 border-rose-500/40' : 'text-slate-400' }}">Exceptions</a>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <th class="py-2.5">Payment ID</th>
                    <th class="py-2.5">Booking Ref</th>
                    <th class="py-2.5">Method</th>
                    <th class="py-2.5">Amount (INR)</th>
                    <th class="py-2.5">Status</th>
                    <th class="py-2.5">Timestamp</th>
                    <th class="py-2.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="py-3 font-mono text-slate-300">{{ $payment->provider_payment_id ?? 'N/A' }}</td>
                        <td class="py-3 font-mono font-bold text-amber-400">{{ $payment->booking->booking_reference }}</td>
                        <td class="py-3 text-slate-400 uppercase text-[10px]">{{ $payment->method }}</td>
                        <td class="py-3 font-mono font-bold text-slate-100">
                            ₹{{ number_format($payment->amount_paise / 100, 2) }}
                        </td>
                        <td class="py-3">
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
                        <td class="py-3 text-slate-500 text-[11px]">{{ $payment->created_at->format('d M H:i') }}</td>
                        <td class="py-3 text-right">
                            @if ($payment->status === 'RECONCILIATION_REQUIRED')
                                <form action="{{ route('admin.payments.reconcile', ['id' => $payment->id]) }}" method="POST" class="inline-flex gap-1">
                                    @csrf
                                    <input type="hidden" name="notes" value="Resolved manually by Finance officer with bank settlement proof.">
                                    <button type="submit" name="resolution_action" value="FORCE_CONFIRM" class="px-2 py-1 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/40 text-emerald-300 text-[10px] font-bold">
                                        Force Confirm
                                    </button>
                                    <button type="submit" name="resolution_action" value="REFUND" class="px-2 py-1 rounded-lg bg-rose-500/20 hover:bg-rose-500/40 text-rose-300 text-[10px] font-bold">
                                        Refund
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] text-slate-600">Settled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">No payment records match.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
