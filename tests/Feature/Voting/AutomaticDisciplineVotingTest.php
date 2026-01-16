<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Services\DisciplineVoteService;

use function Pest\Laravel\mock;

uses()->group('voting');

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('does not re-generate discipline votes when already generated without overwrite', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create(['id' => 3]);
    $employee = Employee::factory()->create();

    Vote::factory()->create([
        'period_id' => $period->id,
        'category_id' => $category->id,
        'employee_id' => $employee->id,
        'voter_id' => $this->admin->id,
    ]);

    mock(DisciplineVoteService::class)
        ->shouldReceive('hasDisciplineVotes')
        ->once()
        ->with($period->id)
        ->andReturnTrue()
        ->shouldReceive('generateVotes')
        ->never();

    $response = $this->actingAs($this->admin)
        ->from("/penilai/voting/{$period->id}/{$category->id}")
        ->post(route('penilai.voting.generate-automatic', [$period->id, $category->id]));

    $response->assertRedirect()
        ->assertSessionHas('success', 'Voting otomatis sudah di-generate.');
});

it('generates discipline votes when not generated yet', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create(['id' => 3]);

    mock(DisciplineVoteService::class)
        ->shouldReceive('hasDisciplineVotes')
        ->once()
        ->with($period->id)
        ->andReturnFalse()
        ->shouldReceive('generateVotes')
        ->once()
        ->with($period->id, $this->admin->id, ['overwrite' => false])
        ->andReturn([
            'success' => 10,
            'failed' => 0,
            'errors' => [],
        ]);

    $response = $this->actingAs($this->admin)
        ->from("/penilai/voting/{$period->id}/{$category->id}")
        ->post(route('penilai.voting.generate-automatic', [$period->id, $category->id]));

    $response->assertRedirect()
        ->assertSessionHas('success', 'Voting otomatis berhasil dibuat untuk 10 pegawai.');
});

it('allows admins to overwrite discipline votes', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create(['id' => 3]);

    mock(DisciplineVoteService::class)
        ->shouldNotReceive('hasDisciplineVotes')
        ->shouldReceive('generateVotes')
        ->once()
        ->with($period->id, $this->admin->id, ['overwrite' => true])
        ->andReturn([
            'success' => 10,
            'failed' => 0,
            'errors' => [],
        ]);

    $response = $this->actingAs($this->admin)
        ->from("/penilai/voting/{$period->id}/{$category->id}")
        ->post(route('penilai.voting.generate-automatic', [$period->id, $category->id]), [
            'overwrite' => true,
        ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Voting otomatis berhasil dibuat untuk 10 pegawai.');
});
