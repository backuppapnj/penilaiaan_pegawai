<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'employee_id',
        'category_id',
        'weighted_score',
        'rank',
        'is_winner',
        'score_details',
    ];

    protected function casts(): array
    {
        return [
            'weighted_score' => 'decimal:2',
            'score_details' => 'array',
            'is_winner' => 'boolean',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByPeriod($query, $periodId)
    {
        return $query->where('period_id', $periodId);
    }

    public function scopeRanked($query)
    {
        return $query->orderBy('rank');
    }
}
