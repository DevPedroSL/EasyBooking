<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barbershop;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarberAgendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_barber_can_see_daily_agenda_with_busy_and_free_hours(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [$barber, $barbershop, $service] = $this->createAgendaScenario();
            $client = User::factory()->customer()->create([
                'name' => 'Cliente Agenda',
            ]);

            Appointment::factory()->create([
                'client_id' => $client->id,
                'barbershop_id' => $barbershop->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-05-06',
                'start_time' => '10:00:00',
                'end_time' => '10:30:00',
                'status' => 'accepted',
            ]);

            Appointment::factory()->create([
                'client_id' => $client->id,
                'barbershop_id' => $barbershop->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-05-06',
                'start_time' => '11:00:00',
                'end_time' => '11:30:00',
                'status' => 'rejected',
            ]);

            $this
                ->actingAs($barber)
                ->get(route('appointments.agenda', ['date' => '2026-05-06']))
                ->assertOk()
                ->assertSee('Agenda')
                ->assertSee('Mes actual y siguiente')
                ->assertSee('May 2026')
                ->assertSee('June 2026')
                ->assertSee('#agenda-summary')
                ->assertSee('id="agenda-summary"', false)
                ->assertSee('1 cita')
                ->assertSee('10:00 - 11:00')
                ->assertSee('Ocupada')
                ->assertSee('Cliente Agenda')
                ->assertSee('11:00 - 12:00')
                ->assertSee('Libre')
                ->assertSee('1 de 4');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_barber_agenda_shows_closed_day_without_schedule(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [$barber] = $this->createAgendaScenario();

            $this
                ->actingAs($barber)
                ->get(route('appointments.agenda', ['date' => '2026-05-07']))
                ->assertOk()
                ->assertSee('Cerrado')
                ->assertSee('La barbería no tiene horario para este día.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_barber_agenda_shows_both_schedule_intervals_for_a_day(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [$barber, $barbershop, $service] = $this->createAgendaScenario();
            $barbershop->update([
                'slot_interval_minutes' => 15,
            ]);
            $barbershop->schedules()->delete();
            $client = User::factory()->customer()->create([
                'name' => 'Cliente Tarde',
            ]);

            Schedule::factory()->create([
                'barbershop_id' => $barbershop->id,
                'day_of_week' => Carbon::create(2026, 5, 6)->dayOfWeekIso,
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
            ]);
            Schedule::factory()->create([
                'barbershop_id' => $barbershop->id,
                'day_of_week' => Carbon::create(2026, 5, 6)->dayOfWeekIso,
                'start_time' => '16:00:00',
                'end_time' => '18:00:00',
            ]);

            Appointment::factory()->create([
                'client_id' => $client->id,
                'barbershop_id' => $barbershop->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-05-06',
                'start_time' => '16:15:00',
                'end_time' => '16:30:00',
                'status' => 'accepted',
            ]);

            $this
                ->actingAs($barber)
                ->get(route('appointments.agenda', ['date' => '2026-05-06']))
                ->assertOk()
                ->assertSee('10:00 - 12:00')
                ->assertSee('16:00 - 18:00')
                ->assertSee('16:15 - 16:30')
                ->assertSee('Cliente Tarde')
                ->assertSee('1 de 16');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_user_without_barbershop_cannot_view_barber_agenda(): void
    {
        $this
            ->actingAs(User::factory()->customer()->create())
            ->get(route('appointments.agenda'))
            ->assertForbidden();
    }

    private function createAgendaScenario(): array
    {
        $barber = User::factory()->barber()->create();
        $barbershop = Barbershop::factory()->create([
            'barber_id' => $barber->id,
        ]);
        $barbershop->schedules()->delete();

        $service = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
            'duration' => 30,
        ]);

        Schedule::factory()->create([
            'barbershop_id' => $barbershop->id,
            'day_of_week' => Carbon::create(2026, 5, 6)->dayOfWeekIso,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
        ]);

        return [$barber, $barbershop, $service];
    }
}
