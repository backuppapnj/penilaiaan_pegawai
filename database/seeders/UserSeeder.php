<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['nip' => '199107132020121003'],
            [
                'name' => 'Muhardiansyah',
                'email' => 'muhardiansyah@pa-penajam.go.id',
                'password' => bcrypt('199107132020121003'),
                'role' => 'SuperAdmin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['nip' => '199605112025212037'],
            [
                'name' => 'Najwa Hijriana',
                'email' => 'najwa.hijriana@pa-penajam.go.id',
                'password' => bcrypt('199605112025212037'),
                'role' => 'Admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['nip' => '199702232022032013'],
            [
                'name' => 'Nur Rizka Fani',
                'email' => 'nur.rizka.fani@pa-penajam.go.id',
                'password' => bcrypt('199702232022032013'),
                'role' => 'Penilai',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['nip' => '199702012022031004'],
            [
                'name' => 'Muhammad Ilham',
                'email' => 'muhammad.ilham@pa-penajam.go.id',
                'password' => bcrypt('199702012022031004'),
                'role' => 'Peserta',
                'is_active' => true,
            ]
        );
    }
}
