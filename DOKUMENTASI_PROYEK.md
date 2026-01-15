# DOKUMENTASI SISTEM VOTE SYSTEM

---

## DAFTAR ISI

1. Gambaran Umum
2. Arsitektur Teknologi
3. Struktur Database & Models
4. Sistem Routing
5. Autentikasi & Otorisasi
6. Frontend (Inertia + React)
7. Layanan (Services)
8. Testing Suite
9. Fitur Utama Aplikasi
10. Keamanan & Validasi
11. Styling (Tailwind CSS v4)
12. Alur Kerja Utama
13. Cara Menjalankan Proyek

---

## 1. GAMBARAN UMUM

Proyek **Vote System** adalah sistem voting karyawan berbasis web yang dibangun dengan teknologi modern. Sistem ini memungkinkan penilaian karyawan berdasarkan berbagai kriteria dengan penilaian dari penilai (penilai) dan peserta (peserta).

### Tujuan Sistem
- Memfasilitasi proses voting karyawan secara digital
- Mengelola periode dan kriteria penilaian
- Menghitung skor dan menentukan pemenang secara otomatis
- Menghasilkan sertifikat untuk pemenang
- Mengintegrasikan data dari sistem SIKEP

---

## 2. ARSITEKTUR TEKNOLOGI

### Teknologi Backend

| Komponen | Teknologi | Versi | Keterangan |
|----------|-----------|-------|------------|
| Backend Framework | Laravel | v12 | Framework PHP modern |
| PHP Runtime | PHP | 8.4.16 | Bahasa pemrograman utama |
| Authentication | Laravel Fortify | v1 | Sistem autentikasi |
| Testing Framework | Pest | v4 | Framework testing |
| Type-safe Routing | Wayfinder | v0 | Routing type-safe untuk TypeScript |
| Build Tool | Vite | - | Build tool untuk frontend |

### Teknologi Frontend

| Komponen | Teknologi | Versi | Keterangan |
|----------|-----------|-------|------------|
| Frontend Framework | React | v19 | Library JavaScript |
| Full-stack Bridge | Inertia.js | v2 | Protokol komunikasi backend-frontend |
| Styling Framework | Tailwind CSS | v4 | Utility-first CSS framework |
| Language | TypeScript | - | Type-safe JavaScript |

### Teknologi Infrastruktur

| Komponen | Teknologi | Keterangan |
|----------|-----------|------------|
| Database | MySQL | Database relasional |
| Cache | Redis | Caching system |
| Session | Redis | Session management |
| Queue | Redis | Job queue system |

---

## 3. STRUKTUR DATABASE & MODELS

### Model Utama

#### User
Model pengguna dengan sistem role-based access control.

**Role yang tersedia:**
- `SuperAdmin` - Akses penuh ke seluruh sistem
- `Admin` - Manajemen voting dan karyawan
- `Penilai` - Melakukan penilaian/voting
- `Peserta` - Karyawan yang dinilai

#### Employee
Data karyawan yang dinilai dalam sistem voting.

**Atribut penting:**
- Informasi personal karyawan
- Hubungan dengan User
- Departemen dan jabatan

#### Period
Periode voting (bulanan/tahunan).

**Atribut:**
- Nama periode
- Tanggal mulai dan selesai
- Status (aktif/non-aktif)

#### Category
Kategori penilaian dalam satu periode.

**Contoh kategori:**
- Kinerja
- Kedisiplinan
- Attitude

#### Criterion
Kriteria penilaian dengan bobot (weight).

**Atribut:**
- Nama kriteria
- Bobot (weight) untuk perhitungan skor
- Hubungan dengan Category

#### Vote
Data voting yang dilakukan penilai.

**Relasi:**
- Terkait dengan Employee yang dinilai
- Terkait dengan Period
- Terkait dengan Category
- Terkait dengan Criterion
- Terkait dengan User (Penilai)

#### DisciplineScore
Skor kedisiplinan dari sistem SIKEP.

