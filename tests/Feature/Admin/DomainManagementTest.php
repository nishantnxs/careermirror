<?php

namespace Tests\Feature\Admin;

use App\Enums\DomainStatus;
use App\Models\Admin;
use App\Models\Domain;
use App\Models\Setting;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAccessSeeder::class);
        $this->admin = Admin::factory()->superAdmin()->create();
    }

    public function test_admin_can_create_a_domain_and_seed_settings(): void
    {
        $default = Domain::query()->where('is_default', true)->firstOrFail();
        Setting::set('site_name', 'Default Site', 'general', 'text', $default->id);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/domains', [
                'name' => 'Jobs Hub',
                'host' => 'jobs-hub.test',
                'url' => 'https://jobs-hub.test',
                'website_name' => 'Jobs Hub',
                'status' => DomainStatus::Active->value,
                'is_default' => '0',
            ])
            ->assertRedirect();

        $domain = Domain::query()->where('host', 'jobs-hub.test')->first();

        $this->assertNotNull($domain);
        $this->assertSame('Jobs Hub', Setting::get('site_name', null, $domain->id));
    }

    public function test_admin_can_deactivate_a_non_default_domain(): void
    {
        $domain = Domain::factory()->create(['is_default' => false]);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/domains/'.$domain->id.'/toggle-status')
            ->assertRedirect();

        $this->assertSame(DomainStatus::Inactive, $domain->fresh()->status);
    }

    public function test_default_domain_cannot_be_deactivated(): void
    {
        $domain = Domain::query()->where('is_default', true)->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/domains/'.$domain->id.'/toggle-status')
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(DomainStatus::Active, $domain->fresh()->status);
    }
}
