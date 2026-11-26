<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\DonationRequest;
use App\Models\AuditLog;
use App\Models\Donation;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function store(DonationRequest $request, InventoryService $inventoryService): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        if (empty($validated['email'])) {
            $validated['email'] = 'donor+' . ($validated['phone'] ?? uniqid()) . '@joygivers.local';
        }
        if (empty($validated['phone'])) {
            $validated['phone'] = '0000000000';
        }

        $donation = $inventoryService->createStandaloneDonation($validated, $request->ip());
        $vpa = config('services.razorpay.merchant_vpa', 'mujammilibrahim007@okhdfcbank');
        $merchantName = config('services.razorpay.merchant_name', 'Joygivers');
        $rupees = number_format($donation->amount_paise / 100, 2, '.', '');
        $upiUri = "upi://pay?pa={$vpa}&pn=" . urlencode($merchantName) . "&am={$rupees}&cu=INR&tr={$donation->donation_reference}&tn=" . urlencode("Joygivers Cause - {$donation->donation_reference}");

        $acknowledgement = "Thank you, {$donation->contact->full_name}. Your cause contribution of {$donation->formatted_amount} has been registered under reference {$donation->donation_reference} (zero hall seats affected).";

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'donation_reference' => $donation->donation_reference,
                'donation_status' => $donation->status,
                'formatted_amount' => $donation->formatted_amount,
                'amount_paise' => $donation->amount_paise,
                'upi_vpa' => $vpa,
                'upi_uri' => $upiUri,
                'acknowledgement_url' => route('donation.show', ['reference' => $donation->donation_reference]),
                'message' => $acknowledgement,
            ], 201);
        }

        return redirect()->route('donation.show', ['reference' => $donation->donation_reference])
            ->with('success', $acknowledgement);
    }

    public function show(string $reference): View|RedirectResponse
    {
        $donation = Donation::with('contact')->where('donation_reference', $reference)->first();
        if (!$donation) {
            return redirect()->route('event.show')->withErrors([
                'amount_rupees' => "Donation reference '{$reference}' could not be found.",
            ]);
        }

        $vpa = config('services.razorpay.merchant_vpa', 'mujammilibrahim007@okhdfcbank');
        $merchantName = config('services.razorpay.merchant_name', 'Joygivers');
        $rupees = number_format($donation->amount_paise / 100, 2, '.', '');
        $upiUri = "upi://pay?pa={$vpa}&pn=" . urlencode($merchantName) . "&am={$rupees}&cu=INR&tr={$donation->donation_reference}&tn=" . urlencode("Joygivers Cause - {$donation->donation_reference}");

        return view('public.donation', [
            'donation' => $donation,
            'upiVpa' => $vpa,
            'upiUri' => $upiUri,
        ]);
    }

    public function submitPaymentProof(Request $request, string $reference): RedirectResponse
    {
        $validated = $request->validate([
            'utr_reference' => ['required', 'string', 'min:4', 'max:120'],
        ]);

        $donation = Donation::where('donation_reference', $reference)->firstOrFail();

        $donation->update([
            'utr_reference' => trim($validated['utr_reference']),
            'status' => $donation->status === Donation::STATUS_ACKNOWLEDGED
                ? Donation::STATUS_ACKNOWLEDGED
                : Donation::STATUS_PAYMENT_SUBMITTED,
        ]);

        AuditLog::log(
            action: 'DONATION_UTR_SUBMITTED',
            targetType: 'Donation',
            targetId: $donation->id,
            newValues: [
                'donation_reference' => $donation->donation_reference,
                'utr_reference' => $donation->utr_reference,
            ]
        );

        return back()->with('success', 'Thank you! Your UTR payment reference has been recorded for acknowledgement.');
    }
}