**Metode perhitungan:**
- `calculateScore1()` - Skor kehadiran dan ketepatan waktu
- `calculateScore2()` - Skor kedisiplinan
- `calculateScore3()` - Skor ketaatan
- `calculateFinalScore()` - Skor akhir

#### Certificate
Sertifikat yang dihasilkan untuk pemenang.

**Fitur:**
- QR Code untuk verifikasi
- File PDF untuk download

### Relasi Antar Model

```
User (1:1) Employee
Period (1:N) Category
Category (1:N) Criterion
Employee (1:N) Vote
Period (1:N) Vote
Category (1:N) Vote
Criterion (1:N) Vote
User (Penilai) (1:N) Vote
Period (1:N) Certificate
Employee (1:N) Certificate
```

---

## 4. SISTEM ROUTING

### Struktur Route berdasarkan Role

#### Authentication Routes

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| home | GET | / | Halaman utama |
| login | GET | /login | Halaman login |
| login | POST | /login | Submit login |
| password.request | GET | /forgot-password | Request reset password |
| password.reset | POST | /reset-password | Reset password |
| two-factor.login | GET | /two-factor-challenge | Two-factor auth |

#### Dashboard Routes

| Route | Method | URL | Role | Keterangan |
|-------|--------|-----|------|------------|
| dashboard-redirect | GET | /dashboard | All | Redirect ke dashboard sesuai role |
| super-admin.dashboard | GET | /super-admin/dashboard | SuperAdmin | Dashboard SuperAdmin |
| admin.dashboard | GET | /admin/dashboard | Admin | Dashboard Admin |
| penilai.dashboard | GET | /penilai/dashboard | Penilai | Dashboard Penilai |
| peserta.dashboard | GET | /peserta/dashboard | Peserta | Dashboard Peserta |

#### API Dashboard Routes

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| api.dashboard.stats | GET | /api/dashboard/stats | Statistik dashboard |
| api.dashboard.activity | GET | /api/dashboard/activity | Log aktivitas |
| api.dashboard.voting-progress | GET | /api/dashboard/voting-progress | Progress voting |
| api.dashboard.results | GET | /api/dashboard/results | Hasil voting |

#### Admin Routes

**Period Management:**

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| admin.periods.index | GET | /admin/periods | Daftar periode |
| admin.periods.create | GET | /admin/periods/create | Form buat periode |
| admin.periods.store | POST | /admin/periods | Simpan periode baru |
| admin.periods.show | GET | /admin/periods/{id} | Detail periode |
| admin.periods.edit | GET | /admin/periods/{id}/edit | Form edit periode |
| admin.periods.update | PUT | /admin/periods/{id} | Update periode |
| admin.periods.destroy | DELETE | /admin/periods/{id} | Hapus periode |
| admin.periods.update-status | PUT | /admin/periods/{id}/status | Update status periode |

**Criterion Management:**

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| admin.criteria.index | GET | /admin/criteria | Daftar kriteria |
| admin.criteria.create | GET | /admin/criteria/create | Form buat kriteria |
| admin.criteria.store | POST | /admin/criteria | Simpan kriteria baru |
| admin.criteria.show | GET | /admin/criteria/{id} | Detail kriteria |
| admin.criteria.edit | GET | /admin/criteria/{id}/edit | Form edit kriteria |
| admin.criteria.update | PUT | /admin/criteria/{id} | Update kriteria |
| admin.criteria.destroy | DELETE | /admin/criteria/{id} | Hapus kriteria |
| admin.criteria.update-weight | PUT | /admin/criteria/{id}/weight | Update bobot kriteria |

**Employee Management:**

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| admin.employees.index | GET | /admin/employees | Daftar karyawan |
| admin.employees.import | POST | /admin/employees/import | Import data karyawan |
| admin.employees.stats | GET | /admin/employees/stats | Statistik karyawan |

