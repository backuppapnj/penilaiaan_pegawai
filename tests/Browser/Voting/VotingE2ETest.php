<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;

uses()->group('voting', 'e2e', 'browser');

beforeEach(function () {
    $this->penilaiEmployee = Employee::factory()->create();
    $this->penilai = User::factory()->penilai()->create([
        'employee_id' => $this->penilaiEmployee->id,
    ]);
});

/**
 * TEST 1: Penilai Dashboard Shows Active Period
 */
test('penilai dashboard shows active period', function () {
    $period = Period::factory()->create([
        'status' => 'open',
        'name' => 'Periode Test Voting 2026',
    ]);

    $category = Category::factory()->create([
        'nama' => 'Kategori 1',
        'urutan' => 1,
    ]);

    $this->actingAs($this->penilai);

    visit('/penilai')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Dashboard')
        ->assertSee($period->name)
        ->assertSee('Kategori 1');
});

/**
 * TEST 2: Penilai Can Access Voting Page
 */
test('penilai can access voting page', function () {
    $period = Period::factory()->create([
        'status' => 'open',
        'name' => 'Periode Test Voting 2026',
    ]);

    $category = Category::factory()->create([
        'nama' => 'Kategori 1',
        'urutan' => 1,
    ]);

    Employee::factory()->count(3)->create(['category_id' => $category->id]);
    Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Voting')
        ->assertSee($period->name)
        ->assertSee('Kategori 1');

    visit("/penilai/voting/{$period->id}/{$category->id}")
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Daftar Pegawai')
        ->assertSee('Simpan');
});

/**
 * TEST 3: Voting Form Validation - Empty Scores
 */
test('voting form validation shows errors for empty scores', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => [],
    ]);

    $response->assertSessionHasErrors('scores');

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    $this->assertDatabaseMissing('votes', [
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
    ]);
});

/**
 * TEST 4: Voting Form Validation - Invalid Score Range (Too High)
 */
test('voting form validation shows errors for scores above 100', function () {
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
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/**
 * TEST 5: Voting Form Validation - Invalid Score Range (Negative)
 */
test('voting form validation shows errors for negative scores', function () {
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
                'score' => -5,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('scores.0.score');

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/**
 * TEST 6: Penilai Can Submit Vote Successfully
 */
test('penilai can submit vote successfully', function () {
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

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('votes', [
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
    ]);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/**
 * TEST 7: Cannot Vote Twice for Same Employee
 */
test('cannot vote twice for same employee in same period and category', function () {
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

    // First vote
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

    // Try to vote again
    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    // Should return error or 500
    expect($response->status())->toBeGreaterThanOrEqual(400);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/**
 * TEST 8: Can Vote for Multiple Employees
 */
test('can vote for multiple employees in same category', function () {
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

    $this->actingAs($this->penilai);

    // Vote for each employee
    foreach ($employees as $employee) {
        $this->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ]);
    }

    // Verify all votes are recorded
    foreach ($employees as $employee) {
        $this->assertDatabaseHas('votes', [
            'period_id' => $period->id,
            'voter_id' => $this->penilaiEmployee->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
        ]);
    }

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    foreach ($employees as $employee) {
        visit('/penilai/voting/history')
            ->assertSee($employee->nama);
    }
});

/**
 * TEST 9: Voting History Displays Correctly
 */
test('voting history displays submitted votes correctly', function () {
    $period = Period::factory()->create([
        'status' => 'open',
        'name' => 'Periode Test 2026',
    ]);
    $category = Category::factory()->create(['nama' => 'Kategori Test']);
    $employee = Employee::factory()->create([
        'category_id' => $category->id,
        'nama' => 'Pegawai Test',
    ]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    $totalScore = 0;
    foreach ($criteria as $criterion) {
        $score = fake()->numberBetween(70, 95);
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => $score,
        ];
        $totalScore += $score;
    }

    Vote::factory()->create([
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
        'category_id' => $category->id,
        'scores' => $scores,
        'total_score' => $totalScore,
    ]);

    $this->actingAs($this->penilai);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Riwayat')
        ->assertSee($period->name)
        ->assertSee($employee->nama)
        ->assertSee((string) $totalScore);
});

/**
 * TEST 10: Cannot Vote When Period is Closed
 */
test('cannot vote when period is closed', function () {
    $period = Period::factory()->create([
        'status' => 'closed',
        'name' => 'Periode Closed',
    ]);
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

    $this->assertDatabaseMissing('votes', [
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $employee->id,
    ]);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/**
 * TEST 11: Voting Progress Updates Correctly
 */
test('voting progress updates after submitting votes', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    $employees = Employee::factory()->count(5)->create(['category_id' => $category->id]);
    $criteria = Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $scores = [];
    foreach ($criteria as $criterion) {
        $scores[] = [
            'criterion_id' => $criterion->id,
            'score' => fake()->numberBetween(70, 95),
        ];
    }

    $this->actingAs($this->penilai);

    // Submit votes for 3 out of 5 employees
    foreach ($employees->take(3) as $employee) {
        $this->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ]);
    }

    // Check that 3 votes are recorded
    $voteCount = Vote::where('period_id', $period->id)
        ->where('voter_id', $this->penilaiEmployee->id)
        ->where('category_id', $category->id)
        ->count();

    expect($voteCount)->toBe(3);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/**
 * TEST 12: Prevents Voting for Yourself
 */
test('prevents voting for yourself', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();
    Criterion::factory()->count(7)->create(['category_id' => $category->id]);

    $this->actingAs($this->penilai);

    $scores = [
        [
            'criterion_id' => 1,
            'score' => 85,
        ],
    ];

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $this->penilaiEmployee->id,
        'category_id' => $category->id,
        'scores' => $scores,
    ]);

    $response->assertSessionHasErrors('employee_id');

    $this->assertDatabaseMissing('votes', [
        'period_id' => $period->id,
        'voter_id' => $this->penilaiEmployee->id,
        'employee_id' => $this->penilaiEmployee->id,
    ]);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/**
 * TEST 13: Voting History Groups by Period
 */
test('voting history groups votes by period', function () {
    $period1 = Period::factory()->create([
        'status' => 'open',
        'name' => 'Semester Ganjil 2026',
    ]);
    $period2 = Period::factory()->create([
        'status' => 'closed',
        'name' => 'Semester Genap 2025',
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
        ->assertNoConsoleLogs()
        ->assertSee($period1->name)
        ->assertSee($period2->name)
        ->assertSee($employee1->nama)
        ->assertSee($employee2->nama);
});

/**
 * TEST 14: Empty State When No Votes in History
 */
test('displays empty state when no votes in history', function () {
    $this->actingAs($this->penilai);

    visit('/penilai/voting/history')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertStatus(200);
});

/**
 * TEST 15: Empty State When No Active Period
 */
test('displays empty state when no active period', function () {
    $this->actingAs($this->penilai);

    visit('/penilai/voting')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs()
        ->assertStatus(200);
});
