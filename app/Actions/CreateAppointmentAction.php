<?php

namespace App\Actions;

use App\Models\Appointment;
use App\Models\Barbershop;
use App\Models\User;
use App\Services\AppointmentSelectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateAppointmentAction
{
    public function __construct(
        private AppointmentSelectionService $appointmentSelectionService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $client, Barbershop $barbershop, array $data): Appointment
    {
        $selection = $this->appointmentSelectionService->validateSelectionData($data, $barbershop, [
            'client_comment' => 'nullable|string|max:150',
        ]);

        return DB::transaction(function () use ($client, $barbershop, $selection): Appointment {
            $startTime = $selection['startTime'];
            $endTime = $selection['endTime'];

            if (! $this->appointmentSelectionService->isSlotAvailable($barbershop, $startTime, $endTime)) {
                throw ValidationException::withMessages([
                    'datetime' => 'El horario seleccionado no está disponible.',
                ]);
            }

            if ($this->appointmentSelectionService->clientHasAppointmentOnDate($client->id, $startTime)) {
                throw ValidationException::withMessages([
                    'datetime' => 'Ya tienes una cita reservada para este día.',
                ]);
            }

            return Appointment::create([
                'client_id' => $client->id,
                'barbershop_id' => $barbershop->id,
                'service_id' => $selection['service']->id,
                'appointment_date' => $startTime->format('Y-m-d'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'client_comment' => $selection['validated']['client_comment'] ?? null,
                'status' => 'pending',
            ])->load([
                'client:id,name,email,phone',
                'service:id,name,duration,price',
                'barbershop:id,name,address,phone,barber_id',
                'barbershop.barber:id,name,email',
            ]);
        });
    }
}
