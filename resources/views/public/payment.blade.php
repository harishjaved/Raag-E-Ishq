@extends('layouts.app')

@section('title', 'Reservation Hold & Payment — ' . $booking->booking_reference . ' | Raag-E-Ishq')

@section('content')
<div class="bg-[#FAF6F0] text-[#231715] py-8 sm:py-12 px-4">
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Top Countdown Banner (Explicit Hold Timer + Expiry Warning) -->
        @if ($booking->is_attending)
            <div id="hold_countdown_banner" class="p-4 sm:p-5 rounded-xl bg-[#3B0A14] text-[#FAF6F0] border border-[#D4AF37]/55 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="text-[11px] uppercase tracking-[0.16em] text-[#E5C158] font-semibold">
                        {{ $booking->isPaymentSubmitted() ? 'Stage 3 of 6 • Payment Proof Under Review' : 'Stage 2 of 6 • Temporary Seat Hold Active' }}
                    </div>
                    <h2 class="font-serif-display text-lg sm:text-xl font-semibold text-[#FAF6F0]">
                        @if ($booking->isPaymentSubmitted())
                            UTR Reference Received — Verification Pending
                        @else
                            Your seats are held for {{ $holdMinutes }} minutes while awaiting payment
                        @endif
                    </h2>
                    <p class="text-xs text-[#E8DFD3]">
                        Unpaid holds automatically release at <strong class="text-[#E5C158]">{{ $booking->hold_expires_at?->format('h:i A') ?? 'expiry' }}</strong> back into public inventory.
                    </p>
                </div>
                <div class="flex-shrink-0 text-left sm:text-right bg-[#24050B] border border-[#D4AF37]/40 rounded-lg px-4 py-2.5 min-w-[140px]">
                    <div class="text-[10px] uppercase tracking-wider text-[#D8CBB8]">Remaining Hold Time</div>
                    <div id="countdown_clock" class="font-mono text-2xl font-semibold text-[#E5C158] tracking-wider">
                        {{ str_pad((string) floor($remainingSeconds / 60), 2, '0', STR_PAD_LEFT) }}:{{ str_pad((string) ($remainingSeconds % 60), 2, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="text-center space-y-2">
            <span class="inline-block px-3 py-1 rounded-lg bg-[#3B0A14]/10 border border-[#D4AF37]/40 text-[#3B0A14] text-xs font-semibold uppercase tracking-wider">
                {{ $booking->isPaymentSubmitted() ? 'Payment Submitted — Awaiting Admin Verification' : 'Reservation Request — Awaiting Payment' }}
            </span>
            <h1 class="text-3xl sm:text-4xl font-semibold text-[#3B0A14] font-serif-display">
                Complete Payment for Your Reservation
            </h1>
            <p class="text-xs text-[#5A4640]">
                Booking Reference: <span class="font-mono text-[#3B0A14] font-bold text-sm bg-white px-2 py-0.5 rounded border border-[#D4AF37]/40">{{ $booking->booking_reference }}</span>
                <span class="block mt-1 text-[11px] text-[#7A5C2E]">(Your reservation is only confirmed once payment verification completes.)</span>
            </p>
        </div>

        <!-- 6-Stage Reservation Lifecycle Progress -->
        <div class="p-5 sm:p-6 rounded-xl bg-white border border-[#D4AF37]/40 space-y-4">
            <div class="flex items-center justify-between border-b border-[#EBE0D0] pb-2.5">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-[#3B0A14]">Reservation &amp; Verification Status</h2>
                <span class="text-[11px] font-medium text-[#7A5C2E]" id="polling_status">Checking verification status automatically...</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                @foreach ($booking->lifecycle_stages as $idx => $stage)
                    <div class="p-2.5 rounded-lg border text-left {{ $stage['state'] === 'completed' ? 'bg-[#F5EFE6] border-[#C59B27]' : ($stage['state'] === 'active' ? 'bg-[#3B0A14] text-[#FAF6F0] border-[#D4AF37]' : 'bg-[#FAF6F0]/70 border-[#E5DDD2] text-[#7B6862]') }}">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="w-4 h-4 rounded-full text-[10px] font-bold flex items-center justify-center {{ $stage['state'] === 'completed' ? 'bg-[#3B0A14] text-[#E5C158]' : ($stage['state'] === 'active' ? 'bg-[#E5C158] text-[#231715]' : 'bg-[#E3D8C8] text-[#6E5952]') }}">
                                {{ $stage['state'] === 'completed' ? '✓' : ($idx + 1) }}
                            </span>
                            <span class="text-[10px] font-mono opacity-80">STEP {{ $idx + 1 }}</span>
                        </div>
                        <div class="text-[11px] font-semibold leading-snug {{ $stage['state'] === 'active' ? 'text-[#FAF6F0]' : ($stage['state'] === 'completed' ? 'text-[#3B0A14]' : 'text-[#6E5952]') }}">
                            {{ $stage['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Two-Column UPI QR + UTR Proof Submission -->
        <div class="grid md:grid-cols-12 gap-6 items-start">
            <!-- Left: UPI QR & Payment Instructions -->
            <div class="md:col-span-6 p-6 rounded-xl bg-white border border-[#D4AF37]/40 space-y-4 text-center">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#7A5C2E]">Scan &amp; Pay via UPI</div>
                <div class="inline-block p-3 bg-white rounded-lg border border-[#D4AF37]/45">
                    {!! $qrSvg !!}
                </div>
                <div class="space-y-1 text-xs">
                    <div class="text-lg font-serif-display font-semibold text-[#3B0A14]">Pay {{ $booking->formatted_total }}</div>
                    <div>UPI ID: <strong class="font-mono text-[#3B0A14]">{{ $upiVpa }}</strong></div>
                    <div class="text-[#5A4640]">Payee: {{ $upiPayee }}</div>
                </div>
                <div class="flex flex-wrap justify-center gap-2 pt-1">
                    <a href="{{ $paymentAttempt->qr_payload }}"
                        class="btn-antique-gold px-4 py-2 rounded-lg text-xs font-semibold">
                        Open UPI App to Pay &rarr;
                    </a>
                    <a href="https://wa.me/917736606506?text={{ urlencode('Hi Joygivers, I have submitted reservation request ' . $booking->booking_reference . ' for ' . $booking->contact->full_name . ' (' . $booking->formatted_total . '). Please confirm payment details.') }}"
                        target="_blank" rel="noopener noreferrer"
                        class="btn-deep-burgundy px-4 py-2 rounded-lg text-xs font-semibold">
                        WhatsApp Payment Desk
                    </a>
                </div>
            </div>

            <!-- Right: Submit UTR / Screenshot Proof + Booking Summary -->
            <div class="md:col-span-6 space-y-5">
                <div class="p-6 rounded-xl bg-white border border-[#D4AF37]/40 space-y-4">
                    <div>
                        <h3 class="font-serif-display text-xl font-semibold text-[#3B0A14]">Submit UTR / Payment Proof</h3>
                        <p class="text-xs text-[#5A4640] mt-1">Once your UPI transfer is complete, enter your 12-digit UTR or transaction reference below so our team can verify and issue your QR Entry Passes.</p>
                    </div>

                    <form action="{{ route('booking.submit-payment', $booking->booking_reference) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="space-y-1">
                            <label for="utr_reference" class="block text-xs font-semibold text-[#3B0A14]">UTR / Transaction Reference *</label>
                            <input type="text" id="utr_reference" name="utr_reference" value="{{ old('utr_reference', $booking->utr_reference) }}" required
                                placeholder="e.g. 432918273645"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-sm font-mono text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                        </div>
                        <div class="space-y-1">
                            <label for="payment_screenshot_reference" class="block text-xs font-semibold text-[#3B0A14]">Payment App / Note (Optional)</label>
                            <input type="text" id="payment_screenshot_reference" name="payment_screenshot_reference" value="{{ old('payment_screenshot_reference', $booking->payment_screenshot_reference) }}"
                                placeholder="e.g. GPay from +91 98... or sent screenshot on WhatsApp"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-[#FAF6F0] border border-[#D4AF37]/45 text-xs text-[#231715] focus:outline-none focus:border-[#3B0A14]">
                        </div>
                        <button type="submit" class="btn-antique-gold w-full py-2.5 px-5 rounded-lg text-xs font-semibold transition">
                            {{ $booking->isPaymentSubmitted() ? 'Update UTR Payment Reference' : 'Submit Payment Proof & Notify Desk' }}
                        </button>
                    </form>
                </div>

                <!-- Summary Card -->
                <div class="p-5 rounded-xl bg-white border border-[#E3D8C8] space-y-3 text-xs">
                    <div class="flex justify-between items-start pb-2.5 border-b border-[#EBE0D0]">
                        <div>
                            <div class="text-[#7A5C2E] uppercase tracking-wider text-[10px] font-semibold">Guest</div>
                            <div class="font-semibold text-[#3B0A14] text-sm">{{ $booking->contact->full_name }}</div>
                            <div class="text-[#5A4640] font-mono">{{ $booking->contact->phone }} &bull; {{ $booking->contact->email }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-[#FAF6F0] border border-[#D4AF37]/40 text-[#3B0A14] font-semibold">
                            {{ $booking->is_attending ? 'In-Person' : 'Supporter' }}
                        </span>
                    </div>

                    @foreach ($booking->items as $item)
                        <div class="flex justify-between text-[#42322D]">
                            <span>{{ $item->category->name }} &times; {{ $item->quantity }}</span>
                            <span class="font-semibold text-[#3B0A14]">{{ '₹' . number_format($item->subtotal_paise / 100, 0) }}</span>
                        </div>
                    @endforeach

                    <div class="pt-2 border-t border-[#EBE0D0] flex justify-between items-center text-sm font-semibold text-[#3B0A14]">
                        <span>Total Amount</span>
                        <span class="font-serif-display text-xl">₹{{ $rupeesFormatted }}</span>
                    </div>

                    <p class="pt-2 border-t border-[#EBE0D0] text-[11px] leading-relaxed text-[#5A4640]">
                        As this is a charitable fundraising event, all confirmed and paid bookings are non-refundable. Ticket transfers to another guest are permitted prior to the event.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        let remaining = {{ (int) $remainingSeconds }};
        const clockEl = document.getElementById('countdown_clock');
        const statusEndpoint = "{{ route('booking.status', ['reference' => $booking->booking_reference]) }}";
        const confirmedUrl = "{{ route('booking.confirmed', ['reference' => $booking->booking_reference]) }}";
        const eventUrl = "{{ route('event.show') }}";

        function renderClock() {
            if (!clockEl) return;
            const mins = String(Math.floor(Math.max(0, remaining) / 60)).padStart(2, '0');
            const secs = String(Math.max(0, remaining) % 60).padStart(2, '0');
            clockEl.textContent = `${mins}:${secs}`;
        }

        if (clockEl && remaining > 0) {
            setInterval(() => {
                if (remaining > 0) {
                    remaining -= 1;
                    renderClock();
                }
            }, 1000);
        }

        function checkPaymentStatus() {
            fetch(statusEndpoint)
                .then(res => res.json())
                .then(data => {
                    if (typeof data.remaining_seconds === 'number') {
                        remaining = data.remaining_seconds;
                        renderClock();
                    }
                    const statusEl = document.getElementById('polling_status');
                    if (data.confirmed) {
                        if (statusEl) statusEl.textContent = 'Payment verified! Loading your QR Entry Passes...';
                        window.location.href = confirmedUrl;
                    } else if (data.hold_expired) {
                        if (statusEl) statusEl.textContent = 'Seat hold expired — returning to seat selection...';
                        window.location.href = eventUrl;
                    } else if (data.payment_submitted && statusEl) {
                        statusEl.textContent = 'UTR received — awaiting Admin verification...';
                    } else if (data.reconciliation && statusEl) {
                        statusEl.textContent = 'Payment received — routed to reconciliation review.';
                    }
                })
                .catch(() => {});
        }
        setInterval(checkPaymentStatus, 4500);
    })();
</script>
@endsection
