<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Barbershop;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AppointmentSelectionService
{
    private const BLOCKING_STATUSES = ['pending', 'accepted'];

    public function getSlotsForService(Barbershop $barbershop, Carbon $date, $schedule, Service $service): array
    {
        return $this->getSlotsForSchedule(
            $barbershop,
            $date,
            $schedule,
            $service,
            $this->blockingAppointmentsForDate($barbershop, $date)
        );
    }

    public function blockingAppointmentsByDate(Barbershop $barbershop, Carbon $startDate, Carbon $endDate): Collection
    {
        return Appointment::where('barbershop_id', $barbershop->id)
            ->whereBetween('appointment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->get(['appointment_date', 'start_time', 'end_time'])
            ->groupBy(fn (Appointment $appointment) => (string) $appointment->getRawOriginal('appointment_date'));
    }

    private function blockingAppointmentsForDate(Barbershop $barbershop, Carbon $date): Collection
    {
        return Appointment::where('barbershop_id', $barbershop->id)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->get(['start_time', 'end_time']);
    }

    private function getSlotsForSchedule(Barbershop $barbershop, Carbon $date, $schedule, Service $service, iterable $existingAppointments): array
    {
        $start = $date->copy()->setTimeFromTimeString($schedule->start_time);
        $end = $date->copy()->setTimeFromTimeString($schedule->end_time);
        $slotInterval = $this->slotIntervalMinutes($barbershop);

        $slots = [];
        $existingAppointments = collect($existingAppointments);

        $current = $start->copy();
        if ($date->isToday() && $current->lessThan(now())) {
            while ($current->lessThan(now())) {
                $current->addMinutes($slotInterval);
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

            $current->addMinutes($slotInterval);
        }

        return $slots;
    }

    public function getAvailableSlotsForService(Barbershop $barbershop, Carbon $date, $schedule, Service $service): array
    {
        return collect($this->getSlotsForService($barbershop, $date, $schedule, $service))
            ->filter(fn (array $slot) => $slot['available'])
            ->pluck('time')
            ->all();
    }

    public function getSlotsForServiceInSchedules(Barbershop $barbershop, Carbon $date, iterable $schedules, Service $service, ?iterable $existingAppointments = null): array
    {
        $appointments = $existingAppointments === null
            ? $this->blockingAppointmentsForDate($barbershop, $date)
            : collect($existingAppointments);

        return collect($schedules)
            ->sortBy('start_time')
            ->flatMap(fn ($schedule) => $this->getSlotsForSchedule($barbershop, $date, $schedule, $service, $appointments))
            ->unique('time')
            ->sortBy('time')
            ->values()
            ->all();
    }

    public function getAvailableSlotsForServiceInSchedules(Barbershop $barbershop, Carbon $date, iterable $schedules, Service $service): array
    {
        return collect($this->getSlotsForServiceInSchedules($barbershop, $date, $schedules, $service))
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

        $service = Service::with('barbershop:id,barber_id')
            ->where('id', $validated['service_id'])
            ->where('barbershop_id', $barbershop->id)
            ->first();

        if (! $service) {
            throw ValidationException::withMessages([
                'service_id' => 'El servicio seleccionado no pertenece a esta barbería.',
            ]);
        }

        if (! $service->isVisibleTo(auth()->user())) {
            throw ValidationException::withMessages([
                'service_id' => 'El servicio seleccionado no está disponible.',
            ]);
        }

        $startTime = Carbon::createFromFormat('Y-m-d H:i', $validated['datetime']);
        $endTime = $startTime->copy()->addMinutes($service->duration);

        if (! $this->isSelectableSlot($barbershop, $service, $startTime)) {
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

    public function isSelectableSlot(Barbershop $barbershop, Service $service, Carbon $startTime): bool
    {
        $barbershop->loadMissing('schedules');

        $bookingWindowStart = Carbon::today();
        $bookingWindowEnd = $bookingWindowStart->copy()->addMonthNoOverflow()->endOfMonth();

        if (! $startTime->betweenIncluded($bookingWindowStart, $bookingWindowEnd)) {
            return false;
        }

        $schedules = $barbershop->schedules
            ->where('day_of_week', $startTime->dayOfWeekIso)
            ->sortBy('start_time')
            ->values();

        if ($schedules->isEmpty()) {
            return false;
        }

        return in_array(
            $startTime->format('H:i'),
            $this->getAvailableSlotsForServiceInSchedules($barbershop, $startTime->copy()->startOfDay(), $schedules, $service),
            true
        );
    }

    public function isSlotAvailable(Barbershop $barbershop, Carbon $startTime, Carbon $endTime): bool
    {
        return ! Appointment::where('barbershop_id', $barbershop->id)
            ->where('appointment_date', $startTime->format('Y-m-d'))
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('start_time', '<', $endTime->format('H:i:s'))
            ->where('end_time', '>', $startTime->format('H:i:s'))
            ->exists();
    }

    public function clientHasAppointmentOnDate(int $clientId, Carbon $date): bool
    {
        return Appointment::where('client_id', $clientId)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->exists();
    }

    public function slotIntervalMinutes(Barbershop $barbershop): int
    {
        $interval = (int) ($barbershop->slot_interval_minutes ?: 60);

        return in_array($interval, [15, 30, 45, 60], true) ? $interval : 60;
    }
}
