<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\UserSeeder::class);
});

test('super admin is not forbidden from super admin dashboard', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $response = $this->actingAs($user)->get('/super-admin');

    $response->assertSuccessful();
});

test('super admin is not forbidden from admin dashboard', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful();
});

test('super admin is not forbidden from penilai dashboard', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $response = $this->actingAs($user)->get('/penilai');

    $response->assertSuccessful();
});

test('super admin is not forbidden from peserta dashboard', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $response = $this->actingAs($user)->get('/peserta');

    $response->assertSuccessful();
});

test('admin is not forbidden from admin dashboard', function () {
    $user = User::where('nip', '199605112025212037')->first();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful();
});

test('admin cannot access super admin dashboard', function () {
    $user = User::where('nip', '199605112025212037')->first();

    $response = $this->actingAs($user)->get('/super-admin');

    $response->assertForbidden();
});

test('admin is not forbidden from penilai dashboard', function () {
    $user = User::where('nip', '199605112025212037')->first();

    $response = $this->actingAs($user)->get('/penilai');

    $response->assertSuccessful();
});

test('admin is not forbidden from peserta dashboard', function () {
    $user = User::where('nip', '199605112025212037')->first();

    $response = $this->actingAs($user)->get('/peserta');

    $response->assertSuccessful();
});

test('penilai is not forbidden from penilai dashboard', function () {
    $user = User::where('nip', '199702232022032013')->first();

    $response = $this->actingAs($user)->get('/penilai');

    $response->assertSuccessful();
});

test('penilai cannot access super admin dashboard', function () {
    $user = User::where('nip', '199702232022032013')->first();

    $response = $this->actingAs($user)->get('/super-admin');

    $response->assertForbidden();
});

