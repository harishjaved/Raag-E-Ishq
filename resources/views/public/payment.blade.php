@extends('layouts.app')

@section('title', 'UPI Payment — Sagar Kinare')

@section('content')
<div class="max-w-xl mx-auto px-4 py-8 space-y-6">
    <!-- Header -->
    <div class="text-center space-y-1">
        <span class="text-xs uppercase font-semibold tracking-wider text-amber-400">Step 2 of 2</span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-100 font-serif-display">Complete UPI Payment</h1>
        <p class="text-xs text-slate-400">Booking Reference: <span class="font-mono text-amber-400 font-bold">{{ $booking->booking_reference }}</span></p>
    </div>

    <!-- Mandatory Educational Warning: Payment QR vs Entry Ticket QR -->
    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-xs flex gap-3">
        <svg class="w-6 h-6 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="space-y-1">
            <div class="font-bold text-amber-300">Important Note on QR Codes</div>
            <p>
                The QR code below is a <strong>UPI Payment QR</strong> used exclusively to make payment from your UPI app.
                This is <strong>NOT your event admission ticket</strong>. Your secure event admission QR tickets will be issued immediately upon verified payment.
            </p>
        </div>
    </div>

    <!-- Booking Summary Card -->
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div>
                <div class="text-xs text-slate-400">Purchaser</div>
                <div class="text-sm font-semibold text-slate-200">{{ $booking->contact->full_name }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-400">Reservation Hold</div>
                <div id="countdown_timer" class="text-sm font-mono font-bold text-amber-400">10:00</div>
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
                <span class="text-slate-200">Total Payable</span>
                <span class="text-amber-400 text-lg">₹{{ $rupeesFormatted }}</span>
            </div>
            <div class="pt-2.5 border-t border-slate-800/80 flex items-center gap-2 text-[11px] text-slate-400">
                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong>No-Cancellation Policy:</strong> Bookings are final and non-refundable once payment is completed.</span>
            </div>
        </div>
    </div>

    <!-- Payment Presentation: QR Code & Mobile Intent -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 text-center space-y-6 shadow-2xl">
        <!-- Desktop / Tablet QR View -->
        <div class="space-y-3">
            <div class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Scan with any UPI App</div>
            <div class="inline-block p-4 bg-white rounded-2xl shadow-inner max-w-[260px] mx-auto">
                <div class="w-full flex justify-center [&>svg]:w-full [&>svg]:h-auto">
                    {!! $qrSvg !!}
                </div>
            </div>
            <div class="space-y-1">
                <div class="text-xs font-semibold text-slate-200">
                    Payee: <span class="text-amber-400 font-bold">{{ config('services.razorpay.merchant_name', 'Muzammil Ibrahim') }}</span>
                </div>
                <div class="text-[11px] font-mono text-slate-400 bg-slate-950/60 py-1 px-3 rounded-lg inline-block border border-slate-800">
                    UPI ID: <span class="text-slate-300 font-bold">{{ config('services.razorpay.merchant_vpa', 'mujammilibrahim007@okhdfcbank') }}</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-500">Google Pay &bull; PhonePe &bull; Paytm &bull; BHIM &bull; Any Banking UPI</p>
        </div>

        <!-- Mobile Same-Phone "Pay via UPI App" Action -->
        <div class="pt-4 border-t border-slate-800/80 space-y-3">
            <div class="text-xs text-slate-400">Paying on this phone?</div>
            <a href="{{ $paymentAttempt->qr_payload }}"
                class="w-full py-3.5 px-6 rounded-2xl bg-emerald-500 hover:bg-emerald-400 active:bg-emerald-600 text-slate-950 font-bold text-sm tracking-wide transition shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Open UPI App to Pay ₹{{ $rupeesFormatted }}</span>
            </a>
            <p class="text-[10px] text-slate-500">Tapping will open your installed UPI app with the exact amount pre-filled.</p>
        </div>

        <!-- Live Status Polling Indicator -->
        <div class="pt-3 border-t border-slate-800 flex items-center justify-center gap-2 text-xs text-slate-400">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
            </span>
            <span id="polling_status">Waiting for payment confirmation from bank...</span>
        </div>
    </div>

    <!-- Payment Help & Direct Support Footer -->
    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-3.5 shadow-xl">
        <div class="flex items-center gap-2.5">
            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">Payment Issue or Need Help?</h3>
                <p class="text-[11px] text-slate-400">If money was debited from your bank, your funds are safe!</p>
            </div>
        </div>

        <p class="text-xs text-slate-400 leading-relaxed">
            In case your UPI payment was successful but your ticket pass didn't load, share your <strong>12-digit UPI Reference No. (UTR)</strong> from Google Pay / PhonePe along with your Booking Reference <span class="font-mono text-amber-400 font-bold">{{ $booking->booking_reference }}</span> with our team:
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
            <!-- WhatsApp Direct Support -->
            <a href="https://wa.me/919876543210?text={{ urlencode('Hi Joygivers Support, I have paid ₹' . $rupeesFormatted . ' for booking ' . $booking->booking_reference . '. Please verify and issue my tickets.') }}"
                target="_blank"
                class="px-4 py-3 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center justify-center gap-2 transition shadow-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                <span>WhatsApp Support</span>
            </a>

            <!-- Email Support -->
            <a href="mailto:events@joygivers.org?subject={{ urlencode('Payment Support: Booking ' . $booking->booking_reference) }}&body={{ urlencode("Booking Reference: " . $booking->booking_reference . "\nName: " . $booking->contact->full_name . "\nAmount: ₹" . $rupeesFormatted . "\nUPI UTR / Reference ID: ") }}"
                class="px-4 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs font-bold flex items-center justify-center gap-2 transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Email: events@joygivers.org</span>
            </a>
        </div>
        <div class="text-[10px] text-slate-500 text-center">
            Helpline: <span class="font-mono text-slate-400 font-semibold">+91 98765 43210</span> &bull; 9:00 AM &ndash; 9:00 PM IST
        </div>
    </div>
</div>

<script>
    // 1. Live Countdown Timer
    let secondsLeft = Math.floor({{ $remainingSeconds }});
    const timerDisplay = document.getElementById('countdown_timer');

    function updateTimer() {
        if (secondsLeft <= 0) {
            timerDisplay.textContent = 'EXPIRED';
            timerDisplay.classList.remove('text-amber-400');
            timerDisplay.classList.add('text-rose-400');
            document.getElementById('polling_status').textContent = 'Hold expired. Please re-register if seats are available.';
            return;
        }
        const mins = Math.floor(secondsLeft / 60);
        const secs = Math.floor(secondsLeft % 60);
        timerDisplay.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        secondsLeft--;
    }
    setInterval(updateTimer, 1000);
    updateTimer();

    // 2. Poll Booking Payment Status
    const statusEndpoint = "{{ route('booking.status', ['reference' => $booking->booking_reference]) }}";
    const confirmedUrl = "{{ route('booking.confirmed', ['reference' => $booking->booking_reference]) }}";

    function checkPaymentStatus() {
        fetch(statusEndpoint)
            .then(res => res.json())
            .then(data => {
                if (data.confirmed) {
                    document.getElementById('polling_status').textContent = 'Payment verified! Generating tickets...';
                    window.location.href = confirmedUrl;
                } else if (data.hold_expired) {
                    document.getElementById('polling_status').textContent = 'Hold has expired.';
                } else if (data.reconciliation) {
                    document.getElementById('polling_status').textContent = 'Payment received. Routed to reconciliation review.';
                }
            })
            .catch(err => console.error('Status check error:', err));
    }
    // Poll every 3 seconds
    setInterval(checkPaymentStatus, 3000);
</script>
@endsection
