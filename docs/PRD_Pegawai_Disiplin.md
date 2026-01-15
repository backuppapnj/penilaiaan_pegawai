# PRD: Perbaikan Kategori "Pegawai Disiplin"

## 1. Ringkasan

Dokumen ini mendefinisikan requirements untuk memperbaiki fungsi penilaian pada kategori "Pegawai Disiplin" yang saat ini tidak menampilkan daftar pegawai untuk dinilai.

**Status:** Draft
**Priority:** High
**Target Release:** Immediate

---

## 2. Problem Statement

### 2.1 Masalah Saat Ini

Kategori **"Pegawai Disiplin"** sudah dikonfigurasi dalam sistem dengan kriteria penilaian, namun saat user (Penilai) membuka halaman voting untuk kategori ini, **tidak ada pegawai yang muncul** sebagai kandidat untuk dinilai.

### 2.2 Root Cause

1. Kategori "Pegawai Disiplin" (ID: 3) dibuat di `CategoryAndCriteriaSeeder.php`
2. Namun di `EmployeeSeeder.php`, pegawai hanya di-assign ke kategori 1 atau 2
3. `VotingController.php` menggunakan query `->where('category_id', $category->id)` untuk mengambil daftar pegawai
4. Karena tidak ada pegawai dengan `category_id = 3`, query mengembalikan hasil kosong

---

## 3. Requirements

### 3.1 Functional Requirements

#### FR-1: Filter Pegawai untuk Kategori "Pegawai Disiplin"
Sistem harus menampilkan **semua pegawai KECUALI pimpinan** saat kategori "Pegawai Disiplin" dipilih.

**Pimpinan yang dikecualikan:**
- Ketua
- Wakil
- Panitera
- Sekretaris

**Catatan:** Hakim TIDAK dikecualikan (boleh dinilai dalam kategori ini)

#### FR-2: Kategori Lain Tetap Berfungsi Normal
Kategori "Pejabat Struktural/Fungsional" dan "Non-Pejabat" harus tetap berfungsi seperti sebelumnya, menggunakan filter `category_id`.

#### FR-3: Voting Berfungsi untuk Kategori Disiplin
User harus bisa melakukan voting untuk kategori "Pegawai Disiplin" dengan kriteria yang sudah didefinisikan:
- Tingkat Kehadiran & Ketepatan Waktu (50%)
- Kedisiplinan (Tanpa Pelanggaran) (35%)
- Ketaatan (Tanpa Izin Berlebih) (15%)

### 3.2 Non-Functional Requirements

#### NFR-1: Performance
Query tambahan untuk filter pegawai tidak boleh mempengaruhi performa aplikasi secara signifikan.

#### NFR-2: Backward Compatibility
Perubahan tidak boleh mempengaruhi fungsi voting untuk kategori yang sudah ada (kategori 1 dan 2).

---

## 4. Technical Specifications

### 4.1 File yang Dimodifikasi

| File | Lokasi | Perubahan |
|------|--------|-----------|
| `VotingController.php` | `app/Http/Controllers/` | Update method `show()` dengan conditional filter |

### 4.2 Implementasi Details

#### Location: `app/Http/Controllers/VotingController.php` - Method `show()`

**Current Implementation (baris 91-102):**
```php
$employees = Employee::with('category')
    ->where('category_id', $category->id)
    ->where('id', '!=', $employeeId)
    ->whereNotIn('id', $votedEmployeeIds)
    ->whereNotIn('jabatan', [...])
    ->get();
```

**New Implementation:**
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
                   ->whereNotIn('jabatan', [
                       'Ketua Pengadilan Tingkat Pertama Klas II',
                       'Wakil Ketua Tingkat Pertama',
                       'Hakim Tingkat Pertama',
                       'Panitera Tingkat Pertama Klas II',
                       'Sekretaris Tingkat Pertama Klas II',
                   ]);
}

