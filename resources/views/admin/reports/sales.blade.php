@extends('layouts.admin')

@section('title', 'Finance Reports & Reconciliation')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100">Finance &amp; Settlement Reports</h1>
            <p class="text-xs text-slate-400 mt-1">Verified gross collections and reconciliation export.</p>
        </div>
        <a href="{{ route('admin.reports.export') }}"
            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition shadow-lg shadow-amber-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span>Export Settlement CSV</span>
        </a>
    </div>

    <!-- Financial KPI Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Gross Verified Revenue</div>
            <div class="text-3xl font-extrabold text-amber-400 font-mono">₹{{ $metrics['gross_collections_rupees'] }}</div>
            <div class="text-[10px] text-slate-500">100% gateway verified receipts</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Paid Admissions Sold</div>
            <div class="text-3xl font-extrabold text-slate-100">{{ $metrics['paid_tickets_issued'] }} <span class="text-xs text-slate-500">tickets</span></div>
            <div class="text-[10px] text-slate-500">Across 3 tiers</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
            <div class="text-[11px] font-semibold text-slate-400 uppercase">Conversion Rate</div>
            <div class="text-3xl font-extrabold text-emerald-400">{{ $metrics['conversion_rate'] }}%</div>
            <div class="text-[10px] text-slate-500">Registrations to paid conversion</div>
        </div>
    </div>
</div>
@endsection
