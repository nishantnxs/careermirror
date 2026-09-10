<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Domain;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteSettingTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected Domain $domain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
        $this->domain = Domain::query()->where('is_default', true)->firstOrFail();
    }

    /** @return array<string, mixed> */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'site_name' => 'CareerMirror',
            'site_tagline' => 'See your career clearly',
            'contact_email' => 'hello@careermirror.com',
            'default_currency' => 'INR',
            'maintenance_mode' => '0',
        ], $overrides);
    }

    public function test_legacy_settings_route_redirects_to_domain_settings(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/settings')
            ->assertRedirect(route('admin.domains.settings.edit', $this->domain));
    }

    public function test_settings_page_renders_every_group(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/domains/'.$this->domain->id.'/settings')
            ->assertOk()
            ->assertSee('General')
            ->assertSee('Contact')
            ->assertSee('Social Links')
            ->assertSee('SEO')
            ->assertSee('Mail')
            ->assertSee('System');
    }

    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('CareerMirror', Setting::get('site_name', null, $this->domain->id));
        $this->assertSame('hello@careermirror.com', Setting::get('contact_email', null, $this->domain->id));
    }

    public function test_site_name_is_required(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload(['site_name' => '']))
            ->assertSessionHasErrors('site_name');
    }

    public function test_contact_email_must_be_a_valid_email(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload(['contact_email' => 'not-an-email']))
            ->assertSessionHasErrors('contact_email');
    }

    public function test_boolean_settings_are_stored_as_flags(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload(['maintenance_mode' => '1']));

        $this->assertSame('1', Setting::get('maintenance_mode', null, $this->domain->id));

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload(['maintenance_mode' => '0']));

        $this->assertSame('0', Setting::get('maintenance_mode', null, $this->domain->id));
    }

    public function test_logo_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload([
                'site_logo' => UploadedFile::fake()->image('logo.png'),
            ]))
            ->assertRedirect();

        $path = Setting::get('site_logo', null, $this->domain->id);
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_blank_mail_password_keeps_existing_value(): void
    {
        Setting::set('mail_password', 'secret', 'mail', 'password', $this->domain->id);

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload(['mail_password' => '']));

        $this->assertSame('secret', Setting::get('mail_password', null, $this->domain->id));
    }

    public function test_logo_can_be_removed(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('logo.png')->store('settings', 'public');
        Setting::set('site_logo', $path, 'general', 'image', $this->domain->id);

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload([
                'remove_site_logo' => '1',
            ]));

        $this->assertNull(Setting::get('site_logo', null, $this->domain->id));
    }

    public function test_guests_cannot_update_settings(): void
    {
        $this->put('/admin/domains/'.$this->domain->id.'/settings', $this->validPayload())
            ->assertRedirect('/admin/login');

        $this->assertNull(Setting::get('site_name', null, $this->domain->id));
    }
}
