<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\DisciplineScore;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Services\DisciplineVoteService;
use Illuminate\Support\Facades\File;

uses()->group('voting');

it('calculates discipline vote average starting from PPPK TMT month', function () {
    File::shouldReceive('exists')->andReturn(false);

    $period = Period::factory()->create([
        'status' => 'open',
        'year' => 2025,
    ]);

    Category::factory()->create(['id' => 3]);
    Criterion::factory()->create(['category_id' => 3, 'urutan' => 1]);
    Criterion::factory()->create(['category_id' => 3, 'urutan' => 2]);
    Criterion::factory()->create(['category_id' => 3, 'urutan' => 3]);

    $employee = Employee::factory()->create([
        'golongan' => 'V',
        'tmt' => '2025-09-01',
    ]);

    User::factory()->peserta()->create([
        'employee_id' => $employee->id,
    ]);

    $admin = User::factory()->admin()->create();

    foreach (range(1, 12) as $month) {
        $isAfterTmt = $month >= 9;

        DisciplineScore::factory()->create([
            'employee_id' => $employee->id,
            'period_id' => null,
            'month' => $month,
            'year' => 2025,
            'score_1' => $isAfterTmt ? 50 : 0,
            'score_2' => 35,
            'score_3' => 15,
            'final_score' => $isAfterTmt ? 100 : 50,
        ]);
    }

    $service = app(DisciplineVoteService::class);
    $service->generateVotes($period->id, $admin->id, ['overwrite' => true]);

    $vote = \App\Models\Vote::query()
        ->where('period_id', $period->id)
        ->where('category_id', 3)
        ->where('employee_id', $employee->id)
        ->firstOrFail();

    expect((float) $vote->total_score)->toBe(100.0);
});
