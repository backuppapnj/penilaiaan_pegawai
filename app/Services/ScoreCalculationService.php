<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Criterion;
use App\Models\Employee;
use App\Models\Period;
use App\Models\Score;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class ScoreCalculationService
{
    /**
     * Calculate scores for a specific period and category.
     */
    public function calculateScores(Period $period, Category $category): void
    {
        $employees = Employee::where('category_id', $category->id)->get();
        $criteria = $category->criteria;

        foreach ($employees as $employee) {
            $this->calculateEmployeeScore($period, $category, $employee, $criteria);
        }

        $this->determineWinner($period, $category);
    }

    /**
     * Calculate weighted score for a specific employee.
     */
    protected function calculateEmployeeScore(Period $period, Category $category, Employee $employee, $criteria): void
    {
        $votes = Vote::where('period_id', $period->id)
            ->where('employee_id', $employee->id)
            ->where('category_id', $category->id)
            ->get();

        if ($votes->isEmpty()) {
            return;
        }

        $scoreDetails = [];
        $totalWeightedScore = 0;

        foreach ($criteria as $criterion) {
            $averageScore = $votes->avg(function ($vote) use ($criterion) {
                $scores = collect($vote->scores);
                $criterionScore = $scores->firstWhere('criterion_id', $criterion->id);

                return $criterionScore['score'] ?? 0;
            });

            $weightedScore = ($averageScore * $criterion->bobot) / 100;
            $totalWeightedScore += $weightedScore;

            $scoreDetails[$criterion->id] = [
                'criterion_id' => $criterion->id,
                'criterion_name' => $criterion->nama,
                'average_score' => round($averageScore, 2),
                'weight' => $criterion->bobot,
                'weighted_score' => round($weightedScore, 2),
            ];
        }

        Score::updateOrCreate(
            [
                'period_id' => $period->id,
                'employee_id' => $employee->id,
                'category_id' => $category->id,
            ],
            [
                'weighted_score' => round($totalWeightedScore, 2),
                'score_details' => $scoreDetails,
                'rank' => null,
                'is_winner' => false,
            ]
        );
    }

    /**
     * Determine the winner for a category in a period.
     */
    public function determineWinner(Period $period, Category $category): void
    {
        $scores = Score::where('period_id', $period->id)
            ->where('category_id', $category->id)
            ->orderByDesc('weighted_score')
            ->get();

        $rank = 1;
        $previousScore = null;
        $scoreCount = 0;

        foreach ($scores as $index => $score) {
            $scoreCount++;

            if ($previousScore !== null && $score->weighted_score < $previousScore) {
                $rank = $scoreCount;
            }

            $score->update(['rank' => $rank]);
            $previousScore = $score->weighted_score;
        }

        $winner = $scores->first();

        if ($winner) {
            $winner->update(['is_winner' => true]);
        }
    }

    /**
     * Calculate scores for all categories in a period.
     */
    public function calculateAllScores(Period $period): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            $this->calculateScores($period, $category);
        }
    }

    /**
     * Recalculate all scores for a period.
     */
    public function recalculateScores(Period $period): void
    {
        DB::transaction(function () use ($period) {
            Score::where('period_id', $period->id)->delete();

            $this->calculateAllScores($period);
        });
    }

    /**
     * Get ranking for a category in a period.
     */
    public function getRanking(Period $period, Category $category)
    {
        return Score::where('period_id', $period->id)
            ->where('category_id', $category->id)
            ->with('employee')
            ->orderBy('rank')
            ->get();
    }

    /**
     * Get winner for a category in a period.
     */
    public function getWinner(Period $period, Category $category): ?Score
    {
        return Score::where('period_id', $period->id)
            ->where('category_id', $category->id)
            ->where('is_winner', true)
            ->with('employee')
            ->first();
    }
}
