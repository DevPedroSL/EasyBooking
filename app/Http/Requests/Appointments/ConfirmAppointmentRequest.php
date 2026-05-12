<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmAppointmentRequest extends FormRequest
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
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'datetime' => ['required', 'date_format:Y-m-d H:i'],
        ];
    }
}
