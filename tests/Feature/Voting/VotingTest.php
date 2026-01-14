<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Support\Facades\Auth;

uses()->group('voting');

beforeEach(function () {
    $this->penilaiEmployee = Employee::factory()->create();
    $this->penilai = User::factory()->penilai()->create([
        'employee_id' => $this->penilaiEmployee->id,
    ]);
});

it('allows penilai to access voting page', function () {
    $this->actingAs($this->penilai)
        ->get('/penilai/voting')
        ->assertStatus(200);
});

it('displays categories when active period exists', function () {
    Period::factory()->create(['status' => 'open']);
    $categories = Category::factory()->count(2)->create();

    $response = $this->actingAs($this->penilai)
        ->get('/penilai/voting');

    $response->assertStatus(200);
});

it('returns empty data when no active period', function () {
    Period::factory()->create(['status' => 'closed']);

    $this->actingAs($this->penilai)
        ->get('/penilai/voting')
        ->assertStatus(200);
});

it('excludes voted employees from the list', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($this->penilai)
        ->get('/penilai/voting')
        ->assertStatus(200);
});

it('excludes penilai themselves from employee list', function () {
    Period::factory()->create(['status' => 'open']);
    Category::factory()->create();

    $this->actingAs($this->penilai)
        ->get('/penilai/voting')
        ->assertStatus(200);
});

it('allows penilai to view voting form for specific period and category', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai)
        ->get("/penilai/voting/{$period->id}/{$category->id}")
        ->assertStatus(200);
});

it('displays all criteria for the category', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai)
        ->get("/penilai/voting/{$period->id}/{$category->id}")
        ->assertStatus(200);
});

it('displays only unvoted employees in voting form', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee1 = Employee::factory()->create(['category_id' => $category->id]);
    $employee2 = Employee::factory()->create(['category_id' => $category->id]);

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee1->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($this->penilai)
        ->get("/penilai/voting/{$period->id}/{$category->id}")
        ->assertStatus(200);
});

it('allows penilai to submit votes for an employee', function () {
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

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('votes', [
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
    ]);

    $this->assertDatabaseHas('vote_details', [
        'criterion_id' => $criteria->first()->id,
    ]);
});

it('calculates total score correctly', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    $expectedTotal = 0.0;
    foreach ($criteria as $criterion) {
        $score = fake()->numberBetween(60, 100);
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => $score,
        ];
        $expectedTotal += $score;
    }

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ]);

    $vote = Vote::where('period_id', $period->id)
        ->where('voter_id', $this->penilaiEmployee->id)
        ->where('employee_id', $employee->id)
        ->first();

    expect((float) $vote->total_score)->toBe($expectedTotal);
});

it('validates that scores are required', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => [],
        ])
        ->assertSessionHasErrors(['scores']);
});

it('validates score range is 1-100', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criterion = Criterion::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => [
                [
                    'criterion_id' => $criterion->id,
                    'score' => 150,
                ],
            ],
        ])
        ->assertSessionHasErrors(['scores.0.score']);

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => [
                [
                    'criterion_id' => $criterion->id,
                    'score' => 0,
                ],
            ],
        ])
        ->assertSessionHasErrors(['scores.0.score']);
});

it('prevents penilai from voting for themselves', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $criterion = Criterion::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $this->penilaiEmployee->id,
            'category_id' => $category->id,
            'scores' => [
                [
                    'criterion_id' => $criterion->id,
                    'score' => 85,
                ],
            ],
        ])
        ->assertSessionHasErrors(['employee_id']);
});

it('prevents duplicate votes for same employee in same period and category', function () {
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

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ])
        ->assertRedirect();

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ])
        ->assertStatus(500);
});

it('prevents voting when period is not open', function () {
    $period = Period::factory()->create(['status' => 'closed']);
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

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('validates that period exists', function () {
    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => 999,
            'employee_id' => 1,
            'category_id' => 1,
            'scores' => [[]],
        ])
        ->assertSessionHasErrors(['period_id']);
});

it('validates that employee exists', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => 999,
            'category_id' => 1,
            'scores' => [[]],
        ])
        ->assertSessionHasErrors(['employee_id']);
});

it('validates that category exists', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => 1,
            'category_id' => 999,
            'scores' => [[]],
        ])
        ->assertSessionHasErrors(['category_id']);
});

it('requires authentication to access voting routes', function () {
    $this->get('/penilai/voting')
        ->assertRedirect('/login');
});

it('allows admin to access voting routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/penilai/voting')
        ->assertStatus(200);
});

it('allows super admin to access voting routes', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->get('/penilai/voting')
        ->assertStatus(200);
});

it('prevents non-authorized users from accessing voting routes', function () {
    $user = User::factory()->create(['role' => 'Guest']);

    $this->actingAs($user)
        ->get('/penilai/voting')
        ->assertStatus(403);
});

it('creates vote details for each criterion', function () {
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

    $this->actingAs($this->penilai)
        ->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ])
        ->assertRedirect();

    $vote = Vote::where('period_id', $period->id)
        ->where('voter_id', $this->penilaiEmployee->id)
        ->where('employee_id', $employee->id)
        ->first();

    expect($vote->voteDetails)->toHaveCount($criteria->count());

    foreach ($criteria as $criterion) {
        $this->assertDatabaseHas('vote_details', [
            'vote_id' => $vote->id,
            'criterion_id' => $criterion->id,
        ]);
    }
});
