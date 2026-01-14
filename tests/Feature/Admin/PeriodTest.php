<?php

use App\Enums\Role;
use App\Models\Period;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin->value]);
    $this->penilai = User::factory()->create(['role' => Role::Penilai->value]);
});

it('allows admin to view periods index', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.periods.index'))
        ->assertOk();
});

it('displays periods on index page', function () {
    Period::factory()->create([
        'name' => 'Periode Test 2026',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.periods.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Periods/Index')
            ->has('periods')
        );
});

it('allows admin to view create period page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.periods.create'))
        ->assertSuccessful();
})->skip('Frontend pages not implemented - Create resources/js/Pages/Admin/Periods/Create.tsx');

it('allows admin to create a period', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Penilaian Test 2026',
            'semester' => 'ganjil',
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'draft',
        ])
        ->assertRedirect(route('admin.periods.index'))
        ->assertSessionHas('success', 'Periode berhasil dibuat');

    $this->assertDatabaseHas('periods', [
        'name' => 'Penilaian Test 2026',
        'semester' => 'ganjil',
        'year' => 2026,
        'status' => 'draft',
    ]);
});

it('validates name is required when creating period', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => '',
            'semester' => 'ganjil',
            'year' => 2026,
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates semester is required and must be valid', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Test Period',
            'semester' => 'invalid',
            'year' => 2026,
        ])
        ->assertSessionHasErrors(['semester']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Test Period',
            'semester' => '',
            'year' => 2026,
        ])
        ->assertSessionHasErrors(['semester']);
});

it('validates year is required and within valid range', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Test Period',
            'semester' => 'ganjil',
            'year' => 2019,
        ])
        ->assertSessionHasErrors(['year']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Test Period',
            'semester' => 'ganjil',
            'year' => 2101,
        ])
        ->assertSessionHasErrors(['year']);
});

it('validates end_date is after start_date', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Invalid Period',
            'semester' => 'ganjil',
            'year' => 2026,
            'start_date' => '2026-12-31',
            'end_date' => '2026-01-01',
        ])
        ->assertSessionHasErrors(['end_date']);
});

it('validates end_date must be after start_date when start_date is provided', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Invalid Period',
            'semester' => 'ganjil',
            'year' => 2026,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-01',
        ])
        ->assertSessionHasErrors(['end_date']);
});

it('allows creating period with null dates', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Period Without Dates',
            'semester' => 'ganjil',
            'year' => 2026,
            'start_date' => null,
            'end_date' => null,
        ])
        ->assertRedirect(route('admin.periods.index'));

    $this->assertDatabaseHas('periods', [
        'name' => 'Period Without Dates',
        'start_date' => null,
        'end_date' => null,
    ]);
});

it('validates status must be valid when provided', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.periods.store'), [
            'name' => 'Test Period',
            'semester' => 'ganjil',
            'year' => 2026,
            'status' => 'invalid_status',
        ])
        ->assertSessionHasErrors(['status']);
});

it('allows admin to view period detail', function () {
    $period = Period::factory()->create([
        'name' => 'Periode Detail Test',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.periods.show', $period))
        ->assertSuccessful();
})->skip('Frontend pages not implemented - Create resources/js/Pages/Admin/Periods/Show.tsx');

it('allows admin to view edit period page', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->get(route('admin.periods.edit', $period))
        ->assertSuccessful();
})->skip('Frontend pages not implemented - Create resources/js/Pages/Admin/Periods/Edit.tsx');

it('allows admin to edit a period', function () {
    $period = Period::factory()->create([
        'name' => 'Original Name',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.periods.update', $period), [
            'name' => 'Updated Name',
            'semester' => 'genap',
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ])
        ->assertRedirect(route('admin.periods.index'))
        ->assertSessionHas('success', 'Periode berhasil diperbarui');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'name' => 'Updated Name',
        'semester' => 'genap',
    ]);
});

it('allows admin to update only some fields', function () {
    $period = Period::factory()->create([
        'name' => 'Original Name',
        'semester' => 'ganjil',
        'year' => 2025,
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.periods.update', $period), [
            'name' => 'Updated Name Only',
        ])
        ->assertRedirect(route('admin.periods.index'));

    $period->refresh();
    expect($period->name)->toBe('Updated Name Only');
    expect($period->semester)->toBe('ganjil');
    expect($period->year)->toBe(2025);
});

