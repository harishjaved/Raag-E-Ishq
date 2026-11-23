@extends('layouts.app')

@section('title', 'Resend Tickets — Raage-E-Ishq')

@section('content')
<div class="max-w-md mx-auto px-4 py-12 space-y-6">
    <div class="text-center space-y-2">
        <h1 class="text-2xl font-bold font-serif-display text-slate-100">Retrieve Your Tickets</h1>
        <p class="text-xs text-slate-400">
            Enter the email address you registered with to receive your admission tickets and booking details.
        </p>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs">
            {{ session('status') }}
        </div>
    @endif

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl">
        <form action="{{ route('tickets.resend.send') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">Registered Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. aarav@example.com"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-400 text-sm">
            </div>

            <button type="submit"
                class="w-full py-3 px-6 rounded-2xl bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-bold text-sm transition flex items-center justify-center gap-2">
                <span>Resend My Tickets</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>
    </div>

    <div class="text-center">
        <a href="/" class="text-xs text-slate-500 hover:text-slate-400">&larr; Return to Event Home</a>
    </div>
</div>
@endsection
