<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\DisciplineScore;
use App\Models\Period;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteDetail;
use Illuminate\Support\Facades\DB;

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

        // Get discipline scores for this period (or without period)
        $disciplineScores = DisciplineScore::where('period_id', $periodId)
            ->orWhereNull('period_id')
            ->get();

        if ($disciplineScores->isEmpty()) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['Tidak ada data discipline_scores untuk periode ini'],
            ];
        }

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
                    // Check if vote already exists
                    $existingVote = Vote::where('period_id', $periodId)
                        ->where('voter_id', $voterId)
                        ->where('employee_id', $disciplineScore->employee_id)
                        ->where('category_id', $this->disciplineCategoryId)
                        ->first();

                    if ($existingVote && ! ($options['overwrite'] ?? false)) {
                        $errors[] = "Vote sudah ada untuk employee {$disciplineScore->employee_id}";
                        $failed++;

                        continue;
                    }

                    // Prepare scores array
                    $scores = [
                        [
                            'criterion_id' => $criterion1->id,
                            'score' => (float) $disciplineScore->score_1,
                        ],
                        [
                            'criterion_id' => $criterion2->id,
                            'score' => (float) $disciplineScore->score_2,
                        ],
                        [
                            'criterion_id' => $criterion3->id,
                            'score' => (float) $disciplineScore->score_3,
                        ],
                    ];

                    $totalScore = (float) $disciplineScore->final_score;

                    // Update or create vote
                    if ($existingVote && ($options['overwrite'] ?? false)) {
                        // Delete existing vote details
                        VoteDetail::where('vote_id', $existingVote->id)->delete();

                        // Update vote
                        $existingVote->update([
                            'scores' => $scores,
                            'total_score' => $totalScore,
                        ]);

                        $vote = $existingVote;
                    } else {
                        // Create new vote
                        $vote = Vote::create([
                            'period_id' => $periodId,
                            'voter_id' => $voterId,
                            'employee_id' => $disciplineScore->employee_id,
                            'category_id' => $this->disciplineCategoryId,
                            'scores' => $scores,
                            'total_score' => $totalScore,
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
                    $errors[] = "Error untuk employee {$disciplineScore->employee_id}: {$e->getMessage()}";
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
}
