<?php

use App\Models\Category;
use App\Models\Criterion;
use App\Models\User;
use App\Enums\Role;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin->value]);
});

it('can display criteria index page', function () {
    $category = Category::factory()->create();
    Criterion::factory()->count(3)->create(['category_id' => $category->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.criteria.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Criteria/Index')
            ->has('criteria')
            ->has('categories')
        );
});

it('can display create criterion page', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.criteria.create'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Criteria/Create')
            ->has('categories')
        );
});

it('can store a new criterion', function () {
    $category = Category::factory()->create();
    $criterionData = [
        'category_id' => $category->id,
        'nama' => 'Kriteria Baru',
        'deskripsi' => 'Deskripsi kriteria',
        'bobot' => 15.5,
        'urutan' => 1,
    ];

    $response = $this->actingAs($this->admin)->post(route('admin.criteria.store'), $criterionData);

    $response->assertRedirect(route('admin.criteria.index'))
        ->assertSessionHas('success', 'Kriteria berhasil dibuat');

    $this->assertDatabaseHas('criteria', [
        'nama' => 'Kriteria Baru',
        'bobot' => 15.5,
    ]);
});

it('can display show criterion page', function () {
    $criterion = Criterion::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.criteria.show', $criterion));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Criteria/Show')
            ->has('criterion')
        );
});

it('can display edit criterion page', function () {
    $criterion = Criterion::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.criteria.edit', $criterion));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Criteria/Edit')
            ->has('criterion')
            ->has('categories')
        );
});

it('can update a criterion', function () {
    $criterion = Criterion::factory()->create(['nama' => 'Nama Lama']);
    $updateData = [
        'category_id' => $criterion->category_id,
        'nama' => 'Nama Baru',
        'bobot' => 20.0,
        'urutan' => 2,
    ];

    $response = $this->actingAs($this->admin)->put(route('admin.criteria.update', $criterion), $updateData);

    $response->assertRedirect(route('admin.criteria.index'))
        ->assertSessionHas('success', 'Kriteria berhasil diperbarui');

    expect($criterion->refresh()->nama)->toBe('Nama Baru');
});

it('can delete a criterion', function () {
    $criterion = Criterion::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.criteria.destroy', $criterion));

    $response->assertRedirect(route('admin.criteria.index'))
        ->assertSessionHas('success', 'Kriteria berhasil dihapus');

    $this->assertDatabaseMissing('criteria', ['id' => $criterion->id]);
});

it('can update criterion weight', function () {
    $criterion = Criterion::factory()->create(['bobot' => 10.0]);

    $response = $this->actingAs($this->admin)->post(route('admin.criteria.update-weight', $criterion), [
        'bobot' => 25.5
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Bobot kriteria berhasil diperbarui');

    expect((float)$criterion->refresh()->bobot)->toBe(25.5);
});