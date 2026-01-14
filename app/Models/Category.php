<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'deskripsi',
        'urutan',
    ];

    /**
     * Get the employees for the category.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the criteria for the category.
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(Criterion::class)->orderBy('urutan');
    }

    /**
     * Get the votes for the category.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the scores for the category.
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Get the certificates for the category.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
