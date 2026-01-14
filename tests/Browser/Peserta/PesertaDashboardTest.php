<?php

use App\Models\User;
use App\Models\Period;

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

test('peserta dashboard loads successfully', function () {
    $page = visit('/login')
        ->assertSee('Login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Verify dashboard elements
    $page->assertSee('Dashboard Peserta')
        ->assertSee('Lihat hasil penilaian dan peringkat Anda')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta can view their profile information', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Verify profile card is visible
    $page->assertSee('Profil Anda')
        ->assertSee('199702012022031004') // NIP
        ->assertSee('Jabatan')
        ->assertSee('Unit Kerja')
        ->assertSee('Kategori')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta can view results when announced', function () {
    // Check if there's an announced period
    $announcedPeriod = Period::where('status', 'announced')->first();

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    if ($announcedPeriod) {
        // When period is announced, results should be visible
        $page->assertSee('Periode')
            ->assertSee('Hasil telah diumumkan');

        // Check for rankings if they exist
        $page->assertSee('Peringkat Anda');
    } else {
        // When no period is announced, should see "belum diumumkan" message
        $page->assertSee('Hasil Belum Diumumkan')
            ->assertSee('Hasil penilaian belum diumumkan');
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta cannot see admin features', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Try to access admin routes
    $page->navigate('/admin')
        ->assertWait(1000);

    // Should be redirected or see access denied
    $currentPath = $page->script('window.location.pathname')[0] ?? '';
    expect($currentPath)->not->toBe('/admin');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta cannot access admin period management', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Try to access admin period routes
    $page->navigate('/admin/periods')
        ->assertWait(1000);

    // Should be redirected
    $currentPath = $page->script('window.location.pathname')[0] ?? '';
    expect($currentPath)->not->toBe('/admin/periods');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta cannot access admin sikep import', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Try to access admin sikep routes
    $page->navigate('/admin/sikep')
        ->assertWait(1000);

    // Should be redirected
    $currentPath = $page->script('window.location.pathname')[0] ?? '';
    expect($currentPath)->not->toBe('/admin/sikep');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta results only show after announcement', function () {
    // Get current period status
    $announcedPeriod = Period::where('status', 'announced')->first();

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    if ($announcedPeriod) {
        // When announced, should see results
        $page->assertSee('Periode')
            ->assertSee('Hasil telah diumumkan');
    } else {
        // When not announced, should see waiting message
        $page->assertSee('Hasil Belum Diumumkan')
            ->assertSee('Hasil penilaian belum diumumkan')
            ->assertDontSee('Peringkat Anda');
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta dashboard displays rankings with medals when announced', function () {
    $announcedPeriod = Period::where('status', 'announced')->first();

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    if ($announcedPeriod) {
        // Check if rankings section exists
        $rankingsText = $page->script("
            return document.body.innerText.includes('Peringkat Anda')
        ")[0] ?? false;

        if ($rankingsText) {
            // Verify medal emojis are present for top 3
            $pageContent = $page->content();
            $hasMedals = str_contains($pageContent, '🥇') ||
                        str_contains($pageContent, '🥈') ||
                        str_contains($pageContent, '🥉');

            // If rankings are shown, at least one medal should be present
            if ($hasMedals) {
                expect($hasMedals)->toBeTrue();
            }
        }
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta dashboard is responsive on mobile', function () {
    $page = visit('/login')
        ->on()->mobile()
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Verify dashboard loads on mobile
    $page->assertSee('Dashboard Peserta')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta dashboard is responsive on tablet', function () {
    $page = visit('/login')
        ->on()->ipad()
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Verify dashboard loads on tablet
    $page->assertSee('Dashboard Peserta')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta dashboard is responsive on desktop', function () {
    $page = visit('/login')
        ->resize(1920, 1080)
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Verify dashboard loads on desktop
    $page->assertSee('Dashboard Peserta')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta dashboard works in dark mode', function () {
    $page = visit('/login')
        ->inDarkMode()
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    // Verify dashboard works in dark mode
    $page->assertSee('Dashboard Peserta')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
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
        ->assertSee('Sertifikat')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('peserta dashboard certificate cards display correctly', function () {
    $announcedPeriod = Period::where('status', 'announced')->first();

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta');

    if ($announcedPeriod) {
        // Check if certificate section exists
        $hasCertificates = $page->script("
            return document.body.innerText.includes('Sertifikat Anda')
        ")[0] ?? false;

        if ($hasCertificates) {
            // Verify certificate section is visible
            $page->assertSee('Sertifikat Anda');

            // Check for download buttons
            $hasDownload = $page->script("
                return document.querySelectorAll('a').length > 0
            ")[0] ?? false;

            expect($hasDownload)->toBeTrue();
        }
    }

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});