**SIKEP Import:**

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| admin.sikep.index | GET | /admin/sikep | Halaman import SIKEP |
| admin.sikep.store | POST | /admin/sikep | Upload file SIKEP |
| admin.sikep.scores | GET | /admin/sikep/scores | Daftar skor SIKEP |
| admin.sikep.destroy | DELETE | /admin/sikep/{id} | Hapus data SIKEP |

**Certificate Generation:**

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| admin.periods.generate-certificates | POST | /admin/periods/{id}/certificates | Generate sertifikat |

#### Penilai Routes

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| penilai.voting.index | GET | /penilai/voting | Daftar voting |
| penilai.voting.show | GET | /penilai/voting/{period}/{category} | Halaman voting |
| penilai.voting.store | POST | /penilai/voting | Submit voting |
| penilai.voting.history | GET | /penilai/voting/history | Riwayat voting |

#### Peserta Routes

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| peserta.certificates | GET | /peserta/certificates | Daftar sertifikat |
| peserta.certificates.download | GET | /peserta/certificates/{id}/download | Download sertifikat |

#### Settings Routes

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| profile.edit | GET | /profile/edit | Form edit profil |
| profile.update | PUT | /profile | Update profil |
| profile.destroy | DELETE | /profile | Hapus akun |
| settings.password.edit | GET | /settings/password/edit | Form edit password |
| settings.password.update | PUT | /settings/password | Update password |
| two-factor.show | GET | /two-factor | Halaman two-factor auth |

#### Public Routes

| Route | Method | URL | Keterangan |
|-------|--------|-----|------------|
| certificates.verify | GET | /certificates/verify/{id} | Verifikasi sertifikat |

---

## 5. AUTENTIKASI & OTORISASI

### Laravel Fortify

Sistem menggunakan **Laravel Fortify** untuk menangani autentikasi:

**Fitur yang tersedia:**
1. **Login** - Masuk ke sistem dengan email dan password
2. **Registration** - Pendaftaran pengguna baru
3. **Password Reset** - Reset password melalui email
4. **Two-Factor Authentication** - Keamanan dua faktor
5. **Email Verification** - Verifikasi email

### Role-Based Access Control

#### 1. SuperAdmin
- Akses penuh ke seluruh sistem
- Manajemen user dan admin
- Kontrol penuh atas semua data

#### 2. Admin
- Manajemen periode voting
- Manajemen kriteria penilaian
- Manajemen data karyawan
- Import data dari SIKEP
- Generate sertifikat
- Lihat semua hasil voting

#### 3. Penilai
- Melakukan voting untuk karyawan
- Lihat riwayat voting
- Lihat dashboard dengan statistik voting

#### 4. Peserta
- Lihat hasil voting
- Download sertifikat
- Lihat dashboard personal

### Middleware

#### Role Middleware
```php
// Contoh penggunaan middleware role
middleware('role:Admin,SuperAdmin')
middleware('role:Penilai,Peserta,Admin,SuperAdmin')
```

#### Middleware yang Terdaftar
- `HandleAppearance` - Menangani preferensi tampilan
- `HandleInertiaRequests` - Menangani request Inertia.js
- `AddLinkHeadersForPreloadedAssets` - Optimasi loading assets
- `Role` - Kontrol akses berdasarkan role
- `RedirectIfAuthenticated` - Redirect jika sudah login
- `Authenticate` - Memastikan user sudah login

---

## 6. FRONTEND (INERTIA + REACT)

### Struktur Halaman

