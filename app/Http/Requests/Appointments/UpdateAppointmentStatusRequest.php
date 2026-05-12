<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
            'rejection_reason' => ['nullable', 'string', 'max:300'],
            'barber_comment' => ['nullable', 'string', 'max:300'],
        ];
    }
}
