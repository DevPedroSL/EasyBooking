<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Barbershop;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = $this->ensureCustomers();

        Barbershop::with(['services', 'schedules'])
            ->orderBy('id')
            ->get()
            ->each(function (Barbershop $barbershop) use ($customers): void {
                if ($barbershop->appointments()->exists()) {
                    return;
                }

                $this->ensureBookingCatalog($barbershop);

                if ($barbershop->services->isEmpty() || $barbershop->schedules->isEmpty()) {
                    return;
                }

                $this->seedAppointmentsForBarbershop($barbershop, $customers);
            });
    }

    /**
     * @return Collection<int, User>
     */
    private function ensureCustomers(): Collection
    {
        $customersToCreate = max(0, 12 - User::where('role', 'customer')->count());

        if ($customersToCreate > 0) {
            User::factory($customersToCreate)->customer()->create();
        }

        return User::where('role', 'customer')->orderBy('id')->get();
    }

    private function ensureBookingCatalog(Barbershop $barbershop): void
    {
        if ($barbershop->services->isEmpty()) {
            foreach ($this->serviceCatalog() as $service) {
                Service::create([
                    'barbershop_id' => $barbershop->id,
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'duration' => $service['duration'],
                    'price' => $service['price'],
                    'visibility' => 'public',
                ]);
            }
        }

        if ($barbershop->schedules->isEmpty()) {
            foreach ([1, 2, 3, 4, 5] as $day) {
                Schedule::create([
                    'barbershop_id' => $barbershop->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00',
                    'end_time' => '21:00',
                ]);
            }
        }

        $barbershop->load(['services', 'schedules']);
    }

    /**
     * @param  Collection<int, User>  $customers
     */
    private function seedAppointmentsForBarbershop(Barbershop $barbershop, Collection $customers): void
    {
        $services = $barbershop->services->values();
        $schedules = $barbershop->schedules->sortBy('day_of_week')->values();
        $statuses = ['pending', 'accepted', 'pending', 'accepted', 'accepted', 'completed', 'rejected', 'cancelled'];

        foreach ($statuses as $index => $status) {
            $schedule = $schedules[$index % $schedules->count()];
            $service = $services[$index % $services->count()];
            $date = $index < 5
                ? $this->nextOpenDate((int) $schedule->day_of_week, intdiv($index, $schedules->count()))
                : $this->previousOpenDate((int) $schedule->day_of_week, intdiv($index - 5, $schedules->count()));
            $start = $this->appointmentStart($date, $schedule->start_time, $schedule->end_time, (int) $service->duration, $index);
            $end = $start->copy()->addMinutes((int) $service->duration);

            Appointment::create([
                'client_id' => $customers[$index % $customers->count()]->id,
                'barbershop_id' => $barbershop->id,
                'service_id' => $service->id,
                'appointment_date' => $date->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'status' => $status,
                'client_comment' => $this->clientComment($index),
                'rejection_reason' => $status === 'rejected' ? 'No tenemos disponibilidad en ese horario.' : null,
                'barber_comment' => $this->barberComment($status),
            ]);
        }
    }

    private function nextOpenDate(int $dayOfWeekIso, int $weekOffset = 0): Carbon
    {
        $daysUntilOpen = ($dayOfWeekIso - Carbon::today()->dayOfWeekIso + 7) % 7;

        return Carbon::today()
            ->addDays($daysUntilOpen === 0 ? 7 : $daysUntilOpen)
            ->addWeeks($weekOffset);
    }

    private function previousOpenDate(int $dayOfWeekIso, int $weekOffset = 0): Carbon
    {
        $daysSinceOpen = (Carbon::today()->dayOfWeekIso - $dayOfWeekIso + 7) % 7;

        return Carbon::today()
            ->subDays($daysSinceOpen === 0 ? 7 : $daysSinceOpen)
            ->subWeeks($weekOffset);
    }

    private function appointmentStart(Carbon $date, string $scheduleStart, string $scheduleEnd, int $duration, int $index): Carbon
    {
        $start = $date->copy()->setTimeFromTimeString($scheduleStart)->addMinutes(($index % 4) * 60);
        $end = $date->copy()->setTimeFromTimeString($scheduleEnd);

        if ($start->copy()->addMinutes($duration)->gt($end)) {
            return $date->copy()->setTimeFromTimeString($scheduleStart);
        }

        return $start;
    }

    private function clientComment(int $index): ?string
    {
        return [
            'Prefiero mantener el largo de arriba.',
            'Llegare cinco minutos antes.',
            'Quiero repasar laterales y nuca.',
            null,
            'Me gustaria un acabado natural.',
            'Servicio realizado sin incidencias.',
            'Necesitaba mover la cita si era posible.',
            null,
        ][$index] ?? null;
    }

    private function barberComment(string $status): ?string
    {
        return match ($status) {
            'accepted' => 'Tu cita queda confirmada.',
            'completed' => 'Servicio completado correctamente.',
            'cancelled' => 'Cita cancelada por el cliente.',
            'rejected' => 'Necesitamos mover esta cita a otro momento.',
            default => null,
        };
    }

    /**
     * @return array<int, array{name: string, description: string, duration: int, price: float}>
     */
    private function serviceCatalog(): array
    {
        return [
            ['name' => 'Corte clasico', 'description' => 'Corte limpio para el dia a dia.', 'duration' => 30, 'price' => 15.00],
            ['name' => 'Degradado', 'description' => 'Degradado marcado con acabado moderno.', 'duration' => 35, 'price' => 18.00],
            ['name' => 'Arreglo de barba', 'description' => 'Recorte y perfilado de barba.', 'duration' => 20, 'price' => 10.00],
        ];
    }
}
