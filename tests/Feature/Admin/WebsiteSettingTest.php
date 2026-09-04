<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteSettingTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
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

    public function test_settings_page_renders_every_group(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/settings')
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
            ->put('/admin/settings', $this->validPayload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('CareerMirror', Setting::get('site_name'));
        $this->assertSame('hello@careermirror.com', Setting::get('contact_email'));
    }

    public function test_site_name_is_required(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload(['site_name' => '']))
            ->assertSessionHasErrors('site_name');
    }

    public function test_contact_email_must_be_a_valid_email(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload(['contact_email' => 'not-an-email']))
            ->assertSessionHasErrors('contact_email');
    }

    public function test_boolean_settings_are_stored_as_flags(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload(['maintenance_mode' => '1']));

        $this->assertSame('1', Setting::get('maintenance_mode'));

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload(['maintenance_mode' => '0']));

        $this->assertSame('0', Setting::get('maintenance_mode'));
    }

    public function test_logo_upload_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload([
                'site_logo' => UploadedFile::fake()->image('logo.png'),
            ]))
            ->assertSessionHasNoErrors();

        $path = Setting::get('site_logo');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_blank_smtp_password_keeps_the_saved_value(): void
    {
        Setting::set('mail_password', 'secret-value', 'mail', 'password');

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload(['mail_password' => '']));

        $this->assertSame('secret-value', Setting::get('mail_password'));
    }

    public function test_removing_an_uploaded_logo_deletes_the_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload([
                'site_logo' => UploadedFile::fake()->image('logo.png'),
            ]));

        $path = Setting::get('site_logo');

        $this->actingAs($this->admin, 'admin')
            ->put('/admin/settings', $this->validPayload(['remove_site_logo' => '1']));

        $this->assertNull(Setting::get('site_logo'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_guests_cannot_update_settings(): void
    {
        $this->put('/admin/settings', $this->validPayload())
            ->assertRedirect('/admin/login');

        $this->assertNull(Setting::get('site_name'));
    }
}
