<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'phone.regex' => 'Please provide a valid 10-digit mobile number.',
            'privacy_consent.accepted' => 'You must acknowledge the privacy notice to complete registration.',
            'ticket_category_id.exists' => 'The selected ticket category is invalid.',
            'quantity.min' => 'You must select at least 1 ticket.',
            'quantity.max' => 'Maximum 10 tickets allowed per order.',
        ];
    }
}
