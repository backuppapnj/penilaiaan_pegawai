<?php

namespace App\Console\Commands;

use App\Models\DisciplineScore;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as SpreadsheetWriter;

class ExportDisciplineCalculationWorkbook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discipline:export-perhitungan
                            {year : Tahun rekap (contoh: 2025)}
                            {--output= : Path output .xlsx (default: docs/Perhitungan_Pegawai_Disiplin_{year}_SEMUA_PEGAWAI.xlsx)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export Excel perhitungan Pegawai Disiplin dari file rekap SIKEP (docs/rekap_kehadiran)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = (int) $this->argument('year');
        if ($year < 2000 || $year > 2100) {
            $this->error('Tahun tidak valid.');

            return self::FAILURE;
        }

        $outputOption = (string) ($this->option('output') ?? '');
        $outputPath = $outputOption !== ''
            ? $this->normalizeOutputPath($outputOption)
            : base_path("docs/Perhitungan_Pegawai_Disiplin_{$year}_SEMUA_PEGAWAI.xlsx");

        $rekapFolder = base_path('docs/rekap_kehadiran');
        if (! File::exists($rekapFolder)) {
            $this->error('Folder docs/rekap_kehadiran tidak ditemukan.');

            return self::FAILURE;
        }

        $seedEmployees = $this->loadSeedEmployees();
        $earlyArrivalCounts = $this->getEarlyArrivalCountsByMonth($year);

        $rekapByMonth = [];
        $lastKnownInfo = [];
        $allNips = [];

        $files = File::glob($rekapFolder.'/*.xlsx');
        sort($files);

        foreach ($files as $filePath) {
            $fileName = basename($filePath);

            if (! preg_match('/PA Penajam_([A-Za-z]+)_(\\d{4})\\.xlsx$/', $fileName, $matches)) {
                continue;
            }

            $fileYear = (int) $matches[2];
            if ($fileYear !== $year) {
                continue;
            }

            $month = $this->monthNameToNumber($matches[1]);
            if ($month === null) {
                continue;
            }

            $parsed = $this->parseRekapFile($filePath);
            $rekapByMonth[$month] = [
                'file_name' => $fileName,
                'total_work_days' => $parsed['total_work_days'],
                'employees' => $parsed['employees'],
            ];

            foreach ($parsed['employees'] as $nip => $employeeRow) {
                $allNips[$nip] = true;
                $lastKnownInfo[$nip] = [
                    'nama' => $employeeRow['nama'],
                    'jabatan' => $employeeRow['jabatan'],
                ];
            }
        }

        if (empty($rekapByMonth)) {
            $this->error("Tidak ditemukan file rekap untuk tahun {$year} di docs/rekap_kehadiran.");

            return self::FAILURE;
        }

        $nips = array_keys($allNips);
        sort($nips);

        $spreadsheet = new Spreadsheet;
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Ringkasan');

        $summarySheet->setCellValue('A1', "Perhitungan Pegawai Disiplin ({$year})");
        $summarySheet->setCellValue('A2', 'Catatan: PPPK dihitung mulai bulan TMT pada tahun berjalan.');
        $summarySheet->setCellValue('A3', 'Catatan: Kolom "Dinilai" hanya menghitung bulan yang ada data di rekap dan memenuhi start bulan.');

        $headers = [
            'NIP',
            'Nama',
            'Jabatan',
            'Gol',
            'TMT',
            'PPPK',
            'Start Bulan',
            'Bulan Dinilai',
            'Rata-rata Total (Dinilai)',
            'Rata-rata Total (12 bulan)',
        ];

        $this->writeRow($summarySheet, 4, 1, $headers);
        $summarySheet->getStyle('A4:J4')->getFont()->setBold(true);

        $summaryRow = 5;

        foreach ($nips as $nip) {
            $seed = $seedEmployees[$nip] ?? null;
            $nama = $seed['nama'] ?? ($lastKnownInfo[$nip]['nama'] ?? '');
            $jabatan = $seed['jabatan'] ?? ($lastKnownInfo[$nip]['jabatan'] ?? '');
            $gol = $seed['gol'] ?? '';
            $tmtDate = $this->parseIndonesianDate($seed['tmt'] ?? '');
            $tmtYmd = $tmtDate?->format('Y-m-d') ?? '';
            $isPppk = $this->isPppk($gol);
            $startMonth = $this->getStartMonth($tmtDate, $isPppk, $year);

            $monthlyScores = [];
            $monthlyDinilaiFlags = [];

            for ($month = 1; $month <= 12; $month++) {
                $monthData = $rekapByMonth[$month]['employees'][$nip] ?? null;
                $hasData = $monthData !== null;

                $totalWorkDays = (int) ($rekapByMonth[$month]['total_work_days'] ?? 0);
                $excusedDays = (int) ($monthData['excused_days'] ?? 0);
                $effectiveDays = (int) ($monthData['effective_days'] ?? max(0, $totalWorkDays - $excusedDays));
                $presentOnTime = (int) ($monthData['present_on_time'] ?? 0);
                $leaveOnTime = (int) ($monthData['leave_on_time'] ?? 0);
                $latePenalty = (float) ($monthData['late_penalty'] ?? 0);
                $earlyPenalty = (float) ($monthData['early_penalty'] ?? 0);
                $excessPermissionCount = (int) ($monthData['excess_permission_count'] ?? 0);

                $score1 = DisciplineScore::calculateScore1($presentOnTime, $leaveOnTime, $effectiveDays);
                $score2 = DisciplineScore::calculateScore2($latePenalty, $earlyPenalty);
                $score3 = DisciplineScore::calculateScore3($excessPermissionCount);
                $totalScore = DisciplineScore::calculateFinalScore($score1, $score2, $score3);

                $monthlyScores[$month] = $totalScore;
                $monthlyDinilaiFlags[$month] = ($hasData && $month >= $startMonth) ? 1 : 0;
            }

            $monthsDinilai = array_sum($monthlyDinilaiFlags);
            $avgDinilai = $monthsDinilai > 0
                ? round(array_sum(array_map(function (int $month) use ($monthlyScores, $monthlyDinilaiFlags): float {
                    return $monthlyDinilaiFlags[$month] === 1 ? $monthlyScores[$month] : 0.0;
                }, array_keys($monthlyScores))) / $monthsDinilai, 2)
                : 0.0;

            $avg12 = round(array_sum($monthlyScores) / 12, 2);

            $this->writeRow($summarySheet, $summaryRow, 1, [
                $nip,
                $nama,
                $jabatan,
                $gol,
                $tmtYmd,
                $isPppk ? 'Ya' : 'Tidak',
                $startMonth,
                $monthsDinilai,
                $avgDinilai,
                $avg12,
            ]);

            $this->createEmployeeSheet(
                $spreadsheet,
                $nip,
                $nama,
                $jabatan,
                $gol,
                $tmtYmd,
                $isPppk,
                $startMonth,
                $rekapByMonth,
                $earlyArrivalCounts[$nip] ?? []
            );

            $summaryRow++;
        }

        $summarySheet->freezePane('A5');
        foreach (range('A', 'J') as $col) {
            $summarySheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new SpreadsheetWriter($spreadsheet);
        $writer->save($outputPath);

        $this->info("Selesai. File dibuat: {$outputPath}");

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{nip: string, nama: string, jabatan: string, tmt: string, gol: string}>
     */
    private function loadSeedEmployees(): array
    {
        $path = base_path('docs/data_pegawai.json');
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);
        if (! is_array($decoded)) {
            return [];
        }

        $employees = [];
        foreach ($decoded as $row) {
            if (! is_array($row) || empty($row['nip'])) {
                continue;
            }

            $employees[(string) $row['nip']] = [
                'nip' => (string) $row['nip'],
                'nama' => (string) ($row['nama'] ?? ''),
                'jabatan' => (string) ($row['jabatan'] ?? ''),
                'tmt' => (string) ($row['tmt'] ?? ''),
                'gol' => (string) ($row['gol'] ?? ''),
            ];
        }

        return $employees;
    }

    private function normalizeOutputPath(string $output): string
    {
        $output = trim($output);
        if ($output === '') {
            return base_path('docs/Perhitungan_Pegawai_Disiplin.xlsx');
        }

        if (str_starts_with($output, DIRECTORY_SEPARATOR)) {
            return $output;
        }

        return base_path($output);
    }

    private function isPppk(string $golongan): bool
    {
        $golongan = trim($golongan);

        if ($golongan === '') {
            return false;
        }

        return ! str_contains($golongan, '/');
    }

    private function getStartMonth(?CarbonImmutable $tmt, bool $isPppk, int $year): int
    {
        if (! $tmt) {
            return 1;
        }

        if (! $isPppk) {
            return 1;
        }

        if ((int) $tmt->format('Y') !== $year) {
            return 1;
        }

        return (int) $tmt->format('n');
    }

    private function parseIndonesianDate(string $value): ?CarbonImmutable
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $parts = preg_split('/\\s+/', $value);
        if (! is_array($parts) || count($parts) < 3) {
            return null;
        }

        $day = (int) $parts[0];
        $monthName = strtolower((string) $parts[1]);
        $year = (int) $parts[2];

        $monthMap = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        if (! isset($monthMap[$monthName])) {
            return null;
        }

        try {
            return CarbonImmutable::create($year, $monthMap[$monthName], $day);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @return array{total_work_days: int, employees: array<string, array{
     *     nama: string,
     *     jabatan: string,
     *     present_on_time: int,
     *     leave_on_time: int,
     *     excused_days: int,
     *     effective_days: int,
     *     late_penalty: float,
     *     early_penalty: float,
     *     excess_permission_count: int
     * }>}
     */
    private function parseRekapFile(string $filePath): array
    {
        $reader = new Xlsx;
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $totalWorkDays = $this->extractTotalWorkDays($worksheet);
        $lastDataRow = $this->getLastDataRow($worksheet);
        $columnMeta = $this->extractColumnMeta($worksheet);
        $penaltyWeights = $this->extractPenaltyWeights($columnMeta);
        $employeeData = $this->extractEmployeeData($worksheet, $columnMeta, 12, $lastDataRow);

        $employees = [];
        foreach ($employeeData as $data) {
            $stats = $this->calculateAttendanceStats(
                $data['attendance'],
                $penaltyWeights,
                $totalWorkDays,
                (float) $data['present_on_time'],
                (float) $data['leave_on_time']
            );

            $employees[$data['nip']] = [
                'nama' => $data['nama'],
                'jabatan' => $data['jabatan'],
                'present_on_time' => (int) $stats['present_on_time'],
                'leave_on_time' => (int) $stats['leave_on_time'],
                'excused_days' => (int) $stats['excused_days'],
                'effective_days' => (int) $stats['total_work_days'],
                'late_penalty' => (float) $stats['late_penalty'],
                'early_penalty' => (float) $stats['early_penalty'],
                'excess_permission_count' => (int) $stats['excess_permission_count'],
            ];
        }

        return [
            'total_work_days' => $totalWorkDays,
            'employees' => $employees,
        ];
    }

    private function extractTotalWorkDays(Worksheet $worksheet): int
    {
        for ($row = 1; $row <= 10; $row++) {
            $label = trim((string) $worksheet->getCell("A{$row}")->getValue());
            if (strtoupper($label) === 'TOTAL HARI KERJA') {
                $value = $worksheet->getCell("C{$row}")->getValue();

                return (int) $this->parseNumeric($value);
            }
        }

        return 0;
    }

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
     * @param  array<string, array{code: string, label: string, weight: float}>  $columnMeta
     * @return array<int, array{
     *     nip: string,
     *     nama: string,
     *     jabatan: string,
     *     present_on_time: float,
     *     leave_on_time: float,
     *     attendance: array<string, float>
     * }>
     */
    private function extractEmployeeData(Worksheet $worksheet, array $columnMeta, int $startRow, int $endRow): array
    {
        $employees = [];

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

            $employees[] = [
                'nip' => $nip,
                'nama' => $name,
                'jabatan' => $jabatan,
                'present_on_time' => $this->parseNumeric($worksheet->getCell("E{$row}")->getValue()),
                'leave_on_time' => $this->parseNumeric($worksheet->getCell("L{$row}")->getValue()),
                'attendance' => $attendance,
            ];
        }

        return $employees;
    }

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

        if (preg_match('/-?\\d+(?:\\.\\d+)?/', $normalized, $matches)) {
            return (float) $matches[0];
        }

        return 0.0;
    }

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
        $latePenalty = $this->calculatePenalty($attendance, $penaltyWeights, $this->latePenaltyCodes());
        $earlyPenalty = $this->calculatePenalty($attendance, $penaltyWeights, $this->earlyPenaltyCodes());
        $totalPenalty = round($latePenalty + $earlyPenalty, 2);

        $excusedDays = $this->countByCodes($attendance, $this->excusedDayCodes());
        $effectiveDays = max(0, $totalWorkDays - $excusedDays);

        return [
            'total_work_days' => $effectiveDays,
            'present_on_time' => $presentOnTime,
            'leave_on_time' => $leaveOnTime,
            'late_minutes' => $latePenalty,
            'early_leave_minutes' => $earlyPenalty,
            'excess_permission_count' => $this->countByCodes($attendance, $this->excessPermissionCodes()),
            'excused_days' => $excusedDays,
            'late_penalty' => $latePenalty,
            'early_penalty' => $earlyPenalty,
            'total_penalty' => $totalPenalty,
        ];
    }

    /**
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
     * @return list<string>
     */
    private function latePenaltyCodes(): array
    {
        return ['tl1', 'tl2', 'tl3', 'tl4', 'thm'];
    }

    /**
     * @return list<string>
     */
    private function earlyPenaltyCodes(): array
    {
        return ['psw1', 'psw2', 'psw3', 'psw4', 'thp'];
    }

    /**
     * @return list<string>
     */
    private function excusedDayCodes(): array
    {
        return ['dls', 'ct', 'ctl', 'tb', 'ld', 'cs1', 'cm1', 'cm2', 'cm3', 'cap1'];
    }

    /**
     * @return list<string>
     */
    private function excessPermissionCodes(): array
    {
        return ['i', 'clt', 'cpp', 'bmt', 'ib', 'tmk', 'cs14', 'cm41', 'cm42', 'cm43', 'cap10', 'cb1', 'cb2', 'cb3'];
    }

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

    /**
     * @param  array<int, string>  $values
     */
    private function writeRow(Worksheet $sheet, int $row, int $startColumnIndex, array $values): void
    {
        foreach (array_values($values) as $offset => $value) {
            $column = Coordinate::stringFromColumnIndex($startColumnIndex + $offset);
            $sheet->setCellValue($column.$row, $value);
        }
    }

    /**
     * @param  array<int, array{file_name: string, total_work_days: int, employees: array<string, array<string, mixed>>}>  $rekapByMonth
     * @param  array<int, int>  $earlyArrivalByMonth
     */
    private function createEmployeeSheet(
        Spreadsheet $spreadsheet,
        string $nip,
        string $nama,
        string $jabatan,
        string $gol,
        string $tmtYmd,
        bool $isPppk,
        int $startMonth,
        array $rekapByMonth,
        array $earlyArrivalByMonth
    ): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($nip);

        $sheet->setCellValue('A1', 'Perhitungan Pegawai Disiplin');
        $sheet->setCellValue('A2', 'NIP');
        $sheet->setCellValue('B2', $nip);
        $sheet->setCellValue('A3', 'Nama');
        $sheet->setCellValue('B3', $nama);
        $sheet->setCellValue('A4', 'Jabatan');
        $sheet->setCellValue('B4', $jabatan);
        $sheet->setCellValue('A5', 'Gol');
        $sheet->setCellValue('B5', $gol);
        $sheet->setCellValue('A6', 'TMT');
        $sheet->setCellValue('B6', $tmtYmd);
        $sheet->setCellValue('A7', 'PPPK');
        $sheet->setCellValue('B7', $isPppk ? 'Ya' : 'Tidak');
        $sheet->setCellValue('A8', 'Start Bulan Penilaian');
        $sheet->setCellValue('B8', $startMonth);

        $headers = [
            'Bulan',
            'Nama Bulan',
            'File Rekap',
            'Total Hari Kerja',
            'Excused Days (0%)',
            'Effective Days',
            'Hadir Tepat Waktu (E)',
            'Pulang Tepat Waktu (L)',
            'Penalti Terlambat',
            'Penalti Pulang Awal',
            'Total Penalti',
            'Izin Berlebih',
            'Score 1 (50%)',
            'Score 2 (35%)',
            'Score 3 (15%)',
            'Total Skor',
            'Dinilai (1/0)',
            'Datang <08 (bulan)',
        ];

        $this->writeRow($sheet, 10, 1, $headers);
        $sheet->getStyle('A10:R10')->getFont()->setBold(true);
        $sheet->freezePane('A11');

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        for ($month = 1; $month <= 12; $month++) {
            $row = 10 + $month;
            $monthMeta = $rekapByMonth[$month] ?? ['file_name' => '', 'total_work_days' => 0, 'employees' => []];
            $employeeMonth = $monthMeta['employees'][$nip] ?? null;
            $hasData = $employeeMonth !== null;

            $totalWorkDays = (int) ($monthMeta['total_work_days'] ?? 0);
            $excusedDays = (int) ($employeeMonth['excused_days'] ?? 0);
            $effectiveDays = (int) ($employeeMonth['effective_days'] ?? max(0, $totalWorkDays - $excusedDays));
            $presentOnTime = (int) ($employeeMonth['present_on_time'] ?? 0);
            $leaveOnTime = (int) ($employeeMonth['leave_on_time'] ?? 0);
            $latePenalty = (float) ($employeeMonth['late_penalty'] ?? 0);
            $earlyPenalty = (float) ($employeeMonth['early_penalty'] ?? 0);
            $excessPermissionCount = (int) ($employeeMonth['excess_permission_count'] ?? 0);
            $dinilai = ($hasData && $month >= $startMonth) ? 1 : 0;
            $earlyArrivals = (int) ($earlyArrivalByMonth[$month] ?? 0);

            $sheet->setCellValue("A{$row}", $month);
            $sheet->setCellValue("B{$row}", $monthNames[$month] ?? (string) $month);
            $sheet->setCellValue("C{$row}", (string) ($monthMeta['file_name'] ?? ''));
            $sheet->setCellValue("D{$row}", $totalWorkDays);
            $sheet->setCellValue("E{$row}", $excusedDays);
            $sheet->setCellValue("F{$row}", $effectiveDays);
            $sheet->setCellValue("G{$row}", $presentOnTime);
            $sheet->setCellValue("H{$row}", $leaveOnTime);
            $sheet->setCellValue("I{$row}", $latePenalty);
            $sheet->setCellValue("J{$row}", $earlyPenalty);
            $sheet->setCellValue("K{$row}", "=ROUND(I{$row}+J{$row},2)");
            $sheet->setCellValue("L{$row}", $excessPermissionCount);
            $sheet->setCellValue("M{$row}", "=IF(F{$row}=0,0,ROUND(((G{$row}+H{$row})/(F{$row}*2))*50,2))");
            $sheet->setCellValue("N{$row}", "=ROUND(MAX(0,(100-K{$row})*0.35),2)");
            $sheet->setCellValue("O{$row}", "=IF(L{$row}>0,0,15)");
            $sheet->setCellValue("P{$row}", "=ROUND(M{$row}+N{$row}+O{$row},2)");
            $sheet->setCellValue("Q{$row}", $dinilai);
            $sheet->setCellValue("R{$row}", $earlyArrivals);
        }

        $sheet->setCellValue('A24', 'Ringkasan (Dinilai)');
        $sheet->setCellValue('A25', 'Rata-rata Score 1');
        $sheet->setCellValue('B25', '=ROUND(AVERAGEIF(Q11:Q22,1,M11:M22),2)');
        $sheet->setCellValue('A26', 'Rata-rata Score 2');
        $sheet->setCellValue('B26', '=ROUND(AVERAGEIF(Q11:Q22,1,N11:N22),2)');
        $sheet->setCellValue('A27', 'Rata-rata Score 3');
        $sheet->setCellValue('B27', '=ROUND(AVERAGEIF(Q11:Q22,1,O11:O22),2)');
        $sheet->setCellValue('A28', 'Rata-rata Total Skor');
        $sheet->setCellValue('B28', '=ROUND(AVERAGEIF(Q11:Q22,1,P11:P22),2)');
        $sheet->setCellValue('A29', 'Total Datang <08');
        $sheet->setCellValue('B29', '=SUMIF(Q11:Q22,1,R11:R22)');

        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function getEarlyArrivalCountsByMonth(int $year): array
    {
        $folderPath = base_path('docs/rekap_kehadiran');
        if (! File::exists($folderPath)) {
            return [];
        }

        $files = File::glob($folderPath.'/*.xls');
        if (empty($files)) {
            return [];
        }

        $counts = [];

        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            if (! preg_match('/([a-z]+)_(\\d{4})/i', $fileName, $matches)) {
                continue;
            }

            $fileYear = (int) $matches[2];
            if ($fileYear !== $year) {
                continue;
            }

            $month = $this->monthNameToNumberFromXlsName(strtolower($matches[1]));
            if ($month === null) {
                continue;
            }

            $fileCounts = $this->parseEarlyArrivalFromFile($filePath);
            foreach ($fileCounts as $nip => $count) {
                $counts[$nip][$month] = ($counts[$nip][$month] ?? 0) + $count;
            }
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    private function parseEarlyArrivalFromFile(string $filePath): array
    {
        $reader = new Xls;
        $sheet = $reader->load($filePath)->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $counts = [];
        $currentNip = null;
        $inRows = false;

        for ($row = 1; $row <= $highestRow; $row++) {
            $label = trim((string) $sheet->getCell("A{$row}")->getFormattedValue());

            if (strcasecmp($label, 'NIP') === 0) {
                $currentNip = trim((string) $sheet->getCell("B{$row}")->getFormattedValue());
                $inRows = false;

                continue;
            }

            if (strcasecmp($label, 'Hari') === 0) {
                $inRows = true;

                continue;
            }

            if (strcasecmp($label, 'Jumlah') === 0) {
                $inRows = false;

                continue;
            }

            if (! $inRows || ! $currentNip) {
                continue;
            }

            $timeValue = $sheet->getCell("D{$row}")->getValue();
            $minutes = $this->parseTimeToMinutes($timeValue);
            if ($minutes !== null && $minutes < 480) {
                $counts[$currentNip] = ($counts[$currentNip] ?? 0) + 1;
            }
        }

        return $counts;
    }

    private function parseTimeToMinutes(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $date = Date::excelToDateTimeObject((float) $value);

            return ((int) $date->format('H') * 60) + (int) $date->format('i');
        }

        $normalized = str_replace('.', ':', trim((string) $value));
        if (preg_match('/^(\\d{1,2}):(\\d{2})/', $normalized, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];

            return ($hours * 60) + $minutes;
        }

        return null;
    }

    private function monthNameToNumberFromXlsName(string $name): ?int
    {
        $months = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        return $months[$name] ?? null;
    }
}
