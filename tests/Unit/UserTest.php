<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\User;
use App\Models\Vote;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_belongs_to_employee(): void
    {
        $employee = Employee::factory()->create();
        $user = User::factory()->create(['employee_id' => $employee->id]);

        $this->assertInstanceOf(Employee::class, $user->employee);
        $this->assertEquals($employee->id, $user->employee->id);
    }

    public function test_user_has_many_votes_cast(): void
    {
        $user = User::factory()->create();
        Vote::factory()->count(2)->create(['voter_id' => $user->id]);

        $this->assertCount(2, $user->votes);
    }

    public function test_user_has_many_audit_logs(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->auditLogs);
    }

    public function test_get_dashboard_route_returns_correct_values(): void
    {
        $superAdmin = User::factory()->create(['role' => 'SuperAdmin']);
        $admin = User::factory()->create(['role' => 'Admin']);
        $penilai = User::factory()->create(['role' => 'Penilai']);
        $peserta = User::factory()->create(['role' => 'Peserta']);
        $guest = User::factory()->create(['role' => 'Guest']);

        $this->assertEquals('super-admin.dashboard', $superAdmin->getDashboardRoute());
        $this->assertEquals('admin.dashboard', $admin->getDashboardRoute());
        $this->assertEquals('penilai.dashboard', $penilai->getDashboardRoute());
        $this->assertEquals('peserta.dashboard', $peserta->getDashboardRoute());
        $this->assertEquals('dashboard', $guest->getDashboardRoute());
    }
}