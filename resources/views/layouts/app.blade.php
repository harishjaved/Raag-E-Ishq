<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Raag-E-Ishq — Live Musical Evening | Joygivers')</title>

    <!-- OpenGraph Meta Tags for Rich WhatsApp Previews -->
    <meta property="og:title" content="@yield('title', 'Raag-E-Ishq — Live Musical Evening | Joygivers')">
    <meta property="og:description" content="Official Admission Pass for Raag-E-Ishq at Chavara Hall, Kochi. Organized by Joygivers for girl education.">
    <meta property="og:image" content="{{ asset('images/music-hero-bg.jpg') }}">
    <meta property="og:type" content="website">

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
            color: #0f172a !important;
            font-weight: 700 !important;
        }
        html.dark .theme-text-sub {
            color: #e2e8f0 !important;
            font-weight: 700 !important;
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

        /* ==========================================================================
           MUSIC THEME DYNAMIC MOVING BACKGROUND
           ========================================================================== */
        .music-bg-container {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
            user-select: none;
        }

        /* 1. Cinematic Moving Image Layer (Ken-Burns Pan & Breathe) */
        .music-bg-canvas {
            position: absolute;
            top: -6%;
            left: -6%;
            width: 112%;
            height: 112%;
            background-image: url('/images/music-hero-bg.jpg');
            background-size: cover;
            background-position: center 20%;
            background-repeat: no-repeat;
            animation: musicHeroDrift 28s ease-in-out infinite alternate;
            will-change: transform;
        }

        @keyframes musicHeroDrift {
            0% {
                transform: scale(1.02) translate3d(0, 0, 0) rotate(0deg);
            }
            33% {
                transform: scale(1.07) translate3d(-1.5%, -1.2%, 0) rotate(0.2deg);
            }
            66% {
                transform: scale(1.04) translate3d(1.2%, -0.8%, 0) rotate(-0.15deg);
            }
            100% {
                transform: scale(1.09) translate3d(-0.8%, -1.5%, 0) rotate(0.1deg);
            }
        }

        /* 2. Theme-Adaptive Gradient Veil (Preserves pristine text contrast) */
        html.dark .music-bg-veil {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(ellipse 95% 70% at 50% 28%, rgba(8, 11, 18, 0.65) 0%, rgba(8, 11, 18, 0.88) 60%, #080b12 100%),
                linear-gradient(180deg, rgba(8, 11, 18, 0.72) 0%, rgba(8, 11, 18, 0.45) 30%, rgba(8, 11, 18, 0.88) 75%, #080b12 100%);
        }

        html.light .music-bg-veil {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(ellipse 95% 70% at 50% 28%, rgba(255, 255, 255, 0.82) 0%, rgba(248, 250, 252, 0.92) 60%, #f8fafc 100%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.8) 0%, rgba(248, 250, 252, 0.65) 30%, rgba(241, 245, 249, 0.92) 75%, #f8fafc 100%);
        }

        /* 3. Floating Musical Notes Particles */
        .music-notes-field {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .music-note {
            position: absolute;
            user-select: none;
            pointer-events: none;
            font-family: 'Times New Roman', serif;
            font-weight: bold;
            animation: floatNote 18s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            will-change: transform, opacity;
        }

        @keyframes floatNote {
            0% {
                transform: translate3d(0, 105vh, 0) rotate(0deg) scale(0.7);
                opacity: 0;
            }
            15% {
                opacity: var(--note-opacity, 0.35);
            }
            50% {
                transform: translate3d(var(--note-sway, 30px), 50vh, 0) rotate(var(--note-rot, 20deg)) scale(1.1);
                opacity: var(--note-opacity, 0.5);
            }
            85% {
                opacity: var(--note-opacity, 0.3);
            }
            100% {
                transform: translate3d(calc(var(--note-sway, 30px) * -1), -10vh, 0) rotate(calc(var(--note-rot, 20deg) * 2)) scale(0.85);
                opacity: 0;
            }
        }

        html.dark .music-note {
            color: #f59e0b;
            text-shadow: 0 0 10px rgba(245, 158, 11, 0.5), 0 0 20px rgba(225, 29, 72, 0.35);
        }

        html.light .music-note {
            color: #e11d48;
            text-shadow: 0 0 8px rgba(245, 158, 11, 0.35);
        }

        /* 4. Undulating Harmonic Soundwave Ribbons */
        .music-wave {
            position: absolute;
            width: 200%;
            height: 100%;
            top: 20%;
            left: -50%;
            animation: waveHarmonic 22s ease-in-out infinite alternate;
            will-change: transform;
        }

        .music-wave-2 {
            top: 35%;
            animation-duration: 26s;
            animation-delay: -5s;
        }

        @keyframes waveHarmonic {
            0% {
                transform: translate3d(-5%, 0, 0) scaleY(1);
            }
            50% {
                transform: translate3d(5%, 2.5%, 0) scaleY(1.12);
            }
            100% {
                transform: translate3d(-3%, -2%, 0) scaleY(0.95);
            }
        }

        /* 5. Audio Frequency Equalizer Bars */
        .music-bar {
            display: inline-block;
            width: 3px;
            border-radius: 9999px;
            background: linear-gradient(180deg, #f59e0b, #e11d48, #9333ea);
            animation: barDance 1.2s ease-in-out infinite alternate;
            transform-origin: bottom;
        }
        .bar-1  { height: 10px; animation-delay: 0.1s; }
        .bar-2  { height: 20px; animation-delay: 0.35s; }
        .bar-3  { height: 8px;  animation-delay: 0.5s; }
        .bar-4  { height: 24px; animation-delay: 0.2s; }
        .bar-5  { height: 14px; animation-delay: 0.65s; }
        .bar-6  { height: 28px; animation-delay: 0.4s; }
        .bar-7  { height: 16px; animation-delay: 0.25s; }
        .bar-8  { height: 22px; animation-delay: 0.55s; }
        .bar-9  { height: 9px;  animation-delay: 0.15s; }
        .bar-10 { height: 26px; animation-delay: 0.3s; }
        .bar-11 { height: 12px; animation-delay: 0.45s; }
        .bar-12 { height: 18px; animation-delay: 0.05s; }

        @keyframes barDance {
            0% { transform: scaleY(0.25); opacity: 0.6; }
            50% { transform: scaleY(1); opacity: 1; }
            100% { transform: scaleY(0.4); opacity: 0.75; }
        }

        /* Reduced Motion Fallback */
        @media (prefers-reduced-motion: reduce) {
            .music-bg-canvas,
            .music-note,
            .music-wave,
            .music-bar {
                animation: none !important;
            }
        }

        @media print {
            header, footer, nav, .no-print, .music-bg-container {
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
<body class="min-h-full flex flex-col transition-colors duration-200 relative">
    <!-- Dynamic Moving Music Theme Background -->
    <div class="music-bg-container" aria-hidden="true">
        <!-- 1. Cinematic Moving Image Layer -->
        <div class="music-bg-canvas"></div>

        <!-- 2. Dual-Mode Harmonizing Veil -->
        <div class="music-bg-veil"></div>

        <!-- 3. Undulating Melodic Soundwave Ribbons -->
        <svg class="music-wave music-wave-1 opacity-25 dark:opacity-30" viewBox="0 0 1440 320" preserveAspectRatio="none" fill="none">
            <path d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,229.3C1248,224,1344,160,1392,128L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z" fill="url(#wave-grad-1)"></path>
            <defs>
                <linearGradient id="wave-grad-1" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.5"/>
                    <stop offset="50%" stop-color="#e11d48" stop-opacity="0.35"/>
                    <stop offset="100%" stop-color="#7c3aed" stop-opacity="0.5"/>
                </linearGradient>
            </defs>
        </svg>

        <svg class="music-wave music-wave-2 opacity-20 dark:opacity-25" viewBox="0 0 1440 320" preserveAspectRatio="none" fill="none">
            <path d="M0,64L48,80C96,96,192,128,288,149.3C384,171,480,181,576,165.3C672,149,768,107,864,117.3C960,128,1056,192,1152,202.7C1248,213,1344,171,1392,149.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z" fill="url(#wave-grad-2)"></path>
            <defs>
                <linearGradient id="wave-grad-2" x1="100%" y1="0%" x2="0%" y2="0%">
                    <stop offset="0%" stop-color="#9333ea" stop-opacity="0.45"/>
                    <stop offset="50%" stop-color="#e11d48" stop-opacity="0.25"/>
                    <stop offset="100%" stop-color="#e5a93b" stop-opacity="0.45"/>
                </linearGradient>
            </defs>
        </svg>

        <!-- 4. Floating Musical Notes Particles (♪ ♫ ♬ 𝄞 ♩) -->
        <div class="music-notes-field">
            <span class="music-note" style="left: 6%; font-size: 28px; --note-opacity: 0.42; --note-sway: 35px; --note-rot: 25deg; animation-duration: 17s; animation-delay: 0s;">𝄞</span>
            <span class="music-note" style="left: 17%; font-size: 20px; --note-opacity: 0.35; --note-sway: -25px; --note-rot: -15deg; animation-duration: 21s; animation-delay: 3s;">♪</span>
            <span class="music-note" style="left: 27%; font-size: 24px; --note-opacity: 0.45; --note-sway: 40px; --note-rot: 30deg; animation-duration: 16s; animation-delay: 7s;">♫</span>
            <span class="music-note" style="left: 39%; font-size: 19px; --note-opacity: 0.32; --note-sway: -30px; --note-rot: -20deg; animation-duration: 24s; animation-delay: 2s;">♬</span>
            <span class="music-note" style="left: 52%; font-size: 30px; --note-opacity: 0.4; --note-sway: 30px; --note-rot: 18deg; animation-duration: 19s; animation-delay: 9s;">𝄞</span>
            <span class="music-note" style="left: 65%; font-size: 22px; --note-opacity: 0.36; --note-sway: -35px; --note-rot: -25deg; animation-duration: 20s; animation-delay: 5s;">♫</span>
            <span class="music-note" style="left: 76%; font-size: 19px; --note-opacity: 0.38; --note-sway: 25px; --note-rot: 15deg; animation-duration: 18s; animation-delay: 11s;">♪</span>
            <span class="music-note" style="left: 87%; font-size: 24px; --note-opacity: 0.42; --note-sway: -40px; --note-rot: -30deg; animation-duration: 22s; animation-delay: 1.5s;">♬</span>
            <span class="music-note" style="left: 94%; font-size: 18px; --note-opacity: 0.3; --note-sway: 20px; --note-rot: 20deg; animation-duration: 25s; animation-delay: 8s;">♩</span>
        </div>
    </div>

    <!-- Main Top Navigation -->
    <header class="border-b transition-colors duration-200 sticky top-0 z-50 backdrop-blur-md relative dark:border-slate-800/80 dark:bg-slate-950/80 light:border-slate-200 light:bg-white/80">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center font-extrabold text-sm shadow-md shadow-rose-500/20">
                    REI
                </span>
                <div>
                    <div class="text-[10px] uppercase tracking-widest text-amber-500 dark:text-amber-400 font-bold">Joygivers Presents</div>
                    <div class="text-base font-extrabold font-serif-display tracking-wide dark:text-slate-100 text-slate-900">Raag-E-Ishq</div>
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

    <main class="flex-grow relative z-10">
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
    <footer class="border-t mt-20 py-10 transition-colors duration-200 relative z-10 dark:border-slate-800/80 dark:bg-slate-950/60 light:border-slate-200 light:bg-slate-100/60">
        <div class="max-w-5xl mx-auto px-4 text-center space-y-3">
            <div class="flex items-center justify-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400 to-rose-500 text-white flex items-center justify-center font-bold text-xs">REI</span>
                <span class="font-serif-display font-bold text-sm tracking-wide dark:text-slate-200 text-slate-800">Joygivers Presents — Raag-E-Ishq</span>
            </div>
            <p class="text-xs dark:text-slate-400 text-slate-600 max-w-md mx-auto">
                A grand musical evening of timeless Hindi melodies at Chavara Hall, Kochi. Organized for girl education and welfare.
            </p>
            <p class="text-[11px] dark:text-slate-500 text-slate-500">
                17th January &bull; 7:00 PM &bull; Total Hall Capacity: 450 Seats &bull; All verified bookings strictly non-refundable.
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
