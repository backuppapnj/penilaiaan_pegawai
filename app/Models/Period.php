<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'semester',
        'year',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isAnnounced(): bool
    {
        return $this->status === 'announced';
    }

    /**
     * Get the discipline scores for the period.
     */
    public function disciplineScores(): HasMany
    {
        return $this->hasMany(DisciplineScore::class);
    }

    /**
     * Get the certificates for the period.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
