<?php

use App\Models\User;

uses()->group('journey', 'e2e', 'auth', 'browser');

// Setup test users
beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'nip' => '199107132020121003',
        'name' => 'Test SuperAdmin',
        'email' => 'superadmin@test.com',
        'password' => bcrypt('199107132020121003'),
        'role' => 'SuperAdmin',
    ]);

    $this->admin = User::factory()->create([
        'nip' => '199605112025212037',
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('199605112025212037'),
        'role' => 'Admin',
    ]);

    $this->penilai = User::factory()->create([
        'nip' => '199702232022032013',
        'name' => 'Test Penilai',
        'email' => 'penilai@test.com',
        'password' => bcrypt('199702232022032013'),
        'role' => 'Penilai',
    ]);

    $this->peserta = User::factory()->create([
        'nip' => '199702012022031004',
        'name' => 'Test Peserta',
        'email' => 'peserta@test.com',
        'password' => bcrypt('199702012022031004'),
        'role' => 'Peserta',
    ]);
});

/**
 * JOURNEY 3: Multi-Role Access Control
 *
 * This test covers the complete end-to-end journey of:
 * 1. SuperAdmin accessing all dashboards
 * 2. Admin accessing appropriate dashboards
 * 3. Penilai accessing voting features
 * 4. Peserta accessing their certificates
 * 5. Verifying 403 errors for unauthorized access
 */
test('complete journey: super admin can access all dashboards', function () {
    expect($this->superAdmin)->not->toBeNull()
        ->and($this->superAdmin->role)->toBe('SuperAdmin');

    $page = visit('/login')
        ->fill('nip', '199107132020121003')
        ->fill('password', '199107132020121003')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/super-admin')
        ->assertSee('Dashboard');

    // Can access SuperAdmin dashboard
    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    // Can access Admin dashboard
    $page->visit('/admin')
        ->assertWait(500)
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    // Can access Penilai dashboard
    $page->visit('/penilai')
        ->assertWait(500)
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    // Can access Peserta dashboard
    $page->visit('/peserta')
        ->assertWait(500)
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    // Can access voting pages
    $page->visit('/penilai/voting')
        ->assertWait(500)
        ->assertOk()
        ->assertNoJavascriptErrors();

    // Can access certificates
    $page->visit('/peserta/sertifikat')
        ->assertWait(500)
        ->assertOk()
        ->assertNoJavascriptErrors();

    // Logout
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');
});

test('complete journey: admin has restricted access', function () {
    expect($this->admin)->not->toBeNull()
        ->and($this->admin->role)->toBe('Admin');

    $page = visit('/login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin')
        ->assertSee('Dashboard');

    // Can access Admin dashboard
    $page->assertSee('Dashboard');

    // CANNOT access SuperAdmin dashboard (should get 403)
    $page->visit('/super-admin')
        ->assertWait(500);

    // Laravel redirects unauthorized users, so check we're not on super-admin
    expect($page->assertPathIsNot('/super-admin'));

    // Can access Penilai dashboard (Admin can vote)
    $page->visit('/penilai')
        ->assertWait(500)
        ->assertOk()
        ->assertSee('Dashboard');

    // Can access Peserta dashboard
    $page->visit('/peserta')
        ->assertWait(500)
        ->assertOk()
        ->assertSee('Dashboard');

    // Can access admin routes
    $page->visit('/admin/periods')
        ->assertWait(500)
        ->assertOk()
        ->assertSee('Periode');

    // Logout
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');
});

test('complete journey: penilai has restricted access', function () {
    expect($this->penilai)->not->toBeNull()
        ->and($this->penilai->role)->toBe('Penilai');

    $page = visit('/login')
        ->fill('nip', '199702232022032013')
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/penilai')
        ->assertSee('Dashboard');

    // Can access Penilai dashboard
    $page->assertSee('Dashboard');

    // Can access voting
    $page->visit('/penilai/voting')
        ->assertWait(500)
        ->assertOk()
        ->assertNoJavascriptErrors();

    // Can access voting history
    $page->visit('/penilai/voting/history')
        ->assertWait(500)
        ->assertOk()
        ->assertNoJavascriptErrors();

    // CANNOT access Admin dashboard
    $page->visit('/admin')
        ->assertWait(500);
    expect($page->assertPathIsNot('/admin'));

    // CANNOT access SuperAdmin dashboard
    $page->visit('/super-admin')
        ->assertWait(500);
    expect($page->assertPathIsNot('/super-admin'));

    // CAN access Peserta dashboard (Penilai can view results)
    $page->visit('/peserta')
        ->assertWait(500)
        ->assertOk()
        ->assertNoJavascriptErrors();

    // CANNOT access admin period management
    $page->visit('/admin/periods')
        ->assertWait(500);
    expect($page->assertPathIsNot('/admin/periods'));

    // Logout
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');
});

