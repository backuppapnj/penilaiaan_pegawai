# Context: Perbaikan Kategori "Pegawai Disiplin"

## Overview

Project Vote System PA Penajam memiliki 3 kategori penilaian pegawai:
1. **Pejabat Struktural/Fungsional** - Untuk pimpinan dan pejabat
2. **Non-Pejabat** - Untuk staff non-kepala
3. **Pegawai Disiplin** - Penilaian berdasarkan data absensi SIKEP

## Problem Statement

### Masalah yang Dilaporkan

Kategori **"Pegawai Disiplin"** sudah dikonfigurasi dalam sistem dengan:
- 3 kriteria penilaian (Tingkat Kehadiran, Kedisiplinan, Ketaatan)
- Deskripsi yang jelas: "Termasuk semua pegawai KECUALI Pimpinan"

Namun saat dibuka dari halaman voting, **tidak ada pegawai yang muncul** sama sekali.

### Root Cause Analysis

#### 1. Seeder Configuration

**File: `database/seeders/CategoryAndCriteriaSeeder.php`**
- Kategori "Pegawai Disiplin" berhasil dibuat (ID: 3)
- Kriteria penilaian sudah terdefinisi dengan benar

#### 2. Employee Assignment

**File: `database/seeders/EmployeeSeeder.php`**
```php
$kategori1 = Category::where('nama', 'Pejabat Struktural/Fungsional')->first();
$kategori2 = Category::where('nama', 'Non-Pejabat')->first();

// Hanya 2 kategori yang di-query!
if (str_contains($jabatan, 'Ketua') || ...) {
    $categoryId = $kategori1?->id;
} else {
    $categoryId = $kategori2?->id;
}
```

**Problem:** Tidak ada pegawai yang di-assign ke `category_id = 3`

#### 3. Query Filter

**File: `app/Http/Controllers/VotingController.php` (baris 91-102)**
```php
$employees = Employee::with('category')
    ->where('category_id', $category->id)  // ← PROBLEM
    ->where('id', '!=', $employeeId)
    ->whereNotIn('id', $votedEmployeeIds)
    ->get();
```

**Problem:** Query menggunakan `category_id = 3` untuk mencari pegawai, tapi tidak ada pegawai dengan `category_id = 3`

---

## Current System Architecture

### Database Structure

```
┌─────────────┐         ┌─────────────┐
│  categories │         │  employees  │
├─────────────┤         ├─────────────┤
│ id (PK)     │──┐    ┌─┤ id (PK)     │
│ nama        │  └────┘ │ nama        │
│ deskripsi   │         │ jabatan     │
│ urutan      │         │ category_id │←─ FK ke categories
└─────────────┘         └─────────────┘
                              │
                              ▼
                    category_id = 1 atau 2 saja
                    (tidak ada yang 3)
```

### Data Distribution

| Kategori | ID | Jumlah Pegawai | Status |
|----------|----|----------------|--------|
| Pejabat Struktural/Fungsional | 1 | 9 | ✓ Ada |
| Non-Pejabat | 2 | 14 | ✓ Ada |
| Pegawai Disiplin | 3 | 0 | ✗ KOSONG |

---

## Business Context

### Definisi Kategori "Pegawai Disiplin"

Berdasarkan konfirmasi dari user:
- **Mencakup:** Semua pegawai KECUALI pimpinan
- **Pimpinan yang dikecualikan:**
  - Ketua
  - Wakil
  - Panitera
  - Sekretaris
- **Catatan penting:** Hakim TIDAK dikecualikan (berbeda dari deskripsi awal)

### Use Case

**Actor:** Penilai
**Goal:** Melakukan penilaian kedisiplinan pegawai berdasarkan kriteria yang sudah ditentukan
**Scope:** Semua pegawai non-pimpinan

---

## Technical Context

### Laravel Version & Stack
- Laravel 12.x
- PHP 8.4+
- Inertia.js v2 + React 19
- MySQL Database

### Related Files

| File | Path | Description |
|------|------|-------------|
| VotingController | `app/Http/Controllers/VotingController.php` | Controller untuk voting |
| CategorySeeder | `database/seeders/CategoryAndCriteriaSeeder.php` | Seeder kategori & kriteria |
| EmployeeSeeder | `database/seeders/EmployeeSeeder.php` | Seeder pegawai |

### Constraints

1. **Database Structure:** Tidak boleh mengubah struktur database jika tidak perlu
2. **Backward Compatibility:** Kategori 1 dan 2 harus tetap berfungsi normal
3. **Performance:** Query tambahan tidak boleh lambat

---

## Success Definition

Perbaikan dianggap berhasil jika:
1. Halaman voting kategori "Pegawai Disiplin" menampilkan daftar pegawai
2. Pimpinan (Ketua, Wakil, Panitera, Sekretaris) TIDAK muncul
3. Hakim BOLEH muncul dalam daftar
4. Voting bisa dilakukan dan tersimpan dengan benar
5. Kategori lain tetap berfungsi normal
