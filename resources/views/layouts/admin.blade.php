<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Operations') — Raage-E-Ishq</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col md:flex-row bg-slate-950 text-slate-100">
    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-slate-900 border-b md:border-b-0 md:border-r border-slate-800 flex-shrink-0 flex flex-col justify-between">
        <div class="p-4 space-y-6">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-tr from-rose-500 to-amber-500 text-slate-950 font-extrabold text-xs flex items-center justify-center shadow-md">REI</span>
                    <div>
                        <div class="text-[10px] uppercase font-bold tracking-widest text-amber-400">Back Office</div>
                        <div class="text-sm font-bold text-slate-100">Raage-E-Ishq</div>
                    </div>
                </a>
            </div>

            <!-- User Badge -->
            <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800/80 space-y-1">
                <div class="text-xs font-bold text-slate-200">{{ auth()->user()->name }}</div>
                <div class="inline-block px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-[10px] font-bold uppercase text-amber-400">
                    {{ str_replace('_', ' ', auth()->user()->role) }}
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="space-y-1 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Overview Dashboard</span>
                </a>

                @if (auth()->user()->isSuperAdmin() || auth()->user()->isEventManager())
                    <a href="{{ route('admin.crm.contacts') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.crm.*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>CRM Registrants</span>
                    </a>

                    <a href="{{ route('admin.bookings') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.bookings*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Enquiries &amp; Bookings</span>
                    </a>

                    <a href="{{ route('admin.dispatch') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.dispatch*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>Ticket Generator &amp; Dispatch</span>
                    </a>

                    <a href="{{ route('admin.allocations') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.allocations*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Comps &amp; Allocations</span>
                    </a>
                @endif

                @if (auth()->user()->canManageFinancials())
                    <a href="{{ route('admin.payments') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.payments*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Payments &amp; Reconciliation</span>
                    </a>

                    <a href="{{ route('admin.reports.sales') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.reports*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Finance Reports &amp; Exports</span>
                    </a>
                @endif

                @if (auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.audit-logs') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition {{ request()->routeIs('admin.audit-logs*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Audit Trail</span>
                    </a>
                @endif

                <div class="pt-4 border-t border-slate-800">
                    <a href="{{ route('staff.scan') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-emerald-400 hover:bg-emerald-950/40 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Open Gate Scanner</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Logout Action -->
        <div class="p-4 border-t border-slate-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-xs font-semibold py-2 px-3 rounded-xl bg-slate-950 hover:bg-rose-950/60 text-slate-400 hover:text-rose-400 transition text-left flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-grow p-4 md:p-8 overflow-y-auto max-w-7xl">
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
