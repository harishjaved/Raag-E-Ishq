@extends('layouts.app')

@section('title', 'Raag-E-Ishq Featuring Kuldeep Sagar | Joygivers')

@section('content')

{{-- =======================================================================
     SECTION 1: OPENING HERO SECTION (Deep Burgundy & Antique Gold)
     ======================================================================= --}}
<section aria-label="Raag-E-Ishq Event Introduction" class="relative bg-[#3B0A14] text-[#FAF6F0] overflow-hidden border-b border-[#D4AF37]/35">
    <!-- Subtle Stage Photography (Mobile Ensemble / Desktop Concert Silhouette) -->
    <div class="relative md:hidden h-64 sm:h-72 w-full overflow-hidden">
        <img src="{{ asset('images/hero-stage-mobile.jpg') }}"
             alt="Illustrative stage scene featuring Indian classical musicians under warm golden spotlights"
             class="w-full h-full object-cover object-center" />
        <div class="absolute inset-0 bg-gradient-to-b from-[#24050B]/70 via-transparent to-[#3B0A14]"></div>
        <div class="absolute top-3 left-0 right-0 flex items-center justify-center gap-3 px-4">
            <span class="h-px w-10 bg-[#D4AF37]/50"></span>
            <div class="flex items-center gap-1.5 text-xs font-serif-display tracking-[0.16em] uppercase text-[#FAF6F0]">
                <svg class="w-4 h-4 text-[#E5C158]" viewBox="0 0 48 36" fill="none" aria-hidden="true">
                    <path d="M24 4C21.5 10.5 19.5 16.5 24 24C28.5 16.5 26.5 10.5 24 4Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M24 24C16.5 20.5 12.5 15 11.5 9.5C16.5 11 20.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M24 24C31.5 20.5 35.5 15 36.5 9.5C31.5 11 27.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.6"/>
                </svg>
                <span>Joygivers Presents</span>
            </div>
            <span class="h-px w-10 bg-[#D4AF37]/50"></span>
        </div>
    </div>

    <div class="hidden md:block absolute inset-0 pointer-events-none">
        <img src="{{ asset('images/hero-stage-desktop.jpg') }}"
             alt="Warm concert stage lighting and live orchestra ambience for Raag-E-Ishq"
             class="w-full h-full object-cover object-right" />
        <div class="absolute inset-0 bg-gradient-to-r from-[#28050C] via-[#3B0A14]/88 to-[#3B0A14]/30"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#2C060E] via-transparent to-[#2C060E]/45"></div>
    </div>

    <!-- Subtle 1px Antique Gold Inner Ornamental Frame -->
    <div class="hidden sm:block pointer-events-none absolute inset-3 md:inset-5 border border-[#D4AF37]/20 rounded-xl"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 pt-4 pb-12 sm:py-16 md:py-20">
        <div class="grid md:grid-cols-12 gap-8 items-center">
            <!-- Left Hero Copy -->
            <div class="md:col-span-7 text-center md:text-left space-y-5">
                <!-- Eyebrow Header (Desktop) -->
                <div class="hidden md:inline-flex items-center gap-3 text-xs uppercase tracking-[0.22em] text-[#E5C158] font-medium">
                    <svg class="w-5 h-5 text-[#E5C158]" viewBox="0 0 48 36" fill="none" aria-hidden="true">
                        <path d="M24 4C21.5 10.5 19.5 16.5 24 24C28.5 16.5 26.5 10.5 24 4Z" stroke="currentColor" stroke-width="1.7"/>
                        <path d="M24 24C16.5 20.5 12.5 15 11.5 9.5C16.5 11 20.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M24 24C31.5 20.5 35.5 15 36.5 9.5C31.5 11 27.5 15.5 24 24Z" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <span class="h-px w-8 bg-[#D4AF37]/50"></span>
                    <span>Joygivers Presents</span>
                    <span class="h-px w-8 bg-[#D4AF37]/50"></span>
                </div>

                <!-- Event Name, Featured Artist & Tagline -->
                <div class="space-y-2">
                    <div class="text-[11px] uppercase tracking-[0.2em] text-[#E5C158] font-medium md:hidden">An Evening of Music With a Purpose</div>
                    <h1 class="font-serif-display text-5xl sm:text-6xl lg:text-7xl font-semibold tracking-tight text-[#E5C158] leading-[1.03]">
                        Raag-E-Ishq
                    </h1>
                    <p class="font-serif-display text-xl sm:text-2xl font-medium text-[#FAF6F0]">
                        Featuring <span class="text-[#E5C158]">Kuldeep Sagar</span>
                    </p>
                    <p class="font-serif-display italic text-lg sm:text-xl text-[#EBD383]">
                        Timeless melodies. Brighter tomorrows.
                    </p>
                </div>

                <p class="text-sm sm:text-base text-[#E8DFD3] max-w-xl mx-auto md:mx-0 leading-relaxed">
                    An evening celebrating timeless Hindi melodies with a live orchestra, in support of girls’ education.
                </p>

                <!-- Date, Time & Venue Strip -->
                <div class="pt-1 flex flex-wrap items-center justify-center md:justify-start gap-x-5 gap-y-2.5 text-xs sm:text-sm text-[#FAF6F0] border-y border-[#D4AF37]/25 py-3.5 max-w-xl mx-auto md:mx-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E5C158] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        <span>17 January 2027 (Sunday)</span>
                    </div>
                    <span class="hidden sm:inline text-[#D4AF37]/50">|</span>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E5C158] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                        </svg>
                        <span>7:00 PM Onwards</span>
                    </div>
                    <span class="hidden sm:inline text-[#D4AF37]/50">|</span>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E5C158] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 21s-6-5.3-6-10.5A6 6 0 1118 10.5C18 15.7 12 21 12 21z"/><circle cx="12" cy="10.5" r="2"/>
                        </svg>
                        <span>Chavara Hall, Kochi</span>
                    </div>
                </div>

                <!-- Primary & Secondary CTAs -->
                <div class="pt-1 flex flex-col sm:flex-row items-center justify-center md:justify-start gap-3">
                    <a href="#register"
                        class="btn-antique-gold w-full sm:w-auto px-7 py-3 rounded-lg text-sm font-semibold tracking-wide inline-flex items-center justify-center gap-2 transition">
                        <span>Reserve Your Seats &rarr;</span>
                    </a>
                    <a href="#donate"
                        class="w-full sm:w-auto px-6 py-3 rounded-lg border border-[#D4AF37]/60 bg-[#24050B]/70 hover:bg-[#4A0E1B] text-[#FAF6F0] text-sm font-medium inline-flex items-center justify-center gap-2 transition">
                        <span>Support the Cause</span>
                        <span aria-hidden="true" class="text-[#E5C158]">&hearts;</span>
                    </a>
                </div>
                <div class="md:hidden pt-1">
                    <a href="#donate" class="text-xs text-[#EBD383] underline underline-offset-4 hover:text-white">
                        Unable to attend? Support the cause
                    </a>
                </div>
            </div>

            <!-- Right Slot: Featured Artist Portrait Card / Photo Slot -->
            <div class="hidden md:flex md:col-span-5 justify-end">
                <div class="w-full max-w-xs rounded-xl border border-[#D4AF37]/40 bg-[#24050B]/75 backdrop-blur-sm p-5 text-center space-y-2.5">
                    <div class="w-20 h-20 mx-auto rounded-full border border-[#D4AF37]/60 bg-[#3B0A14] flex items-center justify-center text-[#E5C158]">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <div class="text-[10px] uppercase tracking-[0.2em] text-[#D4AF37]">Featured Vocalist</div>
                    <div class="font-serif-display italic text-3xl font-medium text-[#E5C158]">Kuldeep Sagar</div>
                    <p class="text-xs text-[#D8CBB8] leading-relaxed">
                        Accompanied by a full acoustic &amp; orchestral ensemble performing evergreen Hindi classics.
                    </p>
                    <div class="text-[10px] text-[#BFA89A] border-t border-[#D4AF37]/20 pt-2">
                        Stage backdrop illustrative; official artist portrait slot ready
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 2: MUSICAL EXPERIENCE STRIP (Warm Ivory, 4 Columns)
     ======================================================================= --}}
