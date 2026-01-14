<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_id',
        'employee_id',
        'period_id',
        'category_id',
        'rank',
        'score',
        'qr_code_path',
        'pdf_path',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getVerificationUrlAttribute(): string
    {
        return url("/verify/{$this->certificate_id}");
    }

    public function getQrCodeUrlAttribute(): string
    {
        return $this->qr_code_path
            ? Storage::url($this->qr_code_path)
            : '';
    }

    protected $appends = [
        'verification_url',
        'qr_code_url',
    ];
}
