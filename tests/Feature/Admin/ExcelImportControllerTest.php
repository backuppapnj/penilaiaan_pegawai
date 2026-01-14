<?php

use App\Models\Period;
use App\Models\User;
use App\Enums\Role;
use App\Models\DisciplineScore;
use App\Models\Employee;
use App\Services\SikepImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\mock;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin->value]);
});

it('can display sikep import index page', function () {
    Period::factory()->count(3)->create();
    $employee = Employee::factory()->create();
    DisciplineScore::factory()->create([
        'employee_id' => $employee->id,
        'period_id' => Period::first()->id
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.sikep.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/SikepImport/Index')
            ->has('periods')
            ->has('recentImports')
        );
});

it('can get discipline scores for a period', function () {
    $period = Period::factory()->create();
    DisciplineScore::factory()->count(5)->create([
        'period_id' => $period->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.sikep.scores', [
        'period_id' => $period->id
    ]));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id', 'rank', 'employee', 'period', 'scores', 'attendance', 'created_at'
                ]
            ]
        ]);
});

it('can delete a discipline score', function () {
    $score = DisciplineScore::factory()->create();

    $response = $this->actingAs($this->admin)->delete(route('admin.sikep.destroy', $score->id));

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('discipline_scores', ['id' => $score->id]);
});

it('can store uploaded excel file and process it', function () {
    Storage::fake('local');
    $period = Period::factory()->create();
    $file = UploadedFile::fake()->create('sikep.xlsx', 100);

    mock(SikepImportService::class)
        ->shouldReceive('import')
        ->once()
        ->andReturn([
            'success' => 10,
            'failed' => 0,
            'errors' => []
        ]);

    $response = $this->actingAs($this->admin)->post(route('admin.sikep.store'), [
        'period_id' => $period->id,
        'excel_file' => $file
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
    
    Storage::disk('local')->assertExists('sikep-imports');
});

it('handles import service exceptions', function () {
    Storage::fake('local');
    $period = Period::factory()->create();
    $file = UploadedFile::fake()->create('sikep.xlsx', 100);

    mock(SikepImportService::class)
        ->shouldReceive('import')
        ->once()
        ->andThrow(new \Exception('Service Error'));

    $response = $this->actingAs($this->admin)->post(route('admin.sikep.store'), [
        'period_id' => $period->id,
        'excel_file' => $file
    ]);

    $response->assertStatus(500)
        ->assertJson(['success' => false, 'message' => 'Import gagal: Service Error']);
});