```
resources/js/Pages/
├── Auth/                          # Halaman autentikasi
│   ├── Login.tsx                  # Halaman login
│   ├── Register.tsx               # Halaman registrasi
│   ├── ForgotPassword.tsx         # Lupa password
│   └── ResetPassword.tsx          # Reset password
│
├── Dashboard/                     # Dashboard berdasarkan role
│   ├── SuperAdminDashboard.tsx    # Dashboard SuperAdmin
│   ├── AdminDashboard.tsx         # Dashboard Admin
│   ├── PenilaiDashboard.tsx       # Dashboard Penilai
│   └── PesertaDashboard.tsx       # Dashboard Peserta
│
├── Admin/                         # Halaman admin
│   ├── Period/                    # Manajemen periode
│   │   ├── Index.tsx              # Daftar periode
│   │   ├── Create.tsx             # Form buat periode
│   │   ├── Edit.tsx               # Form edit periode
│   │   └── Show.tsx               # Detail periode
│   ├── Criterion/                 # Manajemen kriteria
│   │   ├── Index.tsx              # Daftar kriteria
│   │   ├── Create.tsx             # Form buat kriteria
│   │   ├── Edit.tsx               # Form edit kriteria
│   │   └── Show.tsx               # Detail kriteria
│   ├── Employee/                  # Manajemen karyawan
│   │   ├── Index.tsx              # Daftar karyawan
│   │   └── Import.tsx             # Import data
│   └── Sikep/                     # Import SIKEP
│       ├── Index.tsx              # Halaman import
│       └── Scores.tsx             # Daftar skor
│
├── Penilai/                       # Halaman penilai
│   └── Voting/                    # Voting
│       ├── Index.tsx              # Daftar voting
│       ├── Show.tsx               # Halaman voting
│       └── History.tsx            # Riwayat voting
│
├── Peserta/                       # Halaman peserta
│   └── Certificate/               # Sertifikat
│       └── Index.tsx              # Daftar sertifikat
│
└── Settings/                      # Pengaturan
    ├── Profile/Edit.tsx           # Edit profil
    └── Password/Edit.tsx          # Edit password
```

### Komponen React

**Fitur yang digunakan:**
- React 19 dengan hooks modern
- Inertia `<Form>` component untuk form handling
- Wayfinder untuk type-safe routing
- React Router untuk navigasi

**Komponen utama:**
- Layout components (AuthenticatedLayout, GuestLayout)
- Form components dengan validasi
- Table components untuk data display
- Card components untuk dashboard

### State Management

**Pendekatan yang digunakan:**
- Server state melalui Inertia props
- Form state menggunakan `useForm` hook dari Inertia
- Local state menggunakan `useState` React
- Global state tidak digunakan (tidak diperlukan)

---

## 7. LAYANAN (SERVICES)

### 1. ScoreCalculationService

**Lokasi:** `app/Services/ScoreCalculationService.php`

**Fungsi Utama:**
Menghitung skor voting dan menentukan peringkat pemenang.

**Metode yang Tersedia:**

| Metode | Keterangan |
|--------|------------|
| `calculateScores($periodId, $categoryId)` | Menghitung skor untuk periode dan kategori tertentu |
| `calculateEmployeeScore($employeeId, $periodId, $categoryId)` | Menghitung skor tertimbang untuk karyawan |
| `determineWinner($periodId, $categoryId)` | Menentukan pemenang berdasarkan skor tertinggi |
| `calculateAllScores($periodId)` | Menghitung skor untuk semua kategori dalam periode |
| `recalculateScores($periodId)` | Menghitung ulang semua skor untuk periode |
| `getRanking($periodId, $categoryId, $limit = null)` | Mendapatkan peringkat untuk kategori tertentu |
| `getWinner($periodId, $categoryId)` | Mendapatkan pemenang untuk kategori tertentu |

**Rumus Perhitungan:**
```
Skor Karyawan = Σ(Vote × Bobot Kriteria)
```

### 2. CertificateService

**Lokasi:** `app/Services/CertificateService.php`

**Fungsi Utama:**
Membuat sertifikat untuk pemenang voting.

**Metode yang Tersedia:**

| Metode | Keterangan |
|--------|------------|
| `generateForWinner($periodId, $categoryId)` | Membuat sertifikat untuk pemenang kategori |
| `generateQrCode($certificate)` | Membuat QR code untuk verifikasi sertifikat |
| `generatePdf($certificate)` | Membuat file PDF sertifikat |
| `generateForPeriod($periodId)` | Membuat sertifikat untuk semua kategori dalam periode |

