<?php

namespace Tests\Feature;

use App\Mail\AppointmentCreated;
use App\Models\Appointment;
use App\Models\Barbershop;
use App\Models\Schedule;
use App\Models\Service;
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

            $this->assertSame(1, Appointment::where('client_id', $client->id)->where('appointment_date', '2026-05-06')->count());
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
                ->assertRedirect(route('appointments.my'))
                ->assertSessionHas('success');

            $this->assertSame(2, Appointment::where('client_id', $client->id)->where('appointment_date', '2026-05-06')->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_client_can_download_appointment_pdf(): void
    {
        $client = User::factory()->customer()->create();
        $barbershop = Barbershop::factory()->create([
            'name' => 'Barberia PDF',
            'address' => 'Calle PDF 10',
            'phone' => '612345678',
        ]);
        $service = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Corte PDF',
            'duration' => 30,
            'price' => 12.50,
        ]);
        $appointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-05-06',
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'accepted',
        ]);

        $response = $this
            ->actingAs($client)
            ->get(route('appointments.pdf', $appointment));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="cita-' . $appointment->id . '.pdf"');

        $this->assertStringStartsWith('%PDF-1.4', $response->getContent());
        $this->assertStringContainsString('Cita aceptada #' . $appointment->id, $response->getContent());
    }

    public function test_other_client_cannot_download_appointment_pdf(): void
    {
        $client = User::factory()->customer()->create();
        $otherClient = User::factory()->customer()->create();
        $barbershop = Barbershop::factory()->create();
        $service = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
        ]);
        $appointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
        ]);

        $this
            ->actingAs($otherClient)
            ->get(route('appointments.pdf', $appointment))
            ->assertForbidden();
    }

    public function test_client_cannot_download_pdf_for_pending_appointment(): void
    {
        $client = User::factory()->customer()->create();
        $barbershop = Barbershop::factory()->create();
        $service = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
        ]);
        $appointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);

        $this
            ->actingAs($client)
            ->get(route('appointments.pdf', $appointment))
            ->assertForbidden();
    }

    public function test_appointment_created_email_uses_current_html_layout(): void
    {
        $client = User::factory()->customer()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'phone' => '600000005',
        ]);
        $barber = User::factory()->barber()->create([
            'name' => 'Barber User',
        ]);
        $barbershop = Barbershop::factory()->create([
            'barber_id' => $barber->id,
            'name' => 'Barberia 1',
        ]);
        $service = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Tinte',
        ]);
        $appointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-05-28',
            'start_time' => '20:00:00',
            'end_time' => '21:00:00',
        ]);

        $html = (new AppointmentCreated($appointment))->render();

        $this->assertStringContainsString('EasyBooking', $html);
        $this->assertStringContainsString('role="presentation"', $html);
        $this->assertStringContainsString('border-radius:18px', $html);
        $this->assertStringContainsString('Detalles de la cita</h3>', $html);
        $this->assertStringNotContainsString('Detalles de la cita:</h3>', $html);
    }

    private function createBookableScenarioWithExistingAppointment(string $existingStatus): array
    {
        $client = User::factory()->customer()->create();
        $barbershop = Barbershop::factory()->create();
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
            'end_time' => '14:00:00',
        ]);

        Appointment::factory()->create([
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
