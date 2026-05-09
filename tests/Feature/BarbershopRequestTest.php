<?php

namespace Tests\Feature;

use App\Mail\BarbershopRequestApproved;
use App\Mail\BarbershopRequestCreated;
use App\Mail\BarbershopRequestRejected;
use App\Models\Barbershop;
use App\Models\BarbershopRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BarbershopRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_a_barbershop(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $response = $this
            ->actingAs($customer)
            ->post(route('barbershop-requests.store'), [
                'name' => 'Barberia Norte',
                'address' => 'Calle Norte 12',
                'phone' => '612345678',
                'visibility' => 'public',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('barbershop-requests.create'));

        $this->assertDatabaseHas('barbershop_requests', [
            'requester_id' => $customer->id,
            'name' => 'Barberia Norte',
            'status' => 'pending',
        ]);

        Mail::assertSent(BarbershopRequestCreated::class, function (BarbershopRequestCreated $mail) use ($admin) {
            return $mail->hasTo($admin->email)
                && $mail->barbershopRequest->name === 'Barberia Norte';
        });
    }

    public function test_customer_cannot_create_two_pending_barbershop_requests(): void
    {
        Mail::fake();

        $customer = User::factory()->customer()->create();

        BarbershopRequest::create([
            'requester_id' => $customer->id,
            'name' => 'Barberia Pendiente',
            'address' => 'Calle Pendiente 1',
            'phone' => '612345678',
            'visibility' => 'public',
        ]);

        $this
            ->actingAs($customer)
            ->post(route('barbershop-requests.store'), [
                'name' => 'Otra Barberia',
                'address' => 'Calle Nueva 2',
                'phone' => '698765432',
                'visibility' => 'private',
            ])
            ->assertRedirect(route('barbershop-requests.create'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('barbershop_requests', [
            'name' => 'Otra Barberia',
        ]);

        Mail::assertNothingSent();
    }

    public function test_admin_can_approve_a_barbershop_request(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $request = BarbershopRequest::create([
            'requester_id' => $customer->id,
            'name' => 'Barberia Aprobada',
            'address' => 'Calle Aprobada 3',
            'phone' => '612345678',
            'visibility' => 'private',
        ]);

        $this
            ->actingAs($admin)
            ->patch(route('admin.barbershop-requests.approve', $request))
            ->assertRedirect(route('admin.barbershop-requests.index'))
            ->assertSessionHas('success');

        $customer->refresh();
        $request->refresh();

        $this->assertSame('barber', $customer->role);
        $this->assertSame('approved', $request->status);
        $this->assertDatabaseHas('barbershops', [
            'barber_id' => $customer->id,
            'name' => 'Barberia Aprobada',
            'visibility' => 'private',
        ]);

        Mail::assertSent(BarbershopRequestApproved::class, function (BarbershopRequestApproved $mail) use ($customer) {
            return $mail->hasTo($customer->email)
                && $mail->barbershopRequest->name === 'Barberia Aprobada';
        });
    }

    public function test_admin_can_reject_a_barbershop_request(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $request = BarbershopRequest::create([
            'requester_id' => $customer->id,
            'name' => 'Barberia Rechazada',
            'address' => 'Calle Rechazada 4',
            'phone' => '612345678',
            'visibility' => 'public',
        ]);

        $this
            ->actingAs($admin)
            ->patch(route('admin.barbershop-requests.reject', $request), [
                'rejection_reason' => 'Faltan datos verificables.',
            ])
            ->assertRedirect(route('admin.barbershop-requests.index'))
            ->assertSessionHas('success');

        $request->refresh();

        $this->assertSame('rejected', $request->status);
        $this->assertSame('Faltan datos verificables.', $request->rejection_reason);
        $this->assertSame(0, Barbershop::where('name', 'Barberia Rechazada')->count());

        Mail::assertSent(BarbershopRequestRejected::class, function (BarbershopRequestRejected $mail) use ($customer) {
            return $mail->hasTo($customer->email)
                && $mail->barbershopRequest->rejection_reason === 'Faltan datos verificables.';
        });
    }
}