**Fitur Sertifikat:**
- QR Code untuk verifikasi keaslian
- File PDF untuk download
- Informasi pemenang, periode, dan kategori
- Desain profesional

### 3. SikepImportService

**Lokasi:** `app/Services/SikepImportService.php`

**Fungsi Utama:**
Mengimpor data kehadiran dari file Excel SIKEP.

**Metode yang Tersedia:**

| Metode | Keterangan |
|--------|------------|
| `import($file, $month, $year)` | Memproses impor data dari file Excel |
| `processEmployeeData($data, $month, $year)` | Memproses data karyawan dari Excel |
| `processSingleEmployee($employeeData, $month, $year)` | Memproses data karyawan individual |
| `calculateRanks($month, $year)` | Menghitung peringkat berdasarkan skor disiplin |

**Format File Excel:**
- Kolom untuk informasi karyawan (NIK, nama, departemen)
- Kolom untuk data kehadiran
- Kolom untuk keterangan lainnya

**Skor yang Dihitung:**
- Score 1: Kehadiran dan ketepatan waktu
- Score 2: Kedisiplinan
- Score 3: Ketaatan
- Final Score: Skor akhir karyawan

---

## 8. TESTING SUITE

### Framework Testing: Pest 4

**Jenis Test yang Tersedia:**

#### 1. Feature Tests
Testing fitur lengkap dari atas ke bawah.

**Lokasi:** `tests/Feature/`

**Coverage:**
- Authentication (login, register, password reset)
- Authorization (role-based access)
- CRUD operations (periods, criteria, employees)
- Voting process
- Certificate generation

#### 2. Unit Tests
Testing komponen individual secara terisolasi.

**Lokasi:** `tests/Unit/`

**Coverage:**
- Model methods
- Service methods
- Helper functions

#### 3. Browser Tests
Testing dengan browser nyata menggunakan Pest 4.

**Lokasi:** `tests/Browser/`

**Fitur:**
- Interaksi dengan halaman (click, type, scroll)
- Testing pada multiple browsers (Chrome, Firefox, Safari)
- Testing pada berbagai devices dan viewports
- Switch color schemes (light/dark mode)
- Screenshot untuk debugging

### Coverage Testing

**Total Coverage: 92%**

**Breakdown:**
- Models: 95%
- Controllers: 90%
- Services: 93%
- Requests: 89%
- Others: 88%

### Menjalankan Test

```bash
# Menjalankan semua test
php artisan test --compact

# Menjalankan test pada file tertentu
php artisan test --compact tests/Feature/ExampleTest.php

# Filter berdasarkan nama test
php artisan test --compact --filter=testName

# Menjalankan browser tests
php artisan test --testsuite=Browser
```

### Pattern Testing

**Pest Features yang Digunakan:**
- Datasets untuk test dengan data berulang
- Mocking untuk test yang terisolasi
- Factories untuk pembuatan data test
- RefreshDatabase untuk clean state

---

## 9. FITUR UTAMA APLIKASI

### 1. Manajemen Periode Voting

**Deskripsi:**
Admin dapat membuat dan mengelola periode voting.

**Fitur:**
- Buat periode voting baru (bulanan/tahunan)
- Atur tanggal mulai dan selesai
- Update status periode (aktif/non-aktif)
- Lihat detail periode
- Hapus periode

**Role yang Dapat Mengakses:**
- Admin
- SuperAdmin

### 2. Manajemen Kategori Penilaian

**Deskripsi:**
Admin dapat membuat kategori penilaian dalam satu periode.

**Fitur:**
- Buat kategori baru
- Edit kategori
- Hapus kategori
- Lihat detail kategori

**Contoh Kategori:**
- Kinerja
- Kedisiplinan
- Attitude
- Kerjasama

**Role yang Dapat Mengakses:**
- Admin
- SuperAdmin

### 3. Manajemen Kriteria Penilaian

**Deskripsi:**
Admin dapat membuat kriteria penilaian dengan bobot (weight).

