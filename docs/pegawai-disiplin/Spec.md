# Spec: Perbaikan Kategori "Pegawai Disiplin"

## 1. Functional Requirements

### FR-1: Dynamic Filter untuk Kategori "Pegawai Disiplin"

**Priority:** P0 (Critical)
**Status:** Pending Implementation

**Description:**
Sistem harus menggunakan dynamic filter berdasarkan `jabatan` saat menampilkan pegawai untuk kategori "Pegawai Disiplin", bukan menggunakan `category_id`.

**Filter Logic:**
- **Include:** Semua pegawai
- **Exclude:** Pegawai dengan jabatan yang mengandung:
  - "Ketua"
  - "Wakil"
  - "Panitera"
  - "Sekretaris"

**Note:** Hakim TIDAK di-exclude

**Acceptance Criteria:**
- [ ] Saat kategori "Pegawai Disiplin" dipilih, daftar pegawai muncul (bukan kosong)
- [ ] Pegawai dengan jabatan mengandung "Ketua" tidak muncul
- [ ] Pegawai dengan jabatan mengandung "Wakil" tidak muncul
- [ ] Pegawai dengan jabatan mengandung "Panitera" tidak muncul
- [ ] Pegawai dengan jabatan mengandung "Sekretaris" tidak muncul
- [ ] Pegawai dengan jabatan "Hakim" BOLEH muncul
- [ ] Jumlah pegawai yang muncul = Total pegawai - Pimpinan - User yang sedang login - Sudah divoting

---

### FR-2: Backward Compatibility untuk Kategori Lain

**Priority:** P0 (Critical)
**Status:** Pending Implementation

**Description:**
Kategori "Pejabat Struktural/Fungsional" dan "Non-Pejabat" harus tetap berfungsi menggunakan filter `category_id` seperti sebelumnya.

**Acceptance Criteria:**
- [ ] Kategori "Pejabat Struktural/Fungsional" menampilkan pegawai dengan `category_id = 1`
- [ ] Kategori "Non-Pejabat" menampilkan pegawai dengan `category_id = 2`
- [ ] Behavior sebelumnya untuk filter pimpinan tetap berlaku
- [ ] Voting untuk kategori 1 dan 2 berfungsi normal

---

### FR-3: Voting Berfungsi untuk Kategori Disiplin

**Priority:** P0 (Critical)
**Status:** Pending Implementation

**Description:**
User harus bisa melakukan voting untuk kategori "Pegawai Disiplin" dengan menyimpan nilai untuk setiap kriteria.

**Kriteria Penilaian:**
1. Tingkat Kehadiran & Ketepatan Waktu (bobot: 50%)
2. Kedisiplinan (Tanpa Pelanggaran) (bobot: 35%)
3. Ketaatan (Tanpa Izin Berlebih) (bobot: 15%)

**Acceptance Criteria:**
- [ ] Form voting muncul dengan 3 kriteria penilaian
- [ ] User bisa memilih pegawai dari daftar
- [ ] User bisa mengisi nilai untuk setiap kriteria
- [ ] Submit berhasil menyimpan data ke database
- [ ] Vote tersimpan dengan `category_id = 3`
- [ ] Riwayat voting menampilkan vote yang baru dibuat

---

## 2. Non-Functional Requirements

### NFR-1: Performance

**Priority:** P1 (High)

**Requirement:**
Query tambahan untuk dynamic filter tidak boleh menambah load time lebih dari 100ms dibanding sebelumnya.

**Acceptance Criteria:**
- [ ] Response time halaman voting < 500ms
- [ ] Tidak ada N+1 query problem
- [ ] Query plan untuk filter `jabatan` menggunakan index (jika ada)

---

### NFR-2: Code Quality

**Priority:** P1 (High)

**Requirement:**
Code harus mengikuti standar coding project (Laravel Pint, Pest testing).

**Acceptance Criteria:**
- [ ] Code lulus `vendor/bin/pint --dirty`
- [ ] Test coverage untuk VotingController > 80%
- [ ] Tidak ada hardcoded magic strings

---

### NFR-3: Maintainability

**Priority:** P2 (Medium)

**Requirement:**
Code harus mudah dipahami dan dimodifikasi di masa depan.

**Acceptance Criteria:**
- [ ] Nama kategori tidak hardcoded di多处 (use constant or config)
- [ ] Comment untuk conditional logic
- [ ] Variable naming yang jelas

---

## 3. Data Requirements

### DR-1: Filter Rules untuk Jabatan

