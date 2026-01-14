<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nip',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Check if user has specific role(s).
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Get dashboard route based on user role.
     */
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            'SuperAdmin' => 'super-admin.dashboard',
            'Admin' => 'admin.dashboard',
            'Penilai' => 'penilai.dashboard',
            'Peserta' => 'peserta.dashboard',
            default => 'dashboard',
        };
    }

    /**
     * Get the employee associated with the user.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the votes cast by the user.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'voter_id');
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === Role::SuperAdmin->value;
    }

    /**
     * Check if user is Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin->value;
    }

    /**
     * Check if user is Penilai.
     */
    public function isPenilai(): bool
    {
        return $this->role === Role::Penilai->value;
    }

    /**
     * Check if user is Peserta.
     */
    public function isPeserta(): bool
    {
        return $this->role === Role::Peserta->value;
    }

    /**
     * Check if user can vote.
     */
    public function canVote(): bool
    {
        return in_array($this->role, [
            Role::SuperAdmin->value,
            Role::Admin->value,
            Role::Penilai->value,
            Role::Peserta->value,
        ], true);
    }

    /**
     * Check if user is admin level (SuperAdmin or Admin).
     */
    public function isAdministrator(): bool
    {
        return in_array($this->role, [
            Role::SuperAdmin->value,
            Role::Admin->value,
        ], true);
    }

    /**
     * Check if user can participate in voting.
     */
    public function canParticipate(): bool
    {
        return in_array($this->role, [
            Role::Penilai->value,
            Role::Peserta->value,
        ], true);
    }
}
