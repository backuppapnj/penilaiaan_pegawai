# Specification: Sistem Penilaian Pegawai Terbaik PA Penajam

## Overview
Sistem penilaian pegawai terbaik di lingkungan Pengadilan Agama Penajam dengan 3 kategori penilaian yang berbeda. Sistem menggunakan kombinasi voting + ranking untuk kategori 1 & 2, dan data absensi SIKEP untuk kategori 3 (disiplin).

## Context
- **Instansi**: Pengadilan Agama Penajam
- **Total Pegawai**: 29 orang
- **Periode Penilaian**: Semesteran
- **Sistem**: Voting + Ranking + Data Absensi

## Functional Requirements

### 1. User Management & Authentication

#### 1.1 Role System (4 Levels)

| Role | Pegawai | Permissions |
|------|---------|-------------|
| **Super Admin** | Muhardiansyah (Pranata Komputer) | Full access, audit trail, all configurations |
| **Admin** | Najwa + 4 Pimpinan | Buka/tutup periode, verifikasi hasil, pengumuman |
| **Penilai Only** | 2 Hakim (Nur Rizka Fani, Vidya Nurchaliza) | Vote only, tidak ikut kompetisi |
| **Penilai + Peserta** | 21 pegawai lainnya | Vote + dinilai |

#### 1.2 Authentication
- Login menggunakan NIP sebagai username
- Default password: nip (diganti saat first login)
- Laravel Fortify untuk authentication

### 2. Employee Data Management

#### 2.1 Import Data
- Source: `docs/data_pegawai.json` dan `docs/org_structure.json`
- Fields: NIP, Nama, Jabatan, Unit Kerja, Golongan, TMT

#### 2.2 Kategorisasi Otomatis
Sistem mengkategorikan pegawai secara otomatis berdasarkan org_structure.json:

**Kategori 1: Pejabat Struktural/Fungsional (14 orang)**
- Struktural (8): Ketua, Wakil, Panitera, Sekretaris, 2 Panitera Muda, 2 Kasubag
- Fungsional (6): 2 Hakim, 2 Panitera Pengganti, Pranata Komputer, Juru Sita Pengganti

**Kategori 2: Non-Pejabat (15 orang)**
- Staff/staf non-kepala: Klerek, Operator, Teknisi, dll

**Kategori 3: Pegawai Disiplin (25 orang)**
- Semua pegawai KECUALI:
  - Pimpinan (Ketua, Wakil, Panitera, Sekretaris)
  - Yudisial (2 Hakim)

### 3. Penilaian Kategori 1 & 2 (Voting + Ranking)

#### 3.1 Kriteria Penilaian per Kategori

**Kategori 1 - Pejabat Struktural/Fungsional:**
| No | Kriteria | Bobot |
|----|----------|-------|
| 1 | Kepemimpinan | 25% |
| 2 | Manajemen SDM & Organisasi | 20% |
| 3 | Inovasi & Peningkatan Pelayanan | 15% |
| 4 | Integritas & Etika | 15% |
| 5 | Kerjasama & Koordinasi | 10% |
| 6 | Pencapaian Target Unit | 10% |
| 7 | Pengembangan Kompetensi Bawahan | 5% |

**Kategori 2 - Non-Pejabat:**
| No | Kriteria | Bobot |
|----|----------|-------|
| 1 | Kinerja & Produktivitas | 25% |
| 2 | Kedisiplinan & Kehadiran | 20% |
| 3 | Pelayanan Prima | 15% |
| 4 | Kerjasama Tim | 15% |
| 5 | Integritas & Tanggung Jawab | 10% |
| 6 | Inisiatif & Kreativitas | 10% |
| 7 | Pengembangan Diri | 5% |

#### 3.2 Mekanisme Voting
- Semua pegawai (kecuali peserta yang sedang dinilai di kategorinya) dapat memberikan penilaian
- Penilai memberikan nilai 1-100 untuk setiap kriteria
- Penilai tidak bisa memilih diri sendiri
- Sistem anonim (hanya Super Admin yang bisa melihat audit trail)

#### 3.3 Formula Skor Akhir
```
Skor Akhir = Σ(Nilai Kriteria × Bobot Kriteria)
```

### 4. Penilaian Kategori 3 (Pegawai Disiplin)

#### 4.1 Import Data SIKEP
- Source: File `02_rekap_kehadiran_*.xlsx`
- Import manual oleh Admin
- Format: Excel dengan kolom rekapitulasi kehadiran

#### 4.2 Kriteria Penilaian dari SIKEP

| No | Kriteria | Bobot | Rumus |
|----|----------|-------|-------|
| 1 | Tingkat Kehadiran & Ketepatan Waktu | 50% | `[(E + L) / (Total Hari Kerja × 2)] × 50` |
| 2 | Kedisiplinan (Tanpa Pelanggaran) | 35% | `[100 - Total Penalti] × 0.35` |
| 3 | Ketaatan (Tanpa Izin Berlebih) | 15% | `Jika ada izin berlebih: 0, Jika tidak: 15` |

