<?php

namespace App\Services;

use App\Models\DisciplineScore;
use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Service for importing SIKEP attendance data from Excel files
 *
 * Excel structure (rekap kehadiran):
 * - Row 8-11: Headers, codes, and penalty weights
 * - Row 12+: Employee data
 */
class SikepImportService
{
    private Xlsx $reader;

    /**
     * Penalti keterlambatan (berdasarkan kode di baris 10).
     *
     * @var list<string>
     */
    private array $latePenaltyCodes = ['tl1', 'tl2', 'tl3', 'tl4', 'thm'];

    /**
     * Penalti pulang awal (berdasarkan kode di baris 10).
     *
     * @var list<string>
     */
    private array $earlyPenaltyCodes = ['psw1', 'psw2', 'psw3', 'psw4', 'thp'];

    /**
     * Hari kerja yang dikecualikan dari perhitungan kehadiran (bobot 0%).
     *
     * @var list<string>
     */
    private array $excusedDayCodes = ['dls', 'ct', 'ctl', 'tb', 'ld', 'cs1', 'cm1', 'cm2', 'cm3', 'cap1'];

    /**
     * Izin berlebih untuk skor ketaatan (bobot > 0%).
     *
     * @var list<string>
     */
    private array $excessPermissionCodes = ['i', 'clt', 'cpp', 'bmt', 'ib', 'tmk', 'cs14', 'cm41', 'cm42', 'cm43', 'cap10', 'cb1', 'cb2', 'cb3'];

    public function __construct()
    {
        $this->reader = new Xlsx;
    }

    /**
     * Import SIKEP data from Excel file
     *
     * @param  int  $month  Month
     * @param  int  $year  Year
     * @return array{success: int, failed: int, errors: array}
     */
    public function import(UploadedFile $file, int $month, int $year): array
    {
        $spreadsheet = $this->reader->load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();

        $totalWorkDays = $this->extractTotalWorkDays($worksheet);
        $lastDataRow = $this->getLastDataRow($worksheet);
        $columnMeta = $this->extractColumnMeta($worksheet);
        $penaltyWeights = $this->extractPenaltyWeights($columnMeta);
        $employeeData = $this->extractEmployeeData($worksheet, $columnMeta, 12, $lastDataRow);

        return $this->processEmployeeData($employeeData, $month, $year, $penaltyWeights, $totalWorkDays);
    }

    /**
     * Extract total work days from the header area.
     */
    private function extractTotalWorkDays(Worksheet $worksheet): int
    {
        for ($row = 1; $row <= 10; $row++) {
            $label = trim((string) $worksheet->getCell("A{$row}")->getValue());
            if (strtoupper($label) === 'TOTAL HARI KERJA') {
                $value = $worksheet->getCell("C{$row}")->getValue();
                $numeric = $this->parseNumeric($value);

                return (int) $numeric;
            }
        }

        return 0;
    }

    /**
     * Detect the last data row using the "NO" column.
     */
    private function getLastDataRow(Worksheet $worksheet): int
    {
        $highestRow = $worksheet->getHighestRow();
        $lastRow = 0;

        for ($row = 12; $row <= $highestRow; $row++) {
            $value = $worksheet->getCell("A{$row}")->getValue();
            if (is_numeric($value)) {
                $lastRow = $row;
            }
        }

        return $lastRow > 0 ? $lastRow : $highestRow;
    }

    /**
     * Extract column codes and weights from header rows.
     *
     * @return array<string, array{code: string, label: string, weight: float}>
     */
    private function extractColumnMeta(Worksheet $worksheet): array
    {
        $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());
        $meta = [];

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $column = Coordinate::stringFromColumnIndex($col);
            $label = trim((string) $worksheet->getCell($column.'9')->getValue());
            $code = trim((string) $worksheet->getCell($column.'10')->getValue());
            $weight = $worksheet->getCell($column.'11')->getValue();

            if ($label === '' && $code === '' && $weight === null) {
                continue;
            }

