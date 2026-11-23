@extends('layouts.admin')

@section('title', 'CRM Registrants')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-100">CRM Registrants</h1>
            <p class="text-xs text-slate-400 mt-1">Total registered guests and contact profiles.</p>
        </div>
        <form action="{{ route('admin.crm.contacts') }}" method="GET" class="w-full sm:w-auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, phone..."
                class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-100 focus:outline-none focus:border-amber-400 w-full sm:w-64">
        </form>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                    <th class="py-2.5">Name</th>
                    <th class="py-2.5">Contact Details</th>
                    <th class="py-2.5">Opt-in</th>
                    <th class="py-2.5">Campaign Source</th>
                    <th class="py-2.5">Total Bookings</th>
                    <th class="py-2.5">Confirmed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @forelse ($contacts as $contact)
                    <tr>
                        <td class="py-3 font-bold text-slate-200">{{ $contact->full_name }}</td>
                        <td class="py-3 text-slate-400 space-y-0.5">
                            <div>{{ $contact->email }}</div>
                            <div class="text-[11px] font-mono">{{ $contact->country_code }} {{ $contact->phone }}</div>
                        </td>
                        <td class="py-3">
                            @if ($contact->marketing_opt_in)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-semibold">Yes</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-500 text-[10px]">No</span>
                            @endif
                        </td>
                        <td class="py-3 text-slate-400 font-mono text-[11px]">{{ $contact->utm_source ?? 'Direct' }}</td>
                        <td class="py-3 font-semibold">{{ $contact->bookings_count }}</td>
                        <td class="py-3 font-bold text-emerald-400">{{ $contact->confirmed_bookings_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-500">No CRM registrants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $contacts->links() }}
        </div>
    </div>
</div>
@endsection
