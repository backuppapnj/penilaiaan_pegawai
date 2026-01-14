<?php

namespace App\Services;

use App\Models\DisciplineScore;
use App\Models\Employee;
use App\Models\Period;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Service for importing SIKEP attendance data from Excel files
 *
 * Excel structure:
 * - Row 8-9: Headers
 * - Row 10: Empty row
 * - Row 11: Penalty weights (G-K, N-R values)
 * - Row 12+: Employee data (NIP, Nama, Daily attendance E-AJ)
 */
class SikepImportService
{
    private Xlsx $reader;

    private array $columnMapping = [
        'A' => 'nip',
        'B' => 'nama',
        'C' => 'jabatan',
    ];

    private array $attendanceColumns = [
        'E' => 'present_on_time', // DATANG TEPAT WAKTU
        'F' => 'late_dinas', // TERLAMBAT DINAS
        'G' => 'late_1_15', // G (1-15 menit)
        'H' => 'late_16_30', // H (16-30 menit)
        'I' => 'late_31_45', // I (31-45 menit)
        'J' => 'late_46_60', // J (46-60 menit)
        'K' => 'late_60_plus', // K (> 60 menit)
        'L' => 'leave_on_time', // PULANG TEPAT WAKTU
        'M' => 'early_dinas', // PULANG AWAL DINAS
        'N' => 'early_1_15', // N (1-15 menit)
        'O' => 'early_16_30', // O (16-30 menit)
        'P' => 'early_31_45', // P (31-45 menit)
        'Q' => 'early_46_60', // Q (46-60 menit)
        'R' => 'early_60_plus', // R (> 60 menit)
        'S' => 'permission_sakit', // S (Sakit)
        'T' => 'permission_ac', // AC (Izin Komputer/Cuti)
        'U' => 'permission_v', // V (Cuti)
        'V' => 'permission_aa', // AA
        'W' => 'permission_ab', // AB
        'X' => 'permission_ae', // AE
        'Y' => 'permission_ai', // AI
        'Z' => 'permission_aj', // AJ
        // AA-AJ: Daily attendance data
    ];

    private array $latePenalties = [
        'G' => 5, // 1-15 menit
        'H' => 10, // 16-30 menit
        'I' => 20, // 31-45 menit
        'J' => 30, // 46-60 menit
        'K' => 40, // > 60 menit
    ];

    private array $earlyPenalties = [
        'N' => 5, // 1-15 menit
        'O' => 10, // 16-30 menit
        'P' => 20, // 31-45 menit
        'Q' => 30, // 46-60 menit
        'R' => 40, // > 60 menit
    ];

    private array $excessPermissionCodes = ['S', 'AC', 'V', 'AA', 'AB', 'AE', 'AI', 'AJ'];

    public function __construct()
    {
        $this->reader = new Xlsx();
    }

    /**
     * Import SIKEP data from Excel file
     *
     * @param UploadedFile $file
     * @param int $month Month
     * @param int $year Year
     * @return array{success: int, failed: int, errors: array}
     */
    public function import(UploadedFile $file, int $month, int $year): array
    {
        $spreadsheet = $this->reader->load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();

        // Extract penalty weights from row 11
        $penaltyWeights = $this->extractPenaltyWeights($worksheet);

        // Extract employee data starting from row 12
        $employeeData = $this->extractEmployeeData($worksheet);

        return $this->processEmployeeData($employeeData, $month, $year, $penaltyWeights);
    }

    // ... (extractPenaltyWeights and extractEmployeeData remain same)

    /**
     * Process employee data and calculate scores
     */
    private function processEmployeeData(Collection $employeeData, int $month, int $year, array $penaltyWeights): array
    {
        $result = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($employeeData as $data) {
                try {
                    $this->processSingleEmployee($data, $month, $year, $penaltyWeights);
                    $result['success']++;
                } catch (\Exception $e) {
                    $result['failed']++;
                    $result['errors'][] = [
                        'nip' => $data['nip'],
                        'nama' => $data['nama'],
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Failed to process employee', [
                        'nip' => $data['nip'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Calculate ranks after all scores are inserted
            $this->calculateRanks($month, $year);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Process a single employee's data
     */
    private function processSingleEmployee(array $data, int $month, int $year, array $penaltyWeights): void
    {
        // Find or create employee
        $employee = Employee::firstOrCreate(
            ['nip' => $data['nip']],
            [
                'nama' => $data['nama'],
                'jabatan' => $data['jabatan'],
                'unit_kerja' => '',
                'golongan' => '',
                'tmt' => now(),
            ]
        );

        // Calculate attendance statistics
        $stats = $this->calculateAttendanceStats($data['attendance'], $penaltyWeights);

        // Calculate scores
        $score1 = DisciplineScore::calculateScore1(
            $stats['present_on_time'],
            $stats['leave_on_time'],
            $stats['total_work_days']
        );

        $score2 = DisciplineScore::calculateScore2(
            $stats['late_minutes'],
            $stats['early_leave_minutes']
        );

        $score3 = DisciplineScore::calculateScore3(
            $stats['excess_permission_count']
        );

        $finalScore = DisciplineScore::calculateFinalScore($score1, $score2, $score3);

        // Update or create discipline score
        DisciplineScore::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'period_id' => null, // No longer strictly linked to a period
                'total_work_days' => $stats['total_work_days'],
                'present_on_time' => $stats['present_on_time'],
                'leave_on_time' => $stats['leave_on_time'],
                'late_minutes' => $stats['late_minutes'],
                'early_leave_minutes' => $stats['early_leave_minutes'],
                'excess_permission_count' => $stats['excess_permission_count'],
                'score_1' => $score1,
                'score_2' => $score2,
                'score_3' => $score3,
                'final_score' => $finalScore,
                'rank' => null, // Will be calculated later
                'raw_data' => $data,
            ]
        );
    }

    // ... (calculateAttendanceStats remains same)

    /**
     * Calculate ranks for a given month and year
     */
    private function calculateRanks(int $month, int $year): void
    {
        $scores = DisciplineScore::where('month', $month)
            ->where('year', $year)
            ->orderByDesc('final_score')
            ->get();

        $rank = 1;
        $prevScore = null;
        $rankOffset = 0;

        foreach ($scores as $index => $score) {
            if ($prevScore !== null && $score->final_score < $prevScore) {
                $rank = $index + 1 - $rankOffset;
            } elseif ($prevScore !== null && $score->final_score === $prevScore) {
                $rankOffset++;
            }

            $score->rank = $rank;
            $score->save();

            $prevScore = $score->final_score;
        }
    }
}
