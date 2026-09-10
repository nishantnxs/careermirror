<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Candidate;
use App\Models\Employer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_dashboard_renders_platform_kpis_for_the_default_range(): void
    {
        Candidate::factory()->count(2)->create();
        Employer::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Candidates (period)')
            ->assertSee('Employers (period)')
            ->assertSee('Jobs posted')
            ->assertSee('Featured jobs')
            ->assertSee('Revenue');
    }

    public function test_dashboard_accepts_preset_and_custom_date_ranges(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin?range=today')
            ->assertOk()
            ->assertSee('Today');

        $this->actingAs($this->admin, 'admin')
            ->get('/admin?range=custom&start_date='.now()->subDays(3)->toDateString().'&end_date='.now()->toDateString())
            ->assertOk();
    }

    public function test_dashboard_rejects_an_incomplete_custom_range(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin?range=custom&start_date='.now()->toDateString())
            ->assertRedirect('/admin?range=this_month')
            ->assertSessionHasErrors('start_date');
    }
}