test('penilai cannot access admin dashboard', function () {
    $user = User::where('nip', '199702232022032013')->first();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('penilai is not forbidden from peserta dashboard', function () {
    $user = User::where('nip', '199702232022032013')->first();

    $response = $this->actingAs($user)->get('/peserta');

    $response->assertSuccessful();
});

test('peserta is not forbidden from peserta dashboard', function () {
    $user = User::where('nip', '199702012022031004')->first();

    $response = $this->actingAs($user)->get('/peserta');

    $response->assertSuccessful();
});

test('peserta cannot access super admin dashboard', function () {
    $user = User::where('nip', '199702012022031004')->first();

    $response = $this->actingAs($user)->get('/super-admin');

    $response->assertForbidden();
});

test('peserta cannot access admin dashboard', function () {
    $user = User::where('nip', '199702012022031004')->first();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('peserta cannot access penilai dashboard', function () {
    $user = User::where('nip', '199702012022031004')->first();

    $response = $this->actingAs($user)->get('/penilai');

    $response->assertForbidden();
});

test('admin routes forbid access for penilai', function () {
    $adminRoutes = [
        '/admin/periods',
        '/admin/criteria',
        '/admin/employees',
        '/admin/sikep',
    ];

    $penilai = User::where('nip', '199702232022032013')->first();

    foreach ($adminRoutes as $route) {
        $this->actingAs($penilai)->get($route)->assertForbidden();
    }
});

test('admin routes forbid access for peserta', function () {
    $adminRoutes = [
        '/admin/periods',
        '/admin/criteria',
        '/admin/employees',
        '/admin/sikep',
    ];

    $peserta = User::where('nip', '199702012022031004')->first();

    foreach ($adminRoutes as $route) {
        $this->actingAs($peserta)->get($route)->assertForbidden();
    }
});

test('super admin is not forbidden from admin routes', function () {
    $user = User::where('nip', '199107132020121003')->first();

    $adminRoutes = [
        '/admin/periods',
        '/admin/criteria',
    ];

    foreach ($adminRoutes as $route) {
        $this->actingAs($user)->get($route)->assertSuccessful();
    }
});

test('admin is not forbidden from admin routes', function () {
    $user = User::where('nip', '199605112025212037')->first();

    $adminRoutes = [
        '/admin/periods',
        '/admin/criteria',
    ];

    foreach ($adminRoutes as $route) {
        $this->actingAs($user)->get($route)->assertSuccessful();
    }
});

test('voting routes are accessible to penilai', function () {
    $user = User::where('nip', '199702232022032013')->first();

    $response = $this->actingAs($user)->get('/penilai/voting');

    $response->assertSuccessful();
});

test('voting routes are accessible to peserta', function () {
    $user = User::where('nip', '199702012022031004')->first();

    $response = $this->actingAs($user)->get('/penilai/voting');

    $response->assertSuccessful();
});

test('peserta certificates route redirects peserta without employee', function () {
    $user = User::where('nip', '199702012022031004')->first();

    $response = $this->actingAs($user)->get('/peserta/sertifikat');

    $response->assertStatus(302);
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
    ];

    foreach ($protectedRoutes as $route) {
        $response = $this->get($route);
        $response->assertRedirect('/login');
    }
});

test('user has role method works correctly', function () {
    $superAdmin = User::where('nip', '199107132020121003')->first();
    $admin = User::where('nip', '199605112025212037')->first();
    $penilai = User::where('nip', '199702232022032013')->first();
    $peserta = User::where('nip', '199702012022031004')->first();

    expect($superAdmin->hasRole('SuperAdmin'))->toBeTrue();
    expect($superAdmin->hasRole('Admin'))->toBeFalse();

    expect($admin->hasRole('Admin'))->toBeTrue();
    expect($admin->hasRole('SuperAdmin'))->toBeFalse();

    expect($penilai->hasRole('Penilai'))->toBeTrue();
    expect($penilai->hasRole('Admin'))->toBeFalse();

    expect($peserta->hasRole('Peserta'))->toBeTrue();
    expect($peserta->hasRole('Penilai'))->toBeFalse();
});

test('user has role can accept multiple roles', function () {
    $superAdmin = User::where('nip', '199107132020121003')->first();
    $admin = User::where('nip', '199605112025212037')->first();

    expect($superAdmin->hasRole('SuperAdmin', 'Admin'))->toBeTrue();
    expect($admin->hasRole('SuperAdmin', 'Admin'))->toBeTrue();
});

test('user is super admin method works correctly', function () {
    $superAdmin = User::where('nip', '199107132020121003')->first();
    $admin = User::where('nip', '199605112025212037')->first();

    expect($superAdmin->isSuperAdmin())->toBeTrue();
    expect($admin->isSuperAdmin())->toBeFalse();
});

test('user is admin method works correctly', function () {
    $admin = User::where('nip', '199605112025212037')->first();
    $penilai = User::where('nip', '199702232022032013')->first();

    expect($admin->isAdmin())->toBeTrue();
    expect($penilai->isAdmin())->toBeFalse();
});

test('user is administrator method works correctly for both admin and super admin', function () {
    $superAdmin = User::where('nip', '199107132020121003')->first();
    $admin = User::where('nip', '199605112025212037')->first();
    $penilai = User::where('nip', '199702232022032013')->first();

    expect($superAdmin->isAdministrator())->toBeTrue();
    expect($admin->isAdministrator())->toBeTrue();
    expect($penilai->isAdministrator())->toBeFalse();
});

test('user can vote method returns true for all roles', function () {
    $superAdmin = User::where('nip', '199107132020121003')->first();
    $admin = User::where('nip', '199605112025212037')->first();
    $penilai = User::where('nip', '199702232022032013')->first();
    $peserta = User::where('nip', '199702012022031004')->first();

    expect($superAdmin->canVote())->toBeTrue();
    expect($admin->canVote())->toBeTrue();
    expect($penilai->canVote())->toBeTrue();
    expect($peserta->canVote())->toBeTrue();
});

test('user can participate method returns true only for penilai and peserta', function () {
    $admin = User::where('nip', '199605112025212037')->first();
    $penilai = User::where('nip', '199702232022032013')->first();
    $peserta = User::where('nip', '199702012022031004')->first();

    expect($admin->canParticipate())->toBeFalse();
    expect($penilai->canParticipate())->toBeTrue();
    expect($peserta->canParticipate())->toBeTrue();
});
