@extends('layouts.app')

@section('title', 'Retrieve Your Passes — Raag-E-Ishq | Joygivers')

@section('content')
<div class="bg-[#FAF6F0] text-[#231715] py-14 sm:py-20 px-4">
    <div class="max-w-md mx-auto space-y-6">
        <div class="text-center space-y-2">
            <span class="text-[11px] uppercase font-semibold tracking-[0.18em] text-[#7A5C2E]">Pass Lookup</span>
            <h1 class="text-3xl font-semibold font-serif-display text-[#3B0A14]">Retrieve Your QR Entry Passes</h1>
            <p class="text-xs text-[#5A4640]">
                Enter the email address used during registration to resend your booking reference and QR Entry Passes.
            </p>
        </div>

        @if (session('status'))
            <div class="p-4 rounded-xl bg-white border border-[#C59B27] text-[#3B0A14] text-xs font-medium">
                {{ session('status') }}
            </div>
        @endif

        <div class="p-6 sm:p-7 rounded-xl bg-white border border-[#D4AF37]/40">
            <form action="{{ route('tickets.resend.send') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="resend_email" class="block text-xs font-semibold text-[#3B0A14]">Registered Email Address</label>
                    <input type="email" id="resend_email" name="email" value="{{ old('email') }}" required placeholder="e.g. aarav@example.com"
                        class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-[#231715] placeholder-[#9B857B] focus:outline-none focus:border-[#3B0A14] text-sm">
                </div>

                <button type="submit" class="btn-antique-gold w-full py-3 px-6 rounded-lg font-semibold text-sm transition">
                    Resend My Passes &rarr;
                </button>
            </form>
        </div>

        <div class="text-center">
            <a href="{{ route('home') }}" class="text-xs font-semibold text-[#7A5C2E] hover:underline">&larr; Return to Main Page</a>
        </div>
    </div>
</div>
@endsection