            $meta[$column] = [
                'code' => $code,
                'label' => $label,
                'weight' => $this->parseWeight($weight),
            ];
        }

        return $meta;
    }

    /**
     * Build penalty weights keyed by code.
     *
     * @param  array<string, array{code: string, label: string, weight: float}>  $columnMeta
     * @return array<string, float>
     */
    private function extractPenaltyWeights(array $columnMeta): array
    {
        $weights = [];

        foreach ($columnMeta as $meta) {
            $code = $meta['code'];
            if ($code === '') {
                continue;
            }

            if (! array_key_exists($code, $weights)) {
                $weights[$code] = $meta['weight'];
            }
        }

        return $weights;
    }

    /**
     * Extract employee data starting from row 12.
     *
     * @param  array<string, array{code: string, label: string, weight: float}>  $columnMeta
     */
    private function extractEmployeeData(
        Worksheet $worksheet,
        array $columnMeta,
        int $startRow,
        int $endRow
    ): Collection {
        $employees = collect();

        for ($row = $startRow; $row <= $endRow; $row++) {
            $no = $worksheet->getCell("A{$row}")->getValue();
            if (! is_numeric($no)) {
                continue;
            }

            $name = trim((string) $worksheet->getCell("B{$row}")->getValue());
            $nip = trim((string) $worksheet->getCell("C{$row}")->getValue());
            $jabatan = trim((string) $worksheet->getCell("D{$row}")->getValue());

            if ($name === '' && $nip === '') {
                continue;
            }

            $attendance = [];
            foreach ($columnMeta as $column => $meta) {
                $code = $meta['code'];
                if ($code === '' || in_array($column, ['E', 'L'], true)) {
                    continue;
                }

                $attendance[$code] = $this->parseNumeric($worksheet->getCell($column.$row)->getValue());
            }

            $employees->push([
                'nip' => $nip,
                'nama' => $name,
                'jabatan' => $jabatan,
                'present_on_time' => $this->parseNumeric($worksheet->getCell("E{$row}")->getValue()),
                'leave_on_time' => $this->parseNumeric($worksheet->getCell("L{$row}")->getValue()),
                'attendance' => $attendance,
                'row' => $row,
            ]);
        }

        return $employees;
    }

    /**
     * Normalize numeric values from the sheet.
     */
    private function parseNumeric(mixed $value): float
    {
        if ($value === null || $value === '-') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', trim((string) $value));

        if (is_numeric($normalized)) {
            return (float) $normalized;
        }

        if (preg_match('/-?\d+(?:\.\d+)?/', $normalized, $matches)) {
            return (float) $matches[0];
        }

        return 0.0;
    }

    /**
     * Normalize weight values like "0.5%".
     */
    private function parseWeight(mixed $value): float
    {
        if ($value === null || $value === '-') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace('%', '', trim((string) $value));
        $normalized = str_replace(',', '.', $normalized);

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    /**
     * Process employee data and calculate scores
     */
    private function processEmployeeData(
        Collection $employeeData,
        int $month,
        int $year,
        array $penaltyWeights,
        int $totalWorkDays
    ): array {
        $result = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($employeeData as $data) {
                try {
                    $this->processSingleEmployee($data, $month, $year, $penaltyWeights, $totalWorkDays);
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
    private function processSingleEmployee(
        array $data,
        int $month,
        int $year,
        array $penaltyWeights,
        int $totalWorkDays
    ): void {
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
        $stats = $this->calculateAttendanceStats(
            $data['attendance'],
            $penaltyWeights,
            $totalWorkDays,
            $data['present_on_time'],
            $data['leave_on_time']
        );

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
        $rawData = $data;
        $rawData['total_work_days_original'] = $totalWorkDays;
        $rawData['excused_days'] = $stats['excused_days'];
        $rawData['penalties'] = [
            'late' => $stats['late_penalty'],
            'early' => $stats['early_penalty'],
            'total' => $stats['total_penalty'],
        ];

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
                'raw_data' => $rawData,
            ]
        );
    }

    /**
     * Calculate attendance stats and penalties.
     *
     * @param  array<string, float>  $attendance
     * @param  array<string, float>  $penaltyWeights
     * @return array{
     *     total_work_days: int,
     *     present_on_time: float,
     *     leave_on_time: float,
     *     late_minutes: float,
     *     early_leave_minutes: float,
     *     excess_permission_count: int,
     *     excused_days: int,
     *     late_penalty: float,
     *     early_penalty: float,
     *     total_penalty: float
     * }
     */
    private function calculateAttendanceStats(
        array $attendance,
        array $penaltyWeights,
        int $totalWorkDays,
        float $presentOnTime,
        float $leaveOnTime
    ): array {
        $latePenalty = $this->calculatePenalty($attendance, $penaltyWeights, $this->latePenaltyCodes);
        $earlyPenalty = $this->calculatePenalty($attendance, $penaltyWeights, $this->earlyPenaltyCodes);
        $totalPenalty = round($latePenalty + $earlyPenalty, 2);

        $excusedDays = $this->countByCodes($attendance, $this->excusedDayCodes);
        $effectiveDays = max(0, $totalWorkDays - $excusedDays);

        return [
            'total_work_days' => $effectiveDays,
            'present_on_time' => $presentOnTime,
            'leave_on_time' => $leaveOnTime,
            'late_minutes' => $latePenalty,
            'early_leave_minutes' => $earlyPenalty,
            'excess_permission_count' => $this->countByCodes($attendance, $this->excessPermissionCodes),
            'excused_days' => $excusedDays,
            'late_penalty' => $latePenalty,
            'early_penalty' => $earlyPenalty,
            'total_penalty' => $totalPenalty,
        ];
    }

    /**
     * Sum penalties using weight percentages.
     *
     * @param  array<string, float>  $attendance
     * @param  array<string, float>  $penaltyWeights
     * @param  list<string>  $codes
     */
    private function calculatePenalty(array $attendance, array $penaltyWeights, array $codes): float
    {
        $total = 0.0;

        foreach ($codes as $code) {
            $count = $attendance[$code] ?? 0.0;
            $weight = $penaltyWeights[$code] ?? 0.0;
            $total += $count * $weight;
        }

        return round($total, 2);
    }

    /**
     * Count occurrences for selected codes.
     *
     * @param  array<string, float>  $attendance
     * @param  list<string>  $codes
     */
    private function countByCodes(array $attendance, array $codes): int
    {
        $total = 0;

        foreach ($codes as $code) {
            $total += (int) ($attendance[$code] ?? 0);
        }

        return $total;
    }

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
