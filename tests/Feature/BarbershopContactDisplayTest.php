<?php

namespace Tests\Feature;

use App\Models\Barbershop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarbershopContactDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_barbershop_phone_is_visible_on_public_pages(): void
    {
        $barbershop = Barbershop::factory()->create([
            'name' => 'Barber Contacto',
            'phone' => '611 222 333',
            'visibility' => 'public',
        ]);

        $this
            ->get(route('inicio'))
            ->assertOk()
            ->assertSee('611 222 333')
            ->assertSee('tel:611222333', false);

        $this
            ->get(route('barbershop', $barbershop->name))
            ->assertOk()
            ->assertSee('611 222 333')
            ->assertSee('tel:611222333', false);
    }
}
