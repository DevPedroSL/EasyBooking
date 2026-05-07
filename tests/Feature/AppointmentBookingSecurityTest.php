<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Barbershop;
use App\Models\Schedules;
use App\Models\Services;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_confirm_a_second_active_appointment_on_the_same_day(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [$client, $barbershop, $service] = $this->createBookableScenarioWithExistingAppointment('pending');

            $this
                ->actingAs($client)
                ->get(route('appointments.confirm', [
                    'barbershop' => $barbershop,
                    'service_id' => $service->id,
                    'datetime' => '2026-05-06 11:00',
                ]))
                ->assertRedirect(route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]))
                ->assertSessionHasErrors(['datetime' => 'Ya tienes una cita reservada para este día.']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_client_cannot_store_a_second_active_appointment_on_the_same_day(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [$client, $barbershop, $service] = $this->createBookableScenarioWithExistingAppointment('accepted');

            $this
                ->actingAs($client)
                ->post(route('appointments.store', $barbershop), [
                    'service_id' => $service->id,
                    'datetime' => '2026-05-06 11:00',
                ])
                ->assertRedirect(route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]))
                ->assertSessionHasErrors(['datetime' => 'Ya tienes una cita reservada para este día.']);

            $this->assertSame(1, Appointments::where('client_id', $client->id)->where('appointment_date', '2026-05-06')->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_cancelled_or_rejected_appointments_do_not_block_a_new_same_day_booking(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');

        try {
            [$client, $barbershop, $service] = $this->createBookableScenarioWithExistingAppointment('rejected');

            $this
                ->actingAs($client)
                ->post(route('appointments.store', $barbershop), [
                    'service_id' => $service->id,
                    'datetime' => '2026-05-06 11:00',
                ])
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('appointments.my'));

            $this->assertSame(2, Appointments::where('client_id', $client->id)->where('appointment_date', '2026-05-06')->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createBookableScenarioWithExistingAppointment(string $existingStatus): array
    {
        $client = User::factory()->customer()->create();
        $barbershop = Barbershop::factory()->create();
        $barbershop->schedules()->delete();

        $service = Services::factory()->create([
            'barbershop_id' => $barbershop->id,
            'duration' => 30,
            'visibility' => 'public',
        ]);

        Schedules::factory()->create([
            'barbershop_id' => $barbershop->id,
            'day_of_week' => Carbon::create(2026, 5, 6)->dayOfWeekIso,
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
        ]);

        Appointments::factory()->create([
            'client_id' => $client->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-05-06',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => $existingStatus,
        ]);

        return [$client, $barbershop, $service];
    }
}
