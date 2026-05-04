<?php

namespace App\Services;

use App\Models\Appointments;
use App\Models\Barbershop;
use App\Models\Services;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AppointmentSelectionService
{
    private const BLOCKING_STATUSES = ['pending', 'accepted'];

    public function getSlotsForService(Barbershop $barbershop, Carbon $date, $schedule, Services $service): array
    {
        $start = $date->copy()->setTimeFromTimeString($schedule->start_time);
        $end = $date->copy()->setTimeFromTimeString($schedule->end_time);

        $slots = [];
        $existingAppointments = Appointments::where('barbershop_id', $barbershop->id)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->get(['start_time', 'end_time']);

        $current = $start->copy();
        if ($date->isToday() && $current->lessThan(now())) {
            $current = now()->copy()->startOfHour();
            if ($current->lessThan(now())) {
                $current->addHour();
            }
        }

        while ($current->copy()->addMinutes($service->duration) <= $end) {
            $slotEnd = $current->copy()->addMinutes($service->duration);
            $available = true;

            foreach ($existingAppointments as $appt) {
                $apptStart = $date->copy()->setTimeFromTimeString(
                    $appt->start_time instanceof Carbon ? $appt->start_time->format('H:i:s') : (string) $appt->start_time
                );
                $apptEnd = $date->copy()->setTimeFromTimeString(
                    $appt->end_time instanceof Carbon ? $appt->end_time->format('H:i:s') : (string) $appt->end_time
                );

                if ($current < $apptEnd && $slotEnd > $apptStart) {
                    $available = false;
                    break;
                }
            }

            $slots[] = [
                'time' => $current->format('H:i'),
                'available' => $available,
            ];

            $current->addHour();
        }

        return $slots;
    }

    public function getAvailableSlotsForService(Barbershop $barbershop, Carbon $date, $schedule, Services $service): array
    {
        return collect($this->getSlotsForService($barbershop, $date, $schedule, $service))
            ->filter(fn (array $slot) => $slot['available'])
            ->pluck('time')
            ->all();
    }

    public function validateSelectionRequest(Request $request, Barbershop $barbershop, array $extraRules = []): array
    {
        return $this->validateSelectionData($request->all(), $barbershop, $extraRules);
    }

    public function validateSelectionData(array $data, Barbershop $barbershop, array $extraRules = []): array
    {
        $validated = Validator::make(array_merge($data, [
            'barbershop_id' => $barbershop->id,
        ]), array_merge([
            'barbershop_id' => 'required|integer',
            'service_id' => 'required|exists:services,id',
            'datetime' => 'required|date_format:Y-m-d H:i',
        ], $extraRules))->validate();

        $service = Services::where('id', $validated['service_id'])
            ->where('barbershop_id', $barbershop->id)
            ->first();

        if (!$service) {
            throw ValidationException::withMessages([
                'service_id' => 'El servicio seleccionado no pertenece a esta barbería.',
            ]);
        }

        if (!$service->isVisibleTo(auth()->user())) {
            throw ValidationException::withMessages([
                'service_id' => 'El servicio seleccionado no está disponible.',
            ]);
        }

        $startTime = Carbon::createFromFormat('Y-m-d H:i', $validated['datetime']);
        $endTime = $startTime->copy()->addMinutes($service->duration);

        if (!$this->isSelectableSlot($barbershop, $service, $startTime)) {
            throw ValidationException::withMessages([
                'datetime' => 'El horario seleccionado no está disponible.',
            ]);
        }

        return [
            'validated' => $validated,
            'service' => $service,
            'startTime' => $startTime,
            'endTime' => $endTime,
        ];
    }

    public function isSelectableSlot(Barbershop $barbershop, Services $service, Carbon $startTime): bool
    {
        $bookingWindowStart = Carbon::today();
        $bookingWindowEnd = $bookingWindowStart->copy()->addMonthNoOverflow()->endOfMonth();

        if (!$startTime->betweenIncluded($bookingWindowStart, $bookingWindowEnd)) {
            return false;
        }

        $schedule = $barbershop->schedules
            ->where('day_of_week', $startTime->dayOfWeekIso)
            ->first();

        if (!$schedule) {
            return false;
        }

        return in_array(
            $startTime->format('H:i'),
            $this->getAvailableSlotsForService($barbershop, $startTime->copy()->startOfDay(), $schedule, $service),
            true
        );
    }

    public function isSlotAvailable(Barbershop $barbershop, Carbon $startTime, Carbon $endTime): bool
    {
        return !Appointments::where('barbershop_id', $barbershop->id)
            ->where('appointment_date', $startTime->format('Y-m-d'))
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('start_time', '<', $endTime->format('H:i:s'))
            ->where('end_time', '>', $startTime->format('H:i:s'))
            ->exists();
    }
}
