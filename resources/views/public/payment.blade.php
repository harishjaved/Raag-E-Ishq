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
</div>

<script>
    // 1. Live Countdown Timer
    let secondsLeft = {{ $remainingSeconds }};
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
        const secs = secondsLeft % 60;
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
