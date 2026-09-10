<?php

namespace Tests\Feature\Admin;

use App\Enums\CategoryStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Employer;
use Database\Seeders\AdminAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminAccessSeeder::class);
        $this->admin = Admin::factory()->superAdmin()->create();
    }

    public function test_admin_can_create_a_category_with_auto_slug(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/categories', [
                'name' => 'Data Science',
                'sort_order' => 5,
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Data Science',
            'slug' => 'data-science',
            'status' => CategoryStatus::Approved->value,
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_approve_employer_suggestion(): void
    {
        $employer = Employer::factory()->create();
        $category = Category::factory()->pending($employer)->create([
            'name' => 'Blockchain',
            'slug' => 'blockchain',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/categories/'.$category->id.'/approve')
            ->assertRedirect();

        $category->refresh();

        $this->assertSame(CategoryStatus::Approved, $category->status);
        $this->assertTrue($category->is_active);
        $this->assertNotNull($category->reviewed_at);
    }

    public function test_admin_can_reject_employer_suggestion(): void
    {
        $category = Category::factory()->pending()->create(['name' => 'Crypto Mining']);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/categories/'.$category->id.'/reject')
            ->assertRedirect();

        $this->assertSame(CategoryStatus::Rejected, $category->fresh()->status);
    }

    public function test_categories_index_shows_managed_and_pending_sections(): void
    {
        Category::factory()->create(['name' => 'Engineering']);
        Category::factory()->pending()->create(['name' => 'Quantum Ops']);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/categories')
            ->assertOk()
            ->assertSee('Approved categories')
            ->assertSee('Employer-added categories')
            ->assertSee('Engineering')
            ->assertSee('Quantum Ops');
    }
}
