<?php

use App\Models\Category;
use App\Models\Employee;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin->value]);
});

it('can display employee index page', function () {
    Category::factory()->create(['nama' => 'Pejabat Struktural/Fungsional']);
    Category::factory()->create(['nama' => 'Non-Pejabat']);
    Employee::factory()->count(5)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Employees/Index')
            ->has('employees')
            ->has('categories')
            ->has('stats')
        );
});

it('can filter employees by category', function () {
    $category = Category::factory()->create();
    $employee = Employee::factory()->create(['category_id' => $category->id]);
    $otherEmployee = Employee::factory()->create();

    $response = $this->actingAs($this->admin)->get(route('admin.employees.index', [
        'category' => $category->id
    ]));

    $response->assertStatus(200);
    $employees = $response->viewData('page')['props']['employees']['data'];
    expect(count($employees))->toBe(1);
    expect($employees[0]['id'])->toBe($employee->id);
});

it('can search employees by name', function () {
    $employee = Employee::factory()->create(['nama' => 'Budi Sudarsono']);
    $otherEmployee = Employee::factory()->create(['nama' => 'Andi Gol']);

    $response = $this->actingAs($this->admin)->get(route('admin.employees.index', [
        'search' => 'Budi'
    ]));

    $response->assertStatus(200);
    $employees = $response->viewData('page')['props']['employees']['data'];
    expect(count($employees))->toBe(1);
    expect($employees[0]['nama'])->toBe('Budi Sudarsono');
});

it('can search employees by nip', function () {
    $employee = Employee::factory()->create(['nip' => '1234567890']);
    $otherEmployee = Employee::factory()->create(['nip' => '0987654321']);

    $response = $this->actingAs($this->admin)->get(route('admin.employees.index', [
        'search' => '123456'
    ]));

    $response->assertStatus(200);
    $employees = $response->viewData('page')['props']['employees']['data'];
    expect(count($employees))->toBe(1);
    expect($employees[0]['nip'])->toBe('1234567890');
});

it('can get employee stats as json', function () {
    $k1 = Category::factory()->create(['nama' => 'Pejabat Struktural/Fungsional']);
    $k2 = Category::factory()->create(['nama' => 'Non-Pejabat']);
    Employee::factory()->count(3)->create(['category_id' => $k1->id]);
    Employee::factory()->count(2)->create(['category_id' => $k2->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.employees.stats'));

    $response->assertStatus(200)
        ->assertJson([
            'total' => 5,
            'category_1' => 3,
            'category_2' => 2,
            'category_3' => 23,
        ]);
});

it('can trigger employee import', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('employees:import', \Mockery::type('array'))
        ->andReturn(0);

    Artisan::shouldReceive('output')
        ->andReturn('Success');

    $response = $this->actingAs($this->admin)->post(route('admin.employees.import'), [
        'truncate' => true
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success', 'Employees imported successfully.');
});

it('handles import failure', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('employees:import', \Mockery::type('array'))
        ->andReturn(1);

    Artisan::shouldReceive('output')
        ->andReturn('Failed message');

    $response = $this->actingAs($this->admin)->post(route('admin.employees.import'));

    $response->assertRedirect()
        ->assertSessionHas('error', 'Import failed: Failed message');
});