<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SanitizesInputs;
use Illuminate\Foundation\Http\FormRequest;

class DonationRequest extends FormRequest
{
    use SanitizesInputs;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rawPhone = preg_replace('/[^0-9]/', '', (string) $this->input('phone'));
        if (strlen($rawPhone) > 10 && str_starts_with($rawPhone, '91')) {
            $rawPhone = substr($rawPhone, -10);
        }

        $this->merge([
            'full_name' => $this->sanitizeString($this->input('full_name')),
            'email' => $this->filled('email') ? strtolower(trim((string) $this->input('email'))) : null,
            'phone' => $rawPhone !== '' ? $rawPhone : null,
            'utr_reference' => $this->filled('utr_reference') ? $this->sanitizeString($this->input('utr_reference')) : null,
            'donor_note' => $this->filled('donor_note') ? $this->sanitizeString($this->input('donor_note')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:255'],
            'phone' => ['nullable', 'required_without:email', 'string', 'regex:/^[0-9]{10}$/'],
            'amount_rupees' => ['required', 'numeric', 'min:100', 'max:5000000'],
            'payment_method' => ['nullable', 'in:UPI,BANK_TRANSFER,CASH,CARD'],
            'utr_reference' => ['nullable', 'string', 'max:120'],
            'donor_note' => ['nullable', 'string', 'max:400'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount_rupees.min' => 'Minimum cause contribution is ₹100.',
            'phone.regex' => 'Please enter a valid 10-digit WhatsApp number.',
            'email.required_without' => 'Please share either a WhatsApp number or an Email address.',
            'phone.required_without' => 'Please share either a WhatsApp number or an Email address.',
        ];
    }
}
