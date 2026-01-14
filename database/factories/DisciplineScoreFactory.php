<?php

namespace Database\Factories;

use App\Models\DisciplineScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DisciplineScore>
 */
class DisciplineScoreFactory extends Factory
{
    protected $model = DisciplineScore::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalWorkDays = fake()->numberBetween(15, 23);
        $presentOnTime = fake()->numberBetween(10, $totalWorkDays);
        $leaveOnTime = fake()->numberBetween(10, $totalWorkDays);
        $lateMinutes = fake()->numberBetween(0, 100);
        $earlyLeaveMinutes = fake()->numberBetween(0, 50);
        $excessPermissionCount = fake()->numberBetween(0, 5);

        $score1 = DisciplineScore::calculateScore1($presentOnTime, $leaveOnTime, $totalWorkDays);
        $score2 = DisciplineScore::calculateScore2($lateMinutes, $earlyLeaveMinutes);
        $score3 = DisciplineScore::calculateScore3($excessPermissionCount);
        $finalScore = DisciplineScore::calculateFinalScore($score1, $score2, $score3);

        return [
            'employee_id' => fake()->numberBetween(1, 30),
            'period_id' => fake()->numberBetween(1, 10),
            'total_work_days' => $totalWorkDays,
            'present_on_time' => $presentOnTime,
            'leave_on_time' => $leaveOnTime,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'excess_permission_count' => $excessPermissionCount,
            'score_1' => $score1,
            'score_2' => $score2,
            'score_3' => $score3,
            'final_score' => $finalScore,
            'rank' => fake()->optional()->numberBetween(1, 30),
            'is_winner' => fake()->boolean(10), // 10% chance of being winner
            'raw_data' => null,
        ];
    }
}
