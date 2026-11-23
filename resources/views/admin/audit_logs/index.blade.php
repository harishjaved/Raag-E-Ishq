@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-100">Audit Trail &amp; System Logs</h1>
        <p class="text-xs text-slate-400 mt-1">Immutable record of material security and operational actions.</p>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <th class="py-2.5">Timestamp</th>
                    <th class="py-2.5">Actor</th>
                    <th class="py-2.5">Action</th>
                    <th class="py-2.5">Target</th>
                    <th class="py-2.5">Details (JSON)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 font-mono">
                @forelse ($logs as $log)
                    <tr>
                        <td class="py-3 text-slate-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="py-3 text-slate-200">{{ $log->user?->name ?? 'SYSTEM' }}</td>
                        <td class="py-3 font-bold text-amber-400">{{ $log->action }}</td>
                        <td class="py-3 text-slate-400">{{ $log->target_type }} #{{ $log->target_id }}</td>
                        <td class="py-3 text-[11px] text-slate-400 max-w-xs truncate">
                            {{ json_encode($log->new_values) }}
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
