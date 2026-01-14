<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Score;
use App\Models\Vote;
use App\Services\ScoreCalculationService;

beforeEach(function () {
    $this->service = new ScoreCalculationService();
});

it('can calculate scores for employees in a category', function () {
    $period = Period::factory()->create();
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criterion1 = Criterion::factory()->create(['category_id' => $category->id, 'bobot' => 60]);
    $criterion2 = Criterion::factory()->create(['category_id' => $category->id, 'bobot' => 40]);

    // Vote 1
    Vote::factory()->create([
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => [
            ['criterion_id' => $criterion1->id, 'score' => 80],
            ['criterion_id' => $criterion2->id, 'score' => 90],
        ]
    ]);

    // Vote 2
    Vote::factory()->create([
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => [
            ['criterion_id' => $criterion1->id, 'score' => 100],
            ['criterion_id' => $criterion2->id, 'score' => 70],
        ]
    ]);

    // Average: C1=(80+100)/2 = 90, C2=(90+70)/2 = 80
    // Weighted: (90 * 0.6) + (80 * 0.4) = 54 + 32 = 86

    $this->service->calculateScores($period, $category);

    $this->assertDatabaseHas('scores', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'weighted_score' => 86.00,
    ]);
});

it('correctly ranks employees and determines winner', function () {
    $period = Period::factory()->create();
    $category = Category::factory()->create();
    $employee1 = Employee::factory()->create(['category_id' => $category->id]);
    $employee2 = Employee::factory()->create(['category_id' => $category->id]);
    $criterion = Criterion::factory()->create(['category_id' => $category->id, 'bobot' => 100]);

    // Employee 1 score 90
    Vote::factory()->create([
        'period_id' => $period->id,
        'employee_id' => $employee1->id,
        'category_id' => $category->id,
        'scores' => [['criterion_id' => $criterion->id, 'score' => 90]]
    ]);

    // Employee 2 score 95
    Vote::factory()->create([
        'period_id' => $period->id,
        'employee_id' => $employee2->id,
        'category_id' => $category->id,
        'scores' => [['criterion_id' => $criterion->id, 'score' => 95]]
    ]);

    $this->service->calculateScores($period, $category);

    $score1 = Score::where('employee_id', $employee1->id)->first();
    $score2 = Score::where('employee_id', $employee2->id)->first();

    expect($score2->rank)->toBe(1);
    expect($score2->is_winner)->toBeTrue();
    expect($score1->rank)->toBe(2);
    expect($score1->is_winner)->toBeFalse();
});

it('can calculate all scores for a period', function () {
    $period = Period::factory()->create();
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    
    $emp1 = Employee::factory()->create(['category_id' => $category1->id]);
    $emp2 = Employee::factory()->create(['category_id' => $category2->id]);
    
    $crit1 = Criterion::factory()->create(['category_id' => $category1->id, 'bobot' => 100]);
    $crit2 = Criterion::factory()->create(['category_id' => $category2->id, 'bobot' => 100]);

    Vote::factory()->create([
        'period_id' => $period->id, 'employee_id' => $emp1->id, 'category_id' => $category1->id,
        'scores' => [['criterion_id' => $crit1->id, 'score' => 80]]
    ]);
    
    Vote::factory()->create([
        'period_id' => $period->id, 'employee_id' => $emp2->id, 'category_id' => $category2->id,
        'scores' => [['criterion_id' => $crit2->id, 'score' => 70]]
    ]);

    $this->service->calculateAllScores($period);

    expect(Score::count())->toBe(2);
});

it('can recalculate scores by deleting existing ones', function () {
    $period = Period::factory()->create();
    Score::factory()->create(['period_id' => $period->id]);

    $this->service->recalculateScores($period);

    // Should be 0 because we didn't create votes for new calculation
    expect(Score::count())->toBe(0);
});

it('can get ranking and winner', function () {
    $period = Period::factory()->create();
    $category = Category::factory()->create();
    $score = Score::factory()->create([
        'period_id' => $period->id,
        'category_id' => $category->id,
        'is_winner' => true,
        'rank' => 1
    ]);

    expect($this->service->getRanking($period, $category))->toHaveCount(1);
    expect($this->service->getWinner($period, $category)->id)->toBe($score->id);
});