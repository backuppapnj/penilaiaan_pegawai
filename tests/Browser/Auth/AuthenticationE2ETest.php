<?php

use App\Models\User;

beforeEach(function () {
    // Create test users for each role
    User::factory()->superAdmin()->create([
        'nip' => '199107132020121003',
        'name' => 'Muhardiansyah',
        'password' => bcrypt('199107132020121003'),
    ]);

    User::factory()->admin()->create([
        'nip' => '199605112025212037',
        'name' => 'Najwa Hijriana',
        'password' => bcrypt('199605112025212037'),
    ]);

    User::factory()->penilai()->create([
        'nip' => '199702232022032013',
        'name' => 'Nur Rizka Fani',
        'password' => bcrypt('199702232022032013'),
    ]);

    User::factory()->peserta()->create([
        'nip' => '199702012022031004',
        'name' => 'Muhammad Ilham',
        'password' => bcrypt('199702012022031004'),
    ]);
});

/*
|--------------------------------------------------------------------------
| Test 1: Home Page Shows Login Form
|--------------------------------------------------------------------------
*/
test('home page displays login form', function () {
    $page = visit('/');

    $page->assertVisible('input[name="nip"]')
        ->assertVisible('input[name="password"]')
        ->assertVisible('button[type="submit"]')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 2: Login with Invalid Credentials
|--------------------------------------------------------------------------
*/
test('login with invalid credentials shows error message', function () {
    $page = visit('/login')
        ->fill('nip', 'invalid_nip')
        ->fill('password', 'wrong_password')
        ->press('button[type="submit"]')
        ->assertWait(1000);

    $page->assertSee('credentials')
        ->assertPathIs('/login')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 3: Login with Valid SuperAdmin Credentials
|--------------------------------------------------------------------------
*/
test('login with valid super admin credentials redirects to super admin dashboard', function () {
    $page = visit('/login')
        ->fill('nip', '199107132020121003')
        ->fill('password', '199107132020121003')
        ->press('button[type="submit"]')
        ->assertWait(2000);

    $page->assertPathIs('/super-admin')
        ->assertSee('Dashboard')
        ->assertVisible('button[type="submit"]')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 4: Login with Valid Admin Credentials
|--------------------------------------------------------------------------
*/
test('login with valid admin credentials redirects to admin dashboard', function () {
    $page = visit('/login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->press('button[type="submit"]')
        ->assertWait(2000);

    $page->assertPathIs('/admin')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 5: Login with Valid Penilai Credentials
|--------------------------------------------------------------------------
*/
test('login with valid penilai credentials redirects to penilai dashboard', function () {
    $page = visit('/login')
        ->fill('nip', '199702232022032013')
        ->fill('password', '199702232022032013')
        ->press('button[type="submit"]')
        ->assertWait(2000);

    $page->assertPathIs('/penilai')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 6: Login with Valid Peserta Credentials
|--------------------------------------------------------------------------
*/
test('login with valid peserta credentials redirects to peserta dashboard', function () {
    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->press('button[type="submit"]')
        ->assertWait(2000);

    $page->assertPathIs('/peserta')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 7: Logout Flow
|--------------------------------------------------------------------------
*/
test('logout redirects to login page', function () {
    $page = visit('/login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->press('button[type="submit"]')
        ->assertWait(2000);

    $page->assertPathIs('/admin');

    // Click logout dropdown button
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(500);

    // Click logout link
    $page->click('a[href="/logout"]')
        ->assertWait(2000);

    $page->assertPathIs('/')
        ->assertVisible('input[name="nip"]')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 8: Protected Routes Require Authentication
|--------------------------------------------------------------------------
*/
test('protected routes redirect unauthenticated users to login', function () {
    // Try accessing admin route
    $page = visit('/admin')
        ->assertWait(1000);

    $page->assertPathIs('/login')
        ->assertSee('Login')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    // Try accessing penilai voting route
    $page2 = visit('/penilai/voting')
        ->assertWait(1000);

    $page2->assertPathIs('/login')
        ->assertSee('Login')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 9: Cross-Role Access Prevention
|--------------------------------------------------------------------------
*/
test('users cannot access routes outside their role', function () {
    // Login as Penilai
    $page = visit('/login')
        ->fill('nip', '199702232022032013')
        ->fill('password', '199702232022032013')
        ->press('button[type="submit"]')
        ->assertWait(2000);

    $page->assertPathIs('/penilai');

    // Try to access admin route
    $page->navigate('/admin')
        ->assertWait(1000);

    // Should be denied or redirected
    $page->assertDontSee('Admin Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    // Logout
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(500)
        ->click('a[href="/logout"]')
        ->assertWait(2000);
});

/*
|--------------------------------------------------------------------------
| Test 10: Session Persistence
|--------------------------------------------------------------------------
*/
test('session persists across page refreshes', function () {
    // Login as admin
    $page = visit('/login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->press('button[type="submit"]')
        ->assertWait(2000);

    $page->assertPathIs('/admin')
        ->assertSee('Dashboard');

    // Refresh the page (navigate to same URL)
    $page->navigate('/admin')
        ->assertWait(1000);

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();

    // Logout
    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(500)
        ->click('a[href="/logout"]')
        ->assertWait(2000);

    // Verify session is cleared
    $page->navigate('/admin')
        ->assertWait(1000);

    $page->assertPathIs('/login');
});

/*
|--------------------------------------------------------------------------
| Test 11: Form Validation - Empty Fields
|--------------------------------------------------------------------------
*/
test('login form validates required fields', function () {
    $page = visit('/login')
        ->press('button[type="submit"]')
        ->assertWait(1000);

    $page->assertSee('required')
        ->assertPathIs('/login')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

/*
|--------------------------------------------------------------------------
| Test 12: All Dashboard Pages Load Without Errors
|--------------------------------------------------------------------------
*/
test('all dashboard pages load without errors for each role', function () {
    $roles = [
        ['nip' => '199107132020121003', 'password' => '199107132020121003', 'path' => '/super-admin', 'name' => 'SuperAdmin'],
        ['nip' => '199605112025212037', 'password' => '199605112025212037', 'path' => '/admin', 'name' => 'Admin'],
        ['nip' => '199702232022032013', 'password' => '199702232022032013', 'path' => '/penilai', 'name' => 'Penilai'],
        ['nip' => '199702012022031004', 'password' => '199702012022031004', 'path' => '/peserta', 'name' => 'Peserta'],
    ];

    foreach ($roles as $role) {
        $page = visit('/login')
            ->fill('nip', $role['nip'])
            ->fill('password', $role['password'])
            ->press('button[type="submit"]')
            ->assertWait(2000);

        $page->assertPathIs($role['path'])
            ->assertSee('Dashboard')
            ->assertNoJavascriptErrors()
            ->assertNoConsoleLogs();

        // Logout before next iteration
        $page->click('[data-dropdown-toggle="user-menu"]')
            ->assertWait(500)
            ->click('a[href="/logout"]')
            ->assertWait(2000);
    }
});
