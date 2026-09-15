<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AdminRole;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_roleless_admin_can_open_dashboard(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => false,
            'admin_role_id' => null,
        ]);

        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->hasPermission('dashboard.view'));

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk();
    }

    public function test_moderator_without_dashboard_permission_is_forbidden(): void
    {
        $this->seed(AdminAccessSeeder::class);

        $role = AdminRole::query()->where('slug', 'candidate-moderator')->firstOrFail();
        $role->permissions()->detach(
            $role->permissions()->where('slug', 'dashboard.view')->pluck('admin_permissions.id')
        );

        $admin = Admin::factory()->moderator($role)->create();

        $this->assertFalse($admin->isSuperAdmin());
        $this->assertFalse($admin->fresh()->hasPermission('dashboard.view'));

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertForbidden();
    }
}
