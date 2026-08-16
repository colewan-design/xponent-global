<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_cannot_list_admin_users(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'editor']));

        $this->getJson('/api/v1/admin/users')->assertStatus(403);
    }

    public function test_editor_cannot_view_settings(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'editor']));

        $this->getJson('/api/v1/admin/settings')->assertStatus(403);
    }

    public function test_admin_can_list_admin_users(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/v1/admin/users')->assertOk();
    }

    public function test_admin_can_view_settings(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/v1/admin/settings')->assertOk();
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/v1/admin/users/{$admin->id}")->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
