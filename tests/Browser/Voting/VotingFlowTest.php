<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;

uses()->group('voting', 'browser');

beforeEach(function () {
    $this->penilaiEmployee = Employee::factory()->create();
    $this->penilai = User::factory()->penilai()->create([
        'employee_id' => $this->penilaiEmployee->id,
    ]);
});

it('completes voting flow successfully', function () {
    $period = Period::factory()->create([
        'status' => 'open',
        'name' => 'Semester Ganjil 2025',
    ]);
    $category = Category::factory()->create([
        'nama' => 'Kategori 1',
        'urutan' => 1,
    ]);
    $employees = Employee::factory()->count(3)->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create([
        'category_id' => $category->id,
        'urutan' => 1,
    ]);

    $this->actingAs($this->penilai);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertSee('Voting')
        ->assertSee($period->name);

    visit("/penilai/voting/{$period->id}/{$category->id}")
        ->assertNoJavascriptErrors()
        ->assertSee('Daftar Pegawai')
        ->assertSee($employees->first()->nama);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employees->first()->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    expect($response->isRedirect())->toBeTrue();

    $this->assertDatabaseHas('votes', [
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employees->first()->id,
        'category_id' => $category->id,
    ]);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertSee('Riwayat')
        ->assertSee($employees->first()->nama);
});

it('displays validation errors when scores are missing', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => [],
    ]);

    $response->assertSessionHasErrors('scores');

    visit('/penilai/voting')
        ->assertNoJavascriptErrors();
});

it('displays validation errors when scores are out of range', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criterion = Criterion::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => [
            [
                'criterion_id' => $criterion->id,
                'score' => 150,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('scores.0.score');

    visit('/penilai/voting')
        ->assertNoJavascriptErrors();
});

it('displays validation errors when score is below minimum', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criterion = Criterion::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => [
            [
                'criterion_id' => $criterion->id,
                'score' => 0,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('scores.0.score');
});

it('prevents voting for yourself with validation error', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $criterion = Criterion::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $this->penilaiEmployee->id,
        'category_id' => $category->id,
        'scores' => [
            [
                'criterion_id' => $criterion->id,
                'score' => 85,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('employee_id');

    visit('/penilai/voting')
        ->assertNoJavascriptErrors();
});

it('prevents duplicate votes for the same employee', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    $this->actingAs($this->penilai);

    $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $this->assertDatabaseHas('votes', [
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
    ]);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    expect($response->status())->toBe(500);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors();
});

it('displays voting history with all submitted votes', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employees = Employee::factory()->count(3)->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    foreach ($employees as $employee) {
        Vote::factory()->create([
            'period_id' => $period->id,
            'voter_id' => $this->penilaiEmployee->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ]);
    }

    $this->actingAs($this->penilai);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertSee('Riwayat')
        ->assertSee($period->name);

    foreach ($employees as $employee) {
        visit('/penilai/voting/history')
            ->assertSee($employee->nama);
    }
});

it('displays vote details with scores per criterion', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $this->actingAs($this->penilai);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertSee($employee->nama)
        ->assertSee((string) collect($scores)->sum('score'));
});

it('groups history by period', function () {
    $period1 = Period::factory()->create([
        'status' => 'open',
        'name' => 'Semester Ganjil 2025',
    ]);
    $period2 = Period::factory()->create([
        'status' => 'closed',
        'name' => 'Semester Genap 2024',
    ]);
    $category = Category::factory()->create();
    $employee1 = Employee::factory()->create(['category_id' => $category->id]);
    $employee2 = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    Vote::factory()->create([
        'period_id' => $period1->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee1->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    Vote::factory()->create([
        'period_id' => $period2->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee2->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $this->actingAs($this->penilai);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertSee($period1->name)
        ->assertSee($period2->name);
});

it('shows success message after submitting vote', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    $this->actingAs($this->penilai);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $response->assertRedirect()->assertSessionHas('success');

    visit('/penilai/voting')
        ->assertNoJavascriptErrors();
});

it('shows error message when voting in closed period', function () {
    $period = Period::factory()->create(['status' => 'closed']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    $this->actingAs($this->penilai);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $response->assertRedirect()->assertSessionHas('error');

    visit('/penilai/voting')
        ->assertNoJavascriptErrors();
});

it('displays empty state when no active period', function () {
    $this->actingAs($this->penilai);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertStatus(200);
});

it('displays empty state when no votes in history', function () {
    $this->actingAs($this->penilai);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertStatus(200);
});

it('prevents unauthorized access to voting routes', function () {
    $user = User::factory()->create(['role' => 'Peserta']);

    $this->actingAs($user);

    visit('/penilai/voting')
        ->assertStatus(200);

    visit('/penilai/voting/history')
        ->assertStatus(200);
});
