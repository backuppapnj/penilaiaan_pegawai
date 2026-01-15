# USER MANUAL
# SISTEM VOTE SYSTEM PENGADILAN AGAMA PENAJAM

---

## DAFTAR ISI

1. [Pendahuluan](#pendahuluan)
2. [Gambaran Umum Sistem](#gambaran-umum-sistem)
3. [Role dan Hak Akses](#role-dan-hak-akses)
4. [Panduan SuperAdmin](#panduan-superadmin)
5. [Panduan Admin](#panduan-admin)
6. [Panduan Penilai](#panduan-penilai)
7. [Panduan Peserta](#panduan-peserta)
8. [Sistem Absensi dan Skor Disiplin](#sistem-absensi-dan-skor-disiplin)
9. [Troubleshooting](#troubleshooting)
10. [FAQ](#faq)

---

## PENDAHULUAN

### Tentang Sistem

**Sistem Vote System Pengadilan Agama Penajam** adalah aplikasi berbasis web untuk melakukan penilaian karyawan secara digital dan transparan. Sistem ini mencakup:

- **Voting Kinerja**: Penilaian karyawan oleh penilai berdasarkan kriteria tertentu
- **Skor Disiplin**: Perhitungan skor kedisiplinan berdasarkan data kehadiran
- **Sertifikat**: Generate sertifikat otomatis untuk pemenang
- **Multi-Role**: Mendukung berbagai role dengan hak akses berbeda

### Teknologi

- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: React 19 + Inertia.js v2
- **Styling**: Tailwind CSS v4
- **Database**: MySQL
- **Authentication**: Laravel Fortify

---

## GAMBARAN UMUM SISTEM

### Arsitektur

```
┌─────────────────────────────────────────────────────────────┐
│                        FRONTEND                              │
│  React 19 + Inertia.js + Tailwind CSS v4                    │
└────────────────────┬────────────────────────────────────────┘
                     │ Inertia Protocol
┌────────────────────┴────────────────────────────────────────┐
│                      BACKEND                                │
│  Laravel 12 + PHP 8.4 + MySQL                               │
└─────────────────────────────────────────────────────────────┘
```

### Struktur Data

```
Users (29 pegawai)
├── SuperAdmin (1)
├── Admin (2)
├── Penilai (3 pimpinan - hanya menilai)
└── Peserta (23 - dinilai)

Employees (29 pegawai)
├── Kategori: Pejabat Struktural/Fungsional (9)
└── Kategori: Non-Pejabat (14)

Periods (Periode Voting)
├── Status: draft, open, closed, announced
└── Categories & Criteria

Votes (Hasil Voting)
└── Vote Details per Criterion

Discipline Scores (Skor Disiplin)
├── Score 1: Kehadiran & Ketepatan Waktu (50%)
├── Score 2: Kedisiplinan (35%)
├── Score 3: Ketaatan (15%)
└── Final Score: Weighted Average

Certificates (Sertifikat)
└── QR Code untuk verifikasi
```

---

## ROLE DAN HAK AKSES

### Tabel Role

| Role | Jumlah | Keterangan | Hak Akses Utama |
|------|--------|------------|-----------------|
| **SuperAdmin** | 1 | Muhardiansyah | Full akses ke semua fitur |
| **Admin** | 2 | Najwa, Sekretaris | Manajemen voting, karyawan, kriteria |
| **Penilai** | 3 | Ketua, Wakil, Panitera | Hanya menilai (tidak dinilai) |
| **Peserta** | 23 | Karyawan lain | Dinilai, bisa lihat hasil & sertifikat |

### Hak Akses per Fitur

| Fitur | SuperAdmin | Admin | Penilai | Peserta |
|-------|-----------|-------|---------|---------|
| Dashboard SuperAdmin | ✅ | ❌ | ❌ | ❌ |
| Dashboard Admin | ✅ | ✅ | ❌ | ❌ |
| Dashboard Penilai | ✅ | ✅ | ✅ | ❌ |
| Dashboard Peserta | ✅ | ✅ | ✅ | ✅ |
| Manajemen Periode | ✅ | ✅ | ❌ | ❌ |
| Manajemen Kriteria | ✅ | ✅ | ❌ | ❌ |
| Manajemen Karyawan | ✅ | ✅ | ❌ | ❌ |
| Import SIKEP | ✅ | ✅ | ❌ | ❌ |
| Voting | ✅ | ✅ | ✅ | ❌ |
| Generate Sertifikat | ✅ | ✅ | ❌ | ❌ |
| Lihat Hasil & Sertifikat | ✅ | ✅ | ✅ | ✅ |
| Log Aktivitas | ✅ | ❌ | ❌ | ❌ |

---

## PANDUAN SUPERADMIN

### Gambaran Role

**SuperAdmin** adalah role tertinggi dengan kontrol penuh atas seluruh sistem.

### Dashboard SuperAdmin

**URL**: `/super-admin`
**Middleware**: `role:SuperAdmin`

#### Informasi yang Ditampilkan:

1. **Statistik Utama**
   - Total Karyawan: 29
   - Periode Aktif: jumlah periode berjalan
   - Total Vote: jumlah vote terproses
   - Sertifikat Dibuat: jumlah sertifikat

2. **Statistik Kategori**
   - Pejabat Struktural/Fungsional: 9 pegawai
   - Non-Pejabat: 14 pegawai

3. **Log Aktivitas** (20 terbaru)
   - Aksi yang dilakukan
   - Pengguna yang melakukan
   - Timestamp aktivitas

### Fitur SuperAdmin

#### 1. Manajemen Periode

**URL**: `/admin/periods`

| Aksi | Deskripsi |
|------|-----------|
| **Create** | Buat periode voting baru |
| **Edit** | Update informasi periode |
| **Delete** | Hapus periode (tidak bisa jika sedang aktif) |
| **Update Status** | Ubah status: draft → open → closed → announced |

**Status Periode**:
- `draft`: Periode disiapkan
- `open`: Voting dibuka
- `closed`: Voting ditutup
- `announced`: Hasil diumumkan

#### 2. Manajemen Kriteria

**URL**: `/admin/criteria`

| Aksi | Deskripsi |
|------|-----------|
| **Create** | Tambah kriteria penilaian |
| **Edit** | Update nama/deskripsi kriteria |
| **Delete** | Hapus kriteria |
| **Update Weight** | Ubah bobot kriteria |

#### 3. Manajemen Karyawan

**URL**: `/admin/employees`

| Fitur | Deskripsi |
|-------|-----------|
| **Daftar Karyawan** | Lihat semua karyawan dengan filter |
| **Statistik** | Jumlah per kategori |
| **Import** | Import dari `data_pegawai.json` |

#### 4. Import Data SIKEP

**URL**: `/admin/sikep`

| Fitur | Deskripsi |
|-------|-----------|
| **Upload Excel** | Upload file rekap kehadiran |
| **Pilih Bulan/Tahun** | Tentukan periode data |
| **Lihat Skor** | Tampilkan skor yang sudah diimport |
| **Hapus Data** | Delete data import |

#### 5. Generate Sertifikat

**URL**: `/admin/periods/{id}/generate-certificates`

**Proses**:
1. Pilih periode yang sudah closed
2. Klik "Generate Certificates"
3. Sistem menghitung pemenang per kategori
4. Sertifikat PDF dibuat dengan QR Code

### Tips SuperAdmin

1. **Keamanan**
   - Verifikasi aksi penting sebelum eksekusi
   - Monitor log aktivitas secara berkala
   - Batasi akses data sensitif

2. **Manajemen Periode**
   - Pastikan status benar sebelum open voting
   - Jangan hapus periode aktif
   - Generate sertifikat setelah closed

3. **Import Data**
   - Cek format Excel sebelum upload
   - Verifikasi hasil import
   - Backup data sebelum import besar

---

## PANDUAN ADMIN

### Gambaran Role

**Admin** mengelola operasional voting dan manajemen data karyawan.

### Dashboard Admin

**URL**: `/admin`
**Middleware**: `role:Admin,SuperAdmin`

#### Informasi yang Ditampilkan:

1. **Statistik Voting**
   - Total periode
   - Periode aktif
   - Progress voting

2. **Statistik Karyawan**
   - Total karyawan
   - Per kategori

3. **Daftar Periode**
   - Semua periode dengan status
   - Aksi quick update status

### Fitur Admin

#### 1. Manajemen Periode

**Langkah-langkah**:

1. **Buat Periode Baru**
   ```
   Klik "Admin" → "Periods" → "Create"
   Isi:
   - Nama: "Voting Januari 2025"
   - Start Date: 2025-01-01
   - End Date: 2025-01-31
   - Description: (opsional)
   Klik "Save"
   ```

2. **Update Status Periode**
   ```
   Buka detail periode
   Klik tombol status:
   - Draft → Open (mulai voting)
   - Open → Closed (tutup voting)
   - Closed → Announced (umumkan hasil)
   ```

#### 2. Manajemen Kriteria

**Struktur**:
```
Period
└── Category (Kinerja, Kedisiplinan, Attitude)
    └── Criterion (Kriteria penilaian dengan bobot)
```

**Langkah-langkah**:

1. **Buat Kriteria**
   ```
   Klik "Admin" → "Criteria" → "Create"
   Isi:
   - Nama: "Kualitas Kerja"
   - Category: Pilih category
   - Bobot: 10 (1-100)
   - Deskripsi: (opsional)
   Klik "Save"
   ```

2. **Update Bobot**
   ```
   Klik "Update Weight" pada kriteria
   Masukkan bobot baru
   Klik "Save"
   ```

#### 3. Import Data SIKEP

**Format File Excel**: `02_rekap_kehadiran_1600_401877_PA Penajam_Bulan_Tahun.xlsx`

**Struktur Excel**:
- Row 8: Headers (NO, NAMA, NIP, JABATAN)
- Row 9: Labels (DATANG TEPAT WAKTU, TERLAMPAT, dll)
- Row 10: Codes (v, tkd, tl1, tl2, tl3, tl4, thm, v, ik, psw1)
- Row 11: Weights (0%, 0%, 0.5%, 1%, 1.25%, 1.5%, 1.5%, 0%, 0%, 0.5%)
- Row 12+: Data pegawai

**Kolom Data**:
| Kolom | Kode | Deskripsi | Bobot |
|-------|------|-----------|-------|
| E | v (DTW) | Datang Tepat Waktu | 0% |
| F | tkd | Terlambat Karena Dinas | 0% |
| G | tl1 | Terlambat 1-30 menit | 0.5% |
| H | tl2 | Terlambat 31-60 menit | 1% |
| I | tl3 | Terlambat 61-90 menit | 1.25% |
| J | tl4 | Terlambat >90 menit | 1.5% |
| K | thm | Tidak Mengisi Daftar Hadir | 1.5% |
| L | v (PTW) | Pulang Tepat Waktu | 0% |
| M | ik | Izin Keluar Kantor | 0% |
| N | psw1 | Pulang Sebelum Waktu 1-30 menit | 0.5% |

**Langkah Import**:
```
1. Klik "Admin" → "SIKEP"
2. Pilih bulan dan tahun
3. Upload file Excel
4. Klik "Import"
5. Verifikasi hasil import
```

#### 4. Generate Sertifikat

**Proses**:
```
1. Pastikan periode status "Closed"
2. Buka detail periode
3. Klik "Generate Certificates"
4. Tunggu proses selesai
5. Sertifikat tersedia untuk di-download
```

### Troubleshooting Admin

| Masalah | Solusi |
|---------|---------|
| Periode tidak bisa dihapus | Cek apakah ada vote terkait |
| Import gagal | Pastikan format Excel sesuai |
| Generate sertifikat gagal | Pastikan semua skor sudah dihitung |
| Voting tidak bisa dibuka | Pastikan kriteria sudah ada |

---

## PANDUAN PENILAI

### Gambaran Role

**Penilai** adalah pegawai yang memberikan nilai/voting kepada pegawai lain (Peserta).

**Catatan**: SEMUA 29 pegawai dapat menjadi penilai, tapi hanya 23 pegawai (bukan pimpinan) yang menjadi peserta.

### Dashboard Penilai

**URL**: `/penilai/dashboard`
**Middleware**: `role:Penilai,Peserta,Admin,SuperAdmin`

#### Informasi yang Ditampilkan:

1. **Statistik Voting**
   - Total voting yang tersedia
   - Voting yang sudah selesai
   - Voting yang belum dilakukan

2. **Progress Voting**
   - Per kategori
   - Periode aktif

3. **Aktivitas Terbaru**
   - Voting yang baru saja dilakukan

### Fitur Penilai

#### 1. Halaman Voting

**URL**: `/penilai/voting`

**Tampilan**:
```
┌─────────────────────────────────────────────┐
│  Voting yang Tersedia                      │
├─────────────────────────────────────────────┤
│  Periode: Voting Januari 2025              │
│  Status: Open                              │
│                                             │
│  Kategori:                                  │
│  ☑ Kinerja                                 │
│  ☑ Kedisiplinan                            │
│  ☑ Attitude                                │
└─────────────────────────────────────────────┘
```

#### 2. Melakukan Voting

**Langkah-langkah**:

```
1. Akses /penilai/voting
2. Pilih periode yang aktif
3. Pilih kategori
4. Pilih pegawai yang akan dinilai
5. Isi nilai untuk setiap kriteria (1-100)
6. Klik "Simpan/Submit"
```

**Form Voting**:
```
┌─────────────────────────────────────────────┐
│  Voting: Kinerja - Januari 2025             │
├─────────────────────────────────────────────┤
│  Pegawai: Ahmad (NIP: 198505092009041006)   │
│                                             │
│  Kriteria:                                  │
│  Kualitas Kerja         [  85  ]           │
│  Kuantitas Kerja        [  90  ]           │
│  Inisiatif              [  80  ]           │
│  Kerjasama Tim          [  88  ]           │
│                                             │
│  [  Simpan Voting  ]                       │
└─────────────────────────────────────────────┘
```

#### 3. Riwayat Voting

**URL**: `/penilai/voting/history`

**Informasi**:
- Daftar semua voting yang dilakukan
- Detail nilai per kriteria
- Timestamp voting

### Aturan Voting

1. **Periode**
   - Hanya bisa voting saat status "Open"
   - Tidak bisa voting setelah "Closed"

2. **Pegawai yang Dinilai**
   - Tidak bisa memilih diri sendiri
   - Tidak bisa menilai pimpinan (Ketua, Wakil, Panitera, Sekretaris)
   - Satu voting per pegawai per kategori

3. **Nilai**
   - Range: 1-100
   - Harus numeric
   - Semua kriteria wajib diisi

### Tips Penilai

1. **Sebelum Voting**
   - Pastikan periode masih aktif
   - Periksa daftar pegawai yang tersedia
   - Siapkan penilaian objektif

2. **Saat Voting**
   - Isi semua kriteria dengan nilai valid
   - Simpan sebelum pindah halaman
   - Periksa kembali sebelum submit

3. **Setelah Voting**
   - Cek riwayat untuk verifikasi
   - Hubungi admin jika ada kesalahan

---

## PANDUAN PESERTA

### Gambaran Role

**Peserta** adalah pegawai yang dinilai (23 pegawai, bukan pimpinan).

### Dashboard Peserta

**URL**: `/peserta/dashboard`
**Middleware**: `role:Peserta` (semua role bisa akses)

#### Informasi yang Ditampilkan:

1. **Statistik Personal**
   - Total voting yang diterima
   - Rata-rata skor
   - Peringkat saat ini

2. **Hasil Voting Terbaru**
   - Periode dan kategori
   - Skor yang diperoleh
   - Peringkat

3. **Sertifikat**
   - Daftar sertifikat yang tersedia
   - Status sertifikat

### Fitur Peserta

#### 1. Melihat Hasil Voting

**Langkah**:
```
1. Login ke sistem
2. Dashboard otomatis menampilkan hasil
3. Klik detail untuk melihat per kriteria
```

**Informasi Hasil**:
```
┌─────────────────────────────────────────────┐
│  Hasil Voting - Januari 2025                │
├─────────────────────────────────────────────┤
│  Kategori: Kinerja                          │
│  Skor Total: 87.5                           │
│  Peringkat: 3 dari 23                       │
│                                             │
│  Detail per Kriteria:                       │
│  Kualitas Kerja    : 85                     │
│  Kuantitas Kerja   : 90                     │
│  Inisiatif         : 88                     │
│  Kerjasama Tim     : 87                     │
└─────────────────────────────────────────────┘
```

#### 2. Download Sertifikat

**URL**: `/peserta/certificates`

**Langkah**:
```
1. Klik menu "Sertifikat"
2. Pilih sertifikat yang ingin di-download
3. Klik "Download PDF"
4. Sertifikat terdownload ke perangkat
```

#### 3. Verifikasi Sertifikat

**URL**: `/certificates/verify/{id}`

**Proses**:
```
1. Buka file PDF sertifikat
2. Scan QR Code dengan smartphone
3. Arahkan ke URL verifikasi
4. Sistem menampilkan informasi sertifikat
```

**Informasi Verifikasi**:
- Nama pemenang
- Periode dan kategori
- Peringkat
- Status validasi

#### 4. Pengaturan Profil

**URL**: `/profile/edit`

**Fitur**:
- Update nama
- Update email
- Ganti password

**URL**: `/settings/password/edit`

**Fitur**:
- Ganti password
- Konfirmasi password baru

### Tips Peserta

1. **Melihat Hasil**
   - Cek dashboard secara berkala
   - Klik detail untuk breakdown nilai
   - Simpan/download hasil untuk arsip

2. **Sertifikat**
   - Download setelah periode announced
   - Verifikasi keaslian dengan QR Code
   - Simpan di lokasi aman

3. **Keamanan**
   - Ganti password secara berkala
   - Jangan share kredensial
   - Logout setelah menggunakan

---

## SISTEM ABSENSI DAN SKOR DISIPLIN

### Gambaran Umum

Sistem menghitung skor kedisiplin berdasarkan data kehadiran dari file Excel SIKEP.

### Struktur Data

**Model**: `DisciplineScore`

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| employee_id | bigint | ID karyawan |
| month | tinyint (1-12) | Bulan |
| year | smallint | Tahun (4 digit) |
| total_work_days | int | Total hari kerja |
| present_on_time | int | Hadir tepat waktu |
| leave_on_time | int | Pulang tepat waktu |
| late_minutes | int | Total menit terlambat |
| early_leave_minutes | int | Total menit pulang awal |
| excess_permission_count | int | Jumlah izin berlebih |
| score_1 | decimal (5,2) | Skor kehadiran (50%) |
| score_2 | decimal (5,2) | Skor kedisiplinan (35%) |
| score_3 | decimal (5,2) | Skor ketaatan (15%) |
| final_score | decimal (5,2) | Skor akhir |
| rank | int | Peringkat |
| is_winner | tinyint | Flag pemenang |
| raw_data | json | Data mentah Excel |

### Rumus Perhitungan

#### Score 1: Kehadiran & Ketepatan Waktu (50%)

```php
Score1 = ((DTW + PTW) / TotalHariKerja) × 100
        - (MenitTerlambat / 30) × 5

DTW     = Datang Tepat Waktu
PTW     = Pulang Tepat Waktu
```

**Keterangan**:
- Hadir tepat waktu = nilai penuh
- Terlambat 1-30 menit = dikurangi
- Terlambat >30 menit = penalti lebih besar

#### Score 2: Kedisiplinan (35%)

```php
Score2 = (DTW × 0% + TKD × 0% + TL1 × 0.5% + TL2 × 1%
         + TL3 × 1.25% + TL4 × 1.5% + THM × 1.5%)

TKD = Terlambat Karena Dinas (0% penalti)
TL1 = Terlambat 1-30 menit (0.5% penalti)
TL2 = Terlambat 31-60 menit (1% penalti)
TL3 = Terlambat 61-90 menit (1.25% penalti)
TL4 = Terlambat >90 menit (1.5% penalti)
THM = Tidak Mengisi Daftar Hadir (1.5% penalti)
```

#### Score 3: Ketaatan (15%)

```php
Score3 = (PTW × 0% + IK × 0% + PSW1 × 0.5%)

PTW  = Pulang Tepat Waktu (0% penalti)
IK   = Izin Keluar (0% penalti)
PSW1 = Pulang Sebelum Waktu 1-30 menit (0.5% penalti)
```

#### Final Score

```php
Final Score = (Score1 × 50%) + (Score2 × 35%) + (Score3 × 15%)
```

### Import Data Absensi

#### Metode 1: AttendanceSeeder (Otomatis)

**Lokasi**: `docs/rekap_kehadiran/`

**Nama File**: `02_rekap_kehadiran_1600_401877_PA Penajam_Bulan_Tahun.xlsx`

**Jalankan**:
```bash
php artisan db:seed --class=AttendanceSeeder
```

**Proses**:
1. Membaca semua file .xlsx di folder
2. Extract nama bulan dan tahun dari filename
3. Parse data dari row 12+
4. Hitung skor menggunakan formula
5. Simpan ke tabel `discipline_scores`

#### Metode 2: SikepImportService (Manual)

**URL**: `/admin/sikep`

**Langkah**:
```
1. Login sebagai Admin/SuperAdmin
2. Klik "Admin" → "SIKEP"
3. Pilih bulan dan tahun
4. Upload file Excel
5. Klik "Import"
```

### Melihat Ranking

**Dashboard**:
```
┌─────────────────────────────────────────────┐
│  Ranking Skor Disiplin - Januari 2025        │
├─────────────────────────────────────────────┤
│  1. Ahmad        - 98.50  ⭐                 │
│  2. Budi         - 97.25                      │
│  3. Citra        - 95.80                      │
│  ...                                        │
└─────────────────────────────────────────────┘
```

**Periode Filter**:
- Pilih bulan
- Pilih tahun
- Filter by kategori

### Troubleshooting Absensi

| Masalah | Solusi |
|---------|---------|
| Import gagal | Cek nama file, format Excel |
| Skor 0 | Pastikan data DTW dan PTW terisi |
| NIP not found | Pegawai tidak ada di data_pegawai.json |
| Duplicate key | Sudah ada data untuk bulan/tahun tersebut |

---

## TROUBLESHOOTING

### Masalah Umum

#### 1. Login Gagal

| Error | Penyebab | Solusi |
|-------|----------|--------|
| Invalid credentials | Email/password salah | Reset password via Admin |
| Account inactive | Akun dinonaktifkan | Hubungi SuperAdmin |
| Role not found | Role tidak terdaftar | Cek database users |

#### 2. Voting Tidak Bisa Dilakukan

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| Periode not found | Periode tidak ada | Buat periode dulu |
| Periode closed | Periode sudah ditutup | Hubungi Admin |
| No criteria | Kriteria belum dibuat | Hubungi Admin |
| Already voted | Sudah pernah voting | Cek riwayat |

#### 3. Import Gagal

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| File not readable | Format Excel salah | Gunakan template yang benar |
| NIP not found | Pegawai tidak terdaftar | Import pegawai dulu |
| Duplicate entry | Data sudah ada | Hapus data lama dulu |

### Error Messages

#### SQL Errors

**Connection timeout**:
```
SQLSTATE[HY000] [2002] Connection timed out
```
**Solusi**: Pastikan MySQL running

#### Validation Errors

**Required field**:
```
The period field is required
```
**Solusi**: Isi semua field yang wajib

**Invalid value**:
```
The score must be between 1 and 100
```
**Solusi**: Masukkan nilai dalam range yang valid

### Contact Support

Jika masalah berlanjut:
1. Cek log di `/storage/logs/laravel.log`
2. Hubungi SuperAdmin
3. Cek dokumentasi teknis

---

## FAQ

### Umum

**Q: Apa bedanya Penilai dan Peserta?**
A: Penilai = yang menilai (SEMUA 29 pegawai). Peserta = yang dinilai (23 pegawai, bukan pimpinan).

**Q: Berapa kali voting bisa dilakukan?**
A: Sekali per pegawai per kategori per periode.

**Q: Apakah voting bisa diubah?**
A: Tidak, voting yang sudah submit tidak bisa diubah.

**Q: Kapan sertifikat tersedia?**
A: Setelah periode status "Announced".

### Login & Keamanan

**Q: Bagaimana reset password?**
A: Hubungi Admin atau gunakan fitur "Forgot Password".

**Q: Apakah password bisa diganti?**
A: Ya, di menu Settings → Password.

**Q: Berapa minimal karakter password?**
A: Minimum 8 karakter.

### Voting

**Q: Siapa saja yang bisa divoting?**
A: Peserta (23 pegawai), bukan pimpinan.

**Q: Apakah bisa voting untuk diri sendiri?**
A: Tidak, sistem akan menolak.

**Q: Bagaimana jika lupa menyimpan voting?**
A: Data akan hilang, isi ulang dari awal.

### Absensi

**Q: Dari mana data absensi berasal?**
A: Dari file Excel SIKEP di `docs/rekap_kehadiran/`.

**Q: Bagaimana jika ada pegawai baru?**
A: Tambahkan ke `data_pegawai.json`, jalankan UserSeeder dan EmployeeSeeder.

**Q: Apakah bisa import ulang untuk bulan yang sama?**
A: Bisa, data akan di-update (gunakan updateOrCreate).

### Sertifikat

**Q: Bagaimana verifikasi keaslian sertifikat?**
A: Scan QR Code, akan redirect ke halaman verifikasi.

**Q: Apakah sertifikat bisa digenerate ulang?**
A: Ya, delete sertifikat lalu generate ulang.

**Q: Apakah QR Code unik?**
A: Ya, setiap sertifikat memiliki QR Code unik.

---

## APPENDIX

### A. Struktur File Project

```
vote_system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── PeriodController.php
│   │   │   │   ├── CriterionController.php
│   │   │   │   ├── EmployeeController.php
│   │   │   │   └── ExcelImportController.php
│   │   │   ├── Auth/
│   │   │   ├── DashboardController.php
│   │   │   ├── Penilai/
│   │   │   │   └── VotingController.php
│   │   │   ├── Settings/
│   │   │   └── CertificateController.php
│   │   ├── Middleware/
│   │   │   └── Role.php
│   │   └── Requests/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Employee.php
│   │   ├── Period.php
│   │   ├── Category.php
│   │   ├── Criterion.php
│   │   ├── Vote.php
│   │   ├── VoteDetail.php
│   │   ├── DisciplineScore.php
│   │   └── Certificate.php
│   └── Services/
│       ├── ScoreCalculationService.php
│       ├── CertificateService.php
│       └── SikepImportService.php
├── database/
│   ├── seeders/
│   │   ├── UserSeeder.php
│   │   ├── EmployeeSeeder.php
│   │   ├── AttendanceSeeder.php
│   │   └── CategoryAndCriteriaSeeder.php
│   └── migrations/
├── resources/
│   └── js/
│       └── Pages/
│           ├── Admin/
│           ├── Penilai/
│           ├── Peserta/
│           └── Settings/
├── routes/
│   ├── web.php
│   └── settings.php
└── docs/
    ├── data_pegawai.json
    └── rekap_kehadiran/
```

### B. Default Credentials

| Role | Email | Password |
|------|-------|----------|
| SuperAdmin | muhardiansyah@pa-penajam.go.id | 199107132020121003 |
| Admin | najwa.hijriana@pa-penajam.go.id | 199605112025212037 |
| Admin | indra.yanita.yuliana@pa-penajam.go.id | 198301042006042003 |
| Penilai | fattahurridlo.al.ghany@pa-penajam.go.id | 198505092009041006 |
| Peserta | [nama]@pa-penajam.go.id | [NIP] |

**Catatan**: Password default = NIP masing-masing

### C. Commands Berguna

```bash
# Reset dan seed ulang
php artisan migrate:fresh --seed

# Jalankan seeder spesifik
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=AttendanceSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Cek route
php artisan route:list

# Cek log
tail -f storage/logs/laravel.log
```

---

**Dokumentasi ini dibuat berdasarkan kode proyek Vote System per 15 Januari 2026**

Untuk informasi lebih lanjut, hubungi tim IT Pengadilan Agama Penajam.
