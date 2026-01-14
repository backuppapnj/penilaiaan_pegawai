<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'SuperAdmin';
    case Admin = 'Admin';
    case Penilai = 'Penilai';
    case Peserta = 'Peserta';

    /**
     * Get the display label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Penilai => 'Penilai',
            self::Peserta => 'Peserta',
        };
    }

    /**
     * Get all available roles as an array.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * Get the role display names as an array.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn ($case) => $case->label(), self::cases())
        );
    }

    /**
     * Check if the role can vote.
     */
    public function canVote(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Penilai, self::Peserta]);
    }

    /**
     * Check if the role can participate in voting.
     */
    public function canParticipate(): bool
    {
        return in_array($this, [self::Penilai, self::Peserta]);
    }

    /**
     * Check if the role is admin level.
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin]);
    }
}
