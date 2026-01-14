<?php

use App\Models\DisciplineScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $this->actingAs($admin);
});

test('admin can access sikep import page', function () {
    $response = $this->get('/admin/sikep');

    $response->assertStatus(200);
});

test('discipline score calculation formulas work correctly', function () {
    // Test Score 1: [(E + L) / (Total Hari Kerja × 2)] × 50
    $score1 = DisciplineScore::calculateScore1(
        presentOnTime: 20, // E
        leaveOnTime: 18, // L
        totalWorkDays: 22
    );
    expect($score1)->toBe((20 + 18) / (22 * 2) * 50);

    // Test with zero work days
    $score1Zero = DisciplineScore::calculateScore1(0, 0, 0);
    expect($score1Zero)->toBe(0.0);

    // Test Score 2: [100 - Total Penalti] × 0.35
    $score2 = DisciplineScore::calculateScore2(
        lateMinutes: 25, // G-K
        earlyLeaveMinutes: 10 // N-R
    );
    expect($score2)->toBe((100 - 35) * 0.35);

    // Test with high penalties (should not go below 0)
    $score2High = DisciplineScore::calculateScore2(150, 50);
    expect($score2High)->toBe(0.0);

    // Test Score 3: Izin berlebih check
    $score3WithPermission = DisciplineScore::calculateScore3(1);
    expect($score3WithPermission)->toBe(0.0);

    $score3WithoutPermission = DisciplineScore::calculateScore3(0);
    expect($score3WithoutPermission)->toBe(15.0);

    // Test Final Score calculation
    $finalScore = DisciplineScore::calculateFinalScore(43.18, 22.75, 15.0);
    expect($finalScore)->toBe(80.93);
});

test('unauthorized users cannot access sikep import', function () {
    $regularUser = User::factory()->create(['role' => 'Peserta']);
    $this->actingAs($regularUser);

    $response = $this->get('/admin/sikep');
    $response->assertStatus(403);
});
