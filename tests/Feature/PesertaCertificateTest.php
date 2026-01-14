<?php

use App\Models\Certificate;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Period;
use App\Models\User;

beforeEach(function () {
    Period::factory()->create([
        'status' => 'announced',
    ]);
});

it('can access certificates page', function () {
    $employee = Employee::factory()->create();
    $user = User::factory()->for($employee)->create(['role' => 'Peserta']);

    $response = $this->actingAs($user)->get('/peserta/sertifikat');

    $response->assertStatus(200);
});

it('can download certificate', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create();
    $user = User::factory()->for($employee)->create(['role' => 'Peserta']);
    $period = Period::factory()->create(['status' => 'announced']);
    $category = Category::factory()->create();

    Storage::put('certificates/test.pdf', 'fake pdf content');

    $certificate = Certificate::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => 'certificates/test.pdf',
    ]);

    $response = $this->actingAs($user)->get("/peserta/sertifikat/{$certificate->id}/download");

    $response->assertStatus(200);
    $response->assertHeader('content-disposition', 'attachment; filename=sertifikat-'.$certificate->certificate_id.'.pdf');
});

it('cannot download certificate belonging to another employee', function () {
    Storage::fake('public');

    $employee = Employee::factory()->create();
    $user = User::factory()->for($employee)->create(['role' => 'Peserta']);

    $otherEmployee = Employee::factory()->create();
    $period = Period::factory()->create(['status' => 'announced']);
    $category = Category::factory()->create();

    Storage::put('certificates/test.pdf', 'fake pdf content');

    $certificate = Certificate::factory()->create([
        'employee_id' => $otherEmployee->id,
        'period_id' => $period->id,
        'category_id' => $category->id,
        'pdf_path' => 'certificates/test.pdf',
    ]);

    $response = $this->actingAs($user)->get("/peserta/sertifikat/{$certificate->id}/download");

    $response->assertStatus(403);
});

it('can access peserta dashboard', function () {
    $employee = Employee::factory()->create();
    $user = User::factory()->for($employee)->create(['role' => 'Peserta']);

    $response = $this->actingAs($user)->get('/peserta');

    $response->assertStatus(200);
});
