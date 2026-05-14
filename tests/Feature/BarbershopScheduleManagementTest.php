<?php

namespace Tests\Feature;

use App\Models\Barbershop;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentSelectionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarbershopScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_barbershop_creation_routes_are_not_available(): void
    {
        $barber = User::factory()->barber()->create();

        $this
            ->actingAs($barber)
            ->get('/my-barbershop/create')
            ->assertNotFound();

        $this
            ->actingAs($barber)
            ->post('/my-barbershop', [
                'name' => 'Horario Nuevo',
                'address' => 'Calle Horario 1',
                'phone' => '612345678',
                'visibility' => 'public',
            ])
            ->assertMethodNotAllowed();

        $this->assertDatabaseMissing('barbershops', [
            'name' => 'Horario Nuevo',
        ]);
    }

    public function test_barber_can_update_barbershop_schedule(): void
    {
        [$barber, $barbershop] = $this->createBarberWithBarbershop();
        $barbershop->schedules()->delete();

        Schedule::factory()->create([
            'barbershop_id' => $barbershop->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
        ]);

        $this
            ->actingAs($barber)
            ->patch(route('barbershops.schedule.update'), [
                'slot_interval_minutes' => 30,
                'schedule_days' => [2, 4],
                'schedules' => [
                    2 => [
                        ['start_time' => '09:00', 'end_time' => '13:30'],
                        ['start_time' => '15:30', 'end_time' => '19:00'],
                    ],
                    4 => [
                        ['start_time' => '16:00', 'end_time' => '21:00'],
                    ],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('barbershops.schedule.edit'));

        $barbershop->refresh();

        $this->assertSame(30, $barbershop->slot_interval_minutes);
        $this->assertSame([2, 2, 4], $barbershop->schedules()->orderBy('day_of_week')->orderBy('start_time')->pluck('day_of_week')->all());
        $this->assertDatabaseMissing('schedules', [
            'barbershop_id' => $barbershop->id,
            'day_of_week' => 1,
        ]);
        $storedSchedules = $barbershop->schedules()->get();

        $this->assertTrue($storedSchedules->contains(
            fn (Schedule $schedule) => $schedule->day_of_week === 4
                && substr($schedule->start_time, 0, 5) === '16:00'
                && substr($schedule->end_time, 0, 5) === '21:00'
        ));
        $this->assertTrue($storedSchedules->contains(
            fn (Schedule $schedule) => $schedule->day_of_week === 2
                && substr($schedule->start_time, 0, 5) === '15:30'
                && substr($schedule->end_time, 0, 5) === '19:00'
        ));
    }

    public function test_barbershop_schedule_requires_closing_after_opening(): void
    {
        [$barber, $barbershop] = $this->createBarberWithBarbershop();

        $this
            ->actingAs($barber)
            ->patch(route('barbershops.schedule.update'), [
                'slot_interval_minutes' => 60,
                'schedule_days' => [1],
                'schedules' => [
                    1 => [
                        ['start_time' => '18:00', 'end_time' => '10:00'],
                    ],
                ],
            ])
            ->assertSessionHasErrors('schedules.1.0.end_time');
    }

    public function test_schedule_edit_form_shows_weekly_schedule_controls(): void
    {
        [$barber, $barbershop] = $this->createBarberWithBarbershop();
        $barbershop->schedules()->delete();

        Schedule::factory()->create([
            'barbershop_id' => $barbershop->id,
            'day_of_week' => 3,
            'start_time' => '12:00:00',
            'end_time' => '19:00:00',
        ]);
        Schedule::factory()->create([
            'barbershop_id' => $barbershop->id,
            'day_of_week' => 3,
            'start_time' => '20:00:00',
            'end_time' => '22:00:00',
        ]);

        $this
            ->actingAs($barber)
            ->get(route('barbershops.schedule.edit'))
            ->assertOk()
            ->assertSee('Cuando puede reservar el cliente')
            ->assertSee('name="schedule_days[]"', false)
            ->assertSee('Frecuencia de citas')
            ->assertSee('Cada 15 minutos')
            ->assertSee('value="12:00"', false)
            ->assertSee('value="19:00"', false)
            ->assertSee('value="20:00"', false)
            ->assertSee('value="22:00"', false);
    }

    public function test_general_edit_form_links_to_schedule_instead_of_showing_schedule_controls(): void
    {
        [$barber] = $this->createBarberWithBarbershop();

        $this
            ->actingAs($barber)
            ->get(route('barbershops.editMy'))
            ->assertOk()
            ->assertSee('Modificar horario')
            ->assertSee(route('barbershops.schedule.edit', absolute: false), false)
            ->assertDontSee('name="schedule_days[]"', false);
    }

    public function test_booking_uses_both_schedule_intervals_for_the_same_day(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [, $barbershop] = $this->createBarberWithBarbershop();
            $barbershop->update([
                'slot_interval_minutes' => 15,
            ]);
            $barbershop->schedules()->delete();

            $service = Service::factory()->create([
                'barbershop_id' => $barbershop->id,
                'duration' => 30,
                'visibility' => 'public',
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

            $this
                ->get(route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]))
                ->assertOk()
                ->assertSee('10:00')
                ->assertSee('10:15')
                ->assertSee('16:00');

            $this
                ->actingAs(User::factory()->customer()->create())
                ->get(route('appointments.confirm', [
                    'barbershop' => $barbershop,
                    'service_id' => $service->id,
                    'datetime' => '2026-05-06 16:15',
                ]))
                ->assertOk()
                ->assertSee('16:15');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_booking_respects_custom_slot_interval(): void
    {
        Carbon::setTestNow('2026-05-04 18:35:00');

        try {
            [, $barbershop] = $this->createBarberWithBarbershop();
            $barbershop->update([
                'slot_interval_minutes' => 15,
            ]);
            $barbershop->schedules()->delete();

            $service = Service::factory()->create([
                'barbershop_id' => $barbershop->id,
                'duration' => 15,
                'visibility' => 'public',
            ]);

            $schedule = Schedule::factory()->create([
                'barbershop_id' => $barbershop->id,
                'day_of_week' => Carbon::today()->dayOfWeekIso,
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
            ]);

            $slots = app(AppointmentSelectionService::class)
                ->getAvailableSlotsForService($barbershop, Carbon::today(), $schedule, $service);

            $this->assertNotContains('18:30', $slots);
            $this->assertContains('18:45', $slots);
            $this->assertContains('19:00', $slots);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createBarberWithBarbershop(): array
    {
        $barber = User::factory()->barber()->create();
        $barbershop = Barbershop::factory()->create([
            'barber_id' => $barber->id,
            'name' => 'Barber Horario',
        ]);

        return [$barber, $barbershop];
    }
}
