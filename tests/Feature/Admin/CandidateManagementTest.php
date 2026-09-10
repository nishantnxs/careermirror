<?php

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Models\Admin;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guests_cannot_view_candidates(): void
    {
        $this->get('/admin/candidates')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_and_search_candidates(): void
    {
        Candidate::factory()->create([
            'name' => 'Jane Candidate',
            'email' => 'jane@example.com',
        ]);
        Candidate::factory()->create([
            'name' => 'John Other',
            'email' => 'john@example.com',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/candidates?search=Jane')
            ->assertOk()
            ->assertSee('Jane Candidate')
            ->assertDontSee('John Other');
    }

    public function test_admin_can_filter_candidates_by_status(): void
    {
        Candidate::factory()->create(['name' => 'Active Person']);
        Candidate::factory()->inactive()->create(['name' => 'Inactive Person']);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/candidates?status=inactive')
            ->assertOk()
            ->assertSee('Inactive Person')
            ->assertDontSee('Active Person');
    }

    public function test_admin_can_view_candidate_details(): void
    {
        $candidate = Candidate::factory()->create([
            'name' => 'Jane Candidate',
            'email' => 'jane@example.com',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/candidates/'.$candidate->id)
            ->assertOk()
            ->assertSee('Jane Candidate')
            ->assertSee('jane@example.com')
            ->assertSee('Resumes')
            ->assertSee('Jobs applied for');
    }

    public function test_create_candidate_page_renders(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/candidates/create')
            ->assertOk()
            ->assertSee('Add Candidate');
    }

    public function test_admin_can_create_a_candidate(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/candidates', [
                'name' => 'Jane Candidate',
                'email' => 'jane@example.com',
                'phone' => '9876543210',
                'password' => 'password',
                'password_confirmation' => 'password',
                'status' => 'active',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $candidate = Candidate::query()->where('email', 'jane@example.com')->first();

        $this->assertNotNull($candidate);
        $this->assertSame('Jane Candidate', $candidate->name);
        $this->assertSame(AccountStatus::Active, $candidate->status);
        $this->assertTrue(password_verify('password', $candidate->password));
    }

    public function test_candidate_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/candidates', [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'status']);
    }

    public function test_candidate_creation_requires_a_unique_email(): void
    {
        Candidate::factory()->create(['email' => 'jane@example.com']);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/candidates', [
                'name' => 'Jane Two',
                'email' => 'jane@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_activate_deactivate_and_suspend_a_candidate(): void
    {
        $candidate = Candidate::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/candidates/'.$candidate->id.'/status', ['status' => 'inactive'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(AccountStatus::Inactive, $candidate->fresh()->status);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/candidates/'.$candidate->id.'/status', ['status' => 'suspended'])
            ->assertRedirect();

        $this->assertSame(AccountStatus::Suspended, $candidate->fresh()->status);

        $this->actingAs($this->admin, 'admin')
            ->patch('/admin/candidates/'.$candidate->id.'/status', ['status' => 'active'])
            ->assertRedirect();

        $this->assertSame(AccountStatus::Active, $candidate->fresh()->status);
    }

    public function test_suspended_candidate_cannot_login(): void
    {
        Candidate::factory()->suspended()->create(['email' => 'locked@example.com']);

        $this->post('/candidate/login', [
            'email' => 'locked@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('candidate');
    }
}
