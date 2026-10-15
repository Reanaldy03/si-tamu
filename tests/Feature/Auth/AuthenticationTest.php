<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Petugas Resmi',
            'email' => 'petugas@sitamu.local',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
    }

    public function test_guest_can_view_login_screen(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('SI-TAMU Back-Office');
    }

    public function test_authenticated_user_redirected_from_login_screen(): void
    {
        $response = $this->actingAs($this->user)->get('/login');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'petugas@sitamu.local',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($this->user);

        // Verify last_login_at updated
        $this->assertNotNull($this->user->fresh()->last_login_at);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.login_success',
            'actor_user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'petugas@sitamu.local',
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Verify audit log for failed login
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.login_failed',
            'actor_type' => 'PUBLIC',
        ]);
    }

    public function test_generic_error_for_non_existent_email(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'unknown@sitamu.local',
            'password' => 'AnyPassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Kredensial yang diberikan tidak cocok dengan data kami.']);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $inactiveUser = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Petugas Nonaktif',
            'email' => 'nonaktif@sitamu.local',
            'password' => 'Password123!',
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'nonaktif@sitamu.local',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.login_blocked_inactive',
            'actor_user_id' => $inactiveUser->id,
        ]);
    }

    public function test_rate_limiting_triggers_after_five_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'petugas@sitamu.local',
                'password' => 'WrongPassword',
            ]);
        }

        // 6th attempt should be blocked with 429
        $response = $this->post('/login', [
            'email' => 'petugas@sitamu.local',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(429);
    }

    public function test_user_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'auth.logout',
            'actor_user_id' => $this->user->id,
        ]);
    }

    public function test_ensure_user_is_active_middleware_blocks_inactive_session(): void
    {
        $this->user->update(['is_active' => false]);

        $response = $this->actingAs($this->user)->get('/admin/dashboard');

        $response->assertStatus(403);
        $this->assertGuest();
    }

    public function test_security_headers_and_request_context_are_attached(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('X-Request-ID'));
    }
}