<section id="the-event" class="scroll-mt-16 bg-[#FAF6F0] text-[#231715] border-b border-[#E3D8C8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 text-center">
            <div class="space-y-2 px-3">
                <div class="w-10 h-10 mx-auto rounded-lg border border-[#D4AF37]/45 bg-white flex items-center justify-center text-[#7A5C2E]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-2v13M9 19a2 2 0 11-4 0 2 2 0 014 0zm12-2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h2 class="font-serif-display text-xl font-semibold text-[#3B0A14]">Live Orchestra</h2>
                <p class="text-xs text-[#5A4640]">A rich, immersive musical experience</p>
            </div>

            <div class="space-y-2 px-3">
                <div class="w-10 h-10 mx-auto rounded-lg border border-[#D4AF37]/45 bg-white flex items-center justify-center text-[#7A5C2E]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.5"/>
                    </svg>
                </div>
                <h2 class="font-serif-display text-xl font-semibold text-[#3B0A14]">Timeless Classics</h2>
                <p class="text-xs text-[#5A4640]">Beloved Hindi melodies across generations</p>
            </div>

            <div class="space-y-2 px-3">
                <div class="w-10 h-10 mx-auto rounded-lg border border-[#D4AF37]/45 bg-white flex items-center justify-center text-[#3B0A14]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                    </svg>
                </div>
                <h2 class="font-serif-display text-xl font-semibold text-[#3B0A14]">A Meaningful Cause</h2>
                <p class="text-xs text-[#5A4640]">To empower girls through education</p>
            </div>

            <div class="space-y-2 px-3">
                <div class="w-10 h-10 mx-auto rounded-lg border border-[#D4AF37]/45 bg-white flex items-center justify-center text-[#7A5C2E]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="font-serif-display text-xl font-semibold text-[#3B0A14]">An Evening Together</h2>
                <p class="text-xs text-[#5A4640]">Music, purpose and community</p>
            </div>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 3: THE PROGRAMME, ARTIST & ORCHESTRA
     ======================================================================= --}}
<section id="artist" class="scroll-mt-16 bg-[#F5EFE6] text-[#231715] py-14 sm:py-18 border-b border-[#E3D8C8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 grid lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        <!-- Left Column: Instruments Photo & Teaser Video Slot -->
        <div class="lg:col-span-5 space-y-5">
            <div class="rounded-xl overflow-hidden border border-[#D4AF37]/45 bg-white p-2">
                <img src="{{ asset('images/programme-instruments.jpg') }}"
                     alt="Tabla and harmonium arranged on deep burgundy velvet in warm concert lighting"
                     class="w-full h-64 sm:h-72 object-cover rounded-lg" />
            </div>

            <!-- Teaser Video Placeholder Slot -->
            <div class="rounded-xl border border-[#D4AF37]/40 bg-white p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] uppercase tracking-wider font-semibold text-[#7A5C2E]">
                    <span>Programme Preview</span>
                    <span>Teaser Video Slot</span>
                </div>
                <div class="aspect-video rounded-lg bg-[#3B0A14] border border-[#D4AF37]/30 flex flex-col items-center justify-center text-center p-5 space-y-2 text-[#FAF6F0]">
                    <div class="w-11 h-11 rounded-full bg-[#D4AF37]/20 border border-[#D4AF37]/60 flex items-center justify-center text-[#E5C158]">
                        <svg class="w-5 h-5 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                    <div class="font-serif-display text-lg font-medium text-[#E5C158]">Raag-E-Ishq Rehearsal &amp; Concert Teaser</div>
                    <p class="text-[11px] text-[#E8DFD3] max-w-xs">
                        YouTube teaser video embed ready — will go live here once the official rehearsal reel is published.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Programme Narrative, Kuldeep Sagar Bio & Repertoire -->
        <div class="lg:col-span-7 space-y-6">
            <div class="space-y-2">
                <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-[#7A5C2E]">The Programme</div>
                <h2 class="font-serif-display text-3xl sm:text-4xl font-semibold text-[#3B0A14] leading-tight">
                    A Musical Journey Through Timeless Hindi Melodies
                </h2>
                <p class="text-sm text-[#4A3B36] leading-relaxed">
                    Experience an intimate concert curated around warmth, acoustic authenticity, and nostalgia — uniting families and music patrons across Kochi for an evening where melody serves a lasting mission.
                </p>
            </div>

            <!-- Bio Cards: Artist + Live Orchestra -->
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="p-5 rounded-xl bg-white border border-[#D4AF37]/40 space-y-2">
                    <div class="text-[10px] uppercase tracking-widest font-semibold text-[#7A5C2E]">Lead Vocalist</div>
                    <h3 class="font-serif-display text-2xl font-semibold text-[#3B0A14]">Kuldeep Sagar</h3>
                    <p class="text-xs text-[#4A3B36] leading-relaxed">
                        Celebrated for evocative phrasing and expressive stage presence, Kuldeep Sagar brings timeless Hindi film classics, ghazal-inspired ballads, and heartfelt romantic melodies to life with warmth and nuance.
                    </p>
                </div>

                <div class="p-5 rounded-xl bg-white border border-[#D4AF37]/40 space-y-2">
                    <div class="text-[10px] uppercase tracking-widest font-semibold text-[#7A5C2E]">Acoustic Ensemble</div>
                    <h3 class="font-serif-display text-2xl font-semibold text-[#3B0A14]">The Live Orchestra</h3>
                    <p class="text-xs text-[#4A3B36] leading-relaxed">
                        A seasoned ensemble of live strings, flute, harmonium, acoustic guitar, tabla, and percussion recreating authentic golden-era concert arrangements in Chavara Hall’s intimate acoustics.
                    </p>
                </div>
            </div>

            <!-- Repertoire & Duration -->
            <div class="p-5 rounded-xl bg-[#FAF6F0] border border-[#D4AF37]/40 grid sm:grid-cols-2 gap-4 text-xs">
                <div class="space-y-1">
                    <div class="font-semibold text-[#3B0A14] uppercase tracking-wider text-[11px]">Expected Repertoire</div>
                    <p class="text-[#4A3B36] leading-relaxed">
                        Evergreen golden-era duets &amp; solos, romantic Hindi film classics, and soulful melodic favourites curated for all generations.
                    </p>
                </div>
                <div class="space-y-1 sm:border-l sm:border-[#E3D8C8] sm:pl-4">
                    <div class="font-semibold text-[#3B0A14] uppercase tracking-wider text-[11px]">Programme Duration &amp; Timings</div>
                    <p class="text-[#4A3B36] leading-relaxed">
                        <strong>Approx. 2.5 &ndash; 3 Hours with intermission</strong><br>
                        Gates open: <strong>6:15 PM</strong> &bull; Curtain rises: <strong>7:00 PM Onwards</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 4: THE CAUSE SECTION (Deep Burgundy Background)
     ======================================================================= --}}
