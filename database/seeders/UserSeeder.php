<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('docs/data_pegawai.json');

        if (! File::exists($jsonPath)) {
            $this->command->error('File docs/data_pegawai.json not found!');

            return;
        }

        $json = File::get($jsonPath);
        $employees = json_decode($json, true);

        if (! $employees) {
            $this->command->error('Failed to decode data_pegawai.json');

            return;
        }

        // Mapping NIP ke Role secara spesifik
        $roleMapping = [
            // SuperAdmin
            '199107132020121003' => 'SuperAdmin', // Muhardiansyah - Pranata Komputer

            // Admin
            '199605112025212037' => 'Admin', // Najwa Hijriana - Operator
            '198301042006042003' => 'Admin', // Indra Yanita Yuliana - Sekretaris

            // Pimpinan (Bukan Peserta - hanya Penilai)
            '198505092009041006' => 'Penilai', // Fattahurridlo Al Ghany - Ketua
            '198410092007042001' => 'Penilai', // Nahdiyanti - Wakil Ketua
            '197503141996031002' => 'Penilai', // Muhammad Hamdi - Panitera
        ];

        $inactiveNips = [
            '198009202002121004',
            '198604122017122001',
        ];

        $count = 0;
        foreach ($employees as $data) {
            $nip = $data['nip'];
            $nama = $data['nama'];

            // Tentukan role dari mapping atau default ke Peserta
            $role = $roleMapping[$nip] ?? 'Peserta';

            // Generate email dari nama
            $email = $this->generateEmail($nama);

            $user = User::updateOrCreate(
                ['nip' => $nip],
                [
                    'name' => $nama,
                    'email' => $email,
                    'password' => bcrypt($nip), // Password default = NIP
                    'role' => $role,
                    'is_active' => ! in_array($nip, $inactiveNips, true),
                ]
            );

            $employee = Employee::where('nip', $nip)->first();
            if ($employee) {
                $user->employee_id = $employee->id;
                $user->save();
            }

            $count++;
        }

        $this->command->info("Successfully seeded {$count} users from data_pegawai.json");
        $this->command->info('Role breakdown:');
        $this->command->info('  - SuperAdmin: 1 (Muhardiansyah)');
        $this->command->info('  - Admin: 2 (Najwa Hijriana, Sekretaris)');
        $this->command->info('  - Penilai (Pimpinan): 3 (Ketua, Wakil Ketua, Panitera)');
        $this->command->info('  - Peserta (yang dinilai): 23 (semua pegawai kecuali pimpinan)');
        $this->command->info('');
        $this->command->info('Keterangan:');
        $this->command->info('  - SEMUA 29 pegawai dapat menjadi PENILAI (mengisi nilai)');
        $this->command->info('  - Hanya 23 pegawai (bukan pimpinan) yang menjadi PESERTA (dinilai)');
        $this->command->info('  - Kategori Peserta diatur di EmployeeSeeder:');
        $this->command->info('    * Pejabat Struktural/Fungsional: 9 pegawai');
        $this->command->info('    * Non-Pejabat: 14 pegawai');
    }

    /**
     * Generate email dari nama lengkap
     * Format: first.last@pa-penajam.go.id
     */
    private function generateEmail(string $nama): string
    {
        // Hapus gelar dan titik
        $nama = preg_replace(
            '/\b[Ss][Kk][Hh]\.?|\b[Ss]\.?[Hh]\.?|\b[Ss][Tt]\.?|\bM\.?[Ss]\.?|\bM\.?[Hh]\.?|\bM\.?[Kk][Nn]\.?|\bMm?\.?|\b[Aa]\.?[Mm]\.?[Bb]\.?|\b[Aa]\.?[Mm]\.?[Dd]\.?|\b[Aa]\.?[Mm]\.?[Kk]\.?|\b[A-Z]\.?/i',
            '',
            $nama
        );

        // Hapus koma
        $nama = str_replace(',', '', $nama);

        // Convert ke lowercase dan trim
        $nama = strtolower(trim($nama));

        // Split menjadi kata-kata
        $words = explode(' ', $nama);

        // Filter kata kosong
        $words = array_filter($words, fn ($word) => $word !== '');

        // Jika hanya 1 kata
        if (count($words) === 1) {
            return $words[0].'@pa-penajam.go.id';
        }

        // Jika 2 kata: first.last
        if (count($words) === 2) {
            return $words[0].'.'.$words[1].'@pa-penajam.go.id';
        }

        // Jika lebih dari 2 kata: first.middle.last
        $first = $words[0];
        $last = end($words);

        // Gabungkan kata tengah jika ada
        $middle = implode('', array_slice($words, 1, -1));

        if ($middle) {
            return $first.'.'.$middle.'.'.$last.'@pa-penajam.go.id';
        }

        return $first.'.'.$last.'@pa-penajam.go.id';
    }
}
