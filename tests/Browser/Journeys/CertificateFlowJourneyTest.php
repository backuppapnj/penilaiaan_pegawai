<?php

use App\Models\Certificate;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\Storage;

uses()->group('journey', 'e2e', 'certificate', 'browser');

/**
 * JOURNEY 2: Complete Certificate Flow
 *
 * This test covers the complete end-to-end journey of:
 * 1. Creating votes and closing period
 * 2. Admin announcing period
 * 3. Admin generating certificates
 * 4. Peserta viewing certificates
 * 5. Peserta downloading certificate
 * 6. Public verifying certificate
 */
test('complete journey: certificate generation and verification', function () {
    Storage::fake('public');

    // Step 1: Setup - Create period with votes
    $period = Period::factory()->create([
        'status' => 'closed',
        'name' => 'Certificate Journey Period',
        'semester' => 'ganjil',
        'year' => 2024,
    ]);

    $category = Category::factory()->create([
        'nama' => 'Struktural',
        'urutan' => 1,
    ]);

    // Create employees and votes
    $employees = Employee::factory()->count(3)->create([
        'category_id' => $category->id,
    ]);

    $voters = User::factory()->count(5)->create(['role' => 'Penilai']);

    foreach ($voters as $voter) {
        foreach ($employees as $index => $employee) {
            Vote::factory()->create([
                'period_id' => $period->id,
                'voter_id' => $voter->id,
                'employee_id' => $employee->id,
                'category_id' => $category->id,
                'total_score' => (3 - $index) * 10 + 70,
            ]);
        }
    }

    // Step 2: Admin logs in and generates certificates
    $admin = User::factory()->create(['role' => 'Admin']);

    $page = visit('/login')
        ->fill('nip', $admin->nip)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin');

    // Navigate to period details
    $page->visit("/admin/periods/{$period->id}")
        ->assertWait(500)
        ->assertSee($period->name)
        ->assertSee('closed');

    // Generate certificates
    $page->click('Buat Sertifikat')
        ->assertWait(500);

    // Accept confirmation dialog
    $page->acceptDialog()
        ->assertWait(2000)
        ->assertSee('Sertifikat berhasil dibuat');

    // Verify certificates were created
    $certificates = Certificate::where('period_id', $period->id)->get();
    expect($certificates)->not->toBeEmpty()
        ->and($certificates)->toHaveCount(3); // One per employee

    // Step 3: Peserta logs in and views certificates
    $employee = $employees->first();
    $peserta = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    // Logout admin
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');

    // Login as peserta
    $page->fill('nip', $peserta->nip)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Navigate to certificates
    $page->click('a[href="/peserta/sertifikat"]')
        ->assertWait(500)
        ->assertSee('Sertifikat')
        ->assertSee($period->name);

    // Get the certificate
    $certificate = Certificate::where('period_id', $period->id)
        ->where('employee_id', $employee->id)
        ->first();

    expect($certificate)->not->toBeNull();

    // Step 4: Logout and verify certificate publicly
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');

    // Public verification
    $page->visit("/verify/{$certificate->certificate_id}")
        ->assertWait(500)
        ->assertSee($employee->nama)
        ->assertSee($period->name)
        ->assertSee($category->nama);

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete journey: peserta downloads certificate and verifies qr code', function () {
    Storage::fake('public');

    // Setup
    $period = Period::factory()->create([
        'name' => 'Download Journey Period',
        'status' => 'closed',
    ]);
    $category = Category::factory()->create();
    $employee = Employee::factory()->create([
        'category_id' => $category->id,
        'nama' => 'Test Download Employee',
        'nip' => '123456789012345678',
    ]);

    // Create votes
    $voters = User::factory()->count(3)->create(['role' => 'Penilai']);
    foreach ($voters as $voter) {
        Vote::factory()->create([
            'period_id' => $period->id,
            'voter_id' => $voter->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'total_score' => 90,
        ]);
    }

    // Generate certificate
    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'DOWNLOAD-JOURNEY-' . strtoupper(fake()->unique()->bothify('??????')),
        'score' => 90.0,
        'rank' => 1,
        'qr_code_path' => 'public/qr-codes/test-download.png',
    ]);

    Storage::put($certificate->qr_code_path, 'fake qr code data');

    // Create PDF file
    $pdfPath = 'public/certificates/test-download.pdf';
    Storage::put($pdfPath, '%PDF-1.4 fake pdf content');

    $certificate->update(['pdf_path' => $pdfPath]);

    // Login as peserta
    $peserta = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $employee->id,
    ]);

    $page = visit('/login')
        ->fill('nip', $peserta->nip)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // View certificates
    $page->visit('/peserta/sertifikat')
        ->assertWait(500)
        ->assertSee($period->name)
        ->assertSee($certificate->certificate_id);

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete journey: full certificate lifecycle from voting to verification', function () {
    Storage::fake('public');

    // Step 1: Create complete voting scenario
    $period = Period::factory()->create([
        'name' => 'Full Lifecycle Period',
        'status' => 'open',
        'semester' => 'ganjil',
        'year' => 2024,
    ]);

    $category = Category::factory()->create([
        'nama' => 'Fungsional',
    ]);

    $employees = Employee::factory()->count(5)->create([
        'category_id' => $category->id,
    ]);

    // Create votes from multiple penilais
    $penilais = User::factory()->count(10)->create(['role' => 'Penilai']);

    foreach ($penilais as $penilai) {
        foreach ($employees as $employee) {
            $criteriaCount = fake()->numberBetween(5, 10);
            $scores = [];
            for ($i = 0; $i < $criteriaCount; $i++) {
                $scores[] = [
                    'criterion_id' => fake()->numberBetween(1, 100),
                    'score' => fake()->numberBetween(70, 95),
                ];
            }

            Vote::factory()->create([
                'period_id' => $period->id,
                'voter_id' => $penilai->id,
                'employee_id' => $employee->id,
                'category_id' => $category->id,
                'total_score' => collect($scores)->sum('score') / count($scores),
                'scores' => $scores,
            ]);
        }
    }

    // Step 2: Admin closes and announces period
    $admin = User::factory()->create(['role' => 'Admin']);

    $page = visit('/login')
        ->fill('nip', $admin->nip)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin');

    // Close period
    $period->update(['status' => 'closed']);
    $page->visit("/admin/periods/{$period->id}")
        ->assertWait(500)
        ->assertSee('closed');

    // Generate certificates
    $page->click('Buat Sertifikat')
        ->assertWait(500)
        ->acceptDialog()
        ->assertWait(2000)
        ->assertSee('Sertifikat berhasil dibuat');

    $certificates = Certificate::where('period_id', $period->id)->get();
    expect($certificates)->toHaveCount(5);

    // Step 3: Each peserta can view their certificate
    $winningEmployee = $employees->random();
    $peserta = User::factory()->create([
        'role' => 'Peserta',
        'employee_id' => $winningEmployee->id,
    ]);

    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000);

    $page->fill('nip', $peserta->nip)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->visit('/peserta/sertifikat')
        ->assertWait(500)
        ->assertSee($period->name);

    // Step 4: Public verification of all certificates
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000);

    foreach ($certificates as $cert) {
        $page->visit("/verify/{$cert->certificate_id}")
            ->assertWait(500)
            ->assertSee($cert->employee->nama)
            ->assertSee($period->name);
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete journey: multiple certificates for different categories', function () {
    Storage::fake('public');

    // Create period with multiple categories
    $period = Period::factory()->create([
        'name' => 'Multi Category Cert Period',
        'status' => 'closed',
    ]);

    $categories = Category::factory()->count(3)->create();

    $certificatesByCategory = [];

    foreach ($categories as $index => $category) {
        $employee = Employee::factory()->create([
            'category_id' => $category->id,
        ]);

        // Create votes
        $voter = User::factory()->create(['role' => 'Penilai']);
        Vote::factory()->create([
            'period_id' => $period->id,
            'voter_id' => $voter->id,
            'employee_id' => $employee->id,
            'category_id' => $category->id,
            'total_score' => 85 + $index,
        ]);

        // Create certificate
        $certificate = Certificate::factory()->create([
            'employee_id' => $employee->id,
            'period_id' => $period->id,
            'category_id' => $category->id,
            'score' => 85 + $index,
            'rank' => 1,
        ]);

        $certificatesByCategory[$category->nama] = $certificate;
    }

    // Admin views all certificates
    $admin = User::factory()->create(['role' => 'Admin']);

    $page = visit('/login')
        ->fill('nip', $admin->nip)
        ->fill('password', 'password')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin');

    $page->visit("/admin/periods/{$period->id}")
        ->assertWait(500);

    foreach ($categories as $category) {
        $page->assertSee($category->nama);
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('certificate verification shows 404 for invalid certificate', function () {
    $page = visit('/verify/INVALID-CERTIFICATE-ID-XYZ')
        ->assertWait(500)
        ->assertStatus(404)
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('certificate verification is accessible without authentication', function () {
    Storage::fake('public');

    // Setup
    $period = Period::factory()->create(['name' => 'Public Verify Period']);
    $category = Category::factory()->create(['nama' => 'Test Category']);
    $employee = Employee::factory()->create(['nama' => 'Public Test Employee']);

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'certificate_id' => 'PUBLIC-VERIFY-' . strtoupper(fake()->unique()->bothify('??????')),
        'qr_code_path' => 'public/qr-codes/public-test.png',
    ]);

    Storage::put($certificate->qr_code_path, 'fake qr data');

    // Visit without authentication
    $page = visit("/verify/{$certificate->certificate_id}")
        ->assertWait(500)
        ->assertSee($employee->nama)
        ->assertSee($period->name)
        ->assertSee($category->nama);

    // Should not show login form
    $page->assertDontSee('Login');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});
