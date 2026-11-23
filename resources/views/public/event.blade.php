@extends('layouts.app')

@section('title', 'Sagar Kinare — Live Musical Evening | Joygivers')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 space-y-12">
    <!-- Hero / Event Header -->
    <section class="text-center space-y-4 pt-4">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold uppercase tracking-wider">
            Joygivers Presents
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight font-serif-display text-slate-100">
            Sagar Kinare
        </h1>
        <p class="text-base sm:text-lg text-slate-400 max-w-xl mx-auto">
            A celebration of timeless Hindi &amp; Malayalam melodies in Kochi, supporting an education initiative for underprivileged students.
        </p>

        <!-- Event Quick Facts -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-w-lg mx-auto pt-4 text-left">
            <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                <div class="text-xs text-slate-500 font-medium">Venue</div>
                <div class="text-sm font-semibold text-slate-200 mt-0.5">{{ $event->venue_name ?? 'Chavara Hall, Kochi' }}</div>
                <div class="text-[10px] text-amber-400/80">Pending formal confirmation</div>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                <div class="text-xs text-slate-500 font-medium">Date &amp; Time</div>
                <div class="text-sm font-semibold text-slate-200 mt-0.5">To Be Announced</div>
                <div class="text-[10px] text-slate-400">Evening Programme</div>
            </div>
            <div class="col-span-2 sm:col-span-1 p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                <div class="text-xs text-slate-500 font-medium">Hall Capacity</div>
                <div class="text-sm font-semibold text-slate-200 mt-0.5">450 Seats Total</div>
                <div class="text-[10px] text-emerald-400">{{ $globalRemaining }} remaining</div>
            </div>
        </div>
    </section>

    <!-- Ticket Categories Overview -->
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-100">Admission Categories</h2>
            <span class="text-xs text-slate-400">Live Available Quotas</span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($categories as $cat)
                <div class="p-5 rounded-2xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-400">{{ $cat->name }}</span>
                            @if ($cat->available > 0)
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                                    {{ $cat->available }} left
                                </span>
                            @else
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 font-medium">
                                    Sold Out
                                </span>
                            @endif
                        </div>
                        <div class="text-2xl font-extrabold text-slate-100 mt-2">{{ $cat->formatted_price }}</div>
                        <p class="text-xs text-slate-400 mt-1.5">{{ $cat->description }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 text-[11px] text-slate-500 flex justify-between">
                        <span>Allocation: {{ $cat->capacity }} seats</span>
                        <span>Single Entry</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Registration Form -->
    <section id="register" class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-6">
        <div>
            <div class="inline-flex items-center gap-1.5 text-xs text-amber-400 font-semibold uppercase tracking-wider mb-1">
                <span>Step 1 of 2</span> &bull; <span>Instant Reservation Hold</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-100">Register &amp; Book Tickets</h2>
            <p class="text-xs text-slate-400 mt-1">
                Selecting tickets places a temporary <strong>10-minute hold</strong> on your seats. You will immediately receive a per-booking UPI payment option.
            </p>
        </div>

        <form action="{{ route('register.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="utm_source" value="{{ $utmParams['utm_source'] }}">
            <input type="hidden" name="utm_medium" value="{{ $utmParams['utm_medium'] }}">
            <input type="hidden" name="utm_campaign" value="{{ $utmParams['utm_campaign'] }}">
            <input type="hidden" name="utm_content" value="{{ $utmParams['utm_content'] }}">

            <div class="grid sm:grid-cols-2 gap-4">
                <!-- Full Name -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Purchaser Full Name <span class="text-amber-400">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                        placeholder="e.g. Aarav Sharma"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-400 text-sm">
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Email Address (for tickets) <span class="text-amber-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="e.g. aarav@example.com"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-400 text-sm">
                </div>

                <!-- Phone -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Mobile Number (WhatsApp/SMS) <span class="text-amber-400">*</span></label>
                    <div class="flex gap-2">
                        <span class="inline-flex items-center px-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-400 text-xs font-medium">+91</span>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required pattern="[0-9]{10}" maxlength="10"
                            placeholder="10-digit number"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-400 text-sm">
                    </div>
                </div>

                <!-- Ticket Category Selection -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-300">Select Category <span class="text-amber-400">*</span></label>
                    <select id="ticket_category_id" name="ticket_category_id" required onchange="updatePriceCalculation()"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-100 focus:outline-none focus:border-amber-400 text-sm">
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
            <div class="space-y-2 p-4 rounded-2xl bg-slate-950/40 border border-slate-800">
                <label class="text-xs font-semibold text-slate-300 block">
                    Will you be attending the programme in person? <span class="text-amber-400">*</span>
                </label>
                <div class="grid sm:grid-cols-2 gap-3 pt-1">
                    <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-900 border border-slate-800 hover:border-amber-400/50 cursor-pointer transition">
                        <input type="radio" name="attending" value="yes" {{ old('attending', 'yes') === 'yes' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0">
                        <div class="text-xs">
                            <span class="font-bold text-slate-200 block">Yes</span>
                            <span class="text-[11px] text-slate-400">I will attend in person at Chavara Hall</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-900 border border-slate-800 hover:border-amber-400/50 cursor-pointer transition">
                        <input type="radio" name="attending" value="no" {{ old('attending') === 'no' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0">
                        <div class="text-xs">
                            <span class="font-bold text-slate-200 block">No</span>
                            <span class="text-[11px] text-slate-400">Unable to attend (Support only)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Quantity & Live Calculation -->
            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-semibold text-slate-300">Number of Admissions</label>
                        <p class="text-[11px] text-slate-500">Max 10 per order. Each attendee receives a unique QR ticket.</p>
                    </div>
                    <div class="w-32">
                        <select id="quantity" name="quantity" required onchange="updatePriceCalculation()"
                            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 text-sm text-center font-bold">
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ old('quantity', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Payable (INR)</span>
                    <span id="calculated_total" class="text-2xl font-extrabold text-amber-400">₹0.00</span>
                </div>
            </div>

            <!-- Terms, No-Cancellation Policy & Consent -->
            <div class="space-y-3 pt-2">
                <!-- No-Cancellation Notice Card -->
                <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/20 text-xs text-slate-300 space-y-1">
                    <div class="flex items-center gap-2 font-bold text-amber-400">
                        <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Strict No-Cancellation &amp; Non-Refundable Policy</span>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        All ticket bookings and payments are <strong>strictly final and non-refundable</strong> once confirmed. No cancellations, seat reassignments, or refunds are permitted under any circumstances as all proceeds directly fund Joygivers educational initiatives for underprivileged children.
                    </p>
                </div>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="privacy_consent" value="1" required class="mt-1 w-4 h-4 rounded border-slate-700 bg-slate-950 text-amber-500 focus:ring-0">
                    <span class="text-xs text-slate-300">
                        I acknowledge that my booking details will be sent to the Joygivers reservations desk. <strong>I agree to the strict No-Cancellation and Non-Refundable policy</strong>, and understand that our payment team will contact me to verify details and complete payment before admission tickets are issued.
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="marketing_opt_in" value="1" class="mt-1 w-4 h-4 rounded border-slate-700 bg-slate-950 text-amber-500 focus:ring-0">
                    <span class="text-xs text-slate-400">
                        Keep me updated on future musical initiatives and cultural events by Joygivers.
                    </span>
                </label>
            </div>

            <!-- Submit Action -->
            <div class="space-y-2">
                <button type="submit" id="submit_btn"
                    class="w-full py-3.5 px-6 rounded-2xl bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-bold text-sm tracking-wide transition shadow-lg shadow-amber-500/10 flex items-center justify-center gap-2">
                    <span>Submit Booking Enquiry &amp; Reserve Seats</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
                <p class="text-[11px] text-slate-400 text-center">
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
    document.addEventListener('DOMContentLoaded', updatePriceCalculation);
</script>
@endsection
