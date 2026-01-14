<?php

use App\Models\Category;
use App\Models\Employee;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Category::factory()->create(['nama' => 'Pejabat Struktural/Fungsional']);
    Category::factory()->create(['nama' => 'Non-Pejabat']);
    Category::factory()->create(['nama' => 'Pegawai Disiplin']);
    
    $this->jsonPath = base_path('tests/data_pegawai_test.json');
    $this->orgPath = base_path('tests/org_structure_test.json');
    
    $employeeData = [
        [
            'nip' => '123456',
            'nama' => 'Pegawai 1',
            'jabatan' => 'Ketua',
            'unit_kerja' => 'Pimpinan',
            'gol' => 'IV/e',
            'tmt' => '13 Mei 2024'
        ],
        [
            'nip' => '789012',
            'nama' => 'Pegawai 2',
            'jabatan' => 'Staff',
            'unit_kerja' => 'Umum',
            'gol' => 'III/a',
            'tmt' => '01 Januari 2020'
        ]
    ];
    
    $orgStructure = [
        'pimpinan' => [['nip' => '123456']],
        'yudisial' => [],
        'panitera' => [
            'panitera' => ['nip' => '999999'],
            'panitera_pengganti' => [],
            'panitera_muda' => []
        ],
        'sekretariat' => [
            'sekretaris' => ['nip' => '888888'],
            'subbagian' => []
        ]
    ];
    
    file_put_contents($this->jsonPath, json_encode($employeeData));
    file_put_contents($this->orgPath, json_encode($orgStructure));
});

afterEach(function () {
    if (file_exists($this->jsonPath)) unlink($this->jsonPath);
    if (file_exists($this->orgPath)) unlink($this->orgPath);
});

it('can import employees from json', function () {
    $this->artisan('employees:import', [
        '--json-path' => 'tests/data_pegawai_test.json',
        '--org-path' => 'tests/org_structure_test.json'
    ])
    ->assertExitCode(0);
    
    $this->assertDatabaseHas('employees', ['nip' => '123456', 'nama' => 'Pegawai 1']);
    $this->assertDatabaseHas('employees', ['nip' => '789012', 'nama' => 'Pegawai 2']);
});

it('can truncate table before import', function () {
    Employee::factory()->create(['nip' => 'old-nip']);
    
    $this->artisan('employees:import', [
        '--json-path' => 'tests/data_pegawai_test.json',
        '--org-path' => 'tests/org_structure_test.json',
        '--truncate' => true
    ])
    ->assertExitCode(0);
    
    $this->assertDatabaseMissing('employees', ['nip' => 'old-nip']);
    expect(Employee::count())->toBe(2);
});

it('fails if files not found', function () {
    $this->artisan('employees:import', [
        '--json-path' => 'non-existent.json',
        '--org-path' => 'non-existent.json'
    ])
    ->assertExitCode(1);
});

it('fails if json is invalid', function () {
    file_put_contents($this->jsonPath, 'invalid json');
    
    $this->artisan('employees:import', [
        '--json-path' => 'tests/data_pegawai_test.json',
        '--org-path' => 'tests/org_structure_test.json'
    ])
    ->assertExitCode(1);
});

it('fails if categories are missing', function () {
    Category::query()->delete();
    
    $this->artisan('employees:import', [
        '--json-path' => 'tests/data_pegawai_test.json',
        '--org-path' => 'tests/org_structure_test.json'
    ])
    ->assertExitCode(1);
});