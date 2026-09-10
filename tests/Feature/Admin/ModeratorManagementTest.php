<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AdminRole;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModeratorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAccessSeeder::class);
        $this->admin = Admin::factory()->superAdmin()->create();
    }

    public function test_admin_can_create_a_moderator_with_a_role(): void
    {
        $role = AdminRole::query()->where('slug', 'finance-admin')->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/moderators', [
                'name' => 'Finance Mod',
                'email' => 'finance.mod@example.com',
                'phone' => '9876543210',
                'password' => 'password',
                'password_confirmation' => 'password',
                'admin_role_id' => $role->id,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/moderators')
            ->assertSessionHas('success');

        $moderator = Admin::query()->where('email', 'finance.mod@example.com')->first();

        $this->assertNotNull($moderator);
        $this->assertFalse($moderator->is_super_admin);
        $this->assertSame($role->id, $moderator->admin_role_id);
        $this->assertTrue($moderator->hasPermission('plans.manage'));
        $this->assertFalse($moderator->hasPermission('roles.manage'));
    }

    public function test_moderator_can_only_access_permitted_modules(): void
    {
        $role = AdminRole::query()->where('slug', 'candidate-moderator')->firstOrFail();
        $moderator = Admin::factory()->moderator($role)->create();

        $this->actingAs($moderator, 'admin')
            ->get('/admin/candidates')
            ->assertOk();

        $this->actingAs($moderator, 'admin')
            ->get('/admin/plans')
            ->assertForbidden();

        $this->actingAs($moderator, 'admin')
            ->get('/admin/moderators')
            ->assertForbidden();
    }

    public function test_admin_can_activate_and_deactivate_a_moderator(): void
    {
        $role = AdminRole::query()->where('slug', 'job-moderator')->firstOrFail();
        $moderator = Admin::factory()->moderator($role)->create(['is_active' => true]);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/moderators/'.$moderator->id.'/status')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($moderator->fresh()->is_active);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/moderators/'.$moderator->id.'/status')
            ->assertRedirect();

        $this->assertTrue($moderator->fresh()->is_active);
    }

    public function test_inactive_moderator_cannot_login_to_admin(): void
    {
        $role = AdminRole::query()->where('slug', 'job-moderator')->firstOrFail();
        $moderator = Admin::factory()->moderator($role)->create([
            'email' => 'inactive.mod@example.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $this->post('/admin/login', [
            'email' => $moderator->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }
}
