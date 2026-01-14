<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;

uses()->group('journey', 'e2e', 'browser');

// Setup test users and data
beforeEach(function () {
    $this->admin = User::factory()->create([
        'nip' => '199605112025212037',
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('199605112025212037'),
        'role' => 'Admin',
    ]);

    $this->penilaiEmployee = Employee::factory()->create();
    $this->penilai = User::factory()->create([
        'nip' => '199702232022032013',
        'name' => 'Test Penilai',
        'email' => 'penilai@test.com',
        'password' => bcrypt('199702232022032013'),
        'role' => 'Penilai',
        'employee_id' => $this->penilaiEmployee->id,
    ]);

    $this->pesertaEmployee = Employee::factory()->create();
    $this->peserta = User::factory()->create([
        'nip' => '199702012022031004',
        'name' => 'Test Peserta',
        'email' => 'peserta@test.com',
        'password' => bcrypt('199702012022031004'),
        'role' => 'Peserta',
        'employee_id' => $this->pesertaEmployee->id,
    ]);

    // Seed categories and criteria
    $category1 = Category::factory()->create(['nama' => 'Struktural', 'urutan' => 1]);
    Category::factory()->create(['nama' => 'Fungsional', 'urutan' => 2]);
    Category::factory()->create(['nama' => 'Pegawai Disiplin', 'urutan' => 3]);

    // Create employees for category 1
    Employee::factory()->count(5)->create(['category_id' => $category1->id]);

    // Create criteria for category 1
    Criterion::factory()->count(7)->create(['category_id' => $category1->id]);
});

/**
 * JOURNEY 1: Admin Creates Period and Penilai Submits Votes
 *
 * This test covers the complete end-to-end journey of:
 * 1. Admin creating a new period
 * 2. Admin opening the period for voting
 * 3. Admin logging out
 * 4. Penilai logging in
 * 5. Penilai submitting votes
 * 6. Penilai viewing voting history
 */
test('complete journey: admin creates period and penilai submits votes', function () {
    // Step 1: Admin creates a new period
    expect($this->admin)->not->toBeNull()
        ->and($this->admin->role)->toBe('Admin');

    $page = visit('/login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin')
        ->assertSee('Dashboard');

    // Navigate to periods page
    $page->click('a[href="/admin/periods"]')
        ->assertWait(500)
        ->assertSee('Periode');

    // Click create new period
    $page->clickLinkOrButton('Buat Periode Baru')
        ->assertWait(500)
        ->assertPathIs('/admin/periods/create')
        ->assertSee('Buat Periode Baru');

    // Fill period form
    $periodName = 'E2E Journey Test ' . now()->format('YmdHis');
    $page->fill('name', $periodName)
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->fill('start_date', '2026-01-01')
        ->fill('end_date', '2026-12-31')
        ->clickLinkOrButton('Simpan')
        ->assertWait(1000)
        ->assertPathIs('/admin/periods')
        ->assertSee('Periode berhasil dibuat')
        ->assertSee($periodName);

    // Get the created period
    $period = Period::where('name', $periodName)->first();
    expect($period)->not->toBeNull();

    // Step 2: Open the period for voting
    $page->visit("/admin/periods/{$period->id}")
        ->assertWait(500)
        ->assertSee($periodName)
        ->assertSee('draft');

    $page->click('Buka')
        ->assertWait(500)
        ->assertSee('Periode dibuka untuk voting')
        ->assertSee('open');

    // Verify status in database
    $period->refresh();
    expect($period->status)->toBe('open');

    // Step 3: Admin logs out
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');

    // Step 4: Penilai logs in
    expect($this->penilai)->not->toBeNull()
        ->and($this->penilai->role)->toBe('Penilai');

    $page->fill('nip', '199702232022032013')
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/penilai')
        ->assertSee('Dashboard');

    // Step 5: Navigate to voting and submit votes
    $page->click('a[href="/penilai/voting"]')
        ->assertWait(500)
        ->assertSee('Voting')
        ->assertSee($periodName);

    // Get category and employees for voting
    $category = Category::where('urutan', 1)->first();
    expect($category)->not->toBeNull();

    $page->visit("/penilai/voting/{$period->id}/{$category->id}")
        ->assertWait(500)
        ->assertSee('Daftar Pegawai');

    // Get first employee to vote for
    $employee = Employee::where('category_id', $category->id)
        ->whereNotNull('category_id')
        ->first();

    if ($employee) {
        // Get criteria for this category
        $criteria = Criterion::where('category_id', $category->id)->get();
        expect($criteria)->not->toBeEmpty();

        // Prepare scores data
        $scores = [];
        foreach ($criteria as $criterion) {
            $scores[] = [
                'criterion_id' => $criterion->id,
                'score' => fake()->numberBetween(70, 95),
            ];
        }

        // Submit vote via HTTP request (more reliable than browser form filling)
        $response = $this->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ]);

        expect($response->isRedirect())->toBeTrue();

        // Verify vote was created
        $this->assertDatabaseHas('votes', [
            'period_id' => $period->id,
            'voter_id' => $this->penilai->employee_id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
        ]);

        // Step 6: View voting history
        $page->visit('/penilai/voting/history')
            ->assertWait(500)
            ->assertSee('Riwayat')
            ->assertSee($periodName);

        $page->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    } else {
        $page->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();
    }
});

