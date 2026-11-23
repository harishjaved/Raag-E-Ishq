@extends('layouts.app')

@section('title', 'Staff Login — Raage-E-Ishq')

@section('content')
<div class="max-w-md mx-auto px-4 py-12 space-y-6">
    <div class="text-center space-y-2">
        <span class="text-xs uppercase font-semibold tracking-wider text-amber-400">Authenticated Portal</span>
        <h1 class="text-2xl font-bold font-serif-display text-slate-100">Staff &amp; Admin Sign In</h1>
        <p class="text-xs text-slate-400">Authorized Joygivers event operations and gate scanning.</p>
    </div>

    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="e.g. gate@joygivers.org"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-400 text-sm">
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-300">Password</label>
                <input type="password" name="password" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-400 text-sm">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-700 bg-slate-950 text-amber-500 focus:ring-0">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3 px-6 rounded-2xl bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-bold text-sm transition">
                Sign In to Platform
            </button>
        </form>
    </div>

    <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-slate-800 text-[11px] text-slate-500 space-y-1 text-center">
        <div><strong>Default Test Accounts (Password: Password123!)</strong></div>
        <div>Super Admin: <code class="text-slate-400">superadmin@joygivers.org</code></div>
        <div>Gate Staff: <code class="text-slate-400">gate@joygivers.org</code></div>
    </div>
</div>
@endsection
