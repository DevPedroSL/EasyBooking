<?php

namespace Tests\Feature;

use App\Models\Barbershop;
use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentSelectionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarbershopManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_barbershop_dashboard_shows_owner_management_menu(): void
    {
        [$user, $barbershop] = $this->createBarberWithBarbershop();

        $response = $this
            ->actingAs($user)
            ->get(route('barbershops.dashboard'));

        $response
            ->assertOk()
            ->assertSee($barbershop->name)
            ->assertSee('Editar barberia')
            ->assertSee('Horario')
            ->assertSee('Servicios')
            ->assertSee('Agenda')
            ->assertSee('Gestionar citas')
            ->assertSee(route('barbershops.editMy', absolute: false), false)
            ->assertSee(route('barbershops.schedule.edit', absolute: false), false)
            ->assertSee(route('barbershops.services.index', absolute: false), false)
            ->assertSee(route('appointments.agenda', absolute: false), false)
            ->assertSee(route('appointments.barber', absolute: false), false);
    }

    public function test_user_without_barbershop_is_redirected_from_barbershop_dashboard(): void
    {
        $user = User::factory()->barber()->create();

        $this
            ->actingAs($user)
            ->get(route('barbershops.dashboard'))
            ->assertRedirect(route('inicio'));
    }

    public function test_general_barbershop_edit_page_shows_services_link_instead_of_services_form(): void
    {
        [$user, $barbershop] = $this->createBarberWithBarbershop();

        Service::factory()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Corte clásico',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('barbershops.editMy'));

        $response
            ->assertOk()
            ->assertSee('Editar servicios')
            ->assertDontSee('Nombre del Servicio')
            ->assertDontSee('Crear nuevo servicio');
    }

    public function test_services_index_lists_existing_services(): void
    {
        [$user, $barbershop] = $this->createBarberWithBarbershop();

        Service::factory()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Arreglo de barba',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('barbershops.services.index'));

        $response
            ->assertOk()
            ->assertSee('Crear nuevo servicio')
            ->assertSee('Arreglo de barba');
    }

    public function test_barber_can_create_and_update_services_from_dedicated_routes(): void
    {
        [$user, $barbershop] = $this->createBarberWithBarbershop();

        $createResponse = $this
            ->actingAs($user)
            ->post(route('barbershops.services.store'), [
                'name' => 'Corte premium',
                'description' => 'Incluye lavado',
                'duration' => 45,
                'price' => 22.50,
                'visibility' => 'private',
            ]);

        $createResponse
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('barbershops.services.index'));

        $service = $barbershop->services()->where('name', 'Corte premium')->first();

        $this->assertNotNull($service);
        $this->assertSame('Corte premium', $service->name);
        $this->assertSame('private', $service->visibility);

        $updateResponse = $this
            ->actingAs($user)
            ->patch(route('barbershops.services.update', $service), [
                'name' => 'Corte premium plus',
                'description' => 'Incluye lavado y peinado',
                'duration' => 60,
                'price' => 30,
                'visibility' => 'public',
            ]);

        $updateResponse
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('barbershops.services.index'));

        $service->refresh();

        $this->assertSame('Corte premium plus', $service->name);
        $this->assertSame('Incluye lavado y peinado', $service->description);
        $this->assertSame(60, $service->duration);
        $this->assertSame('30.00', $service->price);
        $this->assertSame('public', $service->visibility);
    }

    public function test_private_services_are_hidden_from_public_barbershop_page_and_booking_route(): void
    {
        [, $barbershop] = $this->createBarberWithBarbershop();

        $publicService = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Corte visible',
            'visibility' => 'public',
        ]);

        $privateService = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Servicio interno',
            'visibility' => 'private',
        ]);

        $response = $this->get(route('barbershop', $barbershop->name));

        $response
            ->assertOk()
            ->assertSee('Corte visible')
            ->assertDontSee('Servicio interno');

        $this->get(route('appointments.create', ['barbershop' => $barbershop, 'service' => $publicService]))
            ->assertOk();

        $this->get(route('appointments.create', ['barbershop' => $barbershop, 'service' => $privateService]))
            ->assertNotFound();
    }

    public function test_booking_page_shows_current_and_next_month_and_allows_confirming_a_slot_next_month(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [, $barbershop] = $this->createBarberWithBarbershop();
            $service = Service::factory()->create([
                'barbershop_id' => $barbershop->id,
                'name' => 'Corte semanal',
                'duration' => 30,
                'visibility' => 'public',
            ]);

            foreach (range(1, 7) as $dayOfWeek) {
                Schedule::factory()->create([
                    'barbershop_id' => $barbershop->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '10:00:00',
                    'end_time' => '14:00:00',
                ]);
            }

            $response = $this->get(route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]));

            $response
                ->assertOk()
                ->assertSee('Seleccionar fecha y hora')
                ->assertSee('Mes actual y siguiente')
                ->assertSee('May 2026')
                ->assertSee('June 2026');

            $client = User::factory()->create();
            $nextMonthLastAvailableDate = Carbon::today()->copy()->addMonthNoOverflow()->endOfMonth()->format('Y-m-d') . ' 10:00';

            $this->actingAs($client)
                ->get(route('appointments.confirm', [
                    'barbershop' => $barbershop,
                    'service_id' => $service->id,
                    'datetime' => $nextMonthLastAvailableDate,
                ]))
                ->assertOk()
                ->assertSee('30/06/2026')
                ->assertSee('10:00');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_booking_page_disables_days_without_available_slots(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [, $barbershop] = $this->createBarberWithBarbershop();
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
                'end_time' => '11:00:00',
            ]);

            $response = $this->get(route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]));

            $content = $response->assertOk()->getContent();

            $this->assertMatchesRegularExpression(
                '/<button(?=[^>]*data-date="2026-05-05")(?=[^>]*\sdisabled(?:\s|>))[^>]*>/s',
                $content
            );
            $this->assertDoesNotMatchRegularExpression(
                '/<button(?=[^>]*data-date="2026-05-06")(?=[^>]*\sdisabled(?:\s|>))[^>]*>/s',
                $content
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_booking_slots_start_on_the_next_available_hour(): void
    {
        Carbon::setTestNow('2026-05-04 18:35:00');

        try {
            [, $barbershop] = $this->createBarberWithBarbershop();
            $barbershop->schedules()->delete();

            $service = Service::factory()->create([
                'barbershop_id' => $barbershop->id,
                'duration' => 30,
                'visibility' => 'public',
            ]);

            $schedule = Schedule::factory()->create([
                'barbershop_id' => $barbershop->id,
                'day_of_week' => Carbon::today()->dayOfWeekIso,
                'start_time' => '17:00:00',
                'end_time' => '21:00:00',
            ]);

            $slots = app(AppointmentSelectionService::class)
                ->getAvailableSlotsForService($barbershop, Carbon::today(), $schedule, $service);

            $this->assertNotContains('17:00', $slots);
            $this->assertNotContains('18:00', $slots);
            $this->assertContains('19:00', $slots);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_change_time_link_returns_to_the_previously_selected_booking_day(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [, $barbershop] = $this->createBarberWithBarbershop();
            $service = Service::factory()->create([
                'barbershop_id' => $barbershop->id,
                'duration' => 30,
                'visibility' => 'public',
            ]);

            foreach (range(1, 7) as $dayOfWeek) {
                Schedule::factory()->create([
                    'barbershop_id' => $barbershop->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '10:00:00',
                    'end_time' => '14:00:00',
                ]);
            }

            $client = User::factory()->create();
            $datetime = '2026-06-12 10:00';

            $this
                ->actingAs($client)
                ->get(route('appointments.confirm', [
                    'barbershop' => $barbershop,
                    'service_id' => $service->id,
                    'datetime' => $datetime,
                ]))
                ->assertOk()
                ->assertSee('datetime=2026-06-12%2010%3A00', false);

            $this
                ->actingAs($client)
                ->get(route('appointments.create', [
                    'barbershop' => $barbershop,
                    'service' => $service,
                    'datetime' => $datetime,
                ]))
                ->assertOk()
                ->assertSee("selectedDatetime: '2026-06-12 10:00'", false)
                ->assertSee('selectedDay === 42', false)
                ->assertSee('visibleMonth: 1', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_pending_and_accepted_appointments_do_not_offer_the_slot_again(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [, $barbershop] = $this->createBarberWithBarbershop();
            $service = Service::factory()->create([
                'barbershop_id' => $barbershop->id,
                'duration' => 30,
                'visibility' => 'public',
            ]);

            $date = Carbon::create(2026, 6, 3);

            Schedule::factory()->create([
                'barbershop_id' => $barbershop->id,
                'day_of_week' => $date->dayOfWeekIso,
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
            ]);

            Appointment::factory()->create([
                'barbershop_id' => $barbershop->id,
                'service_id' => $service->id,
                'appointment_date' => $date->format('Y-m-d'),
                'start_time' => '10:00:00',
                'end_time' => '10:30:00',
                'status' => 'pending',
            ]);

            Appointment::factory()->create([
                'barbershop_id' => $barbershop->id,
                'service_id' => $service->id,
                'appointment_date' => $date->format('Y-m-d'),
                'start_time' => '11:00:00',
                'end_time' => '11:30:00',
                'status' => 'accepted',
            ]);

            Appointment::factory()->create([
                'barbershop_id' => $barbershop->id,
                'service_id' => $service->id,
                'appointment_date' => $date->format('Y-m-d'),
                'start_time' => '12:00:00',
                'end_time' => '12:30:00',
                'status' => 'rejected',
            ]);

            $schedule = $barbershop->schedules()->first();
            $slots = app(AppointmentSelectionService::class)
                ->getAvailableSlotsForService($barbershop, $date, $schedule, $service);

            $this->assertNotContains('10:00', $slots);
            $this->assertNotContains('11:00', $slots);
            $this->assertContains('12:00', $slots);
            $this->assertContains('13:00', $slots);

            $this->assertFalse(app(AppointmentSelectionService::class)->isSlotAvailable(
                $barbershop,
                Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' 10:00'),
                Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' 10:30'),
            ));

            $this->assertTrue(app(AppointmentSelectionService::class)->isSlotAvailable(
                $barbershop,
                Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' 12:00'),
                Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' 12:30'),
            ));

            $response = $this->get(route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]));

            $response
                ->assertOk()
                ->assertSee('10:00')
                ->assertSee('11:00')
                ->assertSee('12:00')
                ->assertSee('slot-choice-unavailable', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createBarberWithBarbershop(): array
    {
        $user = User::factory()->create([
            'role' => 'barber',
        ]);

        $barbershop = Barbershop::factory()->create([
            'barber_id' => $user->id,
            'name' => 'Barber Test',
        ]);

        return [$user, $barbershop];
    }
}
