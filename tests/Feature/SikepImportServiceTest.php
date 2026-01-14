<?php

use App\Models\Period;
use App\Models\Employee;
use App\Models\DisciplineScore;
use App\Services\SikepImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function () {
    $this->service = new SikepImportService();
});

it('can import SIKEP data from excel file', function () {
    $period = Period::factory()->create(['year' => 2025, 'semester' => 'ganjil']);
    
    // Create a temporary excel file
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Row 11: Penalty weights
    $sheet->setCellValue('G11', 5);
    $sheet->setCellValue('H11', 10);
    $sheet->setCellValue('I11', 20);
    $sheet->setCellValue('J11', 30);
    $sheet->setCellValue('K11', 40);
    $sheet->setCellValue('N11', 5);
    $sheet->setCellValue('O11', 10);
    $sheet->setCellValue('P11', 20);
    $sheet->setCellValue('Q11', 30);
    $sheet->setCellValue('R11', 40);
    
    // Row 12: Employee 1
    $sheet->setCellValue('A12', '1234567890');
    $sheet->setCellValue('B12', 'Pegawai Tes 1');
    $sheet->setCellValue('C12', 'Jabatan Tes 1');
    $sheet->setCellValue('E12', 'E'); // Present on time
    $sheet->setCellValue('L12', 'L'); // Leave on time
    $sheet->setCellValue('G12', 1);   // Late 1-15 min
    
    // Row 13: Employee 2
    $sheet->setCellValue('A13', '0987654321');
    $sheet->setCellValue('B13', 'Pegawai Tes 2');
    $sheet->setCellValue('C13', 'Jabatan Tes 2');
    $sheet->setCellValue('E13', 'E');
    $sheet->setCellValue('L13', 'L');
    $sheet->setCellValue('N13', 1);   // Early leave 1-15 min
    
    $tempFile = tempnam(sys_get_temp_dir(), 'sikep');
    $writer = new Xlsx($spreadsheet);
    $writer->save($tempFile);
    
    $file = new UploadedFile($tempFile, 'sikep.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    
    $result = $this->service->import($file, $period->id);
    
    expect($result['success'])->toBe(2);
    expect($result['failed'])->toBe(0);
    
    $this->assertDatabaseHas('employees', ['nip' => '1234567890']);
    $this->assertDatabaseHas('employees', ['nip' => '0987654321']);
    
    expect(DisciplineScore::where('period_id', $period->id)->count())->toBe(2);
    
    unlink($tempFile);
});

it('handles corrupted excel file', function () {
    $period = Period::factory()->create();
    $tempFile = tempnam(sys_get_temp_dir(), 'corrupted');
    file_put_contents($tempFile, 'Not an excel file');
    
    $file = new UploadedFile($tempFile, 'sikep.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    
    expect(fn() => $this->service->import($file, $period->id))->toThrow(\Exception::class);
    
    unlink($tempFile);
});