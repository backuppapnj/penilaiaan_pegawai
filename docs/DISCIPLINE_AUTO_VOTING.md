# Penilaian Otomatis Pegawai Disiplin

## Overview

Sistem penilaian untuk kategori **Pegawai Disiplin** sekarang dapat dilakukan secara **OTOMATIS** berdasarkan data kehadiran dari SIKEP (`discipline_scores`).

## Cara Menggunakan

### Generate Votes dari Discipline Scores

```bash
# Basic usage (gunakan admin sebagai voter)
php artisan discipline:generate-votes 1

# Dengan voter tertentu
php artisan discipline:generate-votes 1 --voter=5

# Overwrite votes yang sudah ada
php artisan discipline:generate-votes 1 --voter=1 --overwrite
```

### Parameter

| Parameter | Deskripsi | Contoh |
|-----------|-----------|--------|
| `period` | Period ID (wajib) | `1` |
| `--voter=` | User ID sebagai voter (opsional) | `--voter=5` |
| `--overwrite` | Timpa vote yang sudah ada (opsional) | `--overwrite` |

## Mapping Skor

| Discipline Score | Kriteria Voting | Bobot |
|------------------|-----------------|-------|
| `score_1` | Tingkat Kehadiran & Ketepatan Waktu | 50% |
| `score_2` | Kedisiplinan (Tanpa Pelanggaran) | 35% |
| `score_3` | Ketaatan (Tanpa Izin Berlebih) | 15% |

## Workflow

1. **Import Data SIKEP**: Import data kehadiran dari Excel menggunakan `SikepImportService`
2. **Generate Votes**: Jalankan command `php artisan discipline:generate-votes`
3. **Review Results**: Votes otomatis tersimpan di database

## Data Sources

- **Excel Files**: `docs/rekap_kehadiran/` - Data kehadiran bulanan dari SIKEP
- **Database Table**: `discipline_scores` - Skor yang sudah dihitung
- **Output**: `votes` + `vote_details` - Hasil voting otomatis

## Service: DisciplineVoteService

Lokasi: `app/Services/DisciplineVoteService.php`

### Methods

```php
// Generate votes
$result = $service->generateVotes($periodId, $voterId, ['overwrite' => false]);

// Get summary
$summary = $service->getSummary($periodId);

// Check if votes exist
$hasVotes = $service->hasDisciplineVotes($periodId);
```

## Status Saat Ini

| Metric | Value |
|--------|-------|
| Discipline Scores | 304 records |
| Votes Generated | 29 votes |
| Vote Details | 87 records |
| Status | ✅ Ready |

## Catatan Penting

1. **Voter Default**: Jika tidak di-specify, system akan auto-select user dengan role Admin/SuperAdmin
2. **Duplicate Prevention**: System akan skip vote yang sudah ada kecuali menggunakan `--overwrite`
3. **Category ID**: Kategori Pegawai Disiplin harus memiliki ID = 3
4. **Criteria**: Harus ada tepat 3 kriteria untuk Pegawai Disiplin
