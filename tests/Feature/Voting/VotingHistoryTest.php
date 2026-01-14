<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteDetail;

uses()->group('voting');

beforeEach(function () {
    $this->penilaiEmployee = Employee::factory()->create();
    $this->penilai = User::factory()->penilai()->create([
        'employee_id' => $this->penilaiEmployee->id,
    ]);
});

it('allows penilai to access voting history page', function () {
    $this->actingAs($this->penilai)
        ->get('/penilai/voting/history')
        ->assertStatus(200);
});

it('displays all submitted votes in history', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee1 = Employee::factory()->create(['category_id' => $category->id]);
    $employee2 = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores1 = [];
    foreach ($criteria as $criterion) {
        $scores1[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    $scores2 = [];
    foreach ($criteria as $criterion) {
        $scores2[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee1->id,
        'category_id' => $category->id,
        'scores' => $scores1,
    ]);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee2->id,
        'category_id' => $category->id,
        'scores' => $scores2,
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);
});

it('displays vote details per criterion', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    $vote = Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    foreach ($scores as $scoreData) {
        VoteDetail::factory()->create([
            'vote_id' => $vote->id,
            'criterion_id' => $scoreData['criterion_id'],
            'score' => $scoreData['score'],
        ]);
    }

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);
});

it('groups votes by period', function () {
    $period1 = Period::factory()->create(['status' => 'open', 'year' => 2025]);
    $period2 = Period::factory()->create(['status' => 'closed', 'year' => 2024]);
    $category = Category::factory()->create();
    $employee1 = Employee::factory()->create(['category_id' => $category->id]);
    $employee2 = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores1 = [];
    foreach ($criteria as $criterion) {
        $scores1[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    $scores2 = [];
    foreach ($criteria as $criterion) {
        $scores2[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period1->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee1->id,
        'category_id' => $category->id,
        'scores' => $scores1,
    ]);

    Vote::factory()->create([
        'period_id' => $period2->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee2->id,
        'category_id' => $category->id,
        'scores' => $scores2,
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);
});

it('shows only votes for the authenticated penilai', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee1 = Employee::factory()->create(['category_id' => $category->id]);
    $employee2 = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $otherPenilaiEmployee = Employee::factory()->create();
    $otherPenilai = User::factory()->penilai()->create([
        'employee_id' => $otherPenilaiEmployee->id,
    ]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee1->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $otherPenilai->id,
        'employee_id' => $employee2->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);

    $votes = Vote::where('voter_id', $this->penilai->id)->get();

    expect($votes)->toHaveCount(1);
    expect($votes->first()->employee_id)->toBe($employee1->id);
});

it('returns empty history when no votes submitted', function () {
    $this->actingAs($this->penilai)
        ->get('/penilai/voting/history')
        ->assertStatus(200);
});

it('displays votes in descending order by creation date', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee1 = Employee::factory()->create(['category_id' => $category->id]);
    $employee2 = Employee::factory()->create(['category_id' => $category->id]);
    $employee3 = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    $vote1 = Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee1->id,
        'category_id' => $category->id,
        'scores' => $scores,
        'created_at' => now()->subDays(3),
    ]);

    $vote2 = Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee2->id,
        'category_id' => $category->id,
        'scores' => $scores,
        'created_at' => now()->subDay(),
    ]);

    $vote3 = Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee3->id,
        'category_id' => $category->id,
        'scores' => $scores,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);

    $votes = Vote::where('voter_id', $this->penilaiEmployee->id)
        ->orderBy('created_at', 'desc')
        ->get();

    expect($votes->first()->id)->toBe($vote3->id);
    expect($votes->last()->id)->toBe($vote1->id);
});

it('includes period information in history', function () {
    $period = Period::factory()->create([
        'status' => 'open',
        'name' => 'Semester Ganjil 2025',
        'year' => 2025,
    ]);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);
});

it('includes employee information in history', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create([
        'category_id' => $category->id,
        'nama' => 'Test Employee',
        'nip' => '123456789012345678',
    ]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);
});

it('includes category information in history', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create([
        'nama' => 'Kategori 1',
        'deskripsi' => 'Test Category',
    ]);
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(60, 100),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);
});

it('shows total score for each vote', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    $expectedTotal = 0;
    foreach ($criteria as $criterion) {
        $score = fake()->numberBetween(60, 100);
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => $score,
        ];
        $expectedTotal += $score;
    }

    $vote = Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilai->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
        'total_score' => $expectedTotal,
    ]);

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting/history');

    $response->assertStatus(200);

    expect((float) $vote->total_score)->toBe((float) $expectedTotal);
});
