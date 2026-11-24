@extends('layouts.app')

@section('title', 'Booking Enquiry Received — Raag-E-Ishq')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8 space-y-6">
    <!-- Header -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            @if ($booking->isInProcess())
                <span>Status: In Process &bull; Team Contacting You</span>
            @else
                <span>Status: Enquiry Received &bull; Pending Verification</span>
            @endif
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Booking Enquiry Registered</h1>
        <p class="text-xs text-slate-400">Booking Reference: <span class="font-mono text-amber-400 font-bold text-sm">{{ $booking->booking_reference }}</span></p>
    </div>

    <!-- 3-Step Verification & Dispatch Timeline -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-5 shadow-2xl">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300">Booking &amp; Payment Process</h2>
        
        <div class="space-y-4">
            <!-- Step 1: Completed -->
            <div class="flex items-start gap-3.5">
                <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs">
                    ✓
                </div>
                <div class="space-y-0.5">
                    <div class="text-xs font-bold text-slate-200">1. Booking Details Submitted &amp; Seats Reserved</div>
                    <p class="text-[11px] text-slate-400">Your seat reservation has been recorded in the Joygivers reservations desk.</p>
                </div>
            </div>

            <!-- Step 2: In-Progress -->
            <div class="flex items-start gap-3.5">
                <div class="w-7 h-7 rounded-full bg-amber-500/20 border border-amber-500/50 text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs animate-pulse">
                    2
                </div>
                <div class="space-y-0.5">
                    <div class="text-xs font-bold text-amber-300">2. Verification Call &amp; Payment Details via WhatsApp</div>
                    <p class="text-[11px] text-slate-300 leading-relaxed">
                        Our payment team will call you on <strong class="text-amber-400 font-mono">{{ $booking->contact->phone }}</strong> shortly to verify your booking details and share the official UPI QR / bank details through WhatsApp or Email.
                    </p>
                </div>
            </div>

            <!-- Step 3: Upcoming -->
            <div class="flex items-start gap-3.5">
                <div class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 text-slate-500 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs">
                    3
                </div>
                <div class="space-y-0.5">
                    <div class="text-xs font-bold text-slate-400">3. Verified Admission Passes Issued</div>
                    <p class="text-[11px] text-slate-500">Upon payment confirmation, your unique cryptographic QR tickets will be generated and dispatched directly to your WhatsApp and email.</p>
                </div>
            </div>
        </div>

        <!-- Live Status Polling Indicator -->
        <div class="pt-4 border-t border-slate-800 flex items-center justify-center gap-2 text-xs text-slate-400">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
            </span>
            <span id="polling_status">Waiting for payment verification from reservations desk...</span>
        </div>
    </div>

    <!-- Booking Summary Card -->
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div>
                <div class="text-xs text-slate-400">Purchaser</div>
                <div class="text-sm font-semibold text-slate-200">{{ $booking->contact->full_name }}</div>
                <div class="text-xs text-slate-400 font-mono">{{ $booking->contact->phone }} &bull; {{ $booking->contact->email }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-400">Attendance</div>
                <div class="text-xs font-bold text-amber-400">
                    {{ $booking->is_attending ? 'In-Person Attendee' : 'Charitable Support' }}
                </div>
            </div>
        </div>

        <div class="space-y-2 text-xs">
            @foreach ($booking->items as $item)
                <div class="flex justify-between text-slate-300">
                    <span>{{ $item->category->name }} &times; {{ $item->quantity }}</span>
                    <span class="font-semibold text-slate-100">₹{{ number_format($item->subtotal_paise / 100, 2) }}</span>
                </div>
            @endforeach
            <div class="pt-2 border-t border-slate-800 flex justify-between text-sm font-bold">
                <span class="text-slate-200">Total Payable Amount</span>
                <span class="text-amber-400 text-lg">₹{{ $rupeesFormatted }}</span>
            </div>
            <div class="pt-2.5 border-t border-slate-800/80 flex items-center gap-2 text-[11px] text-slate-400">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong>No-Cancellation Policy:</strong> All confirmed bookings are final and non-refundable.</span>
            </div>
        </div>
    </div>

    <!-- Fast-Track WhatsApp Payment Contact Card -->
    <div class="p-6 rounded-3xl bg-emerald-950/40 border border-emerald-500/30 text-center space-y-4 shadow-xl">
        <div class="space-y-1">
            <h3 class="text-sm font-bold text-emerald-300">Want to Fast-Track Your Confirmation?</h3>
            <p class="text-xs text-slate-300">
                You can message our payment team right away on WhatsApp to get the payment QR or bank details instantly!
            </p>
        </div>

        <a href="https://wa.me/919876543210?text={{ urlencode('Hi Joygivers Team, I have submitted booking enquiry ' . $booking->booking_reference . ' for ' . $booking->contact->full_name . ' (₹' . $rupeesFormatted . '). Please share the payment QR / details.') }}"
            target="_blank"
            class="w-full py-3.5 px-6 rounded-2xl bg-emerald-500 hover:bg-emerald-400 active:bg-emerald-600 text-slate-950 font-bold text-sm tracking-wide transition shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
            <span>Message Payment Team on WhatsApp</span>
        </a>
    </div>

    <!-- Help & Support Desk Card -->
    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-3.5 shadow-xl text-xs text-slate-400 text-center">
        <div>
            Questions or booking changes? Email our reservations desk at <a href="mailto:events@joygivers.org" class="text-amber-400 font-semibold hover:underline">events@joygivers.org</a> or call <span class="font-mono text-slate-200 font-semibold">+91 98765 43210</span> (9:00 AM &ndash; 9:00 PM).
        </div>
    </div>
</div>

<script>
    // Poll Booking Payment Status every 4 seconds
    const statusEndpoint = "{{ route('booking.status', ['reference' => $booking->booking_reference]) }}";
    const confirmedUrl = "{{ route('booking.confirmed', ['reference' => $booking->booking_reference]) }}";

    function checkPaymentStatus() {
        fetch(statusEndpoint)
            .then(res => res.json())
            .then(data => {
                if (data.confirmed) {
                    document.getElementById('polling_status').textContent = 'Payment verified! Opening your tickets...';
                    window.location.href = confirmedUrl;
                } else if (data.reconciliation) {
                    document.getElementById('polling_status').textContent = 'Payment received. Routed to reconciliation review.';
                }
            })
            .catch(err => console.error('Status check error:', err));
    }
    setInterval(checkPaymentStatus, 4000);
</script>
@endsection