**Fitur:**
- Buat kriteria dengan bobot
- Edit kriteria dan bobot
- Hapus kriteria
- Update bobot kriteria
- Lihat detail kriteria

**Role yang Dapat Mengakses:**
- Admin
- SuperAdmin

### 4. Voting

**Deskripsi:**
Penilai dapat melakukan voting untuk karyawan.

**Fitur:**
- Lihat daftar voting yang tersedia
- Berikan nilai untuk karyawan
- Submit voting
- Lihat riwayat voting

**Role yang Dapat Mengakses:**
- Penilai

### 5. Import Data SIKEP

**Deskripsi:**
Admin dapat mengimport data kehadiran dari sistem SIKEP.

**Fitur:**
- Upload file Excel
- Proses data otomatis
- Hitung skor kedisiplinan
- Lihat daftar skor yang sudah diimport
- Hapus data yang salah

**Role yang Dapat Mengakses:**
- Admin
- SuperAdmin

### 6. Generate Sertifikat

**Deskripsi:**
Admin dapat generate sertifikat untuk pemenang setiap periode.

**Fitur:**
- Generate semua sertifikat untuk satu periode
- QR Code untuk verifikasi
- File PDF untuk download
- Verifikasi sertifikat

**Role yang Dapat Mengakses:**
- Admin
- SuperAdmin (generate)
- Peserta (download & view)

### 7. Dashboard Role-Based

**Deskripsi:**
Setiap role memiliki dashboard yang berbeda sesuai kebutuhan.

**SuperAdmin Dashboard:**
- Statistik keseluruhan sistem
- Data user dan admin
- Aktivitas sistem

**Admin Dashboard:**
- Statistik voting
- Periode aktif
- Progress voting
- Daftar karyawan

**Penilai Dashboard:**
- Voting yang perlu dilakukan
- Progress voting
- Riwayat voting
- Statistik personal

**Peserta Dashboard:**
- Hasil voting
- Peringkat
- Sertifikat
- Statistik personal

### 8. Pengaturan Profil

**Deskripsi:**
Pengguna dapat mengatur profil mereka sendiri.

**Fitur:**
- Edit profil (nama, email)
- Update password
- Two-factor authentication
- Hapus akun

**Role yang Dapat Mengakses:**
- Semua role

---

## 10. KEAMANAN & VALIDASI

### Form Request Classes

Validasi input menggunakan Form Request classes untuk keamanan.

#### StorePeriodRequest
**Lokasi:** `app/Http/Requests/StorePeriodRequest.php`

**Validasi:**
- `name` - required, string, max:255
- `start_date` - required, date
- `end_date` - required, date, after:start_date
- `description` - nullable, string

#### UpdatePeriodRequest
**Lokasi:** `app/Http/Requests/UpdatePeriodRequest.php`

**Validasi:**
- Sama seperti StorePeriodRequest
- Opsional untuk field yang tidak diubah

#### StoreVoteRequest
**Lokasi:** `app/Http/Requests/StoreVoteRequest.php`

**Validasi:**
- `period_id` - required, exists:periods,id
- `category_id` - required, exists:categories,id
- `votes` - required, array
- `votes.*.employee_id` - required, exists:employees,id
- `votes.*.criterion_id` - required, exists:criteria,id
- `votes.*.score` - required, numeric, between:1,5

#### ProfileUpdateRequest
**Lokasi:** `app/Http/Requests/ProfileUpdateRequest.php`

**Validasi:**
- `name` - required, string, max:255
- `email` - required, email, unique:users,email

### Perlindungan Keamanan

#### 1. CSRF Protection
- Laravel built-in CSRF token
- Setiap form POST memiliki CSRF token
- Validasi otomatis oleh Laravel

#### 2. XSS Protection
- React escaping otomatis untuk output
- Validasi input menggunakan Form Request
- Sanitasi data sebelum disimpan

#### 3. SQL Injection Prevention
- Menggunakan Eloquent ORM
- Parameter binding otomatis
- Tidak menggunakan raw query tanpa validasi

