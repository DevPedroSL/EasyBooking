<?php

namespace Tests\Feature;

use App\Models\Barbershop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_non_admin_cannot_access_admin_area(): void
    {
        $customer = User::factory()->customer()->create();

        $this
            ->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_manage_barbershops_through_policy(): void
    {
        $admin = User::factory()->admin()->create();
        $barbershop = Barbershop::factory()->create();

        $this
            ->actingAs($admin)
            ->get(route('admin.barbershops.edit', $barbershop))
            ->assertOk();
    }

    public function test_non_admin_cannot_manage_barbershops_through_policy(): void
    {
        $customer = User::factory()->customer()->create();
        $barbershop = Barbershop::factory()->create();

        $this
            ->actingAs($customer)
            ->get(route('admin.barbershops.edit', $barbershop))
            ->assertForbidden();
    }

    public function test_backup_downloads_are_not_available_through_get_requests(): void
    {
        $admin = User::factory()->admin()->create();

        $this
            ->actingAs($admin)
            ->get('/admin/backups')
            ->assertMethodNotAllowed();

        $this
            ->actingAs($admin)
            ->get('/admin/backups/database')
            ->assertMethodNotAllowed();
    }

    public function test_admin_dashboard_uses_post_forms_for_backups(): void
    {
        $admin = User::factory()->admin()->create();

        $this
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee(route('admin.backup', absolute: false), false)
            ->assertSee(route('admin.backup.database', absolute: false), false)
            ->assertSee('name="_token"', false);
    }
}
