<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\DisciplineScore;
use App\Models\Employee;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Service untuk meng-convert DisciplineScores menjadi Votes
 *
 * Mapping:
 * - Score 1 (50%): Tingkat Kehadiran & Ketepatan Waktu
 * - Score 2 (35%): Kedisiplinan - Tanpa Pelanggaran
 * - Score 3 (15%): Ketaatan - Tanpa Izin Berlebih
 */
class DisciplineVoteService
{
    private int $disciplineCategoryId = 3;

    /**
     * Generate votes from discipline scores for a specific period
     *
     * @param  int  $periodId  Period ID
     * @param  int|null  $voterId  User ID sebagai voter (default: admin user pertama)
     * @param  array  $options  Additional options
     * @return array{success: int, failed: int, errors: array}
     */
    public function generateVotes(int $periodId, ?int $voterId = null, array $options = []): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        $period = Period::find($periodId);
        if (! $period) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['Periode tidak ditemukan'],
            ];
        }

        $targetYear = $period->year ?? (int) DisciplineScore::max('year');
        if (! $targetYear) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['Tahun penilaian tidak ditemukan'],
            ];
        }

        if (($options['overwrite'] ?? false) === true) {
            Vote::where('period_id', $periodId)
                ->where('category_id', $this->disciplineCategoryId)
                ->delete();
        }

        $excludedNips = $this->getExcludedPimpinanNips();

        $earlyArrivalCountsByMonth = $this->getEarlyArrivalCountsByMonth($targetYear);

        $allDisciplineScores = DisciplineScore::where('year', $targetYear)
            ->whereNotNull('month')
            ->whereHas('employee', function ($query) use ($excludedNips) {
                if (! empty($excludedNips)) {
                    $query->whereNotIn('nip', $excludedNips);
                }
            })
            ->whereHas('employee.user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('employee')
            ->get();

        if ($allDisciplineScores->isEmpty()) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['Tidak ada data discipline_scores untuk tahun ini'],
            ];
        }

        $disciplineScores = $allDisciplineScores
            ->groupBy('employee_id');

        // Get voter - use admin if not specified
        if ($voterId === null) {
            $voter = User::where('role', 'Admin')->orWhere('role', 'SuperAdmin')->first();
            if (! $voter) {
                return [
                    'success' => 0,
                    'failed' => 0,
                    'errors' => ['Tidak ada user Admin/SuperAdmin untuk dijadikan voter'],
                ];
            }
            $voterId = $voter->id;
        }

        // Get criteria for Pegawai Disiplin category
        $criteria = Criterion::where('category_id', $this->disciplineCategoryId)
            ->orderBy('urutan')
            ->get();

        if ($criteria->count() !== 3) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['Harus ada 3 kriteria untuk Pegawai Disiplin'],
            ];
        }

        // Map criteria by order
        $criterion1 = $criteria->firstWhere('urutan', 1); // Tingkat Kehadiran & Ketepatan Waktu
        $criterion2 = $criteria->firstWhere('urutan', 2); // Kedisiplinan
        $criterion3 = $criteria->firstWhere('urutan', 3); // Ketaatan

        DB::beginTransaction();
        try {
            foreach ($disciplineScores as $disciplineScore) {
                try {
                    $employee = $disciplineScore->first()?->employee;
                    if (! $employee) {
                        $failed++;
                        $errors[] = 'Employee tidak ditemukan untuk discipline score';

                        continue;
                    }

                    $startMonth = $this->getStartMonthForEmployee($employee, $targetYear);
                    $eligibleScores = $this->filterScoresByStartMonth($disciplineScore, $startMonth);

                    if ($eligibleScores->isEmpty()) {
                        $failed++;
                        $errors[] = "Tidak ada data disiplin untuk employee {$employee->id} pada rentang yang valid";

                        continue;
                    }

                    $avgScore1 = round((float) $eligibleScores->avg('score_1'), 2);
                    $avgScore2 = round((float) $eligibleScores->avg('score_2'), 2);
                    $avgScore3 = round((float) $eligibleScores->avg('score_3'), 2);
                    $totalScore = DisciplineScore::calculateFinalScore($avgScore1, $avgScore2, $avgScore3);
                    $earlyArrivalCount = $this->sumEarlyArrivalsForEmployee(
                        $earlyArrivalCountsByMonth[$employee->nip] ?? [],
                        $startMonth
                    );

                    // Check if vote already exists from ANY voter (not just current voter)
                    // This prevents duplicate votes for automatic discipline voting
                    $existingVote = Vote::where('period_id', $periodId)
                        ->where('employee_id', $employee->id)
                        ->where('category_id', $this->disciplineCategoryId)
                        ->first();

                    if ($existingVote && ! ($options['overwrite'] ?? false)) {
                        $existingVoter = \App\Models\User::find($existingVote->voter_id);
                        $errors[] = "Vote sudah ada untuk employee {$employee->id} (oleh voter: {$existingVote->voter_id})";
                        $failed++;

                        continue;
                    }

                    // Prepare scores array
                    $scores = [
                        [
                            'criterion_id' => $criterion1->id,
                            'score' => $avgScore1,
                        ],
                        [
                            'criterion_id' => $criterion2->id,
                            'score' => $avgScore2,
                        ],
                        [
                            'criterion_id' => $criterion3->id,
                            'score' => $avgScore3,
                        ],
                    ];

                    // Update or create vote
                    if ($existingVote && ($options['overwrite'] ?? false)) {
                        // Delete existing vote details
                        VoteDetail::where('vote_id', $existingVote->id)->delete();

                        // Update vote
                        $existingVote->update([
                            'scores' => $scores,
                            'total_score' => $totalScore,
                            'early_arrival_count' => $earlyArrivalCount,
                        ]);

                        $vote = $existingVote;
                    } else {
                        // Create new vote
                        $vote = Vote::create([
                            'period_id' => $periodId,
                            'voter_id' => $voterId,
                            'employee_id' => $employee->id,
                            'category_id' => $this->disciplineCategoryId,
                            'scores' => $scores,
                            'total_score' => $totalScore,
                            'early_arrival_count' => $earlyArrivalCount,
                        ]);
                    }

                    // Create vote details
                    foreach ($scores as $scoreData) {
                        VoteDetail::create([
                            'vote_id' => $vote->id,
                            'criterion_id' => $scoreData['criterion_id'],
                            'score' => $scoreData['score'],
                        ]);
                    }

                    $success++;
                } catch (\Exception $e) {
                    $errors[] = "Error untuk employee {$employee->id}: {$e->getMessage()}";
                    $failed++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => 0,
                'failed' => $disciplineScores->count(),
                'errors' => [$e->getMessage()],
            ];
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Get summary of discipline votes for a period
     */
    public function getSummary(int $periodId): array
    {
        $category = \App\Models\Category::find($this->disciplineCategoryId);

        $votes = Vote::where('period_id', $periodId)
            ->where('category_id', $this->disciplineCategoryId)
            ->with(['employee', 'voteDetails.criterion'])
            ->orderByDesc('total_score')
            ->orderByDesc('early_arrival_count')
            ->get();

        return [
            'category' => $category,
            'total_votes' => $votes->count(),
            'votes' => $votes,
            'average_score' => $votes->avg('total_score'),
        ];
    }

    /**
     * Check if discipline votes exist for a period
     */
    public function hasDisciplineVotes(int $periodId): bool
    {
        return Vote::where('period_id', $periodId)
            ->where('category_id', $this->disciplineCategoryId)
            ->exists();
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

            $month = $this->monthNameToNumber(strtolower($matches[1]));
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

    /**
     * Parse time cell value to minutes from midnight.
     */
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

    /**
     * @param  array<int, int>  $monthlyCounts
     */
    private function sumEarlyArrivalsForEmployee(array $monthlyCounts, int $startMonth): int
    {
        $total = 0;
        foreach ($monthlyCounts as $month => $count) {
            if ($month >= $startMonth) {
                $total += $count;
            }
        }

        return $total;
    }

    private function getStartMonthForEmployee(Employee $employee, int $year): int
    {
        if ($employee->golongan !== 'IX' || ! $employee->tmt) {
            return 1;
        }

        $tmtYear = (int) $employee->tmt->format('Y');
        if ($tmtYear !== $year) {
            return 1;
        }

        return (int) $employee->tmt->format('n');
    }

    /**
     * @param  Collection<int, DisciplineScore>  $scores
     * @return Collection<int, DisciplineScore>
     */
    private function filterScoresByStartMonth(Collection $scores, int $startMonth): Collection
    {
        return $scores->filter(function (DisciplineScore $score) use ($startMonth) {
            return (int) $score->month >= $startMonth;
        });
    }

    /**
     * @return array<int, string>
     */
    private function getExcludedPimpinanNips(): array
    {
        $path = base_path('docs/org_structure.json');
        if (! File::exists($path)) {
            return [];
        }

        $org = json_decode(File::get($path), true);
        if (! is_array($org)) {
            return [];
        }

        $nips = [];

        foreach ($org['pimpinan'] ?? [] as $pimpinan) {
            if (! empty($pimpinan['nip'])) {
                $nips[] = $pimpinan['nip'];
            }
        }

        if (! empty($org['panitera']['panitera']['nip'])) {
            $nips[] = $org['panitera']['panitera']['nip'];
        }

        if (! empty($org['sekretariat']['sekretaris']['nip'])) {
            $nips[] = $org['sekretariat']['sekretaris']['nip'];
        }

        return array_values(array_unique($nips));
    }

    private function monthNameToNumber(string $name): ?int
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
