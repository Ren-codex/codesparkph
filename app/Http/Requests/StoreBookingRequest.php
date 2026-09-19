<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /** @var list<string> */
    public const SERVICES = [
        'Modern & Responsive Website',
        'Business Website / Landing Page',
        'Custom Web Application',
        'Mobile App Development',
        'Reservation & Booking System',
        'Business Digital Solution',
    ];

    /** @var list<string> */
    public const BUDGETS = [
        'Under ₱20,000',
        '₱20,000 – ₱50,000',
        '₱50,000 – ₱100,000',
        'Above ₱100,000',
        'Not sure yet',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['required', Rule::in(self::SERVICES)],
            'budget' => ['nullable', Rule::in(self::BUDGETS)],
            'details' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }
}
