@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100">Audit Trail &amp; System Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Immutable record of material security and operational actions.</p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <a href="{{ route('admin.audit-logs') }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ !$currentAction ? 'bg-slate-800 text-amber-400' : 'text-slate-400 hover:text-slate-200' }}">All</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'GATE_ADMISSION_CHECKIN']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentAction === 'GATE_ADMISSION_CHECKIN' ? 'bg-slate-800 text-sky-400' : 'text-slate-400 hover:text-slate-200' }}">Gate Check-ins</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'PAYMENT_VERIFIED_CONFIRMED']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentAction === 'PAYMENT_VERIFIED_CONFIRMED' ? 'bg-slate-800 text-emerald-400' : 'text-slate-400 hover:text-slate-200' }}">Payments</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'EXPIRATIONS']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentAction === 'EXPIRATIONS' ? 'bg-slate-800 text-rose-400' : 'text-slate-400 hover:text-slate-200' }}">Expired Holds &amp; Payments</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'TICKETS_ISSUED']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentAction === 'TICKETS_ISSUED' ? 'bg-slate-800 text-amber-400' : 'text-slate-400 hover:text-slate-200' }}">Tickets Issued</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'TICKET_ALLOCATION_CREATED']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 {{ $currentAction === 'TICKET_ALLOCATION_CREATED' ? 'bg-slate-800 text-purple-400' : 'text-slate-400 hover:text-slate-200' }}">Allocations</a>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <th class="py-2.5">Timestamp</th>
                    <th class="py-2.5">Actor</th>
                    <th class="py-2.5">Action</th>
                    <th class="py-2.5">Target</th>
                    <th class="py-2.5">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 font-mono">
                @forelse ($logs as $log)
                    <tr>
                        <td class="py-3 text-slate-400 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3 text-slate-200 whitespace-nowrap font-sans">{{ $log->user?->name ?? 'SYSTEM' }}</td>
                        <td class="py-3">
                            @if (str_contains($log->action, 'CHECKIN'))
                                <span class="text-sky-400 font-bold">{{ $log->action }}</span>
                            @elseif (str_contains($log->action, 'PAYMENT_VERIFIED') || str_contains($log->action, 'RECONCILED'))
                                <span class="text-emerald-400 font-bold">{{ $log->action }}</span>
                            @elseif (str_contains($log->action, 'EXPIRED') || str_contains($log->action, 'FAILED'))
                                <span class="text-rose-400 font-bold">{{ $log->action }}</span>
                            @elseif (str_contains($log->action, 'ALLOCATION'))
                                <span class="text-purple-400 font-bold">{{ $log->action }}</span>
                            @else
                                <span class="text-amber-400 font-bold">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-slate-400 whitespace-nowrap">{{ $log->target_type }} #{{ $log->target_id }}</td>
                        <td class="py-3 font-sans">
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                @if (isset($log->new_values['booking_reference']))
                                    <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 font-mono font-semibold">{{ $log->new_values['booking_reference'] }}</span>
                                @endif

                                @if (isset($log->new_values['amount_rupees']))
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono font-bold">₹{{ $log->new_values['amount_rupees'] }}</span>
                                @elseif (isset($log->new_values['amount_paise']))
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono font-bold">₹{{ number_format($log->new_values['amount_paise'] / 100, 2) }}</span>
                                @endif

                                @if (isset($log->new_values['gate']))
                                    <span class="px-1.5 py-0.5 rounded bg-sky-500/10 text-sky-400 font-mono text-[10px]">Gate: {{ $log->new_values['gate'] }}</span>
                                @endif

                                @if (isset($log->new_values['tickets_count']))
                                    <span class="text-slate-300">{{ $log->new_values['tickets_count'] }} ticket(s)</span>
                                @endif

                                @if (isset($log->new_values['category']))
                                    <span class="text-slate-400 font-mono">({{ $log->new_values['category'] }})</span>
                                @endif

                                @if (isset($log->new_values['reason']))
                                    <span class="text-slate-400 italic text-[11px]">{{ $log->new_values['reason'] }}</span>
                                @endif

                                @if (isset($log->new_values['status']))
                                    <span class="text-slate-400 text-[10px]">Status: {{ $log->new_values['status'] }}</span>
                                @endif
                            </div>

                            <details class="mt-1 cursor-pointer text-slate-500 hover:text-slate-400 text-[10px]">
                                <summary>raw JSON</summary>
                                <pre class="p-2 mt-1 rounded-xl bg-slate-950 font-mono text-[10px] text-slate-400 overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-500 font-sans">No audit records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 font-sans">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
