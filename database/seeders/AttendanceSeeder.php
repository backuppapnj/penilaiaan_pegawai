<?php

namespace Database\Seeders;

use App\Models\DisciplineScore;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folderPath = base_path('docs/rekap_kehadiran');

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

            // Process the Excel file
            $imported = $this->processExcelFile($filePath, $month, $year);
            $totalImported += $imported;
            $totalProcessed++;
        }

        $this->command->info(PHP_EOL."Successfully processed {$totalProcessed} files");
        $this->command->info("Total attendance records imported: {$totalImported}");
    }

    /**
     * Process a single Excel file and import attendance data.
     */
    private function processExcelFile(string $filePath, int $month, int $year): int
    {
        $reader = new Xlsx;
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $imported = 0;

        // Data starts from row 12 (after headers)
        for ($row = 12; $row <= $highestRow; $row++) {
            $nip = $sheet->getCell('C'.$row)->getValue();

            // Skip if NIP is empty
            if (empty($nip)) {
                continue;
            }

            // Find employee by NIP
            $employee = Employee::where('nip', $nip)->first();
            if (! $employee) {
                $this->command->warn("  Row {$row}: Employee with NIP {$nip} not found");

                continue;
            }

            // Get attendance data
            $dtw = $this->parseCellValue($sheet->getCell('E'.$row)->getValue()); // Datang Tepat Waktu
            $tkd = $this->parseCellValue($sheet->getCell('F'.$row)->getValue()); // Terlambat Karena Dinas
            $tl1 = $this->parseCellValue($sheet->getCell('G'.$row)->getValue()); // Terlambat 1-30 menit
            $tl2 = $this->parseCellValue($sheet->getCell('H'.$row)->getValue()); // Terlambat 31-60 menit
            $tl3 = $this->parseCellValue($sheet->getCell('I'.$row)->getValue()); // Terlambat 61-90 menit
            $tl4 = $this->parseCellValue($sheet->getCell('J'.$row)->getValue()); // Terlambat >90 menit
            $thm = $this->parseCellValue($sheet->getCell('K'.$row)->getValue()); // Tidak Mengisi Daftar Hadir
            $ptw = $this->parseCellValue($sheet->getCell('L'.$row)->getValue()); // Pulang Tepat Waktu
            $ik = $this->parseCellValue($sheet->getCell('M'.$row)->getValue()); // Izin Keluar
            $psw1 = $this->parseCellValue($sheet->getCell('N'.$row)->getValue()); // Pulang Sebelum Waktu

            // Calculate total work days
            $totalWorkDays = $dtw + $tkd + $tl1 + $tl2 + $tl3 + $tl4 + $thm;

            // Calculate scores based on weights
            // Weights: DTW=0%, TKD=0%, TL1=0.5%, TL2=1%, TL3=1.25%, TL4=1.5%, THM=1.5%, PTW=0%, IK=0%, PSW1=0.5%
            $lateMinutes = ($tl1 * 15) + ($tl2 * 45) + ($tl3 * 75) + ($tl4 * 105);
            $earlyLeaveMinutes = $psw1 * 15;

            $score1 = DisciplineScore::calculateScore1($totalWorkDays, $dtw + $ptw, $lateMinutes);
            $score2 = DisciplineScore::calculateScore2($dtw, $tkd, $tl1, $tl2, $tl3, $tl4, $thm);
            $score3 = DisciplineScore::calculateScore3($ptw, $ik, $psw1);
            $finalScore = DisciplineScore::calculateFinalScore($score1, $score2, $score3);

            // Store raw data
            $rawData = json_encode([
                'dtw' => $dtw,
                'tkd' => $tkd,
                'tl1' => $tl1,
                'tl2' => $tl2,
                'tl3' => $tl3,
                'tl4' => $tl4,
                'thm' => $thm,
                'ptw' => $ptw,
                'ik' => $ik,
                'psw1' => $psw1,
            ]);

            // Create or update discipline score
            DisciplineScore::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'total_work_days' => $totalWorkDays,
                    'present_on_time' => $dtw,
                    'leave_on_time' => $ptw,
                    'late_minutes' => $lateMinutes,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'excess_permission_count' => $ik,
                    'score_1' => $score1,
                    'score_2' => $score2,
                    'score_3' => $score3,
                    'final_score' => $finalScore,
                    'raw_data' => $rawData,
                ]
            );

            $imported++;
        }

        $this->command->info("  Imported {$imported} attendance records for month {$month}/{$year}");

        return $imported;
    }

    /**
     * Parse cell value - convert "-" to 0, otherwise return the value.
     */
    private function parseCellValue($value): int
    {
        if ($value === '-' || $value === null || $value === '') {
            return 0;
        }

        return (int) $value;
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
