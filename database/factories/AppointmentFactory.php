<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->time('H:i');
        $end = date('H:i', strtotime($start) + 3600);
        $barbershop = \App\Models\Barbershop::inRandomOrder()->first() ?? \App\Models\Barbershop::factory()->create();
        $service = \App\Models\Service::where('barbershop_id', $barbershop->id)->inRandomOrder()->first()
            ?? \App\Models\Service::factory()->create(['barbershop_id' => $barbershop->id]);
        $status = fake()->randomElement(['pending', 'accepted', 'rejected', 'completed', 'cancelled']);
        $barberComment = in_array($status, ['accepted', 'rejected'], true)
            ? fake()->optional(0.7)->randomElement($this->barberComments())
            : null;

        return [
            'client_id' => User::where('role', 'customer')->inRandomOrder()->first()->id ?? User::factory()->state(['role' => 'customer'])->create()->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'start_time' => $start,
            'end_time' => $end,
            'client_comment' => fake()->optional(0.6)->randomElement($this->clientComments()),
            'rejection_reason' => $status === 'rejected' ? $barberComment : null,
            'barber_comment' => $barberComment,
            'status' => $status,
        ];
    }

    private function clientComments(): array
    {
        return [
            'Prefiero un corte sencillo.',
            'Llegare cinco minutos antes.',
            'Quiero mantener el largo de arriba.',
            'Necesito repasar los laterales.',
            'Tengo el pelo sensible.',
            'Me gustaria un acabado natural.',
            'Voy con prisa, gracias.',
            'Quiero arreglar tambien la nuca.',
        ];
    }

    private function barberComments(): array
    {
        return [
            'Tu cita queda confirmada.',
            'Te esperamos unos minutos antes.',
            'Por favor, avisanos si llegas tarde.',
            'No tendremos disponibilidad en ese horario.',
            'Necesitamos mover esta cita a otro momento.',
            'La reserva esta revisada por la barberia.',
            'Gracias por reservar con nosotros.',
            'Puedes traer una referencia del corte.',
        ];
    }
}