#### 4. Role-Based Authorization
- Middleware role untuk setiap route
- Policy checks untuk akses data
- Gate checks untuk operasi khusus

#### 5. Password Security
- Hashing password menggunakan bcrypt
- Password validation rules (Fortify)
- Two-factor authentication opsional

#### 6. Session Security
- Redis untuk session management
- Session timeout konfigurasi
- Secure cookie settings

---

## 11. STYLING (TAILWIND CSS V4)

### Konfigurasi Tailwind

**Lokasi:** `resources/css/app.css`

### Fitur Utama

#### 1. Dark Mode
Fully supported dengan custom color scheme.

```css
@theme {
  --color-dark-bg: oklch(0.1 0 0);
  --color-dark-text: oklch(0.9 0 0);
}
```

#### 2. Custom Theme
Menggunakan OKLCH color space untuk warna yang lebih presisi.

**Primary Colors:**
```css
--color-primary: oklch(0.6 0.2 250);
--color-secondary: oklch(0.7 0.15 200);
```

#### 3. Custom Variants
Sidebar-specific color variables.

#### 4. Font Configuration
Menggunakan 'Instrument Sans' sebagai font utama.

```css
font-family: 'Instrument Sans', sans-serif;
```

#### 5. Custom Radius
Custom border radius dengan variabel.

```css
--radius-lg: 0.5rem;
--radius-md: 0.375rem;
--radius-sm: 0.25rem;
```

### Pendekatan Styling

**Utility-First:**
- Menggunakan class Tailwind untuk styling
- Custom CSS hanya untuk spesifik case
- Responsive design dengan mobile-first approach

**Component-Based:**
- Reusable components dengan class yang konsisten
- Tailwind @apply untuk custom components
- Variant grouping untuk dark mode

---

## 12. ALUR KERJA UTAMA

### Alur Proses Voting

```
┌─────────────────┐
│   1. ADMIN      │
└────────┬────────┘
         │
         ├──► Buat Periode Voting
         │    └──► Atur tanggal dan status
         │
         ├──► Buat Kategori Penilaian
         │    └──► Kinerja, Kedisiplinan, dll
         │
         ├──► Buat Kriteria Penilaian
         │    └──► Set bobot (weight) untuk setiap kriteria
         │
         └──► Import Data Karyawan
              └──► Dari SIKEP atau manual

┌─────────────────┐
│   2. PENILAI    │
└────────┬────────┘
         │
         └──► Login ke sistem
              │
              ├──► Lihat voting yang tersedia
              │    └──► Periode dan kategori aktif
              │
              └──► Lakukan Voting
                   ├──► Pilih karyawan
                   ├──► Berikan nilai untuk setiap kriteria
                   └──► Submit voting

┌─────────────────┐
│   3. SYSTEM     │
└────────┬────────┘
         │
         └──► Calculate Scores
              ├──► Hitung skor tertimbang
              │    └─── Score = Σ(Vote × Bobot Kriteria)
              │
              ├──► Determine Winners
              │    └─── Karyawan dengan skor tertinggi
              │
              └──► Get Rankings
                   └─── Peringkat semua karyawan

┌─────────────────┐
│   4. ADMIN      │
└────────┬────────┘
         │
         └──► Generate Certificates
              ├──► Untuk setiap kategori
              ├──► Buat QR Code
              └──► Generate PDF

┌─────────────────┐
│   5. PESERTA    │
└────────┬────────┘
         │
         └──► View Results
              ├──► Lihat peringkat
              ├──► Download sertifikat
              └──► Verifikasi sertifikat
```

### Alur Data

```
Database (MySQL)
      ↓
   Eloquent Model
      ↓
   Controller
      ↓
   Service (Business Logic)
      ↓
   Inertia Props
      ↓
   React Component
      ↓
   User Interface
```

---

## 13. CARA MENJALANKAN PROYEK

### Prerequisites

**Software yang diperlukan:**
- PHP 8.4.16 atau higher
- Composer (PHP package manager)
- Node.js dan NPM
- MySQL
- Redis (opsional, untuk production)

