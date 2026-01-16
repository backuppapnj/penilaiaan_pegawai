<?php

namespace Database\Seeders;

use App\Services\SikepImportService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folderPath = base_path('docs/rekap_kehadiran');
        $importService = app(SikepImportService::class);

        if (! File::exists($folderPath)) {
            $this->command->error('Folder docs/rekap_kehadiran not found!');

            return;
        }

        $files = File::glob($folderPath.'/*.xlsx');

        if (empty($files)) {
            $this->command->error('No Excel files found in docs/rekap_kehadiran');

            return;
        }

        $this->command->info('Found '.count($files).' Excel files');

        $totalProcessed = 0;
        $totalImported = 0;
        $totalFailed = 0;

        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            $this->command->info("Processing: {$fileName}");

            // Extract month and year from filename
            // Format: 02_rekap_kehadiran_1600_401877_PA Penajam_Januari_2025.xlsx
            if (! preg_match('/PA Penajam_([A-Za-z]+)_(\d{4})\.xlsx$/', $fileName, $matches)) {
                $this->command->warn('  Skipping: Could not extract month/year from filename');

                continue;
            }

            $monthName = $matches[1];
            $year = (int) $matches[2];

            $month = $this->monthNameToNumber($monthName);
            if ($month === null) {
                $this->command->warn("  Skipping: Invalid month name '{$monthName}'");

                continue;
            }

            $result = $this->processExcelFile($filePath, $fileName, $month, $year, $importService);
            $totalImported += $result['success'];
            $totalFailed += $result['failed'];

            $this->command->info("  Imported {$result['success']} attendance records for month {$month}/{$year}");

            if ($result['failed'] > 0) {
                $this->command->warn("  Failed: {$result['failed']}");
                foreach ($result['errors'] as $error) {
                    $this->command->warn("   - {$error['nip']} {$error['nama']}: {$error['error']}");
                }
            }

            $totalProcessed++;
        }

        $this->command->info(PHP_EOL."Successfully processed {$totalProcessed} files");
        $this->command->info("Total attendance records imported: {$totalImported}");
        if ($totalFailed > 0) {
            $this->command->warn("Total attendance records failed: {$totalFailed}");
        }
    }

    /**
     * Process a single Excel file and import attendance data.
     */
    private function processExcelFile(
        string $filePath,
        string $fileName,
        int $month,
        int $year,
        SikepImportService $importService
    ): array {
        $uploadedFile = new UploadedFile($filePath, $fileName, null, null, true);

        return $importService->import($uploadedFile, $month, $year);
    }

    /**
     * Convert Indonesian month name to number.
     */
    private function monthNameToNumber(string $monthName): ?int
    {
        $months = [
            'Januari' => 1,
            'Februari' => 2,
            'Maret' => 3,
            'April' => 4,
            'Mei' => 5,
            'Juni' => 6,
            'Juli' => 7,
            'Agustus' => 8,
            'September' => 9,
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12,
        ];

        return $months[$monthName] ?? null;
    }
}
