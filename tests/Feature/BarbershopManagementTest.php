<?php

namespace Tests\Feature;

use App\Models\Barbershop;
use App\Models\Services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarbershopManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_barbershop_edit_page_shows_services_link_instead_of_services_form(): void
    {
        [$user, $barbershop] = $this->createBarberWithBarbershop();

        Services::factory()->create([
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

        Services::factory()->create([
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

        $service = $barbershop->services()->first();

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

        $publicService = Services::factory()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Corte visible',
            'visibility' => 'public',
        ]);

        $privateService = Services::factory()->create([
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