<section id="the-cause" class="scroll-mt-16 relative bg-[#3B0A14] text-[#FAF6F0] overflow-hidden border-b border-[#D4AF37]/30">
    <!-- Atmospheric Left Blend Image -->
    <div class="hidden lg:block absolute inset-y-0 left-0 w-1/2 pointer-events-none">
        <img src="{{ asset('images/cause-education.jpg') }}"
             alt="Young student reading by warm light, representing Joygivers girls' education initiatives"
             class="w-full h-full object-cover object-left opacity-80" />
        <div class="absolute inset-0 bg-gradient-to-r from-[#24050B]/40 via-[#3B0A14]/75 to-[#3B0A14]"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-14 sm:py-18 grid lg:grid-cols-12 gap-10 items-center">
        <!-- Mobile Image -->
        <div class="lg:hidden rounded-xl overflow-hidden border border-[#D4AF37]/35">
            <img src="{{ asset('images/cause-education.jpg') }}"
                 alt="Young girl reading a book in warm light for girls' education"
                 class="w-full h-56 object-cover object-left" />
        </div>

        <!-- Cause Narrative (Center/Left Offset on Desktop over dark burgundy gradient) -->
        <div class="lg:col-span-7 lg:pl-44 space-y-4">
            <div class="text-[11px] uppercase tracking-[0.22em] font-semibold text-[#E5C158]">The Cause</div>
            <h2 class="font-serif-display italic text-3xl sm:text-4xl lg:text-[2.6rem] font-semibold text-[#FAF6F0] leading-[1.12]">
                One evening for you.<br>A brighter tomorrow for her.
            </h2>
            <p class="text-sm text-[#E8DFD3] leading-relaxed max-w-xl">
                Every girl deserves uninterrupted schooling, books, safe learning environments, and encouragement to pursue her ambitions. Through community-funded scholarships and partner school programmes, Joygivers works so financial barriers never force a young girl out of the classroom.
            </p>
            <div class="p-4 rounded-xl bg-[#24050B]/85 border border-[#D4AF37]/40 text-xs text-[#FAF6F0] space-y-1 max-w-xl">
                <div class="font-semibold text-[#E5C158]">Transparent Proceeds Allocation</div>
                <p class="text-[#E8DFD3] leading-relaxed">
                    <strong>100% of net proceeds go directly towards girls’ education initiatives.</strong> If event production and hall expenses are fully underwritten by generous sponsors, 100% of your ticket amount goes directly into classroom &amp; scholarship grants.
                </p>
            </div>
        </div>

        <!-- Right Column: 4 Icon Pillars -->
        <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3.5 lg:border-l lg:border-[#D4AF37]/30 lg:pl-8">
            <div class="flex items-center gap-3.5 p-3 rounded-lg bg-[#24050B]/60 border border-[#D4AF37]/25">
                <div class="w-9 h-9 rounded-lg border border-[#D4AF37]/40 flex items-center justify-center text-[#E5C158] flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147L12 6l7.74 4.147M4.26 10.147v5.25C7.3 17.6 10 18.75 12 18.75s4.7-1.15 7.74-3.353v-5.25"/></svg>
                </div>
                <div>
                    <div class="font-serif-display text-lg font-medium text-[#FAF6F0]">Education Support</div>
                    <div class="text-[11px] text-[#D8CBB8]">Tuition assistance &amp; exam fee grants</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 p-3 rounded-lg bg-[#24050B]/60 border border-[#D4AF37]/25">
                <div class="w-9 h-9 rounded-lg border border-[#D4AF37]/40 flex items-center justify-center text-[#E5C158] flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292"/></svg>
                </div>
                <div>
                    <div class="font-serif-display text-lg font-medium text-[#FAF6F0]">Learning Resources</div>
                    <div class="text-[11px] text-[#D8CBB8]">Textbooks, notebooks &amp; digital study kits</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 p-3 rounded-lg bg-[#24050B]/60 border border-[#D4AF37]/25">
                <div class="w-9 h-9 rounded-lg border border-[#D4AF37]/40 flex items-center justify-center text-[#E5C158] flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"/></svg>
                </div>
                <div>
                    <div class="font-serif-display text-lg font-medium text-[#FAF6F0]">Safe &amp; Nurturing Spaces</div>
                    <div class="text-[11px] text-[#D8CBB8]">Safe study rooms &amp; mentorship hubs</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 p-3 rounded-lg bg-[#24050B]/60 border border-[#D4AF37]/25">
                <div class="w-9 h-9 rounded-lg border border-[#D4AF37]/40 flex items-center justify-center text-[#E5C158] flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m0-12c-3 0-5-2-5-5 3 0 5 2 5 5zm0 5c3 0 5-2 5-5-3 0-5 2-5 5z"/></svg>
                </div>
                <div>
                    <div class="font-serif-display text-lg font-medium text-[#FAF6F0]">Holistic Development</div>
                    <div class="text-[11px] text-[#D8CBB8]">Confidence, guidance &amp; life skills</div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 5: TICKETS & INTERACTIVE HALL SEATING LAYOUT (Warm Ivory)
     ======================================================================= --}}
<section id="tickets" class="scroll-mt-16 bg-[#FAF6F0] text-[#231715] py-14 sm:py-18 border-b border-[#E3D8C8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-10">
        <!-- Section Heading -->
        <div class="text-center space-y-2">
            <div class="flex items-center justify-center gap-3">
                <span class="h-px w-12 bg-[#D4AF37]"></span>
                <span class="font-serif-display text-2xl sm:text-3xl font-semibold tracking-wide text-[#3B0A14]">CHOOSE YOUR SEATS</span>
                <span class="h-px w-12 bg-[#D4AF37]"></span>
            </div>
            <p class="text-xs sm:text-sm text-[#5A4640]">Select a ticket category below or tap a zone on the Chavara Hall seating diagram.</p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8 items-center">
            <!-- Left 7 cols: Gold / Silver / Bronze Cards -->
            <div class="lg:col-span-7 grid sm:grid-cols-3 gap-4">
                @foreach ($categories as $cat)
                    @php
                        $lower = strtolower($cat->name);
                        $isGold = str_contains($lower, 'gold');
                        $isSilver = str_contains($lower, 'silver');
                    @endphp
                    <div data-tier-card="{{ $cat->id }}"
                         onclick="{{ !$cat->is_sold_out ? 'selectCategory(' . $cat->id . ')' : '' }}"
                         class="relative rounded-xl border p-5 flex flex-col justify-between transition {{ !$cat->is_sold_out ? 'cursor-pointer hover:border-[#3B0A14]' : 'opacity-65 cursor-not-allowed' }} {{ $isGold ? 'bg-white border-[#C59B27]' : 'bg-[#F5EFE6]/70 border-[#D4AF37]/40' }}">
                        <div class="space-y-3 text-center">
                            <span class="inline-block px-4 py-1 rounded-md text-xs font-semibold tracking-wider uppercase {{ $isGold ? 'btn-antique-gold' : ($isSilver ? 'bg-[#E5E0D8] text-[#3B0A14] border border-[#BEB5A6]' : 'bg-[#E6D5C8] text-[#3B0A14] border border-[#C6A28B]') }}">
                                {{ $cat->name }}
                            </span>

                            <div class="font-serif-display text-3xl sm:text-4xl font-semibold text-[#3B0A14]">
                                {{ $cat->formatted_price }}
                            </div>

                            <p class="text-xs text-[#5A4640] min-h-[2rem]">
                                {{ $cat->description }}
                            </p>

                            <!-- Seat Silhouette Ornament -->
                            <div class="flex items-center justify-center gap-1 pt-1 text-[#7A5C2E]/70" aria-hidden="true">
                                @for ($s = 0; $s < 5; $s++)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v7h1a1 1 0 011 1v4h-2v-2H5v2H3v-4a1 1 0 011-1h1V4z"/></svg>
                                @endfor
                            </div>

                            <!-- Genuine Low-Availability Badge ONLY when < 20 left or Sold Out -->
                            @if ($cat->is_sold_out)
                                <span class="inline-block px-2.5 py-0.5 rounded bg-[#3B0A14] text-[#FAF6F0] text-[10px] font-semibold">Sold Out</span>
                            @elseif ($cat->low_availability)
                                <span class="inline-block px-2.5 py-0.5 rounded bg-[#FAF6F0] border border-[#3B0A14]/40 text-[#3B0A14] text-[10px] font-semibold">
                                    Only {{ $cat->available }} seats left
                                </span>
                            @endif
                        </div>

                        <button type="button"
                            onclick="event.stopPropagation(); selectCategory({{ $cat->id }})"
                            {{ $cat->is_sold_out ? 'disabled' : '' }}
                            class="mt-5 w-full py-2 px-3 rounded-lg text-xs font-semibold transition {{ $cat->is_sold_out ? 'bg-[#E5DDD2] text-[#8E7F79]' : ($isGold ? 'btn-deep-burgundy' : 'bg-white hover:bg-[#3B0A14] hover:text-[#FAF6F0] border border-[#D4AF37]/55 text-[#3B0A14]') }}">
                            {{ $cat->is_sold_out ? 'Sold Out' : 'Select Category' }}
                        </button>
                    </div>
                @endforeach
            </div>

            <!-- Right 5 cols: Colour-Coded Interactive Hall Seating Layout (Chavara Hall) -->
            <div class="lg:col-span-5 rounded-xl bg-white border border-[#D4AF37]/45 p-5 space-y-4">
                <div class="text-center space-y-0.5">
                    <div class="text-xs font-semibold uppercase tracking-widest text-[#7A5C2E]">Seating Layout (Chavara Hall)</div>
                    <p class="text-[11px] text-[#5A4640]">Tap a zone to select your category</p>
                </div>

                <!-- Curved Stage Banner -->
                <div class="w-4/5 mx-auto py-2 rounded-b-2xl rounded-t-md bg-[#3B0A14] text-[#E5C158] text-center text-xs font-semibold tracking-[0.25em] uppercase border border-[#D4AF37]/50">
                    Stage
                </div>

                <!-- Tiered Hall Zones -->
                @php
                    $goldCat = $categories->first(fn($c) => str_contains(strtolower($c->name), 'gold'));
                    $silverCat = $categories->first(fn($c) => str_contains(strtolower($c->name), 'silver'));
                    $bronzeCat = $categories->first(fn($c) => str_contains(strtolower($c->name), 'bronze'));
                @endphp
                <div class="space-y-2.5 max-w-sm mx-auto">
                    <!-- Gold Zone -->
                    <button type="button"
                        @if($goldCat && !$goldCat->is_sold_out) onclick="selectCategory({{ $goldCat->id }})" @endif
                        class="w-full p-2.5 rounded-lg border border-[#C59B27] bg-[#FAF6F0] hover:bg-[#F3E9CE] transition text-center space-y-1.5">
                        <div class="flex justify-between text-[10px] font-semibold text-[#3B0A14]">
                            <span>Gold &bull; Rows A&ndash;D</span>
                            <span>₹2,500</span>
                        </div>
                        <div class="flex flex-wrap justify-center gap-1" aria-hidden="true">
                            @for ($i = 0; $i < 24; $i++)
                                <span class="w-2.5 h-2.5 rounded-[2px] bg-[#D4AF37]"></span>
                            @endfor
                        </div>
                    </button>

                    <!-- Silver Zone -->
                    <button type="button"
                        @if($silverCat && !$silverCat->is_sold_out) onclick="selectCategory({{ $silverCat->id }})" @endif
                        class="w-full p-2.5 rounded-lg border border-[#BEB5A6] bg-[#FAF6F0] hover:bg-[#ECE8E1] transition text-center space-y-1.5">
                        <div class="flex justify-between text-[10px] font-semibold text-[#3B0A14]">
                            <span>Silver &bull; Rows E&ndash;H</span>
                            <span>₹1,500</span>
                        </div>
                        <div class="flex flex-wrap justify-center gap-1" aria-hidden="true">
                            @for ($i = 0; $i < 28; $i++)
                                <span class="w-2.5 h-2.5 rounded-[2px] bg-[#9E978E]"></span>
                            @endfor
                        </div>
                    </button>

                    <!-- Bronze Zone -->
                    <button type="button"
                        @if($bronzeCat && !$bronzeCat->is_sold_out) onclick="selectCategory({{ $bronzeCat->id }})" @endif
                        class="w-full p-2.5 rounded-lg border border-[#C6A28B] bg-[#FAF6F0] hover:bg-[#F3E6DE] transition text-center space-y-1.5">
                        <div class="flex justify-between text-[10px] font-semibold text-[#3B0A14]">
                            <span>Bronze &bull; Rows J&ndash;S</span>
                            <span>₹1,000</span>
                        </div>
                        <div class="flex flex-wrap justify-center gap-1" aria-hidden="true">
                            @for ($i = 0; $i < 42; $i++)
                                <span class="w-2.5 h-2.5 rounded-[2px] bg-[#B47B68]"></span>
                            @endfor
                        </div>
                    </button>
                </div>

                <!-- Legend & Seating Rule -->
                <div class="flex items-center justify-center gap-4 text-[11px] text-[#42322D] pt-1">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#D4AF37]"></span>Gold</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#9E978E]"></span>Silver</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#B47B68]"></span>Bronze</span>
                </div>
                <p class="text-[11px] text-[#5A4640] text-center leading-relaxed border-t border-[#EBE0D0] pt-2.5">
                    Within each category, seating is on a <strong>first-come, first-served</strong> basis (gates open at 6:15 PM), except designated Sponsor/VIP front rows.
                </p>
            </div>
        </div>

        <!-- Unified Common Benefits Bar (Single clean strip below all categories) -->
        <div class="rounded-xl bg-white border border-[#D4AF37]/40 px-5 py-3.5 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-xs font-medium text-[#3B0A14]">
            <div class="inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-[#7A5C2E]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h2m2 0h2m-4-3h4v4h-2"/></svg>
                <span>Individual QR Entry Pass</span>
            </div>
            <span class="hidden sm:inline text-[#D4AF37]">|</span>
            <div class="inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-[#7A5C2E]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Admit 1 per ticket</span>
            </div>
            <span class="hidden sm:inline text-[#D4AF37]">|</span>
            <div class="inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-[#3B0A14]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                <span>Proceeds towards girls’ education</span>
            </div>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 6: SIMPLIFIED TICKET BOOKING FORM
     ======================================================================= --}}
