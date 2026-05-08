<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Barbershop;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_barber_can_reject_an_appointment_with_a_reason(): void
    {
        [$barber, $appointment] = $this->createPendingAppointmentForBarber();

        $response = $this
            ->actingAs($barber)
            ->patch(route('appointments.updateStatus', $appointment), [
                'status' => 'rejected',
                'barber_comment' => 'No tendremos personal disponible a esa hora.',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $appointment->refresh();

        $this->assertSame('rejected', $appointment->status);
        $this->assertSame('No tendremos personal disponible a esa hora.', $appointment->barber_comment);
        $this->assertSame('No tendremos personal disponible a esa hora.', $appointment->rejection_reason);
    }

    public function test_barber_can_accept_an_appointment_with_a_comment(): void
    {
        [$barber, $appointment] = $this->createPendingAppointmentForBarber([
            'status' => 'rejected',
            'rejection_reason' => 'Motivo anterior.',
        ]);

        $response = $this
            ->actingAs($barber)
            ->patch(route('appointments.updateStatus', $appointment), [
                'status' => 'accepted',
                'barber_comment' => 'Tu cita queda confirmada. Te esperamos unos minutos antes.',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $appointment->refresh();

        $this->assertSame('accepted', $appointment->status);
        $this->assertSame('Tu cita queda confirmada. Te esperamos unos minutos antes.', $appointment->barber_comment);
        $this->assertNull($appointment->rejection_reason);
    }

    public function test_accepting_an_appointment_clears_any_previous_rejection_reason(): void
    {
        [$barber, $appointment] = $this->createPendingAppointmentForBarber([
            'status' => 'rejected',
            'rejection_reason' => 'Motivo anterior.',
            'barber_comment' => 'Motivo anterior.',
        ]);

        $response = $this
            ->actingAs($barber)
            ->patch(route('appointments.updateStatus', $appointment), [
                'status' => 'accepted',
                'rejection_reason' => 'Este texto no debe guardarse.',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $appointment->refresh();

        $this->assertSame('accepted', $appointment->status);
        $this->assertSame('Este texto no debe guardarse.', $appointment->barber_comment);
        $this->assertNull($appointment->rejection_reason);
    }

    public function test_barber_list_only_links_to_details_for_pending_appointments(): void
    {
        [$barber] = $this->createPendingAppointmentForBarber();

        $this
            ->actingAs($barber)
            ->get(route('appointments.barber'))
            ->assertOk()
            ->assertSee('Ver detalles')
            ->assertDontSee('Aceptar cita')
            ->assertDontSee('Rechazar cita')
            ->assertDontSee('Confirmar rechazo');
    }

    public function test_barber_can_filter_appointments_by_status(): void
    {
        [$barber, $pendingAppointment] = $this->createPendingAppointmentForBarber();
        $barbershop = $pendingAppointment->barbershop;
        $service = $pendingAppointment->service;
        $acceptedClient = User::factory()->customer()->create(['name' => 'Cliente Aceptado']);
        $rejectedClient = User::factory()->customer()->create(['name' => 'Cliente Rechazado']);

        Appointment::factory()->create([
            'client_id' => $acceptedClient->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'accepted',
        ]);

        Appointment::factory()->create([
            'client_id' => $rejectedClient->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '12:00:00',
            'end_time' => '12:30:00',
            'status' => 'rejected',
        ]);

        $this
            ->actingAs($barber)
            ->get(route('appointments.barber', ['status' => 'accepted']))
            ->assertOk()
            ->assertSee('Cliente Aceptado')
            ->assertSee('Aceptada')
            ->assertDontSee($pendingAppointment->client->name)
            ->assertDontSee('Cliente Rechazado');
    }

    public function test_barber_can_manage_pending_appointment_from_details_page(): void
    {
        [$barber, $appointment] = $this->createPendingAppointmentForBarber();

        $this
            ->actingAs($barber)
            ->get(route('appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Gestionar cita')
            ->assertSee('Comentario para el cliente')
            ->assertSee('Aceptar cita')
            ->assertSee('Rechazar cita')
            ->assertSee('data-confirm-title="Rechazar cita"', false)
            ->assertSee('data-confirm-message="Vas a rechazar esta cita. El comentario que hayas escrito se mostrara al cliente."', false);
    }

    public function test_client_can_see_barber_comment_on_rejected_appointment(): void
    {
        [, $appointment, $client] = $this->createPendingAppointmentForBarber([
            'status' => 'rejected',
            'rejection_reason' => 'El barbero no estará disponible por una emergencia.',
            'barber_comment' => 'El barbero no estará disponible por una emergencia.',
        ]);

        $this
            ->actingAs($client)
            ->get(route('appointments.my'))
            ->assertOk()
            ->assertSee('Comentario de la barbería')
            ->assertSee('El barbero no estará disponible por una emergencia.');

        $this
            ->actingAs($client)
            ->get(route('appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Comentario de la barbería')
            ->assertSee('El barbero no estará disponible por una emergencia.');
    }

    public function test_client_can_see_barber_comment_on_accepted_appointment(): void
    {
        [, $appointment, $client] = $this->createPendingAppointmentForBarber([
            'status' => 'accepted',
            'barber_comment' => 'Tu cita esta confirmada. Te esperamos cinco minutos antes.',
        ]);

        $this
            ->actingAs($client)
            ->get(route('appointments.my'))
            ->assertOk()
            ->assertSee('Comentario de la barbería')
            ->assertSee('Tu cita esta confirmada. Te esperamos cinco minutos antes.');

        $this
            ->actingAs($client)
            ->get(route('appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Comentario de la barbería')
            ->assertSee('Tu cita esta confirmada. Te esperamos cinco minutos antes.');
    }

    private function createPendingAppointmentForBarber(array $appointmentOverrides = []): array
    {
        $barber = User::factory()->barber()->create();
        $client = User::factory()->customer()->create();
        $barbershop = Barbershop::factory()->create([
            'barber_id' => $barber->id,
        ]);
        $service = Service::factory()->create([
            'barbershop_id' => $barbershop->id,
        ]);
        $appointment = Appointment::factory()->create(array_merge([
            'client_id' => $client->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-05-10',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => 'pending',
            'rejection_reason' => null,
            'barber_comment' => null,
        ], $appointmentOverrides));

        return [$barber, $appointment, $client];
    }
}
