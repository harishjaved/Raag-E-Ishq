<!DOCTYPE html>
<html lang="en" class="h-full bg-[#080c14] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Operations') — Raag-E-Ishq</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&family=JetBrains+Mono:wght@500;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .font-serif-display { font-family: 'Playfair Display', serif; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #080c14; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="min-h-full flex flex-col md:flex-row bg-[#080c14] text-slate-100 antialiased">
    <!-- Left Sidebar Navigation -->
    <aside class="w-full md:w-64 lg:w-72 bg-[#0d121f] border-b md:border-b-0 md:border-r border-slate-800/80 flex-shrink-0 flex flex-col justify-between">
        <div class="p-4 sm:p-5 space-y-6">
            <!-- Brand Logo & Back Office Identity -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-800/70">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-500 via-amber-500 to-amber-400 text-slate-950 font-extrabold text-sm flex items-center justify-center shadow-lg shadow-amber-500/20 group-hover:scale-105 transition transform">REI</span>
                    <div>
                        <div class="text-[10px] uppercase font-extrabold tracking-widest bg-gradient-to-r from-amber-400 to-rose-400 bg-clip-text text-transparent">Back Office Portal</div>
                        <div class="text-sm font-bold text-slate-100 flex items-center gap-1.5">
                            <span>Raag-E-Ishq</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Staff User Info Card -->
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800/70 flex items-center justify-between gap-3 shadow-inner">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-xl bg-slate-800 border border-slate-700 text-amber-400 font-bold text-xs flex items-center justify-center flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-200 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-amber-400 font-bold uppercase tracking-wider">
                            {{ str_replace('_', ' ', auth()->user()->role) }}
                        </div>
                    </div>
                </div>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse flex-shrink-0" title="Session Active"></span>
            </div>

            <!-- Grouped Navigation Menu -->
            <nav class="space-y-6 text-xs font-semibold">
                <!-- Group 1: Operations Desk -->
                <div class="space-y-1">
                    <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        Desk &amp; Operations
                    </div>

                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>Overview Dashboard</span>
                        </div>
                    </a>

                    <!-- Enquiries & Bookings (with active count pill) -->
                    @if (auth()->user()->isSuperAdmin() || auth()->user()->isEventManager())
                        <a href="{{ route('admin.bookings') }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.bookings*') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.bookings*') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Enquiries &amp; Bookings</span>
                            </div>
                            @if (($sidebarPendingCount ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-extrabold text-[10px] animate-pulse shadow-sm">
                                    {{ $sidebarPendingCount }}
                                </span>
                            @endif
                        </a>

                        <!-- Ticket Generator & Dispatch -->
                        <a href="{{ route('admin.dispatch') }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dispatch*') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.dispatch*') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                <span>Ticket Dispatch Desk</span>
                            </div>
                            @if (($sidebarNeedsDispatchCount ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-full bg-sky-500/20 border border-sky-500/40 text-sky-300 font-bold text-[10px]">
                                    {{ $sidebarNeedsDispatchCount }}
                                </span>
                            @endif
                        </a>
                    @endif
                </div>

                <!-- Group 2: CRM & Allocations -->
                @if (auth()->user()->isSuperAdmin() || auth()->user()->isEventManager())
                    <div class="space-y-1">
                        <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            Audience &amp; Allocations
                        </div>

                        <!-- CRM Registrants -->
                        <a href="{{ route('admin.crm.contacts') }}"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.crm.*') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.crm.*') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>CRM Registrants</span>
                        </a>

                        <!-- Comps & Allocations -->
                        <a href="{{ route('admin.allocations') }}"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.allocations*') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.allocations*') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Comps &amp; Allocations</span>
                        </a>
                    </div>
                @endif

                <!-- Group 3: Finance & Audit -->
                <div class="space-y-1">
                    <div class="px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        Finance &amp; Security
                    </div>

                    @if (auth()->user()->canManageFinancials())
                        <!-- Payments & Reconciliation -->
                        <a href="{{ route('admin.payments') }}"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.payments*') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.payments*') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Payments &amp; Reconciliation</span>
                        </a>

                        <!-- Finance Reports -->
                        <a href="{{ route('admin.reports.sales') }}"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reports*') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.reports*') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Finance &amp; Settlement</span>
                        </a>
                    @endif

                    @if (auth()->user()->isSuperAdmin())
                        <!-- Audit Trail -->
                        <a href="{{ route('admin.audit-logs') }}"
                            class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.audit-logs*') ? 'bg-gradient-to-r from-amber-500/20 to-transparent border-l-2 border-amber-400 text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.audit-logs*') ? 'text-amber-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Audit Trail &amp; Logs</span>
                        </a>
                    @endif
                </div>

                <!-- Group 4: Gate Operations -->
                <div class="pt-2">
                    <a href="{{ route('staff.scan') }}" target="_blank"
                        class="flex items-center justify-between p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 text-emerald-400 transition group shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span class="font-bold">Open Gate Scanner</span>
                        </div>
                        <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">LIVE</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Logout Action Bar -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-xs font-semibold py-2.5 px-3.5 rounded-xl bg-slate-900/80 hover:bg-rose-950/40 text-slate-400 hover:text-rose-300 border border-slate-800 transition text-left flex items-center justify-between group">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-rose-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Sign Out Portal</span>
                    </span>
                    <span class="text-[10px] text-slate-600 font-mono">&larr;</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-w-0 bg-[#080c14]">
        <!-- Top Command & Status Bar -->
        <header class="border-b border-slate-800/70 bg-[#0d121f]/90 backdrop-blur sticky top-0 z-40 px-6 py-3.5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Left: Breadcrumb / Section Context -->
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-500 font-medium">Back Office</span>
                    <span class="text-slate-600">/</span>
                    <span class="font-bold text-amber-400">@yield('title', 'Admin Operations')</span>
                </div>

                <!-- Right: Live System Indicators -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Event Status Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/90 border border-slate-800 text-[11px] text-slate-300 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-semibold">Shared Pool: 450 Seats</span>
                        <span class="text-slate-600">&bull;</span>
                        <span class="text-amber-400 font-mono font-bold">17 Jan, 7PM</span>
                    </div>

                    <!-- Public Site Shortcut -->
                    <a href="{{ route('home') }}" target="_blank"
                        class="px-3 py-1 rounded-full bg-slate-900 border border-slate-800 hover:border-slate-700 text-[11px] font-semibold text-slate-300 hover:text-white transition flex items-center gap-1.5">
                        <span>Public Page</span>
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash Message Alerts -->
        @if (session('success'))
            <div class="mx-6 mt-6 p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 flex items-center justify-between text-xs font-semibold shadow-lg shadow-emerald-950/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</div>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-6 p-4 rounded-2xl bg-rose-950/80 border border-rose-500/40 text-rose-300 flex items-center justify-between text-xs font-semibold shadow-lg shadow-rose-950/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">&times;</div>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200">&times;</button>
            </div>
        @endif

        <!-- Main View Container -->
        <main class="p-6 md:p-8 flex-grow">
            @yield('content')
        </main>
    </div>
</body>
</html>