it('validates end_date on update', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->put(route('admin.periods.update', $period), [
            'name' => 'Updated Period',
            'semester' => 'ganjil',
            'year' => 2026,
            'start_date' => '2026-12-31',
            'end_date' => '2026-01-01',
        ])
        ->assertSessionHasErrors(['end_date']);
});

it('allows admin to delete a draft period', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->delete(route('admin.periods.destroy', $period))
        ->assertRedirect(route('admin.periods.index'))
        ->assertSessionHas('success', 'Periode berhasil dihapus');

    $this->assertDatabaseMissing('periods', [
        'id' => $period->id,
    ]);
});

it('prevents admin from deleting an open period', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin)
        ->delete(route('admin.periods.destroy', $period))
        ->assertRedirect()
        ->assertSessionHas('error', 'Tidak dapat menghapus periode yang sedang berlangsung');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
    ]);
});

it('allows admin to delete a closed period', function () {
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($this->admin)
        ->delete(route('admin.periods.destroy', $period))
        ->assertRedirect(route('admin.periods.index'))
        ->assertSessionHas('success', 'Periode berhasil dihapus');

    $this->assertDatabaseMissing('periods', [
        'id' => $period->id,
    ]);
});

it('allows admin to delete an announced period', function () {
    $period = Period::factory()->create(['status' => 'announced']);

    $this->actingAs($this->admin)
        ->delete(route('admin.periods.destroy', $period))
        ->assertRedirect(route('admin.periods.index'))
        ->assertSessionHas('success', 'Periode berhasil dihapus');

    $this->assertDatabaseMissing('periods', [
        'id' => $period->id,
    ]);
});

it('redirects non-admin users from periods index', function () {
    $this->actingAs($this->penilai)
        ->get(route('admin.periods.index'))
        ->assertStatus(403);
});

it('redirects non-admin users from creating periods', function () {
    $this->actingAs($this->penilai)
        ->get(route('admin.periods.create'))
        ->assertStatus(403);
});

it('redirects non-admin users from storing periods', function () {
    $this->actingAs($this->penilai)
        ->post(route('admin.periods.store'), [
            'name' => 'Unauthorized Period',
            'semester' => 'ganjil',
            'year' => 2026,
        ])
        ->assertStatus(403);
});

it('redirects non-admin users from viewing period details', function () {
    $period = Period::factory()->create();

    $this->actingAs($this->penilai)
        ->get(route('admin.periods.show', $period))
        ->assertStatus(403);
});

it('redirects non-admin users from editing periods', function () {
    $period = Period::factory()->create();

    $this->actingAs($this->penilai)
        ->get(route('admin.periods.edit', $period))
        ->assertStatus(403);
});

it('redirects non-admin users from updating periods', function () {
    $period = Period::factory()->create();

    $this->actingAs($this->penilai)
        ->put(route('admin.periods.update', $period), [
            'name' => 'Unauthorized Update',
        ])
        ->assertStatus(403);
});

it('redirects non-admin users from deleting periods', function () {
    $period = Period::factory()->create();

    $this->actingAs($this->penilai)
        ->delete(route('admin.periods.destroy', $period))
        ->assertStatus(403);
});

it('redirects guests from periods index', function () {
    $this->get(route('admin.periods.index'))
        ->assertRedirect(route('login'));
});

it('redirects guests from creating periods', function () {
    $this->get(route('admin.periods.create'))
        ->assertRedirect(route('login'));
});

it('orders periods by year and semester descending', function () {
    Period::factory()->create([
        'year' => 2025,
        'semester' => 'ganjil',
        'name' => '2025 Ganjil',
    ]);

    Period::factory()->create([
        'year' => 2026,
        'semester' => 'genap',
        'name' => '2026 Genap',
    ]);

    Period::factory()->create([
        'year' => 2026,
        'semester' => 'ganjil',
        'name' => '2026 Ganjil',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.periods.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Periods/Index')
            ->has('periods', 3)
            ->where('periods.0.name', '2026 Genap')
            ->where('periods.1.name', '2026 Ganjil')
            ->where('periods.2.name', '2025 Ganjil')
        );
});
