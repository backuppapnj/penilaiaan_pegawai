<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'voter_id',
        'employee_id',
        'category_id',
        'scores',
        'total_score',
        'early_arrival_count',
    ];

    protected function casts(): array
    {
        return [
            'scores' => 'array',
            'total_score' => 'decimal:2',
            'voted_at' => 'datetime',
            'early_arrival_count' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function voteDetails(): HasMany
    {
        return $this->hasMany(VoteDetail::class);
    }
}
