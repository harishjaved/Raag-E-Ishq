@extends('layouts.app')

@section('title', 'Raag-E-Ishq — Live Musical Evening | Joygivers')

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
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-black tracking-tight font-serif-display dark:text-slate-100 text-slate-950 drop-shadow-sm">
                Raag-E-Ishq
            </h1>
            <p class="text-lg sm:text-2xl dark:text-slate-100 text-slate-900 max-w-2xl mx-auto font-bold leading-relaxed drop-shadow-sm">
                A celebration of timeless Hindi melodies in Kochi, for child girls education.
            </p>
        </div>

        <!-- Live Music Equalizer Visualizer Accent -->
        <div class="flex items-center justify-center gap-1.5 py-1 select-none" aria-hidden="true">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full dark:bg-slate-900/60 light:bg-white/70 border border-amber-500/30 backdrop-blur-md shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                <span class="text-[11px] uppercase tracking-wider text-amber-500 dark:text-amber-400 font-bold mr-1">
                    Live Orchestra
                </span>
                <div class="flex items-end gap-1 h-5 px-1">
                    <span class="music-bar bar-1"></span>
                    <span class="music-bar bar-2"></span>
                    <span class="music-bar bar-3"></span>
                    <span class="music-bar bar-4"></span>
                    <span class="music-bar bar-5"></span>
                    <span class="music-bar bar-6"></span>
                    <span class="music-bar bar-7"></span>
                    <span class="music-bar bar-8"></span>
                    <span class="music-bar bar-9"></span>
                    <span class="music-bar bar-10"></span>
                    <span class="music-bar bar-11"></span>
                    <span class="music-bar bar-12"></span>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
            <a href="#register"
                class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 via-rose-500 to-purple-600 hover:from-amber-400 hover:to-rose-400 text-white font-bold text-sm tracking-wide transition shadow-xl shadow-rose-500/20 flex items-center gap-2 transform active:scale-95">
                <span>Reserve Seats &amp; Book Tickets</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            </a>
            <a href="#categories"
                class="px-5 py-3.5 rounded-2xl border transition text-xs font-bold flex items-center gap-2 dark:border-slate-800 dark:bg-slate-900/60 dark:hover:bg-slate-800 dark:text-slate-200 light:border-slate-300 light:bg-white light:hover:bg-slate-100 light:text-slate-800 shadow-sm">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>View Ticket Categories</span>
            </a>
        </div>

        <!-- Event Quick Facts Grid (4 Highlight Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 max-w-4xl mx-auto pt-6 text-left">
            <!-- Venue -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/90 dark:border-slate-800 light:bg-white light:border-slate-300 shadow-md">
                <div class="text-xs uppercase font-extrabold tracking-wider text-rose-600 dark:text-rose-400">Venue</div>
                <div class="text-base font-extrabold dark:text-slate-100 text-slate-900 mt-1">Chavara Hall</div>
                <div class="text-xs font-bold dark:text-slate-300 text-slate-700">Near South Rly Stn, Kochi</div>
            </div>

            <!-- Date & Time (17th December, 7pm) -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/90 dark:border-slate-800 light:bg-white light:border-slate-300 shadow-md">
                <div class="text-xs uppercase font-extrabold tracking-wider text-amber-600 dark:text-amber-400">Date &amp; Time</div>
                <div class="text-base font-extrabold dark:text-slate-100 text-slate-900 mt-1">17th December</div>
                <div class="text-xs font-bold dark:text-amber-300 text-amber-700">7:00 PM IST Evening</div>
            </div>

            <!-- Noble Cause -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/90 dark:border-slate-800 light:bg-white light:border-slate-300 shadow-md">
                <div class="text-xs uppercase font-extrabold tracking-wider text-purple-600 dark:text-purple-400">Noble Cause</div>
                <div class="text-base font-extrabold dark:text-slate-100 text-slate-900 mt-1">For Child Girls Education</div>
                <div class="text-xs font-bold dark:text-slate-300 text-slate-700">Welfare &amp; Development</div>
            </div>

            <!-- Capacity -->
            <div class="p-4 rounded-2xl border transition-all dark:bg-slate-900/90 dark:border-slate-800 light:bg-white light:border-slate-300 shadow-md">
                <div class="text-xs uppercase font-extrabold tracking-wider text-emerald-600 dark:text-emerald-400">Hall Capacity</div>
                <div class="text-base font-extrabold dark:text-slate-100 text-slate-900 mt-1">450 Seats Total</div>
                <div class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">{{ $globalRemaining }} seats available</div>
            </div>
        </div>
    </section>

    <!-- Admission Categories & Pricing Cards (Inspired by Reference Cards) -->
    <section id="categories" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
            <div>
                <span class="text-xs uppercase font-extrabold tracking-widest text-amber-600 dark:text-amber-400">Choose Your Experience</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold dark:text-slate-100 text-slate-900 font-serif-display">Admission Categories</h2>
            </div>
            <span class="text-xs font-bold dark:text-slate-300 text-slate-700">Live Available Quotas &bull; Instant Seat Reservation</span>
        </div>

        <div class="grid sm:grid-cols-3 gap-6">
            @foreach ($categories as $cat)
                @php
                    $isTier1 = str_contains(strtolower($cat->name), 'tier 1');
                    $isTier2 = str_contains(strtolower($cat->name), 'tier 2');
                @endphp
                <div class="relative p-6 sm:p-7 rounded-3xl border-2 transition-all flex flex-col justify-between group shadow-xl {{ $isTier1 ? 'dark:border-amber-500/60 dark:bg-gradient-to-b dark:from-slate-900 dark:to-slate-950 light:border-amber-500 light:bg-white' : 'dark:border-slate-800 dark:bg-slate-900/90 light:border-slate-300 light:bg-white' }}">
                    @if ($isTier1)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-1 rounded-full bg-gradient-to-r from-amber-500 to-rose-500 text-white text-[11px] font-black uppercase tracking-wider shadow-lg">
                            VIP CHOICE
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">{{ $cat->name }}</span>
                            @if ($cat->available > 0)
                                <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-500/30 font-black">
                                    {{ $cat->available }} left
                                </span>
                            @else
                                <span class="text-xs px-3 py-1 rounded-full bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-400 border border-rose-500/30 font-black">
                                    Sold Out
                                </span>
                            @endif
                        </div>

                        <div>
                            <div class="text-3xl sm:text-4xl font-extrabold font-serif-display dark:text-slate-100 text-slate-950 tracking-tight">{{ $cat->formatted_price }}</div>
                            <p class="text-sm font-bold dark:text-slate-200 text-slate-800 mt-2 leading-relaxed">{{ $cat->description }}</p>
                        </div>

                        <ul class="text-xs space-y-2.5 pt-3 border-t-2 dark:border-slate-800 border-slate-200">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-900 dark:text-slate-100 font-bold text-xs sm:text-sm">Unique Admission Pass &bull; Admit 1</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-900 dark:text-slate-100 font-bold text-xs sm:text-sm">Cryptographic Gate Scan QR</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-slate-900 dark:text-slate-100 font-bold text-xs sm:text-sm">100% Proceeds For Child Girls Education</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 pt-4 border-t dark:border-slate-800 border-slate-200">
                        <button type="button" onclick="selectCategory({{ $cat->id }})"
                            class="w-full py-3 px-4 rounded-xl text-xs sm:text-sm font-extrabold transition flex items-center justify-center gap-2 tracking-wide {{ $cat->available > 0 ? 'bg-slate-900 hover:bg-slate-800 text-white dark:bg-amber-500 dark:hover:bg-amber-400 dark:text-slate-950 shadow-md' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}"
                            {{ $cat->available <= 0 ? 'disabled' : '' }}>
                            <span>Select &amp; Book</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Cause Feature Spotlight: For Child Girls Education -->
    <section class="p-8 sm:p-10 rounded-3xl border-2 transition-all dark:bg-gradient-to-r dark:from-slate-900 dark:via-purple-950/30 dark:to-slate-900 dark:border-slate-800 light:bg-gradient-to-r light:from-rose-50 light:via-purple-50 light:to-amber-50 light:border-rose-300 shadow-xl">
        <div class="flex flex-col md:flex-row items-center gap-6 justify-between">
            <div class="space-y-3 max-w-xl text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-extrabold uppercase tracking-wider">
                    Our Core Mission
                </div>
                <h3 class="text-2xl sm:text-3xl font-extrabold font-serif-display dark:text-slate-100 text-slate-950">
                    Every Melodic Note Sings For Child Girls Education
                </h3>
                <p class="text-sm sm:text-base dark:text-slate-200 text-slate-800 font-bold leading-relaxed">
                    Joygivers believes that every child girl deserves access to quality education, safe shelter, and bright opportunities. All proceeds from <strong>Raag-E-Ishq</strong> are dedicated to our girl-child welfare programmes.
                </p>
                <p class="text-xs sm:text-sm dark:text-amber-300 text-amber-900 font-bold italic">
                    Unable to attend in person? You can still book your ticket and select "No" for in-person attendance — 100% of your amount will directly fund child girls' education, and you will receive an Official Supporter Receipt.
                </p>
            </div>
            <div class="flex-shrink-0 text-center">
                <a href="#register" class="px-7 py-4 rounded-2xl bg-gradient-to-r from-rose-500 to-amber-500 hover:from-rose-400 hover:to-amber-400 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-lg shadow-rose-500/20 inline-block">
                    Support The Cause
                </a>
            </div>
        </div>
    </section>

    <!-- Registration Form Desk -->
    <section id="register" class="p-6 sm:p-10 rounded-3xl border-2 transition-all dark:bg-slate-900 dark:border-slate-800 light:bg-white light:border-slate-300 shadow-2xl space-y-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-extrabold uppercase tracking-wider mb-2">
                <span>Direct Reservations Desk</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold dark:text-slate-100 text-slate-950 font-serif-display">Register &amp; Reserve Seats</h2>
            <p class="text-xs sm:text-sm font-bold dark:text-slate-300 text-slate-700 mt-1">
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
                    <label class="text-xs sm:text-sm font-extrabold dark:text-slate-200 text-slate-900">Purchaser Full Name <span class="text-amber-500">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                        placeholder="e.g. Aarav Sharma"
                        class="w-full px-4 py-3 rounded-2xl border text-sm font-bold transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-300 light:text-slate-950">
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label class="text-xs sm:text-sm font-extrabold dark:text-slate-200 text-slate-900">Email Address (for tickets) <span class="text-amber-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="e.g. aarav@example.com"
                        class="w-full px-4 py-3 rounded-2xl border text-sm font-bold transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-300 light:text-slate-950">
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                    <label class="text-xs sm:text-sm font-extrabold dark:text-slate-200 text-slate-900">WhatsApp / Phone Number <span class="text-amber-500">*</span></label>
                    <div class="flex gap-2">
                        <span class="inline-flex items-center px-3.5 rounded-2xl border text-xs font-bold dark:bg-slate-950 dark:border-slate-800 dark:text-slate-300 light:bg-slate-100 light:border-slate-300 light:text-slate-800">+91</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required pattern="[0-9]{10}" maxlength="10"
                            placeholder="10-digit number"
                            class="w-full px-4 py-3 rounded-2xl border text-sm font-bold transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-300 light:text-slate-950">
                    </div>
                </div>

                <!-- Ticket Category Selection -->
                <div class="space-y-1.5">
                    <label class="text-xs sm:text-sm font-extrabold dark:text-slate-200 text-slate-900">Select Admission Category <span class="text-amber-500">*</span></label>
                    <select id="ticket_category_id" name="ticket_category_id" required onchange="updatePriceCalculation()"
                        class="w-full px-4 py-3 rounded-2xl border text-sm font-bold transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-300 light:text-slate-950">
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
            <div class="space-y-2 p-5 rounded-2xl border-2 dark:bg-slate-950/60 dark:border-slate-800 light:bg-slate-50 light:border-slate-300">
                <label class="text-xs sm:text-sm font-extrabold dark:text-slate-200 text-slate-900 block">
                    Will you be attending the programme in person? <span class="text-amber-500">*</span>
                </label>
                <div class="grid sm:grid-cols-2 gap-3 pt-1">
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition dark:bg-slate-900 dark:border-slate-800 dark:hover:border-amber-500/50 light:bg-white light:border-slate-300 light:hover:border-amber-500">
                        <input type="radio" name="attending" value="yes" {{ old('attending', 'yes') === 'yes' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0">
                        <div class="text-xs">
                            <span class="font-black dark:text-slate-100 text-slate-950 text-sm block">Yes</span>
                            <span class="text-xs font-bold dark:text-slate-300 text-slate-700">I will attend in person at Chavara Hall</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition dark:bg-slate-900 dark:border-slate-800 dark:hover:border-amber-500/50 light:bg-white light:border-slate-300 light:hover:border-amber-500">
                        <input type="radio" name="attending" value="no" {{ old('attending') === 'no' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0">
                        <div class="text-xs">
                            <span class="font-black dark:text-slate-100 text-slate-950 text-sm block">No (Charitable Supporter)</span>
                            <span class="text-xs font-bold dark:text-slate-300 text-slate-700">Unable to attend (100% for child girls education)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Number of Admissions -->
            <div class="grid sm:grid-cols-2 gap-5 items-center">
                <div class="space-y-1.5">
                    <label class="text-xs sm:text-sm font-extrabold dark:text-slate-200 text-slate-900">Number of Admissions</label>
                    <select id="quantity" name="quantity" required onchange="updatePriceCalculation()"
                        class="w-full px-4 py-3 rounded-2xl border text-sm font-bold transition focus:outline-none focus:border-amber-500 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-100 light:bg-slate-50 light:border-slate-300 light:text-slate-950">
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('quantity', 1) == $i ? 'selected' : '' }}>
                                {{ $i }} {{ $i === 1 ? 'Admission (1 attendee)' : 'Admissions' }}
                            </option>
                        @endfor
                    </select>
                    <p class="text-xs font-bold dark:text-slate-400 text-slate-600">Each attendee receives a unique cryptographic QR entry pass.</p>
                </div>

                <div class="p-5 rounded-2xl border-2 text-right dark:bg-slate-950/60 dark:border-slate-800 light:bg-slate-50 light:border-slate-300">
                    <div class="text-xs font-black dark:text-slate-300 text-slate-700 uppercase tracking-wider">Total Payable</div>
                    <div id="calculated_total" class="text-3xl sm:text-4xl font-black font-serif-display text-amber-600 dark:text-amber-400 mt-1">₹0.00</div>
                </div>
            </div>

            <!-- Terms & Consent -->
            <div class="space-y-3 pt-2">
                <div class="p-4 rounded-2xl border-2 text-xs space-y-1 dark:bg-amber-500/10 dark:border-amber-500/30 light:bg-amber-50 light:border-amber-300">
                    <div class="flex items-center gap-2 font-black text-amber-800 dark:text-amber-300 text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Strict No-Cancellation &amp; Non-Refundable Policy</span>
                    </div>
                    <p class="text-xs font-bold dark:text-slate-200 text-slate-900 leading-relaxed">
                        All ticket bookings and payments are strictly final and non-refundable once confirmed, as all proceeds directly support child girls' education and welfare.
                    </p>
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="privacy_consent" value="1" required class="mt-1 w-4 h-4 rounded text-amber-500 border-slate-600 dark:bg-slate-950 focus:ring-0">
                    <span class="text-xs sm:text-sm font-bold dark:text-slate-200 text-slate-900">
                        I acknowledge that my booking details will be sent to the Joygivers reservations desk. <strong>I agree to the strict No-Cancellation policy</strong>, and understand our team will contact me to complete payment before tickets are issued.
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="marketing_opt_in" value="1" class="mt-1 w-4 h-4 rounded text-amber-500 border-slate-600 dark:bg-slate-950 focus:ring-0">
                    <span class="text-xs sm:text-sm font-bold dark:text-slate-300 text-slate-700">
                        Keep me updated on future musical initiatives and cultural events by Joygivers.
                    </span>
                </label>
            </div>

            <!-- Submit Action -->
            <div class="space-y-2">
                <button type="submit" id="submit_btn"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-500 via-rose-500 to-purple-600 hover:from-amber-400 hover:to-rose-400 text-white font-black text-sm sm:text-base tracking-wide transition shadow-xl shadow-rose-500/20 flex items-center justify-center gap-2 transform active:scale-95">
                    <span>Submit Booking Enquiry &amp; Reserve Seats</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
                <p class="text-xs font-bold dark:text-slate-300 text-slate-700 text-center">
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
