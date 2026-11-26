@extends('layouts.app')

@section('title', 'Staff Sign In — Raag-E-Ishq | Joygivers')

@section('content')
<div class="bg-[#FAF6F0] text-[#231715] py-14 sm:py-20 px-4">
    <div class="max-w-md mx-auto space-y-6">
        <div class="text-center space-y-2">
            <span class="inline-block text-[11px] uppercase font-semibold tracking-[0.2em] text-[#7A5C2E]">Authorized Access Only</span>
            <h1 class="text-3xl font-semibold font-serif-display text-[#3B0A14]">Staff &amp; Operations Sign In</h1>
            <p class="text-xs text-[#5A4640]">Sign in with your Joygivers staff credentials for Back Office or Gate Operations.</p>
        </div>

        <div class="p-6 sm:p-8 rounded-xl bg-white border border-[#D4AF37]/40 space-y-5">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-semibold text-[#3B0A14]">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        placeholder="staff@joygivers.org"
                        class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-[#231715] placeholder-[#9B857B] focus:outline-none focus:border-[#3B0A14] text-sm">
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-semibold text-[#3B0A14]">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                        class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-[#231715] placeholder-[#9B857B] focus:outline-none focus:border-[#3B0A14] text-sm">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-[#5A4640]">
                        <input type="checkbox" name="remember" value="1" class="rounded border-[#C59B27] text-[#3B0A14] focus:ring-0">
                        <span>Keep me signed in on this trusted device</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-6 rounded-lg bg-gradient-to-r from-[#E5C158] to-[#B8860B] hover:brightness-105 text-[#231715] font-semibold text-sm tracking-wide transition">
                    Sign In to Portal
                </button>
            </form>

            <div class="pt-4 border-t border-[#E6DCD0] text-[11px] text-[#6E5952] text-center space-y-1">
                <p>Protected by server-side RBAC permissions, login rate limiting, and optional TOTP two-factor authentication.</p>
                <p>Need access or a password reset? Contact your Joygivers Super Administrator.</p>
            </div>
        </div>
    </div>
</div>
@endsection
