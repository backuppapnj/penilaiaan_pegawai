<?php

use App\Enums\Role;
use App\Models\Period;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin->value]);
    $this->penilai = User::factory()->create(['role' => Role::Penilai->value]);
});

it('allows admin to update period status to open', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'open']))
        ->assertRedirect()
        ->assertSessionHas('success', 'Periode dibuka untuk voting');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'open',
    ]);
});

it('allows admin to update period status to closed', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'closed']))
        ->assertRedirect()
        ->assertSessionHas('success', 'Periode ditutup');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'closed',
    ]);
});

it('allows admin to update period status to announced', function () {
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'announced']))
        ->assertRedirect()
        ->assertSessionHas('success', 'Hasil periode diumumkan');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'announced',
    ]);
});

it('allows admin to revert period status to draft', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'draft']))
        ->assertRedirect()
        ->assertSessionHas('success', 'Periode dikembalikan ke draft');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'draft',
    ]);
});

it('rejects invalid status values', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'invalid_status']))
        ->assertRedirect()
        ->assertSessionHas('error', 'Status tidak valid');

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'draft',
    ]);
});

it('allows status transition from draft to open', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'open']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('open');
});

it('allows status transition from open to closed', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'closed']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('closed');
});

it('allows status transition from closed to announced', function () {
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'announced']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('announced');
});

it('allows status transition from announced to closed', function () {
    $period = Period::factory()->create(['status' => 'announced']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'closed']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('closed');
});

it('allows status transition from closed to open', function () {
    $period = Period::factory()->create(['status' => 'closed']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'open']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('open');
});

it('allows status transition from open to draft', function () {
    $period = Period::factory()->create(['status' => 'open']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'draft']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('draft');
});

it('allows setting same status without error', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'draft']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('draft');
});

it('allows status transition from draft to closed', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'closed']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('closed');
});

it('allows status transition from draft to announced', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, 'announced']))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('announced');
});

it('prevents non-admin users from updating period status', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->penilai)
        ->post(route('admin.periods.update-status', [$period, 'open']))
        ->assertStatus(403);

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'draft',
    ]);
});

it('prevents guests from updating period status', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->post(route('admin.periods.update-status', [$period, 'open']))
        ->assertRedirect(route('login'));

    $this->assertDatabaseHas('periods', [
        'id' => $period->id,
        'status' => 'draft',
    ]);
});

it('redirects back to period detail after status update', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->from(route('admin.periods.show', $period))
        ->post(route('admin.periods.update-status', [$period, 'open']))
        ->assertRedirect(route('admin.periods.show', $period));
});

it('updates status for all valid status values', function (string $status) {
    $period = Period::factory()->create(['status' => 'draft']);

    $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, $status]))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe($status);
})->with(['draft', 'open', 'closed', 'announced']);

it('ensures period model has isOpen helper method', function () {
    $period = Period::factory()->create(['status' => 'open']);

    expect($period->isOpen())->toBeTrue();
    expect($period->isClosed())->toBeFalse();
    expect($period->isDraft())->toBeFalse();
    expect($period->isAnnounced())->toBeFalse();
});

it('ensures period model has isClosed helper method', function () {
    $period = Period::factory()->create(['status' => 'closed']);

    expect($period->isClosed())->toBeTrue();
    expect($period->isOpen())->toBeFalse();
    expect($period->isDraft())->toBeFalse();
    expect($period->isAnnounced())->toBeFalse();
});

it('ensures period model has isDraft helper method', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    expect($period->isDraft())->toBeTrue();
    expect($period->isOpen())->toBeFalse();
    expect($period->isClosed())->toBeFalse();
    expect($period->isAnnounced())->toBeFalse();
});

it('ensures period model has isAnnounced helper method', function () {
    $period = Period::factory()->create(['status' => 'announced']);

    expect($period->isAnnounced())->toBeTrue();
    expect($period->isOpen())->toBeFalse();
    expect($period->isClosed())->toBeFalse();
    expect($period->isDraft())->toBeFalse();
});

it('correctly identifies draft periods', function () {
    $period = Period::factory()->create(['status' => 'draft']);

    expect($period->isDraft())->toBeTrue();
    expect($period->status)->toBe('draft');
});

it('correctly identifies open periods', function () {
    $period = Period::factory()->create(['status' => 'open']);

    expect($period->isOpen())->toBeTrue();
    expect($period->status)->toBe('open');
});

it('correctly identifies closed periods', function () {
    $period = Period::factory()->create(['status' => 'closed']);

    expect($period->isClosed())->toBeTrue();
    expect($period->status)->toBe('closed');
});

it('correctly identifies announced periods', function () {
    $period = Period::factory()->create(['status' => 'announced']);

    expect($period->isAnnounced())->toBeTrue();
    expect($period->status)->toBe('announced');
});

it('returns proper success message for each status', function (string $status, string $expectedMessage) {
    $period = Period::factory()->create(['status' => 'draft']);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.periods.update-status', [$period, $status]));

    $response->assertSessionHas('success', $expectedMessage);
})->with([
    ['open', 'Periode dibuka untuk voting'],
    ['closed', 'Periode ditutup'],
    ['announced', 'Hasil periode diumumkan'],
    ['draft', 'Periode dikembalikan ke draft'],
]);
