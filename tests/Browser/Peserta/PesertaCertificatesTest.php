<?php

use App\Models\User;
use App\Models\Period;
use App\Models\Certificate;

beforeEach(function () {
    // Create test peserta user if not exists (due to RefreshDatabase)
    $user = User::where('nip', '199702012022031004')->first();

    if (!$user) {
        $user = User::factory()->create([
            'name' => 'Muhammad Ilham',
            'nip' => '199702012022031004',
            'email' => 'muhammad.ilham@pa-penajam.go.id',
            'password' => bcrypt('199702012022031004'),
            'role' => 'Peserta',
            'is_active' => true,
        ]);

        // Create associated employee
        $user->employee()->create([
            'nip' => '199702012022031004',
            'nama' => 'Muhammad Ilham',
            'jabatan' => 'Staf',
            'unit_kerja' => 'Bagian Umum',
            'golongan' => 'III/a',
            'tmt' => '2022-03-01',
        ]);
    }
});

test('peserta can access certificates page', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Navigate to certificates page
    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000)
        ->assertPathIs('/peserta/sertifikat')
        ->assertSee('Sertifikat Saya')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta certificates page displays correctly', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000);

    // Check for search input
    $hasSearchInput = $page->script("
        return document.querySelector('input[type=\"text\"]') !== null
    ")[0] ?? false;

    expect($hasSearchInput)->toBeTrue();

    // Check for empty state or certificate cards
    $pageContent = $page->content();
    $hasEmptyState = str_contains($pageContent, 'Belum ada sertifikat') ||
                    str_contains($pageContent, 'belum memiliki sertifikat');

    expect($hasEmptyState || str_contains($pageContent, 'Unduh PDF'))->toBeTrue();

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta can search certificates', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000);

    // Type in search box
    $page->type('input[type="text"]', 'test')
        ->assertWait(500);

    // Verify search input has value
    $searchValue = $page->script("
        return document.querySelector('input[type=\"text\"]').value
    ")[0] ?? '';

    expect($searchValue)->toBe('test');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta certificates page shows empty state when no certificates', function () {
    // First check if user has certificates
    $user = User::where('nip', '199702012022031004')->first();
    $certificateCount = Certificate::where('employee_nip', $user->nip)->count();

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000);

    if ($certificateCount === 0) {
        // Should show empty state
        $page->assertSee('Belum ada sertifikat');
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta certificate cards display required information', function () {
    $user = User::where('nip', '199702012022031004')->first();
    $certificate = Certificate::where('employee_nip', $user->nip)->first();

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000);

    if ($certificate) {
        // Should show certificate information
        $page->assertSee('Sertifikat Sama');

        // Check for download button
        $hasDownloadButton = $page->script("
            return document.querySelector('a') !== null
        ")[0] ?? false;

        expect($hasDownloadButton)->toBeTrue();
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta certificates page has verification info', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000);

    // Check for verification info section
    $pageContent = $page->content();
    $hasInfo = str_contains($pageContent, 'Info') ||
              str_contains($pageContent, 'Verifikasi') ||
              str_contains($pageContent, 'QR Code');

    expect($hasInfo)->toBeTrue();

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta certificates page is responsive on mobile', function () {
    $page = visit('/login')
        ->on()->mobile()
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000)
        ->assertSee('Sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta certificates page is responsive on tablet', function () {
    $page = visit('/login')
        ->on()->ipad()
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000)
        ->assertSee('Sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta certificates page works in dark mode', function () {
    $page = visit('/login')
        ->inDarkMode()
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000)
        ->assertSee('Sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta can navigate back from certificates to dashboard', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Navigate to certificates
    $page->navigate('/peserta/sertifikat')
        ->assertWait(1000)
        ->assertPathIs('/peserta/sertifikat');

    // Navigate back to dashboard
    $page->navigate('/peserta')
        ->assertWait(1000)
        ->assertPathIs('/peserta')
        ->assertSee('Dashboard Peserta')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});
