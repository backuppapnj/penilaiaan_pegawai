<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class EmployeeSeeder extends Seeder
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

        $kategori1 = Category::where('nama', 'Pejabat Struktural/Fungsional')->first();
        $kategori2 = Category::where('nama', 'Non-Pejabat')->first();

        foreach ($employees as $data) {
            $jabatan = $data['jabatan'];
            $categoryId = null;

            // Logika Mapping Kategori
            if (
                str_contains($jabatan, 'Ketua') ||
                str_contains($jabatan, 'Wakil') ||
                str_contains($jabatan, 'Hakim') ||
                str_contains($jabatan, 'Panitera') || // Covers Panitera, Panitera Muda, Panitera Pengganti
                str_contains($jabatan, 'Sekretaris') ||
                str_contains($jabatan, 'Kepala Subbagian') ||
                str_contains($jabatan, 'Juru Sita') ||
                str_contains($jabatan, 'Pranata Komputer')
            ) {
                $categoryId = $kategori1?->id;
            } else {
                $categoryId = $kategori2?->id;
            }

            $employee = Employee::updateOrCreate(
                ['nip' => $data['nip']],
                [
                    'nama' => $data['nama'],
                    'jabatan' => $data['jabatan'],
                    'unit_kerja' => $data['unit_kerja'],
                    // Mapping Golongan dan TMT dari JSON (format TMT di JSON: "13 Mei 2024", perlu convert date)
                    'golongan' => $data['gol'],
                    'tmt' => $this->parseIndonesianDate($data['tmt']),
                    'category_id' => $categoryId,
                ]
            );

            User::where('nip', $data['nip'])->update([
                'employee_id' => $employee->id,
            ]);
        }

        $this->command->info('Employees seeded successfully from JSON.');
    }

    private function parseIndonesianDate($dateString)
    {
        // Contoh: "13 Mei 2024"
        $months = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
        ];

        foreach ($months as $indo => $num) {
            $dateString = str_replace($indo, $num, $dateString);
        }

        // Sekarang formatnya "13 05 2024", ubah ke Y-m-d
        try {
            return \Carbon\Carbon::createFromFormat('d m Y', $dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // Fallback jika format error
        }
    }
}
