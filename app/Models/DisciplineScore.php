<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $employee_id
 * @property int $period_id
 * @property int $total_work_days
 * @property int $present_on_time
 * @property int $leave_on_time
 * @property int $late_minutes
 * @property int $early_leave_minutes
 * @property int $excess_permission_count
 * @property float $score_1
 * @property float $score_2
 * @property float $score_3
 * @property float $final_score
 * @property int|null $rank
 * @property bool $is_winner
 * @property array|null $raw_data
 */
class DisciplineScore extends Model
{
    /** @use HasFactory<\Database\Factories\DisciplineScoreFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'period_id',
        'month',
        'year',
        'total_work_days',
        'present_on_time',
        'leave_on_time',
        'late_minutes',
        'early_leave_minutes',
        'excess_permission_count',
        'score_1',
        'score_2',
        'score_3',
        'final_score',
        'rank',
        'is_winner',
        'raw_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score_1' => 'decimal:2',
            'score_2' => 'decimal:2',
            'score_3' => 'decimal:2',
            'final_score' => 'decimal:2',
            'is_winner' => 'boolean',
            'raw_data' => 'array',
        ];
    }

    /**
     * Get the employee that owns the discipline score.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the period that owns the discipline score.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Calculate score 1: Tingkat Kehadiran & Ketepatan Waktu (50%)
     *
     * Formula: [(E + L) / (Total Hari Kerja × 2)] × 50
     */
    public static function calculateScore1(int $presentOnTime, int $leaveOnTime, int $totalWorkDays): float
    {
        if ($totalWorkDays === 0) {
            return 0.0;
        }

        $numerator = $presentOnTime + $leaveOnTime;
        $denominator = $totalWorkDays * 2;

        return ($numerator / $denominator) * 50;
    }

    /**
     * Calculate score 2: Kedisiplinan - Tanpa Pelanggaran (35%)
     *
     * Formula: [100 - Total Penalti (G-K, N-R)] × 0.35
     */
    public static function calculateScore2(float $lateMinutes, float $earlyLeaveMinutes): float
    {
        $totalPenalty = $lateMinutes + $earlyLeaveMinutes;

        return max(0, (100 - $totalPenalty) * 0.35);
    }

    /**
     * Calculate score 3: Ketaatan - Tanpa Izin Berlebih (15%)
     *
     * Formula: Jika ada izin berlebih: 0, jika tidak: 15
     */
    public static function calculateScore3(int $excessPermissionCount): float
    {
        return $excessPermissionCount > 0 ? 0.0 : 15.0;
    }

    /**
     * Calculate final score
     *
     * Formula: Score 1 + Score 2 + Score 3
     */
    public static function calculateFinalScore(float $score1, float $score2, float $score3): float
    {
        return round($score1 + $score2 + $score3, 2);
    }
}
