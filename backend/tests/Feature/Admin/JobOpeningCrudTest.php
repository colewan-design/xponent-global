<?php

namespace Tests\Feature\Admin;

use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobOpeningCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_list_update_and_delete_a_job_opening(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $create = $this->postJson('/api/v1/admin/job-openings', [
            'title' => 'Field Engineer',
            'employment_type' => 'full_time',
            'status' => 'open',
        ]);
        $create->assertCreated();
        $id = $create->json('data.id');
        $this->assertDatabaseHas('job_openings', ['id' => $id, 'slug' => 'field-engineer']);

        $this->getJson('/api/v1/admin/job-openings')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Field Engineer']);

        $this->putJson("/api/v1/admin/job-openings/{$id}", [
            'title' => 'Senior Field Engineer',
            'employment_type' => 'full_time',
            'status' => 'closed',
        ])->assertOk()->assertJsonPath('data.title', 'Senior Field Engineer');

        $this->assertDatabaseHas('job_openings', ['id' => $id, 'status' => 'closed']);

        $this->deleteJson("/api/v1/admin/job-openings/{$id}")->assertOk();
        $this->assertDatabaseMissing('job_openings', ['id' => $id]);
    }

    public function test_editor_can_manage_job_openings(): void
    {
        // Content CRUD is editor territory — only Users/Settings are admin-only.
        Sanctum::actingAs(User::factory()->create(['role' => 'editor']));

        $this->postJson('/api/v1/admin/job-openings', [
            'title' => 'HSE Officer',
            'employment_type' => 'contract',
            'status' => 'open',
        ])->assertCreated();
    }

    public function test_search_filters_job_openings_by_title(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        JobOpening::create(['title' => 'Field Engineer', 'slug' => 'field-engineer', 'employment_type' => 'full_time', 'status' => 'open']);
        JobOpening::create(['title' => 'Business Development Manager', 'slug' => 'bdm', 'employment_type' => 'full_time', 'status' => 'open']);

        $this->getJson('/api/v1/admin/job-openings?search=Engineer')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Field Engineer');
    }
}
