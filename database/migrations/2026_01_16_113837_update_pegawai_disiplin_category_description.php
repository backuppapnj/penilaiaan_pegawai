<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newDescription = 'Kategori penilaian berdasarkan data absensi SIKEP. Termasuk semua pegawai KECUALI Pimpinan (Ketua, Wakil, Panitera, Sekretaris)';

        DB::table('categories')
            ->where('nama', 'Pegawai Disiplin')
            ->update(['deskripsi' => $newDescription]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $oldDescription = 'Kategori penilaian berdasarkan data absensi SIKEP. Termasuk semua pegawai KECUALI Pimpinan (Ketua, Wakil, Panitera, Sekretaris) dan Yudisial (Hakim)';

        DB::table('categories')
            ->where('nama', 'Pegawai Disiplin')
            ->update(['deskripsi' => $oldDescription]);
    }
};
