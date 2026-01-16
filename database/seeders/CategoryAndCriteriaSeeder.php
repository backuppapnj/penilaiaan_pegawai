<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Criterion;
use Illuminate\Database\Seeder;

class CategoryAndCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Category 1: Pejabat Struktural/Fungsional (14 orang)
        $kategori1 = Category::create([
            'nama' => 'Pejabat Struktural/Fungsional',
            'deskripsi' => 'Kategori untuk pejabat struktural (Ketua, Wakil, Panitera, Sekretaris, Panitera Muda, Kasubag) dan fungsional (Hakim, Panitera Pengganti, Pranata Komputer, Juru Sita)',
            'urutan' => 1,
        ]);

        // Kriteria Kategori 1
        $criteriaKategori1 = [
            ['nama' => 'Kepemimpinan', 'bobot' => 25.00, 'urutan' => 1],
            ['nama' => 'Manajemen SDM & Organisasi', 'bobot' => 20.00, 'urutan' => 2],
            ['nama' => 'Inovasi & Peningkatan Pelayanan', 'bobot' => 15.00, 'urutan' => 3],
            ['nama' => 'Integritas & Etika', 'bobot' => 15.00, 'urutan' => 4],
            ['nama' => 'Kerjasama & Koordinasi', 'bobot' => 10.00, 'urutan' => 5],
            ['nama' => 'Pencapaian Target Unit', 'bobot' => 10.00, 'urutan' => 6],
            ['nama' => 'Pengembangan Kompetensi Bawahan', 'bobot' => 5.00, 'urutan' => 7],
        ];

        foreach ($criteriaKategori1 as $criterion) {
            Criterion::create(array_merge($criterion, ['category_id' => $kategori1->id]));
        }

        // Category 2: Non-Pejabat (15 orang)
        $kategori2 = Category::create([
            'nama' => 'Non-Pejabat',
            'deskripsi' => 'Kategori untuk staff/anggota non-kepala (Klerek, Operator, Teknisi, dll)',
            'urutan' => 2,
        ]);

        // Kriteria Kategori 2
        $criteriaKategori2 = [
            ['nama' => 'Kinerja & Produktivitas', 'bobot' => 25.00, 'urutan' => 1],
            ['nama' => 'Kedisiplinan & Kehadiran', 'bobot' => 20.00, 'urutan' => 2],
            ['nama' => 'Pelayanan Prima', 'bobot' => 15.00, 'urutan' => 3],
            ['nama' => 'Kerjasama Tim', 'bobot' => 15.00, 'urutan' => 4],
            ['nama' => 'Integritas & Tanggung Jawab', 'bobot' => 10.00, 'urutan' => 5],
            ['nama' => 'Inisiatif & Kreativitas', 'bobot' => 10.00, 'urutan' => 6],
            ['nama' => 'Pengembangan Diri', 'bobot' => 5.00, 'urutan' => 7],
        ];

        foreach ($criteriaKategori2 as $criterion) {
            Criterion::create(array_merge($criterion, ['category_id' => $kategori2->id]));
        }

        // Category 3: Pegawai Disiplin (25 orang, excluding pimpinan)
        $kategori3 = Category::create([
            'nama' => 'Pegawai Disiplin',
            'deskripsi' => 'Kategori penilaian berdasarkan data absensi SIKEP. Termasuk semua pegawai KECUALI Pimpinan (Ketua, Wakil, Panitera, Sekretaris)',
            'urutan' => 3,
        ]);

        // Kriteria Kategori 3 (berdasarkan data SIKEP)
        $criteriaKategori3 = [
            ['nama' => 'Tingkat Kehadiran & Ketepatan Waktu', 'bobot' => 50.00, 'urutan' => 1],
            ['nama' => 'Kedisiplinan (Tanpa Pelanggaran)', 'bobot' => 35.00, 'urutan' => 2],
            ['nama' => 'Ketaatan (Tanpa Izin Berlebih)', 'bobot' => 15.00, 'urutan' => 3],
        ];

        foreach ($criteriaKategori3 as $criterion) {
            Criterion::create(array_merge($criterion, ['category_id' => $kategori3->id]));
        }

        $this->command->info('Categories and criteria seeded successfully.');
    }
}
