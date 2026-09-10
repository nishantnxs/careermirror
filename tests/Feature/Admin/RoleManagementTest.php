<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Support\AdminPermissions;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAccessSeeder::class);
        $this->admin = Admin::factory()->superAdmin()->create();
    }

    public function test_super_admin_can_manage_roles_and_permissions(): void
    {
        $permissionIds = AdminPermission::query()
            ->whereIn('slug', ['jobs.view', 'jobs.manage', 'jobs.review'])
            ->pluck('id')
            ->all();

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/roles', [
                'name' => 'Custom Job Reviewer',
                'description' => 'Reviews jobs only.',
                'is_active' => '1',
                'permissions' => $permissionIds,
            ])
            ->assertRedirect('/admin/roles')
            ->assertSessionHas('success');

        $role = AdminRole::query()->where('name', 'Custom Job Reviewer')->first();

        $this->assertNotNull($role);
        $this->assertSame(3, $role->permissions()->count());
        $this->assertTrue($role->permissions->contains('slug', 'jobs.review'));
    }

    public function test_role_permissions_can_be_updated(): void
    {
        $role = AdminRole::query()->where('slug', 'candidate-moderator')->firstOrFail();
        $permissionIds = AdminPermission::query()
            ->whereIn('slug', ['candidates.view', 'dashboard.view'])
            ->pluck('id')
            ->all();

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/roles/'.$role->id, [
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => 'Updated description',
                'is_active' => '1',
                'permissions' => $permissionIds,
            ])
            ->assertRedirect('/admin/roles');

        $this->assertSame(
            ['candidates.view', 'dashboard.view'],
            $role->fresh()->permissions()->orderBy('slug')->pluck('slug')->all()
        );
    }

    public function test_moderator_without_roles_manage_cannot_access_roles(): void
    {
        $role = AdminRole::query()->where('slug', 'job-moderator')->firstOrFail();
        $moderator = Admin::factory()->moderator($role)->create();

        $this->actingAs($moderator, 'admin')
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_seeded_permissions_cover_expected_modules(): void
    {
        foreach (AdminPermissions::allSlugs() as $slug) {
            $this->assertDatabaseHas('admin_permissions', ['slug' => $slug]);
        }

        $this->assertDatabaseHas('admin_roles', ['slug' => 'job-moderator']);
        $this->assertDatabaseHas('admin_roles', ['slug' => 'candidate-moderator']);
        $this->assertDatabaseHas('admin_roles', ['slug' => 'finance-admin']);
    }
}