| Pattern | Keterangan | Contoh |
|---------|-----------|--------|
| `%Ketua%` | Exclude semua jabatan dengan "Ketua" | Ketua Pengadilan..., Ketua... |
| `%Wakil%` | Exclude semua jabatan dengan "Wakil" | Wakil Ketua... |
| `%Panitera%` | Exclude semua jabatan dengan "Panitera" | Panitera, Panitera Muda, Panitera Pengganti |
| `%Sekretaris%` | Exclude semua jabatan dengan "Sekretaris" | Sekretaris... |

**Note:** Pattern menggunakan SQL LIKE operator (case-insensitive di MySQL)

---

## 4. Interface Requirements

### IR-1: No UI Changes Required

**Description:**
Perubahan hanya pada logika backend. Tidak ada perubahan pada frontend/React components.

**Rationale:**
- Frontend sudah menerima daftar pegawai dari props
- Dynamic filter dilakukan di backend sebelum data dikirim ke frontend
- Inertia akan merender data yang diterima

---

## 5. Security Requirements

### SR-1: Authorization Tidak Berubah

**Description:**
Hanya user dengan role "Penilai", "Admin", atau "SuperAdmin" yang bisa mengakses halaman voting.

**Acceptance Criteria:**
- [ ] Middleware 'role:Penilai,Peserta,Admin,SuperAdmin' tetap berlaku
- [ ] User dengan role "Peserta" hanya bisa melihat, tidak bisa voting (jika applicable)

---

### SR-2: Vote Integrity

**Description:**
Satu user hanya bisa voting satu kali per pegawai per kategori per periode.

**Acceptance Criteria:**
- [ ] Unique constraint `(period_id, voter_id, employee_id, category_id)` di tabel `votes` tetap berlaku
- [ ] Pegawai yang sudah divoting tidak muncul di daftar
- [ ] Tidak bisa submit vote untuk pegawai yang sama

---

## 6. Test Specifications

### TS-1: Unit Test untuk Dynamic Filter

**File:** `tests/Unit/VotingControllerTest.php` (baru)

```php
it('filters out pimpinan for pegawai disiplin category', function () {
    // Arrange: Create employees with various jabatan
    // Act: Call show() with pegawai disiplin category
    // Assert: Pimpinan not in results, others in results
});
```

---

### TS-2: Feature Test untuk Voting Flow

**File:** `tests/Feature/VotingTest.php`

```php
it('allows voting for pegawai disiplin category', function () {
    // Act: Login as penilai, vote for employee in pegawai disiplin category
    // Assert: Vote saved to database with category_id = 3
});
```

---

### TS-3: Regression Test untuk Kategori Lain

```php
it('still works for other categories', function () {
    // Act: Test category 1 and 2
    // Assert: Still using category_id filter
});
```

---

## 7. Edge Cases & Error Handling

### EC-1: Kategori dengan Nama Berbeda

**Scenario:** Nama kategori diubah di database

**Handling:**
- Code harus tetap bekerja jika nama kategori berubah
- **Recommendation:** Gunakan `category_id = 3` sebagai fallback atau buat constant

---

### EC-2: Pegawai Tanpa Jabatan

**Scenario:** Ada pegawai dengan `jabatan = null`

**Current Behavior:**
- Pegawai dengan jabatan null akan tetap muncul (tidak ter-filter)

**Acceptance:**
- [ ] Dokumentasikan behavior ini
- [ ] Atau tambah handling khusus

---

### EC-3: Periode Tanpa Kategori Disiplin

**Scenario:** Periode yang dibuat sebelum kategori "Pegawai Disiplin" ada

**Handling:**
- Sistem akan tetap berjalan normal
- Kategori "Pegawai Disiplin" hanya muncul jika exist di database

---

## 8. Dependencies

### Internal Dependencies

| Component | Version | Status |
|-----------|---------|--------|
| Laravel | 12.x | ✓ Installed |
| VotingController | - | ✓ Exists |
| Category Model | - | ✓ Exists |
| Employee Model | - | ✓ Exists |

### External Dependencies

None - hanya menggunakan Laravel built-in features

---

## 9. Rollback Plan

### Jika Implementasi Gagal

1. **Revert Code:**
   ```bash
   git revert <commit-hash>
   ```

2. **Verification:**
   - [ ] Kategori 1 dan 2 berfungsi normal
   - [ ] Tidak ada error di logs

3. **Alternative Approach:**
   - Consider using many-to-many relationship ( lebih kompleks )

---

## 10. Sign-Off

### Approval Checklist

- [ ] Context reviewed and approved
- [ ] Functional requirements approved
- [ ] Non-functional requirements approved
- [ ] Test cases reviewed
- [ ] Edge cases considered

### Stakeholders

| Role | Name | Approval |
|------|------|----------|
| Product Owner | - | Pending |
| Developer | - | Pending |
| Tester | - | Pending |

---

*End of Spec*
