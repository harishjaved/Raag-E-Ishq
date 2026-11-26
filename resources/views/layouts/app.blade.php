<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Raag-E-Ishq Featuring Kuldeep Sagar | Joygivers')</title>
    <meta name="description" content="@yield('meta_description', 'An evening celebrating timeless Hindi melodies with a live orchestra, in support of girls’ education. 17 January 2027, 7:00 PM at Chavara Hall, Kochi.')">

    <!-- OpenGraph & WhatsApp Rich Link Preview Meta Tags -->
    <meta property="og:site_name" content="Joygivers — Raag-E-Ishq">
    <meta property="og:title" content="@yield('og_title', 'Raag-E-Ishq Featuring Kuldeep Sagar | Joygivers')">
    <meta property="og:description" content="@yield('og_description', 'Timeless melodies. Brighter tomorrows. Sunday, 17 January 2027 • 7:00 PM • Chavara Hall, Kochi. Proceeds support girls’ education.')">
    <meta property="og:image" content="@yield('og_image', asset('images/hero-stage-desktop.jpg'))">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Raag-E-Ishq Featuring Kuldeep Sagar | Joygivers')">
    <meta name="twitter:description" content="@yield('og_description', '17 January 2027 • 7:00 PM • Chavara Hall, Kochi. An evening of timeless Hindi melodies for girls’ education.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/hero-stage-desktop.jpg'))">

    <!-- Fonts: Cormorant Garamond, Playfair Display, Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        burgundy: {
                            950: '#24050B',
                            900: '#3B0A14',
                            800: '#4A0E1B',
                            700: '#641728',
                        },
                        gold: {
                            300: '#EBD383',
                            400: '#E5C158',
                            500: '#D4AF37',
                            600: '#C59B27',
                            700: '#B8860B',
                        },
                        ivory: {
                            50: '#FAF6F0',
                            100: '#F5EFE6',
                            200: '#EBE0D0',
                        },
                        espresso: '#231715',
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', '"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: #FAF6F0;
            color: #231715;
        }

        .font-serif-display {
            font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
        }

        .btn-antique-gold {
            background: linear-gradient(135deg, #E5C158 0%, #D4AF37 48%, #B8860B 100%);
            color: #231715;
            border: 1px solid rgba(255, 240, 195, 0.45);
        }
        .btn-antique-gold:hover {
            filter: brightness(1.06);
        }

        .btn-deep-burgundy {
            background-color: #3B0A14;
            color: #FAF6F0;
            border: 1px solid rgba(212, 175, 55, 0.45);
        }
        .btn-deep-burgundy:hover {
            background-color: #4A0E1B;
        }

        /* Accordion accessible summary & animation */
        details.faq-item > summary {
            list-style: none;
            cursor: pointer;
        }
        details.faq-item > summary::-webkit-details-marker {
            display: none;
        }
        details.faq-item[open] > summary .faq-chevron {
            transform: rotate(180deg);
        }
        .faq-chevron {
            transition: transform 0.2s ease;
        }

        dialog:modal {
            overscroll-behavior: contain;
        }
        dialog::backdrop {
            background: rgba(36, 5, 11, 0.82);
            backdrop-filter: blur(4px);
            overflow: hidden;
            overscroll-behavior: contain;
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
@php
    $isLandingPage = request()->routeIs('home') || request()->routeIs('event.show');
    $baseHref = $isLandingPage ? '' : route('home');
@endphp
<body id="top" class="min-h-full flex flex-col bg-[#FAF6F0] text-[#231715] antialiased selection:bg-[#D4AF37]/30 selection:text-[#3B0A14]">

    <!-- Slim Sticky Header & Navigation Bar -->
    <header class="sticky top-0 z-50 bg-[#2C060E]/95 text-[#FAF6F0] border-b border-[#D4AF37]/30 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex items-center justify-between gap-4">
            <!-- Left: Lotus Emblem + Joygivers -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group flex-shrink-0" aria-label="Joygivers Home">
                <svg class="w-7 h-7 text-[#E5C158] transition-transform group-hover:scale-105" viewBox="0 0 48 36" fill="none" aria-hidden="true">
                    <path d="M24 4C21.5 10.5 19.5 16.5 24 24C28.5 16.5 26.5 10.5 24 4Z" stroke="currentColor" stroke-width="1.6" fill="currentColor" fill-opacity="0.16"/>
                    <path d="M24 24C16.5 20.5 12.5 15 11.5 9.5C16.5 11 20.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M24 24C31.5 20.5 35.5 15 36.5 9.5C31.5 11 27.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M24 25C15 24.5 9.5 21 5.5 17C10.5 16.5 17 18.5 24 25Z" stroke="currentColor" stroke-width="1.4"/>
                    <path d="M24 25C33 24.5 38.5 21 42.5 17C37.5 16.5 31 18.5 24 25Z" stroke="currentColor" stroke-width="1.4"/>
                    <path d="M12 29.5H36" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                <div class="border-l border-[#D4AF37]/30 pl-2.5">
                    <span class="font-serif-display text-lg sm:text-xl font-semibold tracking-wide text-[#FAF6F0]">Joygivers</span>
                </div>
            </a>

            <!-- Center Desktop Links -->
            <nav aria-label="Primary Navigation" class="hidden lg:flex items-center gap-6 text-[13px] font-medium text-[#E8DFD3]">
                <a href="{{ $baseHref }}#top" class="hover:text-[#E5C158] transition-colors whitespace-nowrap">Home</a>
                <a href="{{ $baseHref }}#the-event" class="hover:text-[#E5C158] transition-colors whitespace-nowrap">The Event</a>
                <a href="{{ $baseHref }}#artist" class="hover:text-[#E5C158] transition-colors whitespace-nowrap">Artist</a>
                <a href="{{ $baseHref }}#the-cause" class="hover:text-[#E5C158] transition-colors whitespace-nowrap">The Cause</a>
                <a href="{{ $baseHref }}#tickets" class="hover:text-[#E5C158] transition-colors whitespace-nowrap">Tickets</a>
                <a href="{{ $baseHref }}#faqs" class="hover:text-[#E5C158] transition-colors whitespace-nowrap">FAQs</a>
                <a href="{{ $baseHref }}#contact" class="hover:text-[#E5C158] transition-colors whitespace-nowrap">Contact</a>
            </nav>

            <!-- Right Actions: Compact Gold CTA + Mobile Drawer Trigger -->
            <div class="flex items-center gap-2.5 flex-shrink-0">
                <a href="{{ $baseHref }}#register"
                    class="btn-antique-gold inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg transition whitespace-nowrap">
                    <span>Reserve Seats</span>
                </a>

                <button type="button"
                    id="mobile_nav_trigger"
                    aria-label="Open navigation menu"
                    aria-controls="mobile_nav_drawer"
                    aria-expanded="false"
                    onclick="openMobileNav()"
                    class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#D4AF37]/40 text-[#E5C158] hover:bg-[#4A0E1B] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Accessible Mobile Navigation Drawer -->
    <dialog id="mobile_nav_drawer" closedby="any" aria-label="Mobile Navigation"
        class="ml-auto mr-0 my-0 h-full max-h-none w-[min(82vw,19rem)] bg-[#2C060E] text-[#FAF6F0] border-l border-[#D4AF37]/30 p-5">
        <div class="flex flex-col h-full justify-between">
            <div class="space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#D4AF37]/25">
                    <span class="font-serif-display text-lg font-semibold text-[#E5C158]">Raag-E-Ishq</span>
                    <button type="button" onclick="closeMobileNav()" aria-label="Close navigation menu"
                        class="w-8 h-8 rounded-lg border border-[#D4AF37]/35 text-[#FAF6F0] flex items-center justify-center hover:bg-[#4A0E1B]">
                        &times;
                    </button>
                </div>
                <nav class="flex flex-col gap-1.5 text-sm font-medium text-[#E8DFD3]">
                    <a href="{{ $baseHref }}#top" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">Home</a>
                    <a href="{{ $baseHref }}#the-event" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">The Event</a>
                    <a href="{{ $baseHref }}#artist" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">Artist</a>
                    <a href="{{ $baseHref }}#the-cause" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">The Cause</a>
                    <a href="{{ $baseHref }}#tickets" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">Tickets</a>
                    <a href="{{ $baseHref }}#donate" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">Support the Cause</a>
                    <a href="{{ $baseHref }}#faqs" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">FAQs</a>
                    <a href="{{ $baseHref }}#contact" onclick="closeMobileNav()" class="px-3 py-2 rounded-lg hover:bg-[#4A0E1B] hover:text-[#E5C158]">Contact</a>
                </nav>
            </div>
            <a href="{{ $baseHref }}#register" onclick="closeMobileNav()"
                class="btn-antique-gold w-full py-2.5 px-4 rounded-lg text-center text-xs font-semibold">
                Reserve Seats
            </a>
        </div>
    </dialog>

    <main class="flex-grow">
        @if (session('success'))
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="p-4 rounded-xl bg-white border border-[#C59B27] text-[#231715] text-sm flex items-start gap-3">
                    <span class="w-5 h-5 rounded-full bg-[#3B0A14] text-[#E5C158] flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">&#10003;</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="max-w-5xl mx-auto px-4 pt-4">
                <div class="p-4 rounded-xl bg-[#FDF2F4] border border-[#641728]/40 text-[#3B0A14] text-sm">
                    <div class="font-semibold mb-1">Please review the details below:</div>
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

    <!-- Contact Footer (Deep Burgundy) -->
    <footer id="contact" class="scroll-mt-16 bg-[#2C060E] text-[#E8DFD3] border-t border-[#D4AF37]/30 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid gap-8 md:grid-cols-3">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#E5C158]" viewBox="0 0 48 36" fill="none" aria-hidden="true">
                            <path d="M24 4C21.5 10.5 19.5 16.5 24 24C28.5 16.5 26.5 10.5 24 4Z" stroke="currentColor" stroke-width="1.6" fill="currentColor" fill-opacity="0.16"/>
                            <path d="M24 24C16.5 20.5 12.5 15 11.5 9.5C16.5 11 20.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M24 24C31.5 20.5 35.5 15 36.5 9.5C31.5 11 27.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        <span class="font-serif-display text-xl font-semibold text-[#FAF6F0]">Joygivers Presents: <em class="text-[#E5C158]">Raag-E-Ishq</em></span>
                    </div>
                    <p class="text-xs leading-relaxed text-[#D8CBB8] max-w-sm">
                        An evening celebrating timeless Hindi melodies with a live orchestra, in support of girls’ education across Kerala.
                    </p>
                </div>

                <div class="space-y-2.5 text-xs">
                    <h3 class="font-serif-display text-base font-semibold text-[#E5C158]">Venue &amp; Official Contact</h3>
                    <p>Chavara Cultural Centre (Chavara Hall)<br>Near South Railway Station, Kochi, Kerala</p>
                    <p>
                        WhatsApp Support:
                        <a href="https://wa.me/917736606506" target="_blank" rel="noopener noreferrer" class="text-[#E5C158] font-semibold hover:underline">+91 77366 06506</a>
                    </p>
                    <p>
                        Email:
                        <a href="mailto:events@joygivers.org" class="text-[#FAF6F0] hover:text-[#E5C158] underline-offset-2 hover:underline">events@joygivers.org</a>
                    </p>
                </div>

                <div class="space-y-2.5 text-xs">
                    <h3 class="font-serif-display text-base font-semibold text-[#E5C158]">Quick Access &amp; Policies</h3>
                    <ul class="space-y-1.5">
                        <li><a href="{{ route('tickets.resend.show') }}" class="hover:text-[#E5C158] transition">Retrieve Existing Passes &rarr;</a></li>
                        <li><a href="{{ route('event.calendar') }}" class="hover:text-[#E5C158] transition">Add Event to Calendar (.ics) &rarr;</a></li>
                        <li><button type="button" onclick="document.getElementById('privacy_policy_dialog')?.showModal()" class="hover:text-[#E5C158] underline-offset-2 hover:underline">Privacy Policy &amp; Terms</button></li>
                        <li><a href="{{ route('login') }}" class="text-[#BFA89A] hover:text-[#E5C158] transition">Staff &amp; Gate Sign In</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-[#D4AF37]/20 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-[#C8B8AA]">
                <p>&copy; 2026&ndash;2027 Joygivers Charitable Initiative &bull; Kochi, Kerala</p>
                <p>17 January 2027 (Sunday) &bull; Gates 6:15 PM &bull; Programme 7:00 PM Onwards</p>
            </div>
        </div>
    </footer>

    <!-- Privacy Policy & Booking Terms Native Modal -->
    <dialog id="privacy_policy_dialog" closedby="any" aria-labelledby="privacy_dialog_title"
        class="m-auto w-[min(92vw,38rem)] max-h-[85vh] overflow-y-auto rounded-xl border border-[#D4AF37]/50 bg-[#FAF6F0] text-[#231715] p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4 pb-3 border-b border-[#E3D8C8]">
            <div>
                <div class="text-[10px] uppercase tracking-[0.18em] text-[#7A5C2E] font-semibold">Joygivers Policy Notice</div>
                <h2 id="privacy_dialog_title" class="font-serif-display text-2xl font-semibold text-[#3B0A14]">Privacy Policy &amp; Event Terms</h2>
            </div>
            <form method="dialog">
                <button type="submit" aria-label="Close privacy policy" class="w-8 h-8 rounded-full border border-[#D4AF37]/50 text-[#3B0A14] hover:bg-[#3B0A14] hover:text-[#FAF6F0] flex items-center justify-center transition">&times;</button>
            </form>
        </div>
        <div class="mt-4 space-y-3 text-xs leading-relaxed text-[#42322D]">
            <p><strong>Data Collection &amp; Use:</strong> Guest names, WhatsApp numbers, and email addresses are used solely to issue your unique Booking Reference, verify payment, deliver QR Entry Passes, and coordinate gate entry at Chavara Hall.</p>
            <p><strong>Marketing Opt-In:</strong> Future Joygivers cultural event updates are strictly opt-in and unchecked by default.</p>
            <p><strong>Charitable Booking &amp; Transfer Policy:</strong> As this is a charitable fundraising event, all confirmed and paid bookings are non-refundable. Ticket transfers to another guest are permitted prior to the event; in the unlikely event of cancellation or postponement, full refunds or rescheduled dates will be offered.</p>
            <p><strong>Data Security:</strong> Payment proofs and patron records are stored securely with role-restricted back-office access. Gate volunteers can only verify admission QR passes and cannot view financial ledgers or exports.</p>
        </div>
    </dialog>

    <script>
        function openMobileNav() {
            const drawer = document.getElementById('mobile_nav_drawer');
            const trigger = document.getElementById('mobile_nav_trigger');
            if (drawer && !drawer.open) {
                drawer.showModal();
                trigger?.setAttribute('aria-expanded', 'true');
            }
        }
        function closeMobileNav() {
            const drawer = document.getElementById('mobile_nav_drawer');
            const trigger = document.getElementById('mobile_nav_trigger');
            if (drawer?.open) drawer.close();
            trigger?.setAttribute('aria-expanded', 'false');
        }
        document.getElementById('mobile_nav_drawer')?.addEventListener('close', () => {
            document.getElementById('mobile_nav_trigger')?.setAttribute('aria-expanded', 'false');
        });

        // Light-dismiss fallback for browsers without native closedby="any"
        if (typeof HTMLDialogElement !== 'undefined' && !('closedBy' in HTMLDialogElement.prototype)) {
            document.querySelectorAll('dialog[closedby="any"]').forEach((dialog) => {
                let pressedOut = false;
                const isOutside = (e) => {
                    const r = dialog.getBoundingClientRect();
                    return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
                };
                dialog.addEventListener('pointerdown', (e) => { pressedOut = isOutside(e); });
                dialog.addEventListener('pointerup', (e) => { if (pressedOut && isOutside(e)) dialog.close(); });
            });
        }
    </script>
</body>
</html>
