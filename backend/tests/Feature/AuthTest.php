<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sanctum only runs the session/cookie pipeline for requests whose
     * Referer/Origin matches SANCTUM_STATEFUL_DOMAINS — exactly like a real
     * browser request from the admin SPA. Without this header the session
     * middleware never boots and $request->session() throws.
     */
    private function fromAdminOrigin(): static
    {
        return $this->withHeader('Referer', 'http://localhost:5174');
    }

    public function test_admin_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->admin()->create(['email' => 'admin@example.com']);

        $response = $this->fromAdminOrigin()->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonPath('data.email', $user->email);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $response = $this->fromAdminOrigin()->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_admin_can_log_out(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->fromAdminOrigin()->postJson('/api/v1/auth/logout')->assertOk();
    }

    public function test_unauthenticated_request_returns_401_without_accept_header(): void
    {
        // Regression test: without an explicit Accept header, Laravel's auth
        // middleware used to try redirecting to a named "login" route that
        // didn't exist, crashing with a 500 instead of a clean 401. A plain
        // get() (unlike getJson()) omits the Accept header, reproducing it.
        $response = $this->followingRedirects()->get('/api/v1/admin/dashboard');

        $response->assertStatus(401);
    }

    public function test_repeated_failed_logins_are_rate_limited(): void
    {
        // The login route is the only public door into the admin. Laravel 11+
        // ships no default throttle on the `api` group, so without an explicit
        // one this endpoint accepted unlimited guesses — verified in production
        // before the fix: 12 rapid attempts, 12 responses, zero 429s.
        User::factory()->create(['email' => 'admin@example.com']);

        $attempt = fn () => $this->fromAdminOrigin()->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        // throttle:5,1 — the first five are processed as ordinary failures.
        for ($i = 0; $i < 5; $i++) {
            $attempt()->assertStatus(422);
        }

        $attempt()->assertStatus(429);
    }

    public function test_rate_limiter_does_not_block_a_legitimate_login(): void
    {
        // Guards against setting the limit so low that a person who fats-fingers
        // their password a couple of times is locked out of their own admin.
        $user = User::factory()->admin()->create(['email' => 'admin@example.com']);

        for ($i = 0; $i < 2; $i++) {
            $this->fromAdminOrigin()->postJson('/api/v1/auth/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        $this->fromAdminOrigin()->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }
}