test('complete journey: peserta has most restricted access', function () {
    expect($this->peserta)->not->toBeNull()
        ->and($this->peserta->role)->toBe('Peserta');

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/peserta')
        ->assertSee('Dashboard');

    // Can access Peserta dashboard
    $page->assertSee('Dashboard');

    // Can access certificates
    $page->visit('/peserta/sertifikat')
        ->assertWait(500)
        ->assertOk()
        ->assertNoJavascriptErrors();

    // Can view voting (read-only)
    $page->visit('/penilai/voting')
        ->assertWait(500)
        ->assertOk()
        ->assertNoJavascriptErrors();

    // CANNOT access Admin dashboard
    $page->visit('/admin')
        ->assertWait(500);
    expect($page->assertPathIsNot('/admin'));

    // CANNOT access SuperAdmin dashboard
    $page->visit('/super-admin')
        ->assertWait(500);
    expect($page->assertPathIsNot('/super-admin'));

    // CANNOT access admin period management
    $page->visit('/admin/periods')
        ->assertWait(500);
    expect($page->assertPathIsNot('/admin/periods'));

    // CANNOT access admin employee management
    $page->visit('/admin/employees')
        ->assertWait(500);
    expect($page->assertPathIsNot('/admin/employees'));

    // CANNOT access admin criteria management
    $page->visit('/admin/criteria')
        ->assertWait(500);
    expect($page->assertPathIsNot('/admin/criteria'));

    // Logout
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');
});

test('complete journey: unauthenticated user redirected to login', function () {
    $page = visit('/admin')
        ->assertWait(500)
        ->assertPathIs('/login')
        ->assertSee('Login');

    $page = visit('/super-admin')
        ->assertWait(500)
        ->assertPathIs('/login')
        ->assertSee('Login');

    $page = visit('/penilai')
        ->assertWait(500)
        ->assertPathIs('/login')
        ->assertSee('Login');

    $page = visit('/peserta')
        ->assertWait(500)
        ->assertPathIs('/login')
        ->assertSee('Login');

    $page = visit('/penilai/voting')
        ->assertWait(500)
        ->assertPathIs('/login');

    $page = visit('/peserta/sertifikat')
        ->assertWait(500)
        ->assertPathIs('/login');

    $page = visit('/admin/periods')
        ->assertWait(500)
        ->assertPathIs('/login');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete journey: session persistence across role changes', function () {
    // This test verifies that when roles change, access changes immediately
    // (requires re-login to pick up new role)

    $page = visit('/login')
        ->fill('nip', $this->admin->nip)
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin');

    // Can access admin areas
    $page->visit('/admin/periods')
        ->assertWait(500)
        ->assertOk();

    // Logout and login as different role
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');

    $page->fill('nip', $this->penilai->nip)
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/penilai');

    // Should no longer access admin areas
    $page->visit('/admin/periods')
        ->assertWait(500);
    expect($page->assertPathIsNot('/admin/periods'));

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete journey: api endpoint access control', function () {
    // SuperAdmin accessing dashboard stats
    $this->actingAs($this->superAdmin)
        ->get('/api/dashboard/stats')
        ->assertOk();

    $this->actingAs($this->superAdmin)
        ->get('/api/dashboard/activity')
        ->assertOk();

    // Admin accessing dashboard stats
    $this->actingAs($this->admin)
        ->get('/api/dashboard/stats')
        ->assertOk();

    $this->actingAs($this->admin)
        ->get('/api/dashboard/voting-progress')
        ->assertOk();

    // Penilai accessing dashboard stats
    $this->actingAs($this->penilai)
        ->get('/api/dashboard/stats')
        ->assertOk();

    // Peserta accessing dashboard stats
    $this->actingAs($this->peserta)
        ->get('/api/dashboard/stats')
        ->assertOk();
});

test('complete journey: logout works for all roles', function () {
    // Test SuperAdmin logout
    $page = visit('/login')
        ->fill('nip', $this->superAdmin->nip)
        ->fill('password', '199107132020121003')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/super-admin');

    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');

    // Test Admin logout
    $page->fill('nip', $this->admin->nip)
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/admin');

    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');

    // Test Penilai logout
    $page->fill('nip', $this->penilai->nip)
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertWait(1000)
        ->assertPathIs('/penilai');

    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(1000)
        ->assertPathIs('/');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});
