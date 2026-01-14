<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nip',
        'nama',
        'jabatan',
        'unit_kerja',
        'golongan',
        'tmt',
        'category_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tmt' => 'date',
        ];
    }

    /**
     * Get the user associated with the employee.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Get the category that the employee belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the votes received by the employee.
     */
    public function votesReceived(): HasMany
    {
        return $this->hasMany(Vote::class, 'employee_id');
    }

    /**
     * Get the votes cast by the employee.
     */
    public function votesCast(): HasMany
    {
        return $this->hasMany(Vote::class, 'voter_id');
    }

    /**
     * Get the scores for the employee.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Get the certificates for the employee.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Get the discipline scores for the employee.
     */
    public function disciplineScores(): HasMany
    {
        return $this->hasMany(DisciplineScore::class);
    }
}
