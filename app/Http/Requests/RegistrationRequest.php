<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SanitizesInputs;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    use SanitizesInputs;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => $this->sanitizeString($this->input('full_name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/[^0-9]/', '', (string) $this->input('phone')),
            'utm_source' => $this->sanitizeString($this->input('utm_source')),
            'utm_medium' => $this->sanitizeString($this->input('utm_medium')),
            'utm_campaign' => $this->sanitizeString($this->input('utm_campaign')),
            'utm_content' => $this->sanitizeString($this->input('utm_content')),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'country_code' => ['nullable', 'string', 'max:5'],
            'ticket_category_id' => ['required', 'integer', 'exists:ticket_categories,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'attending' => ['nullable', 'in:yes,no,true,false,1,0'],
            'attendee_names' => ['nullable', 'array'],
            'attendee_names.*' => ['nullable', 'string', 'max:255'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            'privacy_consent' => ['required', 'accepted'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:100'],
            'utm_content' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid 10-digit WhatsApp number.',
            'privacy_consent.accepted' => 'Please confirm the privacy & charitable booking notice to proceed.',
            'ticket_category_id.exists' => 'Please select a valid ticket category (Gold, Silver, or Bronze).',
            'quantity.min' => 'Please select at least 1 guest.',
            'quantity.max' => 'Maximum 10 guests per reservation request.',
        ];
    }
}
