@extends('layouts.app')

@section('title', 'Contribution Acknowledgement — Joygivers Cause')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10 space-y-6">
    <div class="relative p-6 sm:p-8 rounded-xl bg-[#3B0A14] text-[#FAF6F0] border border-[#D4AF37]/50 text-center space-y-3">
        <a href="{{ route('home') }}"
            aria-label="Close acknowledgement and return to main page"
            title="Return to Main Page"
            class="no-print absolute top-4 right-4 w-9 h-9 rounded-full bg-[#24050B] hover:bg-[#D4AF37] text-[#FAF6F0] hover:text-[#231715] border border-[#D4AF37]/40 flex items-center justify-center transition">
            &times;
        </a>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#D4AF37]/15 border border-[#D4AF37]/40 text-[#E5C158] text-[11px] font-semibold uppercase tracking-wider">
            <span>{{ $donation->status === 'ACKNOWLEDGED' ? 'Contribution Acknowledged' : ($donation->status === 'PAYMENT_SUBMITTED' ? 'UTR Received — Verifying' : 'Contribution Registered') }}</span>
        </div>
        <h1 class="font-serif-display text-3xl font-semibold text-[#FAF6F0]">Thank You for Supporting Girls’ Education</h1>
        <p class="text-xs text-[#E8DFD3] max-w-md mx-auto">
            Your standalone contribution goes directly towards Joygivers educational initiatives without deducting any hall seating.
        </p>
        <div class="pt-1 text-xs text-[#E5C158] font-mono">
            Donation Reference: <strong>{{ $donation->donation_reference }}</strong>
        </div>
    </div>

    <div class="p-6 sm:p-7 rounded-xl bg-white border border-[#D4AF37]/40 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-[#EBE0D0]">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-[#7A5C2E] font-semibold">Donor Details</div>
                <div class="text-base font-semibold text-[#3B0A14]">{{ $donation->contact->full_name }}</div>
                <div class="text-xs text-[#5A4640]">{{ $donation->contact->phone }} &bull; {{ $donation->contact->email }}</div>
            </div>
            <div class="text-right">
                <div class="text-[11px] uppercase tracking-wider text-[#7A5C2E] font-semibold">Amount</div>
                <div class="text-2xl font-serif-display font-semibold text-[#3B0A14]">{{ $donation->formatted_amount }}</div>
            </div>
        </div>

        @if ($donation->status !== 'ACKNOWLEDGED')
            <div class="p-4 rounded-lg bg-[#FAF6F0] border border-[#E3D8C8] space-y-3 text-xs">
                <div class="font-semibold text-[#3B0A14]">Complete UPI Transfer &amp; Submit Reference</div>
                <p class="text-[#5A4640]">UPI ID: <strong class="font-mono text-[#3B0A14]">{{ $upiVpa }}</strong></p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $upiUri }}" class="btn-antique-gold px-4 py-2 rounded-lg text-xs font-semibold">Pay via UPI App</a>
                </div>
                <form action="{{ route('donation.submit-payment', $donation->donation_reference) }}" method="POST" class="flex flex-wrap gap-2 pt-2">
                    @csrf
                    <input type="text" name="utr_reference" value="{{ $donation->utr_reference }}" required placeholder="Enter 12-digit UTR / UPI Ref"
                        class="flex-grow min-w-[200px] px-3 py-2 rounded-lg bg-white border border-[#D4AF37]/50 text-xs font-mono text-[#231715]">
                    <button type="submit" class="btn-deep-burgundy px-4 py-2 rounded-lg text-xs font-semibold">
                        Submit UTR Reference
                    </button>
                </form>
            </div>
        @else
            <div class="p-4 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/50 text-xs text-[#3B0A14]">
                Official Acknowledgement confirmed on {{ $donation->acknowledged_at?->format('d M Y, h:i A') }}
                @if ($donation->utr_reference)
                    &bull; UTR: <span class="font-mono font-semibold">{{ $donation->utr_reference }}</span>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 no-print">
            <button onclick="window.print()" class="btn-deep-burgundy px-4 py-2 rounded-lg text-xs font-semibold">Print Acknowledgement</button>
            <a href="{{ route('home') }}" class="text-xs font-semibold text-[#7A5C2E] hover:underline">&larr; Return to Main Page</a>
        </div>
    </div>
</div>
@endsection
