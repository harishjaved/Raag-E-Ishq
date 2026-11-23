<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Raage-E-Ishq — Live Musical Evening | Joygivers')</title>

    <!-- Theme Preference Initialization (Prevents theme flash) -->
    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme === 'light' || (!storedTheme && window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches)) {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                } else {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                }
            } catch (e) {}
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            pink: '#ff2d75',
                            rose: '#e11d48',
                            amber: '#f59e0b',
                            gold: '#e5a93b',
                            purple: '#9333ea',
                            violet: '#7c3aed',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-display { font-family: 'Playfair Display', serif; }

        /* Dark / Light Global Theme Stylings */
        html.dark body {
            background-color: #080b12 !important;
            color: #f8fafc !important;
        }
        html.light body {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }

        html.light .theme-bg-surface {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        html.dark .theme-bg-surface {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
        }

        html.light .theme-text-title {
            color: #0f172a !important;
        }
        html.dark .theme-text-title {
            color: #f8fafc !important;
        }

        html.light .theme-text-sub {
            color: #475569 !important;
        }
        html.dark .theme-text-sub {
            color: #94a3b8 !important;
        }

        html.light .theme-input {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        html.dark .theme-input {
            background-color: #020617 !important;
            border-color: #1e293b !important;
            color: #f8fafc !important;
        }

        @media print {
            header, footer, nav, .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-full flex flex-col transition-colors duration-200">
    <!-- Main Top Navigation -->
    <header class="border-b transition-colors duration-200 sticky top-0 z-50 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-950/80 light:border-slate-200 light:bg-white/80">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-rose-500/20">
                    REI
                </span>
                <div>
                    <div class="text-[10px] uppercase tracking-widest text-amber-500 dark:text-amber-400 font-bold">Joygivers Presents</div>
                    <div class="text-base font-extrabold font-serif-display tracking-wide dark:text-slate-100 text-slate-900">Raage-E-Ishq</div>
                </div>
            </a>

            <!-- Right Navigation Actions -->
            <div class="flex items-center gap-3">
                <!-- Theme Toggle Button -->
                <button id="theme_toggle_btn" onclick="toggleTheme()" aria-label="Toggle Theme"
                    class="p-2 rounded-xl border transition-all duration-200 flex items-center justify-center dark:border-slate-800 dark:bg-slate-900/60 dark:hover:bg-slate-800 dark:text-amber-400 light:border-slate-200 light:bg-slate-100 light:hover:bg-slate-200 light:text-slate-700 shadow-sm">
                    <!-- Sun Icon (Active in dark mode -> switch to light) -->
                    <svg id="theme_icon_sun" class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon Icon (Active in light mode -> switch to dark) -->
                    <svg id="theme_icon_moon" class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <!-- Book Tickets CTA -->
                <a href="#register"
                    class="text-xs font-bold px-4 py-2 rounded-full bg-gradient-to-r from-amber-500 to-rose-500 hover:from-amber-400 hover:to-rose-400 text-white shadow-md shadow-rose-500/20 transition transform active:scale-95">
                    Reserve Seats
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        @if (session('success'))
            <div class="max-w-4xl mx-auto px-4 mt-4">
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3 shadow">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="max-w-4xl mx-auto px-4 mt-4">
                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm shadow">
                    <div class="font-bold mb-1">Please check the following:</div>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="border-t mt-20 py-10 transition-colors duration-200 dark:border-slate-800/80 dark:bg-slate-950/60 light:border-slate-200 light:bg-slate-100/60">
        <div class="max-w-5xl mx-auto px-4 text-center space-y-3">
            <div class="flex items-center justify-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400 to-rose-500 text-white flex items-center justify-center font-bold text-xs">REI</span>
                <span class="font-serif-display font-bold text-sm tracking-wide dark:text-slate-200 text-slate-800">Joygivers Presents — Raage-E-Ishq</span>
            </div>
            <p class="text-xs dark:text-slate-400 text-slate-600 max-w-md mx-auto">
                A grand musical evening of timeless Hindi melodies at Chavara Hall, Kochi. Organized to support child girls' education and welfare.
            </p>
            <p class="text-[11px] dark:text-slate-500 text-slate-500">
                17th December &bull; 7:00 PM &bull; Total Hall Capacity: 450 Seats &bull; All verified bookings strictly non-refundable.
            </p>
        </div>
    </footer>

    <!-- Theme Toggle Logic Script -->
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.remove('light');
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</body>
</html>
