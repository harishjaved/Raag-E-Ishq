<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Sagar Kinare — Joygivers')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ocean: {
                            800: '#0c2238',
                            900: '#071624',
                            950: '#030c14',
                        },
                        amberGold: '#e5a93b',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-display { font-family: 'Playfair Display', serif; }
        @media print {
            header, footer, .no-print {
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
<body class="min-h-full flex flex-col bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    <header class="border-b border-slate-800/80 bg-slate-950/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm border border-amber-500/30">SK</span>
                <div>
                    <div class="text-xs uppercase tracking-widest text-amber-400 font-semibold">Joygivers Presents</div>
                    <div class="text-base font-bold text-slate-100">Sagar Kinare</div>
                </div>
            </a>
            <div class="flex items-center gap-3">
                <a href="#register" class="text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 transition font-medium">Book Tickets</a>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        @if (session('success'))
            <div class="max-w-4xl mx-auto px-4 mt-4">
                <div class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="max-w-4xl mx-auto px-4 mt-4">
                <div class="p-4 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-200 text-sm">
                    <div class="font-semibold mb-1">Please correct the following:</div>
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

    <footer class="border-t border-slate-800 mt-16 py-8 text-center text-xs text-slate-500">
        <div class="max-w-4xl mx-auto px-4 space-y-2">
            <p class="text-slate-400 font-medium">Joygivers Presents — Sagar Kinare (Kochi, Kerala)</p>
            <p>A musical evening in support of educational initiatives. Total hall capacity: 450 seats.</p>
            <p class="text-slate-600">All payments processed via Razorpay. Tickets are issued only upon verified gateway confirmation.</p>
        </div>
    </footer>
</body>
</html>
