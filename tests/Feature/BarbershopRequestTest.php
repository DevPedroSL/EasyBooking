<?php

namespace Tests\Feature;

use App\Mail\BarbershopRequestApproved;
use App\Mail\BarbershopRequestCreated;
use App\Mail\BarbershopRequestRejected;
use App\Models\Barbershop;
use App\Models\BarbershopRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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
            'visibility' => 'private',
        ]);
        $this->assertDatabaseHas('barbershops', [
            'barber_id' => $customer->id,
            'name' => 'Barberia Norte',
            'visibility' => 'private',
            'is_approved' => false,
        ]);

        Mail::assertSent(BarbershopRequestCreated::class, function (BarbershopRequestCreated $mail) use ($admin) {
            return $mail->hasTo($admin->email)
                && $mail->barbershopRequest->name === 'Barberia Norte';
        });
    }

    public function test_customer_can_edit_private_barbershop_before_admin_approval(): void
    {
        Mail::fake();

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this
            ->actingAs($customer)
            ->post(route('barbershop-requests.store'), [
                'name' => 'Barberia Editable',
                'address' => 'Calle Editable 1',
                'phone' => '612345678',
            ])
            ->assertRedirect(route('barbershop-requests.create'));

        $barbershop = $customer->barbershop()->firstOrFail();

        $this
            ->actingAs($customer)
            ->get(route('barbershops.editMy'))
            ->assertOk()
            ->assertSee('Mientras tanto, puedes editar sus datos', false)
            ->assertDontSee('<option value="public"', false);

        $this
            ->actingAs($customer)
            ->patch(route('barbershops.updateMy'), [
                'name' => 'Barberia Editable Retocada',
                'address' => 'Calle Editable 2',
                'phone' => '698765432',
                'visibility' => 'private',
            ])
            ->assertRedirect(route('barbershops.editMy'))
            ->assertSessionHas('success');

        $barbershop->refresh();

        $this->assertSame('Barberia Editable Retocada', $barbershop->name);
        $this->assertSame('private', $barbershop->visibility);
        $this->assertFalse($barbershop->is_approved);
    }

    public function test_customer_cannot_publish_barbershop_before_admin_approval(): void
    {
        Mail::fake();

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this
            ->actingAs($customer)
            ->post(route('barbershop-requests.store'), [
                'name' => 'Barberia Bloqueada',
                'address' => 'Calle Bloqueada 1',
                'phone' => '612345678',
            ]);

        $barbershop = $customer->barbershop()->firstOrFail();

        $this
            ->actingAs($customer)
            ->patch(route('barbershops.updateMy'), [
                'name' => 'Barberia Bloqueada',
                'address' => 'Calle Bloqueada 1',
                'phone' => '612345678',
                'visibility' => 'public',
            ])
            ->assertSessionHasErrors('visibility');

        $this->assertSame('private', $barbershop->refresh()->visibility);
    }

    public function test_customer_can_request_a_barbershop_even_when_email_delivery_fails(): void
    {
        Log::spy();
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('Mailbox does not exist.'));

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this
            ->actingAs($customer)
            ->post(route('barbershop-requests.store'), [
                'name' => 'Barberia Sin Email',
                'address' => 'Calle Sin Email 12',
                'phone' => '612345678',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('barbershop-requests.create'));

        $this->assertDatabaseHas('barbershop_requests', [
            'requester_id' => $customer->id,
            'name' => 'Barberia Sin Email',
            'status' => 'pending',
            'visibility' => 'private',
        ]);

        Log::shouldHaveReceived('warning')->once();
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

    public function test_barbershop_request_form_does_not_offer_public_visibility(): void
    {
        $customer = User::factory()->customer()->create();

        $this
            ->actingAs($customer)
            ->get(route('barbershop-requests.create'))
            ->assertOk()
            ->assertDontSee('name="visibility"', false)
            ->assertSee('La barberia se creara como privada', false);
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
            'visibility' => 'public',
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
            'is_approved' => true,
        ]);

        Mail::assertSent(BarbershopRequestApproved::class, function (BarbershopRequestApproved $mail) use ($customer) {
            return $mail->hasTo($customer->email)
                && $mail->barbershopRequest->name === 'Barberia Aprobada'
                && $mail->barbershop?->name === 'Barberia Aprobada';
        });
    }

    public function test_approved_barber_can_publish_the_barbershop_after_approval(): void
    {
        $barber = User::factory()->barber()->create();
        $barbershop = Barbershop::factory()->create([
            'barber_id' => $barber->id,
            'name' => 'Barberia Publicable',
            'address' => 'Calle Publicable 5',
            'phone' => '612345678',
            'visibility' => 'private',
        ]);

        $this
            ->actingAs($barber)
            ->patch(route('barbershops.updateMy'), [
                'name' => 'Barberia Publicable',
                'address' => 'Calle Publicable 5',
                'phone' => '612345678',
                'visibility' => 'public',
            ])
            ->assertRedirect(route('barbershops.editMy'))
            ->assertSessionHas('success');

        $this->assertSame('public', $barbershop->refresh()->visibility);
    }

    public function test_barbershop_request_approved_email_renders(): void
    {
        $customer = User::factory()->customer()->create();
        $barbershopRequest = BarbershopRequest::create([
            'requester_id' => $customer->id,
            'name' => 'Barberia Render',
            'address' => 'Calle Render 4',
            'phone' => '612345678',
            'visibility' => 'public',
            'status' => 'approved',
        ]);
        $barbershop = Barbershop::factory()->create([
            'barber_id' => $customer->id,
            'name' => 'Barberia Render',
            'address' => 'Calle Render 4',
            'phone' => '612345678',
            'visibility' => 'public',
        ]);

        $html = (new BarbershopRequestApproved($barbershopRequest->load('requester'), $barbershop))->render();

        $this->assertStringContainsString('Barberia Render', $html);
        $this->assertStringContainsString('Solicitud aceptada', $html);
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
