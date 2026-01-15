# Ralph Loop: Fix Kategori Pegawai Disiplin

## Context
Kamu sedang bekerja pada proyek Laravel voting system bernama "vote_system". Terdapat masalah pada kategori "Pegawai Disiplin" di mana halaman voting tidak menampilkan daftar pegawai untuk dinilai.

## Problem Statement

### Masalah
Kategori **"Pegawai Disiplin"** sudah dikonfigurasi dengan ID 3, namun saat user membuka halaman voting untuk kategori ini, **tidak ada pegawai yang muncul** sebagai kandidat untuk dinilai.

### Root Cause
1. Kategori "Pegawai Disiplin" (ID: 3) dibuat di `CategoryAndCriteriaSeeder.php`
2. Namun di `EmployeeSeeder.php`, pegawai hanya di-assign ke kategori 1 atau 2
3. `VotingController.php` menggunakan query `->where('category_id', $category->id)` untuk mengambil daftar pegawai
4. Karena tidak ada pegawai dengan `category_id = 3`, query mengembalikan hasil kosong

## Requirements

### FR-1: Filter Pegawai untuk Kategori "Pegawai Disiplin"
Sistem harus menampilkan **semua pegawai KECUALI pimpinan** saat kategori "Pegawai Disiplin" dipilih.

**Pimpinan yang dikecualikan:**
- Ketua
- Wakil
- Panitera
- Sekretaris

**Catatan:** Hakim TIDAK dikecualikan (boleh dinilai dalam kategori ini)

### FR-2: Kategori Lain Tetap Berfungsi Normal
Kategori "Pejabat Struktural/Fungsional" dan "Non-Pejabat" harus tetap berfungsi seperti sebelumnya, menggunakan filter `category_id`.

### FR-3: Voting Berfungsi untuk Kategori Disiplin
User harus bisa melakukan voting untuk kategori "Pegawai Disiplin" dengan kriteria yang sudah didefinisikan:
- Tingkat Kehadiran & Ketepatan Waktu (50%)
- Kedisiplinan (Tanpa Pelanggaran) (35%)
- Ketaatan (Tanpa Izin Berlebih) (15%)

## Implementation Task

### File to Modify
`app/Http/Controllers/VotingController.php` - Method `show()`

### Current Implementation Pattern (around line 91-102)
```php
$employees = Employee::with('category')
    ->where('category_id', $category->id)
    ->where('id', '!=', $employeeId)
    ->whereNotIn('id', $votedEmployeeIds)
    ->whereNotIn('jabatan', [...])
    ->get();
```

### Required Implementation
1. Baca file `VotingController.php` terlebih dahulu
2. Modifikasi method `show()` untuk menggunakan **conditional filter**:
   - Jika kategori adalah "Pegawai Disiplin": filter dengan `WHERE jabatan NOT LIKE '%Ketua%' AND NOT LIKE '%Wakil%' AND NOT LIKE '%Panitera%' AND NOT LIKE '%Sekretaris%'`
   - Jika kategori lain: gunakan filter `category_id` seperti biasa

3. Struktur yang diharapkan:
```php
$employeesQuery = Employee::with('category')
    ->where('id', '!=', $employeeId)
    ->whereNotIn('id', $votedEmployeeIds);

// Conditional filter berdasarkan kategori
if ($category->nama === 'Pegawai Disiplin') {
    // Filter dinamis untuk kategori disiplin
    $employeesQuery->where('jabatan', 'not like', '%Ketua%')
                   ->where('jabatan', 'not like', '%Wakil%')
                   ->where('jabatan', 'not like', '%Panitera%')
                   ->where('jabatan', 'not like', '%Sekretaris%');
} else {
    // Filter standar untuk kategori lain
    $employeesQuery->where('category_id', $category->id)
                   ->whereNotIn('jabatan', [/* existing exclusions */]);
}

$employees = $employeesQuery->get();
```

## Acceptance Criteria

1. [ ] Halaman voting kategori "Pegawai Disiplin" menampilkan daftar pegawai
2. [ ] Pimpinan (Ketua, Wakil, Panitera, Sekretaris) TIDAK muncul dalam daftar
3. [ ] Hakim BOLEH muncul dalam daftar
4. [ ] User bisa memberikan nilai untuk setiap kriteria
5. [ ] Nilai tersimpan dengan benar di database
6. [ ] Kategori lain (1 dan 2) tetap berfungsi normal
7. [ ] Tidak ada error di logs

## Quality Gates

Sebelum menyelesaikan task, pastikan:
1. Jalankan `vendor/bin/pint --dirty` untuk code formatting
2. Jalankan `php artisan test --compact` untuk memastikan tidak ada test yang broke
3. Jika ada file test yang relevan, tambahkan test case untuk kategori "Pegawai Disiplin"

## Working Directory
`/home/moohard/project/penilaiaan_pegawai`

## Success Promise
Output `<promise>PEGAWAI_DISIPLIN_FIXED</promise>` ketika semua acceptance criteria terpenuhi dan task selesai.
