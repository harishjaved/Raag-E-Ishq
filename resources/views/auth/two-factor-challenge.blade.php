@extends('layouts.app')

@section('title', 'Two-Factor Verification — Raag-E-Ishq')

@section('content')
<div class="bg-[#FAF6F0] text-[#231715] py-14 sm:py-20 px-4">
    <div class="max-w-md mx-auto space-y-6">
        <div class="text-center space-y-2">
            <span class="inline-block text-[11px] uppercase font-semibold tracking-[0.2em] text-[#7A5C2E]">Security Check</span>
            <h1 class="text-3xl font-semibold font-serif-display text-[#3B0A14]">Two-Factor Verification</h1>
            <p class="text-xs text-[#5A4640]">Enter the 6-digit code from your authenticator app or an 8-character recovery code.</p>
        </div>

        <div class="p-6 sm:p-8 rounded-xl bg-white border border-[#D4AF37]/40 space-y-5">
            <form action="{{ route('two-factor.verify') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="code" class="block text-xs font-semibold text-[#3B0A14]">Authenticator or Recovery Code</label>
                    <input type="text" id="code" name="code" required autofocus autocomplete="one-time-code"
                        placeholder="123456 or ABCD-EFGH"
                        class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-[#231715] font-mono tracking-widest text-center focus:outline-none focus:border-[#3B0A14] text-base">
                </div>

                <button type="submit"
                    class="w-full py-3 px-6 rounded-lg bg-gradient-to-r from-[#E5C158] to-[#B8860B] hover:brightness-105 text-[#231715] font-semibold text-sm transition">
                    Verify &amp; Continue
                </button>
            </form>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-medium text-[#7A5C2E] hover:underline">&larr; Back to Sign In</a>
            </div>
        </div>
    </div>
</div>
@endsection