### Instalasi

#### 1. Clone Repository
```bash
git clone <repository-url>
cd vote_system
```

#### 2. Install Dependencies

**Backend (PHP):**
```bash
composer install
```

**Frontend (Node.js):**
```bash
npm install
```

#### 3. Environment Configuration

**Copy file environment:**
```bash
cp .env.example .env
```

**Edit `.env` file:**
```env
APP_NAME="Vote System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vote_system_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### 4. Generate Application Key
```bash
php artisan key:generate
```

#### 5. Run Migrations
```bash
php artisan migrate
```

#### 6. Seed Data (Opsional)
```bash
php artisan db:seed
```

### Menjalankan Development Server

#### Opsi 1: Artisan Serve
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

#### Opsi 2: Laravel Sail (Docker)
```bash
./vendor/bin/sail up
```

#### Opsi 3: Vite Development Server

**Terminal 1 - Backend:**
```bash
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
npm run dev
```

### Build untuk Production

```bash
# Build assets
npm run build

# Optimize Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Menjalankan Tests

```bash
# Semua test
php artisan test --compact

# Test tertentu
php artisan test --compact tests/Feature/VotingTest.php

# Dengan filter
php artisan test --compact --filter=voting
```

### Maintenance Commands

```bash
# Clear cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear all cache
php artisan optimize:clear
```

---

## APPENDIX

### Struktur Direktori

```
vote_system/
├── app/
│   ├── Actions/               # Action classes (Fortify)
│   ├── Http/
│   │   ├── Controllers/       # Controllers
│   │   ├── Middleware/        # Middleware
│   │   └── Requests/          # Form Request classes
│   ├── Models/                # Eloquent Models
│   ├── Services/              # Business logic services
│   └── Providers/             # Service providers
├── bootstrap/
│   ├── app.php                # Application bootstrap
│   └── providers.php          # Service providers
├── config/                    # Configuration files
├── database/
│   ├── factories/             # Model factories
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── public/                    # Public files
├── resources/
│   ├── css/                   # CSS files
│   └── js/                    # React components & pages
│       ├── Components/        # Reusable components
│       └── Pages/             # Inertia pages
├── routes/
│   ├── console.php            # Console routes
│   ├── settings.php           # Settings routes
│   └── web.php                # Web routes
├── tests/
│   ├── Browser/               # Browser tests
│   ├── Feature/               # Feature tests
│   ├── Unit/                  # Unit tests
│   └── TestCase.php           # Base test class
├── vendor/                    # Composer dependencies
├── .env                       # Environment configuration
├── .env.example               # Environment template
├── artisan                    # Artisan console
├── composer.json              # PHP dependencies
├── package.json               # Node dependencies
├── phpunit.xml                # PHPUnit configuration
├── tailwind.config.js         # Tailwind configuration
├── vite.config.ts             # Vite configuration
└── README.md                  # Project documentation
```

### Konvensi Kode

#### PHP
- Menggunakan PSR-12 coding standard
- Type declarations untuk semua method
- Constructor property promotion
- PHPDoc blocks untuk dokumentasi

#### React/TypeScript
- Functional components dengan hooks
- Type-safe props dengan TypeScript
- Wayfinder untuk routing

#### Testing
- Menggunakan Pest syntax
- Test cases deskriptif
- Arrange-Act-Assert pattern

---

## DOKUMENTASI VERSI

**Versi:** 1.0.0
**Tanggal:** 15 Januari 2026
**Penulis:** Development Team

---

## CATATAN

1. Dokumentasi ini dibuat berdasarkan kode per tanggal 15 Januari 2026
2. Beberapa fitur mungkin telah berubah sejak dokumentasi ini dibuat
3. Untuk informasi terbaru, silakan cek source code terbaru
4. Dokumentasi ini dibuat dalam bahasa Indonesia

---

**Dokumentasi Vote System - Sistem Voting Karyawan**

Copyright © 2026. All rights reserved.
