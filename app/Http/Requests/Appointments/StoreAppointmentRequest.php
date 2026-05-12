<?php

namespace App\Http\Requests\Appointments;

class StoreAppointmentRequest extends ConfirmAppointmentRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'client_comment' => ['nullable', 'string', 'max:150'],
        ]);
    }
}
