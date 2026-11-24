@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Security &amp; Forensic Log</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Audit Trail &amp; System Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Immutable record of material gate admissions, payment verifications, session expirations, and inventory actions.</p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <a href="{{ route('admin.audit-logs') }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 transition font-semibold {{ !$currentAction ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-400 hover:text-slate-200' }}">All</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'GATE_ADMISSION_CHECKIN']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 transition font-semibold {{ $currentAction === 'GATE_ADMISSION_CHECKIN' ? 'bg-sky-500 text-slate-950 font-bold' : 'bg-slate-900 text-sky-400 hover:text-sky-300' }}">Gate Check-ins</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'PAYMENT_VERIFIED_CONFIRMED']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 transition font-semibold {{ $currentAction === 'PAYMENT_VERIFIED_CONFIRMED' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-emerald-400 hover:text-emerald-300' }}">Payments</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'EXPIRATIONS']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 transition font-semibold {{ $currentAction === 'EXPIRATIONS' ? 'bg-rose-500 text-slate-950 font-bold' : 'bg-slate-900 text-rose-400 hover:text-rose-300' }}">Expired Holds &amp; Payments</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'TICKETS_ISSUED']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 transition font-semibold {{ $currentAction === 'TICKETS_ISSUED' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 text-amber-400 hover:text-amber-300' }}">Tickets Issued</a>
            <a href="{{ route('admin.audit-logs', ['action' => 'TICKET_ALLOCATION_CREATED']) }}"
                class="text-xs px-3 py-1.5 rounded-xl border border-slate-800 transition font-semibold {{ $currentAction === 'TICKET_ALLOCATION_CREATED' ? 'bg-purple-500 text-slate-950 font-bold' : 'bg-slate-900 text-purple-400 hover:text-purple-300' }}">Allocations</a>
        </div>
    </div>

    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                    <th class="py-3 px-3">Timestamp</th>
                    <th class="py-3 px-3">Actor</th>
                    <th class="py-3 px-3">Action Type</th>
                    <th class="py-3 px-3">Target Entity</th>
                    <th class="py-3 px-3">Audit Details &amp; Direct Links</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 font-mono">
                @forelse ($logs as $log)
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="py-3.5 px-3 text-slate-400 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3.5 px-3 text-slate-200 whitespace-nowrap font-sans font-semibold">{{ $log->user?->name ?? 'SYSTEM' }}</td>
                        <td class="py-3.5 px-3">
                            @if (str_contains($log->action, 'CHECKIN'))
                                <span class="px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20 text-[10px] font-bold">{{ $log->action }}</span>
                            @elseif (str_contains($log->action, 'PAYMENT_VERIFIED') || str_contains($log->action, 'RECONCILED'))
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">{{ $log->action }}</span>
                            @elseif (str_contains($log->action, 'EXPIRED') || str_contains($log->action, 'FAILED') || str_contains($log->action, 'CANCELLED'))
                                <span class="px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-bold">{{ $log->action }}</span>
                            @elseif (str_contains($log->action, 'ALLOCATION'))
                                <span class="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-400 border border-purple-500/20 text-[10px] font-bold">{{ $log->action }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold">{{ $log->action }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-slate-400 whitespace-nowrap">{{ $log->target_type }} #{{ $log->target_id }}</td>
                        <td class="py-3.5 px-3 font-sans">
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                @if (isset($log->new_values['booking_reference']))
                                    @php
                                        $bookingObj = \App\Models\Booking::where('booking_reference', $log->new_values['booking_reference'])->first();
                                    @endphp
                                    @if ($bookingObj)
                                        <a href="{{ route('admin.bookings.show', $bookingObj->id) }}" class="px-2 py-0.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/25 border border-amber-500/30 text-amber-400 font-mono font-bold transition flex items-center gap-1" title="Click to view full booking">
                                            <span>{{ $log->new_values['booking_reference'] }}</span>
                                            <span class="text-[9px]">&rarr;</span>
                                        </a>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 font-mono font-semibold">{{ $log->new_values['booking_reference'] }}</span>
                                    @endif
                                @endif

                                @if (isset($log->new_values['amount_rupees']))
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono font-bold">₹{{ $log->new_values['amount_rupees'] }}</span>
                                @elseif (isset($log->new_values['amount_paise']))
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono font-bold">₹{{ number_format($log->new_values['amount_paise'] / 100, 2) }}</span>
                                @endif

                                @if (isset($log->new_values['reason']))
                                    <span class="text-rose-400 text-xs italic">({{ $log->new_values['reason'] }})</span>
                                @endif

                                @if (isset($log->new_values['gate']))
                                    <span class="px-1.5 py-0.5 rounded bg-sky-500/10 text-sky-400 font-bold text-[10px]">{{ $log->new_values['gate'] }}</span>
                                @endif

                                @if (isset($log->new_values['ticket_number']))
                                    <span class="text-slate-400 font-mono">Ticket: {{ $log->new_values['ticket_number'] }}</span>
                                @endif

                                @if (isset($log->new_values['status']))
                                    <span class="text-slate-400 text-[10px]">Status: {{ $log->new_values['status'] }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-500">No audit log records match the selected filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 pt-4 border-t border-slate-800/60">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