test('complete journey: admin creates multiple periods and manages statuses', function () {
    // Admin logs in
    $page = visit('/login')
        ->fill('nip', $this->admin->nip)
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin');

    // Create first period
    $period1Name = 'E2E Journey Period 1 ' . now()->format('YmdHis');
    $page->visit('/admin/periods/create')
        ->fill('name', $period1Name)
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertWait(1000)
        ->assertSee($period1Name);

    $period1 = Period::where('name', $period1Name)->first();
    expect($period1)->not->toBeNull();

    // Create second period
    $period2Name = 'E2E Journey Period 2 ' . now()->format('YmdHis');
    $page->visit('/admin/periods/create')
        ->fill('name', $period2Name)
        ->select('semester', 'genap')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertWait(1000)
        ->assertSee($period2Name);

    $period2 = Period::where('name', $period2Name)->first();
    expect($period2)->not->toBeNull();

    // Open first period
    $page->visit("/admin/periods/{$period1->id}")
        ->click('Buka')
        ->assertWait(500)
        ->assertSee('open');

    // Close first period
    $page->click('Tutup')
        ->assertWait(500)
        ->assertSee('closed');

    // Announce first period
    $page->click('Umumkan')
        ->assertWait(500)
        ->assertSee('announced');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete journey: penilai submits votes for all categories', function () {
    // Setup: Create an open period
    $period = Period::factory()->create([
        'status' => 'open',
        'name' => 'Multi Category Journey Test',
    ]);

    // Get all categories
    $categories = Category::orderBy('urutan')->get();

    if ($categories->isEmpty()) {
        $this->markTestSkipped('No categories available for testing');
    }

    $page = visit('/login')
        ->fill('nip', $this->penilai->nip)
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/penilai');

    $votedCount = 0;

    // Vote for each category
    foreach ($categories as $category) {
        $employee = Employee::where('category_id', $category->id)
            ->whereNotNull('category_id')
            ->first();

        if (!$employee) {
            continue;
        }

        $criteria = Criterion::where('category_id', $category->id)->get();

        if ($criteria->isEmpty()) {
            continue;
        }

        // Prepare scores
        $scores = [];
        foreach ($criteria as $criterion) {
            $scores[] = [
                'criterion_id' => $criterion->id,
                'score' => fake()->numberBetween(70, 95),
            ];
        }

        // Submit vote
        $response = $this->post('/penilai/voting', [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'scores' => $scores,
        ]);

        if ($response->isRedirect()) {
            $votedCount++;
        }
    }

    // View history showing all votes
    $page->visit('/penilai/voting/history')
        ->assertWait(500)
        ->assertSee('Riwayat')
        ->assertSee($period->name)
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    expect($votedCount)->toBeGreaterThan(0);
});

test('complete journey: admin edits period before opening', function () {
    // Admin creates period
    $page = visit('/login')
        ->fill('nip', $this->admin->nip)
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin');

    $periodName = 'E2E Edit Journey ' . now()->format('YmdHis');
    $page->visit('/admin/periods/create')
        ->fill('name', $periodName)
        ->select('semester', 'ganjil')
        ->fill('year', '2026')
        ->clickLinkOrButton('Simpan')
        ->assertWait(1000);

    $period = Period::where('name', $periodName)->first();
    expect($period)->not->toBeNull();

    // Edit period
    $newName = $periodName . ' (Updated)';
    $page->visit("/admin/periods/{$period->id}/edit")
        ->fill('name', $newName)
        ->select('semester', 'genap')
        ->fill('year', '2027')
        ->clickLinkOrButton('Simpan')
        ->assertWait(1000)
        ->assertSee($newName);

    // Verify changes
    $period->refresh();
    expect($period->name)->toBe($newName)
        ->and($period->semester)->toBe('genap')
        ->and($period->year)->toBe(2027);

    // Open period and verify
    $page->visit("/admin/periods/{$period->id}")
        ->click('Buka')
        ->assertWait(500)
        ->assertSee('open');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete journey: penilai cannot vote for themselves', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create();

    $page = visit('/login')
        ->fill('nip', $this->penilai->nip)
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/penilai');

    // Try to vote for self - should get error
    $criterion = Criterion::factory()->create(['category_id' => $category->id]);

    $response = $this->post('/penilai/voting', [
        'period_id' => $period->id,
        'employee_id' => $this->penilai->employee_id,
        'category_id' => $category->id,
        'scores' => [
            [
                'criterion_id' => $criterion->id,
                'score' => 85,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('employee_id');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});
