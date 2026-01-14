<?php

use App\Models\User;

test('complete login flow for super admin', function () {
    $user = User::where('nip', '199107132020121003')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->assertSee('Login')
        ->fill('nip', '199107132020121003')
        ->fill('password', '199107132020121003')
        ->click('button[type="submit"]')
        ->assertPathIs('/super-admin')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete login flow for admin', function () {
    $user = User::where('nip', '199605112025212037')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->assertSee('Login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertPathIs('/admin')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete login flow for penilai', function () {
    $user = User::where('nip', '199702232022032013')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->assertSee('Login')
        ->fill('nip', '199702232022032013')
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertPathIs('/penilai')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('complete login flow for peserta', function () {
    $user = User::where('nip', '199702012022031004')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->assertSee('Login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertPathIs('/peserta')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('login validation shows errors when submitting empty form', function () {
    $page = visit('/login')
        ->assertSee('Login')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertSee('The nip field is required.')
        ->assertSee('The password field is required.')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('login validation shows error for invalid credentials', function () {
    $page = visit('/login')
        ->assertSee('Login')
        ->fill('nip', '999999999999999999')
        ->fill('password', 'wrongpassword')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertSee('These credentials do not match our records.')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('login validation shows error for invalid password', function () {
    $page = visit('/login')
        ->assertSee('Login')
        ->fill('nip', '199107132020121003')
        ->fill('password', 'wrongpassword')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertSee('These credentials do not match our records.')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('logout flow works correctly for super admin', function () {
    $user = User::where('nip', '199107132020121003')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->fill('nip', '199107132020121003')
        ->fill('password', '199107132020121003')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertPathIs('/super-admin');

    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(500)
        ->assertPathIs('/')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('logout flow works correctly for admin', function () {
    $user = User::where('nip', '199605112025212037')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertPathIs('/admin');

    $page->click('[data-dropdown-toggle="user-menu"]')
        ->assertWait(200)
        ->click('a[href="/logout"]')
        ->assertWait(500)
        ->assertPathIs('/')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('unauthenticated user visiting protected route is redirected to login', function () {
    $page = visit('/admin')
        ->assertWait(500)
        ->assertPathIs('/login')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('login page has no javascript or console errors', function () {
    $page = visit('/login')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('dashboard pages have no javascript or console errors for super admin', function () {
    $user = User::where('nip', '199107132020121003')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->fill('nip', '199107132020121003')
        ->fill('password', '199107132020121003')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertPathIs('/super-admin')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('dashboard pages have no javascript or console errors for admin', function () {
    $user = User::where('nip', '199605112025212037')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->fill('nip', '199605112025212037')
        ->fill('password', '199605112025212037')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertPathIs('/admin')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('dashboard pages have no javascript or console errors for penilai', function () {
    $user = User::where('nip', '199702232022032013')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->fill('nip', '199702232022032013')
        ->fill('password', '199702232022032013')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertPathIs('/penilai')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

test('dashboard pages have no javascript or console errors for peserta', function () {
    $user = User::where('nip', '199702012022031004')->first();

    expect($user)->not->toBeNull();

    $page = visit('/login')
        ->fill('nip', '199702012022031004')
        ->fill('password', '199702012022031004')
        ->click('button[type="submit"]')
        ->assertWait(500)
        ->assertPathIs('/peserta')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});
