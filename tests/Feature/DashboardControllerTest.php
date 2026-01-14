<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Category;
use App\Models\Vote;
use App\Models\Score;
use App\Models\Certificate;
use App\Enums\Role;

beforeEach(function () {
    $this->superAdmin = User::factory()->create(['role' => Role::SuperAdmin->value]);
    $this->admin = User::factory()->create(['role' => Role::Admin->value]);
    $this->penilaiEmployee = Employee::factory()->create();
    $this->penilai = User::factory()->create([
        'role' => Role::Penilai->value,
        'employee_id' => $this->penilaiEmployee->id
    ]);
    $this->pesertaEmployee = Employee::factory()->create();
    $this->peserta = User::factory()->create([
        'role' => Role::Peserta->value,
        'employee_id' => $this->pesertaEmployee->id
    ]);
});

it('displays super admin dashboard', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('super-admin.dashboard'));
    $response->assertStatus(200);
});

it('displays admin dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
    $response->assertStatus(200);
});

it('displays penilai dashboard', function () {
    $response = $this->actingAs($this->penilai)->get(route('penilai.dashboard'));
    $response->assertStatus(200);
});

it('displays peserta dashboard', function () {
    $response = $this->actingAs($this->peserta)->get(route('peserta.dashboard'));
    $response->assertStatus(200);
});

it('can get api dashboard stats', function () {
    $response = $this->actingAs($this->admin)->get('/api/dashboard/stats');
    $response->assertStatus(200)
        ->assertJsonStructure(['periods', 'category_1_count', 'category_2_count', 'voting_progress', 'has_active_period']);
});

it('can get api activity logs', function () {
    $response = $this->actingAs($this->admin)->get('/api/dashboard/activity');
    $response->assertStatus(200);
});

it('can get api voting progress', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $response = $this->actingAs($this->admin)->get('/api/dashboard/voting-progress');
    $response->assertStatus(200);
});

it('can get api results', function () {
    $period = Period::factory()->create(['status' => 'announced']);
    $response = $this->actingAs($this->admin)->get('/api/dashboard/results');
    $response->assertStatus(200);
});

it('can handle dashboard with no active period', function () {
    $response = $this->actingAs($this->admin)->get('/api/dashboard/voting-progress');
    $response->assertStatus(200)->assertJson([]);
});

it('can get api results when no announced period', function () {
    $response = $this->actingAs($this->admin)->get('/api/dashboard/results');
    $response->assertStatus(200);
});

it('can handle penilai dashboard with no active period', function () {
    $response = $this->actingAs($this->penilai)->get(route('penilai.dashboard'));
    $response->assertStatus(200);
});

it('can handle peserta dashboard with no employee linked', function () {
    $user = User::factory()->create(['role' => Role::Peserta->value]);
    $response = $this->actingAs($user)->get(route('peserta.dashboard'));
    $response->assertStatus(200);
});

it('can handle getStatsApi for different roles', function () {
    $this->actingAs($this->superAdmin)->get('/api/dashboard/stats')->assertOk();
    $this->actingAs($this->admin)->get('/api/dashboard/stats')->assertOk();
    $this->actingAs($this->penilai)->get('/api/dashboard/stats')->assertOk();
    $this->actingAs($this->peserta)->get('/api/dashboard/stats')->assertOk();
});

it('can handle activity logs when empty', function () {
    AuditLog::query()->delete();
    $response = $this->actingAs($this->admin)->get('/api/dashboard/activity');
    $response->assertStatus(200)->assertJson([]);
});

it('can handle voting progress with no employees in category', function () {
    $period = Period::factory()->create(['status' => 'open']);
    Category::factory()->create(['nama' => 'Empty Category']);
    
    $response = $this->actingAs($this->admin)->get('/api/dashboard/voting-progress');
    $response->assertStatus(200);
});

it('can handle penilai dashboard with many categories', function () {
    $period = Period::factory()->create(['status' => 'open']);
    Category::factory()->count(5)->create();
    
    $response = $this->actingAs($this->penilai)->get(route('penilai.dashboard'));
    $response->assertStatus(200);
});

it('can handle peserta dashboard with announced period', function () {
    $period = Period::factory()->create(['status' => 'announced']);
    $category = Category::factory()->create();
    $this->pesertaEmployee->update(['category_id' => $category->id]);
    
    Certificate::factory()->create([
        'period_id' => $period->id,
        'employee_id' => $this->pesertaEmployee->id,
        'category_id' => $category->id,
        'rank' => 1,
        'score' => 95.5,
        'issued_at' => now(),
    ]);

    $response = $this->actingAs($this->peserta)->get(route('peserta.dashboard'));
    $response->assertStatus(200);
});