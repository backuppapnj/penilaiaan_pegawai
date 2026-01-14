<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $log = AuditLog::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    public function test_audit_log_with_action_scope(): void
    {
        AuditLog::factory()->create(['action' => 'LOGIN']);
        AuditLog::factory()->create(['action' => 'UPDATE']);

        $this->assertCount(1, AuditLog::withAction('LOGIN')->get());
    }

    public function test_audit_log_for_model_scope(): void
    {
        AuditLog::factory()->create(['model_type' => 'User', 'model_id' => 1]);
        AuditLog::factory()->create(['model_type' => 'Employee', 'model_id' => 1]);

        $this->assertCount(1, AuditLog::forModel('User', 1)->get());
    }

    public function test_audit_log_recent_scope(): void
    {
        AuditLog::factory()->create(['created_at' => now()->subDays(40)]);
        AuditLog::factory()->create(['created_at' => now()]);

        $this->assertCount(1, AuditLog::recent(30)->get());
    }
}