$employees = $employeesQuery->get();
```

---

## 5. User Stories

### US-1: Penilai Melakukan Voting untuk Kategori Disiplin

**Sebagai:** Penilai
**Saya ingin:** Melihat daftar pegawai non-pimpinan saat memilih kategori "Pegawai Disiplin"
**Agar:** Saya bisa menilai kedisiplinan mereka berdasarkan kriteria yang sudah ditentukan

**Acceptance Criteria:**
1. Saat membuka halaman voting untuk kategori "Pegawai Disiplin", daftar pegawai muncul
2. Pimpinan (Ketua, Wakil, Panitera, Sekretaris) TIDAK muncul dalam daftar
3. Hakim BOLEH muncul dalam daftar
4. User bisa memberikan nilai untuk setiap kriteria
5. Nilai tersimpan dengan benar di database

---

## 6. Test Cases

### TC-1: Tampilkan Pegawai untuk Kategori Disiplin

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login sebagai Penilai | Berhasil login |
| 2 | Buka halaman Voting | Daftar kategori muncul |
| 3 | Klik kategori "Pegawai Disiplin" | Halaman voting untuk kategori disiplin muncul |
| 4 | Cek daftar pegawai | Daftar pegawai non-pimpinan muncul (bukan kosong) |
| 5 | Cek apakah pimpinan muncul | Pimpinan TIDAK muncul |
| 6 | Cek apakah Hakim muncul | Hakim BOLEH muncul |

### TC-2: Voting untuk Kategori Disiplin

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Pilih pegawai dari daftar | Pegawai terpilih |
| 2 | Isi nilai untuk setiap kriteria | Nilai terisi |
| 3 | Klik submit | Nilai berhasil disimpan |
| 4 | Cek riwayat voting | Voting muncul di riwayat |

### TC-3: Kategori Lain Tetap Berfungsi

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Buka kategori "Pejabat Struktural" | Daftar pegawai kategori 1 muncul |
| 2 | Buka kategori "Non-Pejabat" | Daftar pegawai kategori 2 muncul |
| 3 | Lakukan voting | Voting berfungsi normal |

---

## 7. UI/UX Considerations

### 7.1 Current UI
Tidak ada perubahan UI yang diperlukan. Perubahan hanya pada logika backend untuk filter pegawai.

### 7.2 User Feedback
Saat kategori "Pegawai Disiplin" dipilih dan pegawai muncul, user akan melihat daftar pegawai yang bisa dinilai seperti biasa.

---

## 8. Dependencies

| Dependency | Type | Status |
|------------|------|--------|
| Laravel 12.x | Framework | ✓ Installed |
| VotingController | Controller | ✓ Exists |
| Category "Pegawai Disiplin" | Data | ✓ Seeded |
| Kriteria Penilaian | Data | ✓ Seeded |

---

## 9. Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Filter `LIKE` mungkin lambat untuk data besar | Medium | Gunakan indexing pada kolom `jabatan` |
| Hardcoded nama kategori | Low | Tambahkan konstanta atau config untuk nama kategori |

---

## 10. Deployment Plan

### 10.1 Pre-Deployment
- [ ] Backup database
- [ ] Review code changes
- [ ] Run linter (`vendor/bin/pint`)
- [ ] Run tests (`php artisan test`)

### 10.2 Deployment
- [ ] Deploy code changes
- [ ] Clear cache (`php artisan cache:clear`)
- [ ] Clear config cache (`php artisan config:clear`)

### 10.3 Post-Deployment
- [ ] Test voting untuk kategori "Pegawai Disiplin"
- [ ] Test voting untuk kategori lain
- [ ] Monitor logs untuk error

---

## 11. Success Metrics

Perbaikan dianggap berhasil jika:
1. [ ] Halaman voting kategori "Pegawai Disiplin" menampilkan daftar pegawai
2. [ ] Pimpinan tidak muncul dalam daftar
3. [ ] Voting berhasil dilakukan dan tersimpan
4. [ ] Kategori lain tetap berfungsi normal
5. [ ] Tidak ada error di logs

---

## 12. Appendix

### 12.1 Related Documents
- [Laravel Boost Guidelines](../CLAUDE.md)
- [User Manual](./USER_MANUAL.md)

### 12.2 Changelog

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-01-15 | Claude | Initial PRD creation |

---

*End of PRD*
