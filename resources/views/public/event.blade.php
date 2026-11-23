@extends('layouts.app')

@section('title', 'Raage-E-Ishq — Live Musical Evening | Joygivers')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 sm:py-10 space-y-16">
    <!-- Hero / Concert Spotlight Header -->
    <section class="relative text-center space-y-6 pt-2 pb-6 overflow-hidden">
        <!-- Ambient Concert Glow Effects -->
        <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-96 h-96 bg-gradient-to-tr from-rose-500/20 via-purple-500/20 to-amber-500/20 blur-3xl -z-10 rounded-full pointer-events-none"></div>

        <!-- Pill Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-purple-500/10 border border-amber-500/30 text-amber-500 dark:text-amber-400 text-xs font-bold uppercase tracking-widest shadow-sm">
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            <span>Joygivers Presents</span>
        </div>

        <!-- Main Headline -->
        <div class="space-y-3">
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-extrabold tracking-tight font-serif-display dark:text-slate-100 text-slate-900 drop-shadow-sm">
                Raage-E-Ishq
            </h1>
            <p class="text-base sm:text-xl dark:text-slate-300 text-slate-700 max-w-2xl mx-auto font-medium leading-relaxed">
                A celebration of timeless Hindi melodies in Kochi, to support child girls.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a href="#register"
                class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 via-rose-500 to-purple-600 hover:from-amber-400 hover:to-rose-400 text-white font-bold text-sm tracking-wide transition shadow-xl shadow-rose-500/20 flex items-center gap-2 transform active:scale-95">
                <span>Reserve Seats &amp; Book Tickets</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            </a>
            <a href="#seating-map"
                class="px-5 py-3.5 rounded-2xl border transition text-xs font-bold flex items-center gap-2 dark:border-slate-800 dark:bg-slate-900/60 dark:hover:bg-slate-800 dark:text-slate-200 light:border-slate-300 light:bg-white light:hover:bg-slate-100 light:text-slate-800 shadow-sm">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>View Seating Layout</span>
            </a>
        </div>

        <!-- Event Quick Facts Grid (4 Highlight Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 max-w-4xl mx-auto pt-6 text-left">
            <!-- Venue -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/80 dark:border-slate-800 light:bg-white light:border-slate-200 shadow-md">
                <div class="text-[11px] uppercase font-bold tracking-wider text-rose-500">Venue</div>
                <div class="text-sm font-bold dark:text-slate-100 text-slate-900 mt-1">Chavara Hall</div>
                <div class="text-[11px] dark:text-slate-400 text-slate-500">Near South Rly Stn, Kochi</div>
            </div>

            <!-- Date & Time (17th December, 7pm) -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/80 dark:border-slate-800 light:bg-white light:border-slate-200 shadow-md">
                <div class="text-[11px] uppercase font-bold tracking-wider text-amber-500">Date &amp; Time</div>
                <div class="text-sm font-bold dark:text-slate-100 text-slate-900 mt-1">17th December</div>
                <div class="text-[11px] dark:text-amber-400 text-amber-600 font-semibold">7:00 PM IST Evening</div>
            </div>

            <!-- Noble Cause -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/80 dark:border-slate-800 light:bg-white light:border-slate-200 shadow-md">
                <div class="text-[11px] uppercase font-bold tracking-wider text-purple-500">Noble Cause</div>
                <div class="text-sm font-bold dark:text-slate-100 text-slate-900 mt-1">To Support Child Girls</div>
                <div class="text-[11px] dark:text-slate-400 text-slate-500">Education &amp; Welfare</div>
            </div>

            <!-- Capacity -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/80 dark:border-slate-800 light:bg-white light:border-slate-200 shadow-md">
                <div class="text-[11px] uppercase font-bold tracking-wider text-emerald-500">Hall Capacity</div>
                <div class="text-sm font-bold dark:text-slate-100 text-slate-900 mt-1">450 Seats Total</div>
                <div class="text-[11px] text-emerald-500 font-semibold">{{ $globalRemaining }} seats available</div>
            </div>
        </div>
    </section>

    <!-- Interactive Seating Layout & Stage Arc (Inspired by reference UI in Image 3) -->
    <section id="seating-map" class="p-6 sm:p-8 rounded-3xl border transition-all dark:bg-slate-900/70 dark:border-slate-800 light:bg-white light:border-slate-200 shadow-xl space-y-6">
        <div class="text-center space-y-1">
            <span class="text-[11px] uppercase font-bold tracking-widest text-rose-500">Auditorium Seating Architecture</span>
            <h2 class="text-2xl font-bold dark:text-slate-100 text-slate-900 font-serif-display">Stage &amp; Seating Plan</h2>
            <p class="text-xs dark:text-slate-400 text-slate-600">Select any category to view tier positions or book directly.</p>
        </div>

        <!-- Visual Auditorium Graphic -->
        <div class="max-w-lg mx-auto py-4 text-center space-y-4">
            <!-- Stage Box -->
            <div class="mx-auto w-48 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-rose-600 text-white font-extrabold text-xs tracking-wider shadow-lg shadow-rose-500/20 uppercase">
                Stage &bull; Live Orchestra
            </div>

            <!-- Curved Seating Bowl Graphic -->
            <svg viewBox="0 0 400 210" class="w-full max-w-md mx-auto drop-shadow-md select-none">
                <!-- Tier 1: Front Arc (Gold/Amber) -->
                <path d="M 80 70 A 130 110 0 0 1 320 70" fill="none" stroke="#f59e0b" stroke-width="18" stroke-linecap="round" opacity="0.9"/>
                <text x="200" y="66" text-anchor="middle" fill="#ffffff" font-size="9" font-weight="bold">TIER 1 &bull; VIP (₹2,500)</text>

                <!-- Tier 2: Middle Arc (Rose/Pink) -->
                <path d="M 50 115 A 170 140 0 0 1 350 115" fill="none" stroke="#e11d48" stroke-width="20" stroke-linecap="round" opacity="0.85"/>
                <text x="200" y="111" text-anchor="middle" fill="#ffffff" font-size="9" font-weight="bold">TIER 2 &bull; PREMIUM (₹1,500)</text>

                <!-- Tier 3: Classic Rear Arc (Purple/Violet) -->
                <path d="M 25 165 A 205 165 0 0 1 375 165" fill="none" stroke="#7c3aed" stroke-width="22" stroke-linecap="round" opacity="0.8"/>
                <text x="200" y="161" text-anchor="middle" fill="#ffffff" font-size="9" font-weight="bold">TIER 3 &bull; CLASSIC (₹1,000)</text>

                <!-- Tier 4: Community Arc (Cyan/Emerald) -->
                <path d="M 10 200 A 225 180 0 0 1 390 200" fill="none" stroke="#059669" stroke-width="12" stroke-linecap="round" opacity="0.75"/>
                <text x="200" y="196" text-anchor="middle" fill="#ffffff" font-size="8" font-weight="bold">TIER 4 &bull; COMMUNITY PASS (₹2.00)</text>
            </svg>
        </div>

        <!-- Seating Legend -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs">
            <div class="p-2.5 rounded-xl border flex items-center gap-2.5 dark:border-slate-800 dark:bg-slate-950/60 light:border-slate-200 light:bg-slate-50">
                <span class="w-3.5 h-3.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                <div>
                    <div class="font-bold dark:text-slate-200 text-slate-800">Tier 1: VIP</div>
                    <div class="text-[10px] dark:text-slate-400 text-slate-500">₹2,500 &bull; Front Rows</div>
                </div>
            </div>
            <div class="p-2.5 rounded-xl border flex items-center gap-2.5 dark:border-slate-800 dark:bg-slate-950/60 light:border-slate-200 light:bg-slate-50">
                <span class="w-3.5 h-3.5 rounded-full bg-rose-500 flex-shrink-0"></span>
                <div>
                    <div class="font-bold dark:text-slate-200 text-slate-800">Tier 2: Prime</div>
                    <div class="text-[10px] dark:text-slate-400 text-slate-500">₹1,500 &bull; Middle Circle</div>
                </div>
            </div>
            <div class="p-2.5 rounded-xl border flex items-center gap-2.5 dark:border-slate-800 dark:bg-slate-950/60 light:border-slate-200 light:bg-slate-50">
                <span class="w-3.5 h-3.5 rounded-full bg-purple-600 flex-shrink-0"></span>
                <div>
                    <div class="font-bold dark:text-slate-200 text-slate-800">Tier 3: Classic</div>
                    <div class="text-[10px] dark:text-slate-400 text-slate-500">₹1,000 &bull; Rear Seating</div>
                </div>
            </div>
            <div class="p-2.5 rounded-xl border flex items-center gap-2.5 dark:border-slate-800 dark:bg-slate-950/60 light:border-slate-200 light:bg-slate-50">
                <span class="w-3.5 h-3.5 rounded-full bg-emerald-600 flex-shrink-0"></span>
                <div>
                    <div class="font-bold dark:text-slate-200 text-slate-800">Tier 4: Entry</div>
                    <div class="text-[10px] dark:text-slate-400 text-slate-500">₹2.00 &bull; Special Pass</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admission Categories & Pricing Cards (Inspired by Reference Cards) -->
    <section class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
            <div>
                <span class="text-[11px] uppercase font-bold tracking-widest text-amber-500">Choose Your Experience</span>
                <h2 class="text-2xl font-bold dark:text-slate-100 text-slate-900 font-serif-display">Admission Categories</h2>
            </div>
            <span class="text-xs dark:text-slate-400 text-slate-500">Live Available Quotas &bull; Instant Seat Reservation</span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($categories as $cat)
                @php
                    $isTier1 = str_contains(strtolower($cat->name), 'tier 1');
                    $isTier2 = str_contains(strtolower($cat->name), 'tier 2');
                @endphp
                <div class="relative p-6 rounded-3xl border transition-all flex flex-col justify-between group shadow-lg {{ $isTier1 ? 'dark:border-amber-500/50 dark:bg-gradient-to-b dark:from-slate-900 dark:to-slate-950 light:border-amber-400 light:bg-amber-50/20' : 'dark:border-slate-800 dark:bg-slate-900/80 light:border-slate-200 light:bg-white' }}">
                    @if ($isTier1)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-gradient-to-r from-amber-500 to-rose-500 text-white text-[10px] font-extrabold uppercase tracking-wider shadow">
                            VIP Choice
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-amber-500 dark:text-amber-400">{{ $cat->name }}</span>
                            @if ($cat->available > 0)
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 font-bold">
                                    {{ $cat->available }} left
                                </span>
                            @else
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-rose-500/10 text-rose-500 border border-rose-500/20 font-bold">
                                    Sold Out
                                </span>
                            @endif
                        </div>

                        <div>
                            <div class="text-3xl font-extrabold font-serif-display dark:text-slate-100 text-slate-900">{{ $cat->formatted_price }}</div>
                            <p class="text-xs dark:text-slate-400 text-slate-600 mt-1.5 leading-relaxed">{{ $cat->description }}</p>
                        </div>

                        <ul class="text-[11px] space-y-1.5 pt-2 border-t dark:border-slate-800 border-slate-100 dark:text-slate-400 text-slate-500">
                            <li class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Unique Admission Pass &bull; Admit 1</span>
                            </li>
                            <li class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Cryptographic Gate Scan QR</span>
                            </li>
                            <li class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>100% Proceeds Support Child Girls</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-3 border-t dark:border-slate-800 border-slate-100">
                        <button type="button" onclick="selectCategory({{ $cat->id }})"
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $cat->available > 0 ? 'bg-slate-800 hover:bg-slate-700 text-white dark:bg-slate-800 dark:hover:bg-amber-500 dark:hover:text-slate-950' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}"
                            {{ $cat->available <= 0 ? 'disabled' : '' }}>
                            <span>Select &amp; Book</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Cause Feature Spotlight: To Support Child Girls -->
    <section class="p-8 sm:p-10 rounded-3xl border transition-all dark:bg-gradient-to-r dark:from-slate-900 dark:via-purple-950/30 dark:to-slate-900 dark:border-slate-800 light:bg-gradient-to-r light:from-rose-50 light:via-purple-50 light:to-amber-50 light:border-rose-200 shadow-xl">
        <div class="flex flex-col md:flex-row items-center gap-6 justify-between">
            <div class="space-y-3 max-w-xl text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs font-bold uppercase tracking-wider">
                    Our Core Mission
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold font-serif-display dark:text-slate-100 text-slate-900">
                    Every Melodic Note Sings To Support Child Girls
                </h3>
                <p class="text-xs sm:text-sm dark:text-slate-300 text-slate-700 leading-relaxed">
                    Joygivers believes that every child girl deserves access to quality education, safe shelter, and bright opportunities. All proceeds from <strong>Raage-E-Ishq</strong> are dedicated to our girl-child welfare programmes.
                </p>
                <p class="text-xs dark:text-slate-400 text-slate-600 italic">
                    Unable to attend in person? You can still book your ticket and select "No" for in-person attendance — 100% of your amount will directly fund child girls' education, and you will receive an Official Supporter Receipt.
                </p>
            </div>
            <div class="flex-shrink-0 text-center">
                <a href="#register" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-400 hover:to-amber-400 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-rose-500/20 inline-block">
                    Support The Cause
                </a>
            </div>
        </div>
    </section>

    <!-- Registration Form Desk -->
    <section id="register" class="p-6 sm:p-10 rounded-3xl border transition-all dark:bg-slate-900 dark:border-slate-800 light:bg-white light:border-slate-200 shadow-2xl space-y-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-500 dark:text-amber-400 text-xs font-bold uppercase tracking-wider mb-2">
                <span>Direct Reservations Desk</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold dark:text-slate-100 text-slate-900 font-serif-display">Register &amp; Reserve Seats</h2>
            <p class="text-xs dark:text-slate-400 text-slate-600 mt-1">
                Fill in your details below to place a seat reservation. Our payment team will call you to confirm your booking and send the payment details via WhatsApp or Email.
            </p>
        </div>

        <form action="{{ route('register.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="utm_source" value="{{ $utmParams['utm_source'] }}">
            <input type="hidden" name="utm_medium" value="{{ $utmParams['utm_medium'] }}">
            <input type="hidden" name="utm_campaign" value="{{ $utmParams['utm_campaign'] }}">
            <input type="hidden" name="utm_content" value="{{ $utmParams['utm_content'] }}">

            <div class="grid sm:grid-cols-2 gap-5">
                <!-- Full Name -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold dark:text-slate-200 text-slate-800">Purchaser Full Name <span class="text-amber-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                        placeholder="e.g. Aarav Sharma"
                        class="w-full px-4 py-3 rounded-2xl border text-sm transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-200 light:text-slate-900">
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold dark:text-slate-200 text-slate-800">Email Address (for tickets) <span class="text-amber-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="e.g. aarav@example.com"
                        class="w-full px-4 py-3 rounded-2xl border text-sm transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-200 light:text-slate-900">
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold dark:text-slate-200 text-slate-800">WhatsApp / Phone Number <span class="text-amber-500">*</span></label>
                    <div class="flex gap-2">
                        <span class="inline-flex items-center px-3.5 rounded-2xl border text-xs font-semibold dark:bg-slate-950 dark:border-slate-800 dark:text-slate-400 light:bg-slate-100 light:border-slate-200 light:text-slate-600">+91</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required pattern="[0-9]{10}" maxlength="10"
                            placeholder="10-digit number"
                            class="w-full px-4 py-3 rounded-2xl border text-sm transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-200 light:text-slate-900">
                    </div>
                </div>

                <!-- Ticket Category Selection -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold dark:text-slate-200 text-slate-800">Select Admission Category <span class="text-amber-500">*</span></label>
                    <select id="ticket_category_id" name="ticket_category_id" required onchange="updatePriceCalculation()"
                        class="w-full px-4 py-3 rounded-2xl border text-sm transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-200 light:text-slate-900">
                        <option value="">-- Choose Category --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" data-price="{{ $cat->price_paise }}" data-available="{{ $cat->available }}" {{ old('ticket_category_id') == $cat->id ? 'selected' : '' }} {{ $cat->available <= 0 ? 'disabled' : '' }}>
                                {{ $cat->name }} — {{ $cat->formatted_price }} ({{ $cat->available }} available)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- In-Person Attendance Question -->
            <div class="space-y-2 p-5 rounded-2xl border dark:bg-slate-950/60 dark:border-slate-800 light:bg-slate-50 light:border-slate-200">
                <label class="text-xs font-bold dark:text-slate-200 text-slate-800 block">
                    Will you be attending the programme in person? <span class="text-amber-500">*</span>
                </label>
                <div class="grid sm:grid-cols-2 gap-3 pt-1">
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition dark:bg-slate-900 dark:border-slate-800 dark:hover:border-amber-500/50 light:bg-white light:border-slate-200 light:hover:border-amber-500">
                        <input type="radio" name="attending" value="yes" {{ old('attending', 'yes') === 'yes' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0">
                        <div class="text-xs">
                            <span class="font-bold dark:text-slate-100 text-slate-900 block">Yes</span>
                            <span class="text-[11px] dark:text-slate-400 text-slate-500">I will attend in person at Chavara Hall</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border cursor-pointer transition dark:bg-slate-900 dark:border-slate-800 dark:hover:border-amber-500/50 light:bg-white light:border-slate-200 light:hover:border-amber-500">
                        <input type="radio" name="attending" value="no" {{ old('attending') === 'no' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0">
                        <div class="text-xs">
                            <span class="font-bold dark:text-slate-100 text-slate-900 block">No (Charitable Supporter)</span>
                            <span class="text-[11px] dark:text-slate-400 text-slate-500">Unable to attend (100% to support child girls)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Number of Admissions -->
            <div class="grid sm:grid-cols-2 gap-5 items-center">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold dark:text-slate-200 text-slate-800">Number of Admissions</label>
                    <select id="quantity" name="quantity" required onchange="updatePriceCalculation()"
                        class="w-full px-4 py-3 rounded-2xl border text-sm transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-200 light:text-slate-900">
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('quantity', 1) == $i ? 'selected' : '' }}>
                                {{ $i }} {{ $i === 1 ? 'Admission (1 attendee)' : 'Admissions' }}
                            </option>
                        @endfor
                    </select>
                    <p class="text-[10px] dark:text-slate-500 text-slate-500">Each attendee receives a unique cryptographic QR entry pass.</p>
                </div>

                <div class="p-5 rounded-2xl border text-right dark:bg-slate-950/60 dark:border-slate-800 light:bg-slate-50 light:border-slate-200">
                    <div class="text-xs dark:text-slate-400 text-slate-500 uppercase tracking-wider font-semibold">Total Payable</div>
                    <div id="calculated_total" class="text-3xl font-extrabold font-serif-display text-amber-500 dark:text-amber-400 mt-1">₹0.00</div>
                </div>
            </div>

            <!-- Terms & Consent -->
            <div class="space-y-3 pt-2">
                <div class="p-4 rounded-2xl border text-xs space-y-1 dark:bg-amber-500/5 dark:border-amber-500/20 light:bg-amber-50/50 light:border-amber-200">
                    <div class="flex items-center gap-2 font-bold text-amber-600 dark:text-amber-400">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Strict No-Cancellation &amp; Non-Refundable Policy</span>
                    </div>
                    <p class="text-[11px] dark:text-slate-400 text-slate-600 leading-relaxed">
                        All ticket bookings and payments are strictly final and non-refundable once confirmed, as all proceeds directly support child girls' education and welfare.
                    </p>
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="privacy_consent" value="1" required class="mt-1 w-4 h-4 rounded text-amber-500 border-slate-600 dark:bg-slate-950 focus:ring-0">
                    <span class="text-xs dark:text-slate-300 text-slate-700">
                        I acknowledge that my booking details will be sent to the Joygivers reservations desk. <strong>I agree to the strict No-Cancellation policy</strong>, and understand our team will contact me to complete payment before tickets are issued.
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="marketing_opt_in" value="1" class="mt-1 w-4 h-4 rounded text-amber-500 border-slate-600 dark:bg-slate-950 focus:ring-0">
                    <span class="text-xs dark:text-slate-400 text-slate-500">
                        Keep me updated on future musical initiatives and cultural events by Joygivers.
                    </span>
                </label>
            </div>

            <!-- Submit Action -->
            <div class="space-y-2">
                <button type="submit" id="submit_btn"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-500 via-rose-500 to-purple-600 hover:from-amber-400 hover:to-rose-400 text-white font-bold text-sm tracking-wide transition shadow-xl shadow-rose-500/20 flex items-center justify-center gap-2 transform active:scale-95">
                    <span>Submit Booking Enquiry &amp; Reserve Seats</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
                <p class="text-[11px] dark:text-slate-400 text-slate-500 text-center">
                    No online payment needed now. Our payment and reservations team will call you at your phone number to confirm details and share payment options via WhatsApp or Email.
                </p>
            </div>
        </form>
    </section>
</div>

<script>
    function updatePriceCalculation() {
        const catSelect = document.getElementById('ticket_category_id');
        const qtySelect = document.getElementById('quantity');
        const totalDisplay = document.getElementById('calculated_total');

        const selectedOption = catSelect.options[catSelect.selectedIndex];
        const pricePaise = selectedOption ? parseInt(selectedOption.getAttribute('data-price') || 0) : 0;
        const qty = parseInt(qtySelect.value || 1);

        const totalPaise = pricePaise * qty;
        const totalRupees = (totalPaise / 100).toFixed(2);
        totalDisplay.textContent = '₹' + Number(totalRupees).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    function selectCategory(catId) {
        const select = document.getElementById('ticket_category_id');
        select.value = catId;
        updatePriceCalculation();
        document.getElementById('register').scrollIntoView({ behavior: 'smooth' });
    }

    document.addEventListener('DOMContentLoaded', updatePriceCalculation);
</script>
@endsection
