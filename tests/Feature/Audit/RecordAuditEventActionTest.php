<?php

namespace Tests\Feature\Audit;

use App\Application\Audit\Actions\RecordAuditEventAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecordAuditEventActionTest extends TestCase
{
    use RefreshDatabase;

    protected RecordAuditEventAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = $this->app->make(RecordAuditEventAction::class);
    }

    public function test_sanitizes_sensitive_keys_and_hashes_ip(): void
    {
        $user = User::create([
            'public_id' => (string) Str::ulid(),
            'name' => 'John Auditor',
            'email' => 'auditor@example.com',
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $log = $this->action->execute(
            eventType: 'security.test_event',
            actorType: 'USER',
            actorUserId: $user->id,
            targetType: 'test',
            targetId: 99,
            ipAddress: '192.168.10.15',
            metadata: [
                'username' => 'john_doe',
                'password' => 'plaintext_secret_123',
                'token' => 'jwt_or_bearer_token',
                'nested' => [
                    'nik' => '3201012345678901',
                    'normal_info' => 'harmless',
                ],
            ],
            beforeState: [
                'password_confirmation' => 'plaintext_secret_123',
                'status' => 'active',
            ],
            afterState: [
                'secret' => 'api_key_secret_val',
                'status' => 'inactive',
            ],
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertSame(hash('sha256', '192.168.10.15'), $log->ip_hash);
        $this->assertSame($user->id, $log->actor_user_id);

        // Check metadata sanitization
        $this->assertSame('john_doe', $log->metadata['username']);
        $this->assertSame('[REDACTED]', $log->metadata['password']);
        $this->assertSame('[REDACTED]', $log->metadata['token']);
        $this->assertSame('[REDACTED]', $log->metadata['nested']['nik']);
        $this->assertSame('harmless', $log->metadata['nested']['normal_info']);

        // Check before_state & after_state
        $this->assertSame('[REDACTED]', $log->before_state['password_confirmation']);
        $this->assertSame('active', $log->before_state['status']);
        $this->assertSame('[REDACTED]', $log->after_state['secret']);
        $this->assertSame('inactive', $log->after_state['status']);
    }
}