<section id="register" class="scroll-mt-16 bg-[#F5EFE6] text-[#231715] py-14 sm:py-18 border-b border-[#E3D8C8]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="rounded-xl bg-white border border-[#D4AF37]/45 p-6 sm:p-10 space-y-7">
            <div class="space-y-1.5 border-b border-[#EBE0D0] pb-4">
                <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-[#7A5C2E]">Attend the Event</div>
                <h2 class="font-serif-display text-3xl sm:text-4xl font-semibold text-[#3B0A14]">Reserve Your Seats</h2>
                <p class="text-xs sm:text-sm text-[#5A4640]">
                    Select your category and guest count below to place a temporary {{ $holdMinutes }}-minute seat hold, receive your booking reference, and complete payment.
                </p>
            </div>

            <form action="{{ route('register.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="attending" value="yes">
                <input type="hidden" name="utm_source" value="{{ $utmParams['utm_source'] }}">
                <input type="hidden" name="utm_medium" value="{{ $utmParams['utm_medium'] }}">
                <input type="hidden" name="utm_campaign" value="{{ $utmParams['utm_campaign'] }}">
                <input type="hidden" name="utm_content" value="{{ $utmParams['utm_content'] }}">

                <!-- STEP 1: Ticket Category & Number of Guests First -->
                <div class="space-y-4 p-5 rounded-xl bg-[#FAF6F0] border border-[#E3D8C8]">
                    <div class="text-xs font-semibold uppercase tracking-wider text-[#3B0A14]">Step 1 &bull; Category &amp; Number of Guests</div>
                    <div class="grid sm:grid-cols-12 gap-4 items-end">
                        <div class="sm:col-span-5 space-y-1.5">
                            <label for="ticket_category_id" class="block text-xs font-semibold text-[#3B0A14]">Ticket Category <span class="text-[#7A5C2E]">*</span></label>
                            <select id="ticket_category_id" name="ticket_category_id" required onchange="updatePriceCalculation()"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-[#D4AF37]/50 text-sm text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                                <option value="">-- Select Ticket Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        data-price="{{ $cat->price_paise }}"
                                        data-name="{{ $cat->name }}"
                                        {{ old('ticket_category_id') == $cat->id ? 'selected' : '' }}
                                        {{ $cat->is_sold_out ? 'disabled' : '' }}>
                                        {{ $cat->name }} — {{ $cat->formatted_price }}{{ $cat->is_sold_out ? ' (Sold Out)' : ($cat->low_availability ? " (Only {$cat->available} left)" : '') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3 space-y-1.5">
                            <label for="quantity" class="block text-xs font-semibold text-[#3B0A14]">Number of Guests <span class="text-[#7A5C2E]">*</span></label>
                            <select id="quantity" name="quantity" required onchange="updatePriceCalculation()"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-[#D4AF37]/50 text-sm text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('quantity', 1) == $i ? 'selected' : '' }}>
                                        {{ $i }} {{ $i === 1 ? 'Guest' : 'Guests' }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="sm:col-span-4 p-3 rounded-lg bg-white border border-[#D4AF37]/50 text-right">
                            <div class="text-[10px] uppercase tracking-wider font-semibold text-[#7A5C2E]">Estimated Total</div>
                            <div id="calculated_total" class="font-serif-display text-lg sm:text-xl font-semibold text-[#3B0A14] mt-0.5">
                                Select a ticket category
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Guest Contact Details -->
                <div class="space-y-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-[#3B0A14]">Step 2 &bull; Guest Contact Details</div>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label for="full_name" class="block text-xs font-semibold text-[#3B0A14]">Full Name <span class="text-[#7A5C2E]">*</span></label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required placeholder="e.g. Aarav Sharma"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-sm text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                        </div>

                        <div class="space-y-1.5">
                            <label for="phone" class="block text-xs font-semibold text-[#3B0A14]">WhatsApp Number <span class="text-[#7A5C2E]">*</span></label>
                            <div class="flex gap-1.5">
                                <span class="inline-flex items-center px-3 rounded-lg border border-[#D4AF37]/45 bg-[#F5EFE6] text-xs font-semibold text-[#3B0A14]">+91</span>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required pattern="[0-9]{10}" maxlength="10" placeholder="10-digit number"
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-sm text-[#231715] font-mono focus:outline-none focus:border-[#3B0A14]">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="email" class="block text-xs font-semibold text-[#3B0A14]">Email Address <span class="text-[#7A5C2E]">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="aarav@example.com"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-sm text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                        </div>
                    </div>
                </div>

                <!-- Calm Charitable Notice & Consents -->
                <div class="space-y-3">
                    <div class="p-4 rounded-xl bg-[#FAF6F0] border border-[#E3D8C8] text-xs text-[#42322D] space-y-1 leading-relaxed">
                        <div class="font-semibold text-[#3B0A14]">Booking &amp; Transfer Notice</div>
                        <p>
                            As this is a charitable fundraising event, all confirmed and paid bookings are non-refundable. Ticket transfers to another guest are permitted prior to the event; in the unlikely event of cancellation or postponement, full refunds or rescheduled dates will be offered. We appreciate your understanding and support for the cause.
                        </p>
                    </div>

                    <label class="flex items-start gap-2.5 cursor-pointer text-xs text-[#3B0A14]">
                        <input type="checkbox" name="privacy_consent" value="1" required class="mt-0.5 w-4 h-4 rounded border-[#C59B27] text-[#3B0A14] focus:ring-0">
                        <span>
                            I confirm the details above for issuing my Booking Reference and QR Entry Passes, and acknowledge the charitable event booking policy.
                        </span>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer text-xs text-[#5A4640]">
                        <input type="checkbox" name="marketing_opt_in" value="1" {{ old('marketing_opt_in') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded border-[#C59B27] text-[#3B0A14] focus:ring-0">
                        <span>
                            Keep me updated on future Joygivers musical evenings and girls’ education initiatives (optional).
                        </span>
                    </label>
                </div>

                <div class="space-y-2">
                    <button type="submit" id="submit_btn"
                        class="btn-antique-gold w-full py-3.5 px-6 rounded-lg font-semibold text-sm tracking-wide transition">
                        Request Reservation &rarr;
                    </button>
                    <p class="text-[11px] text-[#5A4640] text-center">
                        Submitting creates a reservation request with a temporary {{ $holdMinutes }}-minute seat hold while you complete UPI payment. Seats are confirmed after payment verification.
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 7: SEPARATE "SUPPORT THE CAUSE" DONATIONS MODULE
     ======================================================================= --}}
<section id="donate" class="scroll-mt-16 bg-[#FAF6F0] text-[#231715] py-14 sm:py-18 border-b border-[#E3D8C8]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="rounded-xl bg-white border border-[#D4AF37]/45 p-6 sm:p-10 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#EBE0D0] pb-4">
                <div>
                    <span class="text-[11px] uppercase tracking-[0.2em] font-semibold text-[#7A5C2E]">Support the Cause &bull; Without Attending</span>
                    <h2 class="font-serif-display text-3xl font-semibold text-[#3B0A14]">Direct Contribution for Girls’ Education</h2>
                    <p class="text-xs sm:text-sm text-[#5A4640] mt-1">
                        Unable to attend in Kochi? Contribute any amount directly towards scholarships and learning kits. <strong>Donations never reduce concert hall seating inventory.</strong>
                    </p>
                </div>
                <span class="self-start px-3 py-1 rounded-md bg-[#3B0A14] text-[#E5C158] text-[11px] font-semibold whitespace-nowrap">
                    0 Hall Seats Used
                </span>
            </div>

            <form id="cause_donation_form" action="{{ route('donate.store') }}" method="POST" class="space-y-5">
                @csrf
                <!-- Preset Amounts + Custom Input -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-[#3B0A14]">Select Contribution Amount (INR)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                        @foreach ([500, 1000, 2500, 5000] as $preset)
                            <button type="button" onclick="setDonationAmount({{ $preset }}, this)"
                                data-preset-btn="{{ $preset }}"
                                class="py-2.5 px-3 rounded-lg border text-sm font-semibold transition {{ $preset === 1000 ? 'bg-[#3B0A14] text-[#E5C158] border-[#D4AF37]' : 'bg-[#FAF6F0] text-[#3B0A14] border-[#D4AF37]/45 hover:border-[#3B0A14]' }}">
                                ₹{{ number_format($preset, 0) }}
                            </button>
                        @endforeach
                        <div class="relative col-span-2 sm:col-span-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-[#7A5C2E]">₹</span>
                            <input type="number" id="donate_amount_rupees" name="amount_rupees" min="100" max="5000000" value="1000" required
                                oninput="clearPresetHighlight()"
                                placeholder="Custom"
                                aria-label="Custom donation amount in rupees"
                                class="w-full pl-7 pr-3 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/50 text-sm font-semibold text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                        </div>
                    </div>
                </div>

                <!-- Donor Info -->
                <div class="grid sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label for="donor_full_name" class="block text-xs font-semibold text-[#3B0A14]">Donor Name *</label>
                        <input type="text" id="donor_full_name" name="full_name" required placeholder="Your full name"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-sm text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                    </div>
                    <div class="space-y-1">
                        <label for="donor_phone" class="block text-xs font-semibold text-[#3B0A14]">WhatsApp Number</label>
                        <input type="tel" id="donor_phone" name="phone" maxlength="10" pattern="[0-9]{10}" placeholder="10-digit WhatsApp"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-sm font-mono text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                    </div>
                    <div class="space-y-1">
                        <label for="donor_email" class="block text-xs font-semibold text-[#3B0A14]">Email Address</label>
                        <input type="email" id="donor_email" name="email" placeholder="donor@example.com"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-sm text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 items-end">
                    <div class="space-y-1">
                        <label for="donor_note" class="block text-xs font-semibold text-[#3B0A14]">Dedication / Message (Optional)</label>
                        <input type="text" id="donor_note" name="donor_note" placeholder="e.g. Towards school books & uniforms"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-xs text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                    </div>
                    <button type="submit" id="donate_submit_btn"
                        class="btn-deep-burgundy py-3 px-6 rounded-lg font-semibold text-xs sm:text-sm tracking-wide transition">
                        Support the Cause &amp; Get Reference &rarr;
                    </button>
                </div>

                <div id="donate_feedback_box" class="hidden p-4 rounded-xl border text-xs space-y-2"></div>
            </form>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 8: ABOUT JOYGIVERS & PAST IMPACT
     ======================================================================= --}}
<section id="about-joygivers" class="scroll-mt-16 bg-[#F5EFE6] text-[#231715] py-14 sm:py-18 border-b border-[#E3D8C8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-10">
        <div class="grid lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-6 space-y-4">
                <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-[#7A5C2E]">About Joygivers</div>
                <h2 class="font-serif-display text-3xl sm:text-4xl font-semibold text-[#3B0A14]">
                    Grassroots Impact Across Kerala
                </h2>
                <p class="text-sm text-[#4A3B36] leading-relaxed">
                    <strong>Joygivers</strong> brings together community volunteers, educators, and cultural patrons to support girls’ schooling across Ernakulam and rural Kerala. Beyond organizing benefit evenings like <em>Raag-E-Ishq</em>, Joygivers delivers school bags, bilingual books, exam-fee stipends, and supervised study support throughout the academic year.
                </p>

                <!-- Impact Metrics Strip -->
                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div class="p-3.5 rounded-lg bg-white border border-[#D4AF37]/40 text-center">
                        <div class="font-serif-display text-2xl font-semibold text-[#3B0A14]">1,200+</div>
                        <div class="text-[11px] text-[#5A4640]">Girls Supported*</div>
                    </div>
                    <div class="p-3.5 rounded-lg bg-white border border-[#D4AF37]/40 text-center">
                        <div class="font-serif-display text-2xl font-semibold text-[#3B0A14]">45+</div>
                        <div class="text-[11px] text-[#5A4640]">Partner Schools*</div>
                    </div>
                    <div class="p-3.5 rounded-lg bg-white border border-[#D4AF37]/40 text-center">
                        <div class="font-serif-display text-2xl font-semibold text-[#3B0A14]">100%</div>
                        <div class="text-[11px] text-[#5A4640]">Net Cause Focus</div>
                    </div>
                </div>

                <!-- Registration & 80G Tax Exemption Slot -->
                <div class="p-4 rounded-xl bg-white border border-[#D4AF37]/40 text-xs text-[#42322D] space-y-1">
                    <div class="font-semibold text-[#3B0A14]">Trust Registration &amp; 80G Details</div>
                    <p>
                        Registered Charitable Initiative — Kochi, Kerala. Official Trust Registration No. &amp; 80G tax-exemption status will be displayed here upon final compliance verification.
                    </p>
                </div>
            </div>

            <!-- Right 6 cols: Initiative Photo Cards & Caption Slots -->
            <div class="lg:col-span-6 grid sm:grid-cols-2 gap-4">
                <div class="rounded-xl bg-white border border-[#D4AF37]/40 overflow-hidden">
                    <img src="{{ asset('images/joygivers-classroom.jpg') }}"
                         alt="Schoolgirls studying together with textbooks in a community classroom"
                         class="w-full h-44 object-cover" />
                    <div class="p-4 space-y-1">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-[#7A5C2E]">Classroom Support</div>
                        <div class="font-serif-display text-lg font-semibold text-[#3B0A14]">Scholarship &amp; Tuition Drives</div>
                        <p class="text-xs text-[#5A4640]">Funding annual exam fees, uniforms, and school kits for secondary students. *(Illustrative photo until latest event gallery is uploaded)*</p>
                    </div>
                </div>

                <div class="rounded-xl bg-white border border-[#D4AF37]/40 p-5 flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="w-full h-36 rounded-lg bg-[#FAF6F0] border border-dashed border-[#D4AF37]/60 flex items-center justify-center text-center p-4">
                            <span class="text-xs text-[#7A5C2E]">Photo Slot 2: Recent Book &amp; Learning Kit Distribution (with consent)</span>
                        </div>
                        <div class="font-serif-display text-lg font-semibold text-[#3B0A14]">Mentorship &amp; Study Kits</div>
                        <p class="text-xs text-[#5A4640]">
                            Community study circles, bilingual reading corners, and mentorship guidance supporting girls through board exams and higher education.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 9: SPONSORS SHOWCASE STRIP
     ======================================================================= --}}
<section id="sponsors" class="scroll-mt-16 bg-[#FAF6F0] text-[#231715] py-10 sm:py-12 border-b border-[#E3D8C8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-6 text-center">
        <div class="space-y-1">
            <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-[#7A5C2E]">With Gratitude</div>
            <h2 class="font-serif-display text-2xl sm:text-3xl font-semibold text-[#3B0A14]">Concert Partners &amp; Sponsors</h2>
            <p class="text-xs text-[#5A4640]">Event sponsorship helps underwrite venue and production costs so maximum proceeds reach girls’ education.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-4xl mx-auto text-xs">
            <div class="p-4 rounded-xl bg-white border border-[#D4AF37]/40 space-y-1">
                <div class="text-[10px] uppercase tracking-wider text-[#7A5C2E]">Venue Partner</div>
                <div class="font-serif-display text-base font-semibold text-[#3B0A14]">Chavara Cultural Centre</div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-[#D4AF37]/40 space-y-1">
                <div class="text-[10px] uppercase tracking-wider text-[#7A5C2E]">Cause Steward</div>
                <div class="font-serif-display text-base font-semibold text-[#3B0A14]">Joygivers Initiative</div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-dashed border-[#D4AF37]/50 space-y-1">
                <div class="text-[10px] uppercase tracking-wider text-[#7A5C2E]">Lead Sponsor Slot</div>
                <div class="font-serif-display text-base font-medium text-[#5A4640]">Partner With Us</div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-dashed border-[#D4AF37]/50 space-y-1">
                <div class="text-[10px] uppercase tracking-wider text-[#7A5C2E]">Hospitality Slot</div>
                <div class="font-serif-display text-base font-medium text-[#5A4640]">Sponsorship Open</div>
            </div>
        </div>
    </div>
</section>


{{-- =======================================================================
     SECTION 10: HELPFUL INFO, FAQS & MAPS (Before Contact Footer)
     ======================================================================= --}}
<section id="faqs" class="scroll-mt-16 bg-[#F5EFE6] text-[#231715] py-14 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-10">
        <!-- Quick Practical Info Strip (Matches Reference Bottom Strip) -->
        <div class="rounded-xl bg-white border border-[#D4AF37]/45 p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-center text-xs">
            <div class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#7A5C2E] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <div>
                    <div class="font-semibold text-[#3B0A14]">Date &amp; Gate Time</div>
                    <div class="text-[#5A4640]">17 Jan 2027 &bull; Gates 6:15 PM<br>Programme 7:00 PM Onwards</div>
                </div>
            </div>

            <div class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#7A5C2E] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-6-5.3-6-10.5A6 6 0 1118 10.5C18 15.7 12 21 12 21z"/><circle cx="12" cy="10.5" r="2"/></svg>
                <div>
                    <div class="font-semibold text-[#3B0A14]">Venue</div>
                    <div class="text-[#5A4640]">Chavara Hall, Near South Rly Stn, Kochi</div>
                </div>
            </div>

            <div class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#7A5C2E] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <div>
                    <div class="font-semibold text-[#3B0A14]">Duration</div>
                    <div class="text-[#5A4640]">Approx. 2.5 &ndash; 3 Hours<br>(with brief intermission)</div>
                </div>
            </div>

            <div class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#7A5C2E] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 17h14M6 9l1.5-4h9L18 9m-12 0h12v6H6V9z"/></svg>
                <div>
                    <div class="font-semibold text-[#3B0A14]">Parking &amp; Access</div>
                    <div class="text-[#5A4640]">On-site parking + wheelchair accessible ground entry</div>
                </div>
            </div>

            <div class="flex flex-wrap lg:justify-end gap-2">
                <a href="{{ route('event.calendar') }}" class="btn-antique-gold px-3.5 py-2 rounded-lg font-semibold text-xs">
                    Add to Calendar (.ics)
                </a>
                <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ urlencode('Raag-E-Ishq Featuring Kuldeep Sagar | Joygivers') }}&dates=20270117T133000Z/20270117T163000Z&location={{ urlencode('Chavara Hall, Near South Railway Station, Kochi, Kerala') }}&details={{ urlencode('Evening of timeless Hindi melodies by Kuldeep Sagar and a live orchestra in support of girls\' education.') }}"
                   target="_blank" rel="noopener noreferrer"
                   class="px-3 py-2 rounded-lg border border-[#D4AF37]/55 bg-[#FAF6F0] hover:bg-white text-[#3B0A14] font-semibold text-xs">
                    Google Calendar
                </a>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-8 items-start">
            <!-- Left 7 cols: Accessible FAQ Accordion -->
            <div class="lg:col-span-7 space-y-3" aria-labelledby="faq_heading">
                <div class="space-y-1 pb-2">
                    <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-[#7A5C2E]">Helpful Information</div>
                    <h2 id="faq_heading" class="font-serif-display text-3xl font-semibold text-[#3B0A14]">Frequently Asked Questions</h2>
                </div>

                @php
                    $faqs = [
                        ['q' => 'What time do gates open and how long is the programme?', 'a' => 'Gates at Chavara Hall open at 6:15 PM for relaxed QR Entry Pass check-in. The live concert begins at 7:00 PM IST and runs approximately 2.5 to 3 hours including a short intermission.'],
                        ['q' => 'How does the 30-minute reservation hold and confirmation process work?', 'a' => 'Submitting the reservation form places a temporary 30-minute hold on your selected seats and gives you a Booking Reference (e.g., REI-...). Once you complete UPI payment and submit your UTR reference, our team verifies it and issues your individual QR Entry Passes via WhatsApp and Email.'],
                        ['q' => 'Are seats within Gold, Silver, and Bronze allocated or first-come, first-served?', 'a' => 'Seating within each zone (Gold front rows, Silver central rows, and Bronze general rows) is first-come, first-served on the evening of the programme, except specifically marked Sponsor and VIP front seats.'],
                        ['q' => 'Can I transfer my ticket if I cannot attend, or donate without attending?', 'a' => 'Yes! While confirmed charitable bookings are non-refundable, ticket transfers to another guest are permitted prior to the event by messaging our WhatsApp desk. If you simply wish to contribute without attending, use the dedicated Support the Cause section above.'],
                        ['q' => 'Is venue parking and wheelchair access available at Chavara Hall?', 'a' => 'Chavara Cultural Centre (near Ernakulam South Railway Station) provides on-site and nearby parking options along with step-free, wheelchair-friendly hall entry. Please inform our gate volunteers on arrival if you need priority seating assistance.'],
                    ];
                @endphp

                @foreach ($faqs as $faq)
                    <details class="faq-item rounded-xl bg-white border border-[#D4AF37]/40 px-5 py-3.5 text-sm">
                        <summary class="flex items-center justify-between gap-3 font-medium text-[#3B0A14]">
                            <span>{{ $faq['q'] }}</span>
                            <svg class="faq-chevron w-4 h-4 text-[#7A5C2E] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <p class="mt-2.5 pt-2.5 border-t border-[#EBE0D0] text-xs text-[#5A4640] leading-relaxed">
                            {{ $faq['a'] }}
                        </p>
                    </details>
                @endforeach
            </div>

            <!-- Right 5 cols: Chavara Hall Google Map & WhatsApp Support -->
            <div class="lg:col-span-5 space-y-4">
                <div class="rounded-xl bg-white border border-[#D4AF37]/45 overflow-hidden">
                    <iframe
                        title="Google Map — Chavara Cultural Centre, Kochi"
                        src="https://www.google.com/maps?q=Chavara+Cultural+Centre+Near+South+Railway+Station+Kochi+Kerala&output=embed"
                        class="w-full h-56 border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <div class="p-4 flex items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="font-semibold text-[#3B0A14]">Chavara Cultural Centre</div>
                            <div class="text-[#5A4640]">South Railway Station Road, Kochi</div>
                        </div>
                        <a href="https://maps.google.com/?q=Chavara+Cultural+Centre+Kochi" target="_blank" rel="noopener noreferrer"
                           class="btn-deep-burgundy px-3.5 py-2 rounded-lg font-semibold whitespace-nowrap">
                            Directions &rarr;
                        </a>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white border border-[#D4AF37]/40 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-semibold text-[#3B0A14]">Need help booking?</div>
                        <div class="text-[#5A4640]">Official Concierge &bull; 9 AM &ndash; 9 PM</div>
                    </div>
                    <a href="https://wa.me/917736606506?text={{ urlencode('Hi Joygivers, I have an enquiry about Raag-E-Ishq tickets at Chavara Hall.') }}"
                       target="_blank" rel="noopener noreferrer"
                       class="btn-antique-gold px-4 py-2 rounded-lg font-semibold whitespace-nowrap">
                        WhatsApp Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function formatInrWhole(paise) {
        const rupees = paise / 100;
        return '₹' + Number(rupees).toLocaleString('en-IN', { maximumFractionDigits: paise % 100 === 0 ? 0 : 2 });
    }

    function updatePriceCalculation() {
        const catSelect = document.getElementById('ticket_category_id');
        const qtySelect = document.getElementById('quantity');
        const totalDisplay = document.getElementById('calculated_total');
        if (!catSelect || !qtySelect || !totalDisplay) return;

        const selectedOption = catSelect.options[catSelect.selectedIndex];
        const pricePaise = selectedOption ? parseInt(selectedOption.getAttribute('data-price') || '0', 10) : 0;
        const qty = parseInt(qtySelect.value || '1', 10);

        if (!catSelect.value || !pricePaise) {
            totalDisplay.textContent = 'Select a ticket category';
            return;
        }

        const unitText = formatInrWhole(pricePaise);
        const totalText = formatInrWhole(pricePaise * qty);
        totalDisplay.textContent = `${unitText} × ${qty} = ${totalText}`;
    }

    function selectCategory(catId) {
        const select = document.getElementById('ticket_category_id');
        if (!select) return;
        select.value = catId;
        updatePriceCalculation();
        document.querySelectorAll('[data-tier-card]').forEach((card) => {
            const match = parseInt(card.getAttribute('data-tier-card'), 10) === catId;
            card.classList.toggle('ring-2', match);
            card.classList.toggle('ring-[#3B0A14]', match);
        });
        document.getElementById('register')?.scrollIntoView({ behavior: 'smooth' });
    }

    function clearPresetHighlight() {
        document.querySelectorAll('[data-preset-btn]').forEach((btn) => {
            btn.className = 'py-2.5 px-3 rounded-lg border text-sm font-semibold transition bg-[#FAF6F0] text-[#3B0A14] border-[#D4AF37]/45 hover:border-[#3B0A14]';
        });
    }

    function setDonationAmount(amount, clickedBtn) {
        const input = document.getElementById('donate_amount_rupees');
        if (input) input.value = amount;
        clearPresetHighlight();
        if (clickedBtn) {
            clickedBtn.className = 'py-2.5 px-3 rounded-lg border text-sm font-semibold transition bg-[#3B0A14] text-[#E5C158] border-[#D4AF37]';
        }
    }

    document.getElementById('cause_donation_form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = this;
        const feedback = document.getElementById('donate_feedback_box');
        const submitBtn = document.getElementById('donate_submit_btn');
        if (!feedback || !submitBtn) return;

        submitBtn.disabled = true;
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Recording Contribution...';
        feedback.classList.add('hidden');

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token'),
                },
                body: formData,
            });

            const data = await res.json();
            if (!res.ok) {
                const errMsg = data?.errors ? Object.values(data.errors).flat()[0] : (data?.message || 'Unable to process donation.');
                feedback.className = 'p-4 rounded-xl border border-[#641728]/40 bg-[#FDF2F4] text-[#3B0A14] text-xs space-y-2';
                feedback.textContent = errMsg;
                feedback.classList.remove('hidden');
            } else {
                feedback.className = 'p-5 rounded-xl border border-[#C59B27] bg-[#FAF6F0] text-[#231715] text-xs space-y-3';
                feedback.innerHTML = `
                    <div class="font-semibold text-[#3B0A14] text-sm">Instant Contribution Acknowledgement &bull; Ref: <span class="font-mono">${data.donation_reference}</span></div>
                    <p>${data.message}</p>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <a href="${data.upi_uri}" class="btn-antique-gold px-4 py-2 rounded-lg font-semibold">Pay ${data.formatted_amount} via UPI &rarr;</a>
                        <a href="${data.acknowledgement_url}" class="btn-deep-burgundy px-4 py-2 rounded-lg font-semibold">Open Receipt &amp; Submit UTR &rarr;</a>
                    </div>
                `;
                feedback.classList.remove('hidden');
                form.reset();
                document.getElementById('donate_amount_rupees').value = 1000;
            }
        } catch (err) {
            form.submit();
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    document.addEventListener('DOMContentLoaded', updatePriceCalculation);
</script>
@endsection
