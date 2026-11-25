@extends('layouts.admin')

@section('title', 'CRM Registrants')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Audience Database</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">CRM Registrants</h1>
            <p class="text-xs text-slate-400 mt-1">Total registered patron directory, contact coordinates, marketing consent, and booking history.</p>
        </div>
        <form action="{{ route('admin.crm.contacts') }}" method="GET" class="w-full sm:w-auto flex items-center gap-2">
            <div class="relative flex-grow sm:flex-grow-0">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, phone (+91)..."
                    class="pl-10 pr-4 py-2.5 rounded-2xl bg-[#0e1424] border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400 w-full sm:w-72 shadow-sm transition">
            </div>
            <button type="submit" class="px-3.5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition">Search</button>
            @if ($search)
                <a href="{{ route('admin.crm.contacts') }}" class="text-xs text-slate-400 hover:text-amber-400 underline font-medium">Reset</a>
            @endif
        </form>
    </div>

    <div class="p-6 rounded-3xl bg-[#0e1424] border border-slate-800/80 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] bg-slate-950/40">
                    <th class="py-3 px-3">Patron Name</th>
                    <th class="py-3 px-3">Contact Coordinates</th>
                    <th class="py-3 px-3">Opt-in (Updates)</th>
                    <th class="py-3 px-3">Campaign Source</th>
                    <th class="py-3 px-3">Total Orders</th>
                    <th class="py-3 px-3 text-right">Confirmed Tickets</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($contacts as $contact)
                    <tr class="hover:bg-slate-900/40 transition">
                        <td class="py-3.5 px-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($contact->full_name, 0, 2)) }}
                                </div>
                                <span class="font-bold text-slate-100 text-sm">{{ $contact->full_name }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-3 text-slate-300 space-y-0.5">
                            <div>{{ $contact->email }}</div>
                            <div class="text-[11px] font-mono text-amber-400">{{ $contact->country_code }} {{ $contact->phone }}</div>
                        </td>
                        <td class="py-3.5 px-3">
                            @if ($contact->marketing_opt_in)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Yes (Opted In)</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-500 text-[10px]">
                                    <span>No</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-slate-400 font-mono text-[11px]">{{ $contact->utm_source ?? 'Direct' }}</td>
                        <td class="py-3.5 px-3 font-semibold text-slate-200">{{ $contact->bookings_count }}</td>
                        <td class="py-3.5 px-3 text-right font-bold font-mono text-emerald-400 text-sm">{{ $contact->confirmed_bookings_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-500">No CRM registrants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 pt-4 border-t border-slate-800/60">
            {{ $contacts->links() }}
        </div>
    </div>
</div>
@endsection
