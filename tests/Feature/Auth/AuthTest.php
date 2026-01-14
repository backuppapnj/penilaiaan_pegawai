<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\UserSeeder::class);
});

test('login route exists', function () {
    expect(route('login', absolute: false))->toBe('/login');
    expect(route('login.store', absolute: false))->toBe('/login');
});

test('super admin can login with correct credentials and is redirected to correct dashboard', function () {
    $user = User::where('nip', '199107132020121003')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('SuperAdmin');

    $response = $this->post('/login', [
        'nip' => '199107132020121003',
        'password' => '199107132020121003',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard-redirect');
});

test('admin can login with correct credentials and is redirected to correct dashboard', function () {
    $user = User::where('nip', '199605112025212037')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('Admin');

    $response = $this->post('/login', [
        'nip' => '199605112025212037',
        'password' => '199605112025212037',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard-redirect');
});

test('penilai can login with correct credentials and is redirected to correct dashboard', function () {
    $user = User::where('nip', '199702232022032013')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('Penilai');

    $response = $this->post('/login', [
        'nip' => '199702232022032013',
        'password' => '199702232022032013',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard-redirect');
});

test('peserta can login with correct credentials and is redirected to correct dashboard', function () {
    $user = User::where('nip', '199702012022031004')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('Peserta');

    $response = $this->post('/login', [
        'nip' => '199702012022031004',
        'password' => '199702012022031004',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard-redirect');
});

test('users cannot authenticate with invalid nip', function () {
    $response = $this->post('/login', [
        'nip' => '999999999999999999',
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

test('users cannot authenticate with invalid password', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $response = $this->post('/login', [
        'nip' => '199107132020121003',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

test('users cannot authenticate with empty credentials', function () {
    $response = $this->post('/login', [
        'nip' => '',
        'password' => '',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['nip', 'password']);
});

test('users can logout', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('unauthenticated users are redirected to login when accessing protected routes', function () {
    $protectedRoutes = [
        '/super-admin',
        '/admin',
        '/penilai',
        '/peserta',
        '/admin/periods',
        '/admin/criteria',
        '/admin/employees',
        '/penilai/voting',
        '/peserta/sertifikat',
    ];

    foreach ($protectedRoutes as $route) {
        $response = $this->get($route);
        $response->assertRedirect('/login');
    }
});

test('authenticated super admin is redirected to their dashboard after login', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $this->actingAs($user)
        ->get('/dashboard-redirect')
        ->assertRedirect('/super-admin');
});

test('authenticated admin is redirected to their dashboard after login', function () {
    $user = User::where('nip', '199605112025212037')->first();

    $this->actingAs($user)
        ->get('/dashboard-redirect')
        ->assertRedirect('/admin');
});

test('authenticated penilai is redirected to their dashboard after login', function () {
    $user = User::where('nip', '199702232022032013')->first();

    $this->actingAs($user)
        ->get('/dashboard-redirect')
        ->assertRedirect('/penilai');
});

test('authenticated peserta is redirected to their dashboard after login', function () {
    $user = User::where('nip', '199702012022031004')->first();

    $this->actingAs($user)
        ->get('/dashboard-redirect')
        ->assertRedirect('/peserta');
});