**Kolom Excel SIKEP:**
- E = DATANG TEPAT WAKTU
- L = PULANG TEPAT WAKTU
- G-K = Terlambat (berdasarkan menit)
- N-R = Pulang awal (berdasarkan menit)
- S/AC/V/AA/AB/AE/AI/AJ = Izin berlebih (potong poin)

#### 4.3 Mapping Kolom Excel ke Database
```
Row 8-9: Header
Row 11: Bobot penalti
Row 12+: Data pegawai
```

### 5. Periode Penilaian

#### 5.1 Setup Periode
- Semesteran (Ganjil/Genap)
- Admin membuka dan menutup periode
- Admin menentukan kapan hasil diumumkan

#### 5.2 Status Periode
- `draft` - Persiapan
- `open` - Voting dibuka
- `closed` - Voting ditutup, perhitungan
- `announced` - Hasil diumumkan

### 6. Dashboard Multi-Role

#### 6.1 Super Admin Dashboard
- Monitoring semua aktivitas
- Audit trail lengkap
- Manajemen user & role
- Export semua data
- Konfigurasi sistem

#### 6.2 Admin Dashboard
- Setup kriteria & bobot
- Buka/tutup periode
- Import data SIKEP
- Verifikasi hasil
- Pengumuman pemenang
- Laporan statistik

#### 6.3 Penilai Dashboard
- Daftar peserta yang bisa dinilai
- Status penilaian (sudah/belum)
- Riwayat penilaian diri
- Hasil setelah diumumkan

#### 6.4 Peserta Dashboard
- Melihat hasil setelah diumumkan
- Riwayat prestasi
- Peringkat diri
- Download sertifikat (jika menang)

### 7. Sertifikat Digital

#### 7.1 Generate Sertifikat
- HTML template → DomPDF
- 1 pemenang per kategori
- Otomatis generate setelah pengumuman

#### 7.2 Konten Sertifikat
```
┌─────────────────────────────────────────────┐
│         PENGADILAN AGAMA PENAJAM            │
│                                             │
│    SERTIFIKAT PEGAWAI TERBAIK               │
│    Kategori: [Nama Kategori]                │
│                                             │
│    Diberikan kepada:                        │
│    [NAMA PEGAWAI]                           │
│    [NIP]                                    │
│    [Jabatan]                                │
│                                             │
│    Atas prestasinya pada periode:           │
│    [Nama Periode]                           │
│                                             │
│  [QR Code]    Penajam, [Tanggal]            │
│              [Tanda Tangan Ketua]           │
└─────────────────────────────────────────────┘
```

#### 7.3 QR Code
- Berisi URL verifikasi: `/verify/{certificate_id}`
- Scan untuk memvalidasi keaslian sertifikat

### 8. Audit Trail

#### 8.1 Logging
- Semua aktivitas dicatat
- Hanya Super Admin yang bisa melihat
- Exportable ke CSV/PDF

#### 8.2 Data yang Dicatat
- User ID (hashed untuk anonimitas)
- Action
- Timestamp
- IP Address
- Details

## Non-Functional Requirements

### Performance
- Load time < 2 detik
- Support 29 concurrent users
- Excel import < 10 detik

### Security
- Password hashing (bcrypt)
- Anonymous voting (encrypted)
- Role-based access control
- CSRF protection

### Usability
- Mobile responsive
- Intuitive UI
- Clear feedback

## Acceptance Criteria

1. ✅ Login dengan 4 role berfungsi
2. ✅ Data pegawai terimport dari JSON
3. ✅ Kategorisasi otomatis berdasarkan org_structure
4. ✅ Voting system berfungsi untuk kategori 1 & 2
5. ✅ Import Excel SIKEP berfungsi untuk kategori 3
6. ✅ Perhitungan skor otomatis untuk semua kategori
7. ✅ Admin bisa buka/tutup periode
8. ✅ Admin bisa umumkan hasil
9. ✅ Sertifikat generate otomatis (HTML to PDF)
10. ✅ Dashboard multi-role berfungsi
11. ✅ Audit trail berfungsi (Super Admin only)

## Out of Scope

- Email notifications (Phase 2)
- Mobile app (Phase 2)
- Advanced analytics/graphs (Phase 2)
- API for external integration (Phase 2)

## Data Sources

- `docs/data_pegawai.json` - Data pegawai lengkap
- `docs/org_structure.json` - Struktur organisasi
- `docs/02_rekap_kehadiran_*.xlsx` - Data absensi SIKEP

## Timeline

- **Deadline**: Besok
- **MVP**: Core features (voting kategori 1&2, dashboard basic)
- **Post-MVP**: SIKEP import, sertifikat, audit trail
