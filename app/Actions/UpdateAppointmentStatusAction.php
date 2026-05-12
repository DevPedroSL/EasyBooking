<?php

namespace App\Actions;

use App\Models\Appointment;

class UpdateAppointmentStatusAction
{
    /**
     * @param  array{status: string, rejection_reason?: string|null, barber_comment?: string|null}  $data
     */
    public function execute(Appointment $appointment, array $data): Appointment
    {
        $barberComment = $data['barber_comment'] ?? $data['rejection_reason'] ?? null;

        $appointment->update([
            'status' => $data['status'],
            'barber_comment' => $barberComment,
            'rejection_reason' => $data['status'] === 'rejected'
                ? $barberComment
                : null,
        ]);

        return $appointment->load([
            'client:id,name,email,phone',
            'service:id,name,duration,price',
            'barbershop:id,name,address,phone,barber_id',
            'barbershop.barber:id,name,email',
        ]);
    }
}
