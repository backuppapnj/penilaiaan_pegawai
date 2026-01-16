<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use Inertia\Testing\AssertableInertia as Assert;

uses()->group('voting');

it('locks discipline results for non-admin users until announced', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create(['id' => 3]);

    $voterEmployee = Employee::factory()->create();
    $penilai = User::factory()->penilai()->create([
        'employee_id' => $voterEmployee->id,
    ]);

    $employee = Employee::factory()->create();
    $employeeUser = User::factory()->peserta()->create([
        'employee_id' => $employee->id,
    ]);

    Vote::factory()->create([
        'period_id' => $period->id,
        'category_id' => $category->id,
        'employee_id' => $employee->id,
        'voter_id' => $employeeUser->id,
    ]);

    $this->actingAs($penilai)
        ->get("/penilai/voting/{$period->id}/{$category->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Penilai/Voting/Show')
            ->where('isAutomaticVoting', true)
            ->where('isResultsLocked', true)
        );
});

it('allows ketua/wakil/panitera to view discipline results before announced', function () {
    $period = Period::factory()->create(['status' => 'open']);
    $category = Category::factory()->create(['id' => 3]);

    $voterEmployee = Employee::factory()->create(['nip' => '198505092009041006']);
    $ketua = User::factory()->penilai()->create([
        'employee_id' => $voterEmployee->id,
        'nip' => '198505092009041006',
    ]);

    $employee = Employee::factory()->create();
    $employeeUser = User::factory()->peserta()->create([
        'employee_id' => $employee->id,
    ]);

    Vote::factory()->create([
        'period_id' => $period->id,
        'category_id' => $category->id,
        'employee_id' => $employee->id,
        'voter_id' => $employeeUser->id,
    ]);

    $this->actingAs($ketua)
        ->get("/penilai/voting/{$period->id}/{$category->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Penilai/Voting/Show')
            ->where('isAutomaticVoting', true)
            ->where('isResultsLocked', false)
            ->has('automaticVotes', 1)
        );
});
