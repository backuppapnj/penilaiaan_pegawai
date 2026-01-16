# PRD Pengembangan - Pegawai Disiplin Otomatis (Rata-rata Tahunan dan Semester)

## Ringkasan
Aplikasi ini perlu menampilkan hasil penilaian Pegawai Disiplin berdasarkan rata-rata data kehadiran. Karena aplikasi baru dibuat dan diminta siang ini, sementara ini hasil otomatis menggunakan rata-rata 1 tahun penuh. Setelah data berjalan, perhitungan berubah menjadi rata-rata per semester (6 bulan) sesuai periode.

## Tujuan
- Menyajikan penilaian Pegawai Disiplin yang lebih adil dengan rata-rata periode yang jelas.
- Menghindari hasil bias karena hanya mengambil 1 bulan terbaru.
- Menyiapkan alur untuk transisi dari rata-rata tahunan ke rata-rata semester.

## Ruang Lingkup
- Perhitungan voting otomatis Pegawai Disiplin (kategori 3).
- Data sumber dari discipline_scores hasil import SIKEP per bulan.
- Penyaringan pegawai: hanya pimpinan yang dikecualikan (Ketua, Wakil, Panitera, Sekretaris).
- UI ringkas menampilkan hasil rata-rata per periode.

## Di luar Ruang Lingkup
- Perubahan struktur data pegawai (mis. status aktif/non-aktif).
- Sinkronisasi data pegawai mutasi secara otomatis.
- Perubahan logika bobot penalti dari file SIKEP.

## Definisi
- Periode aktif: periode yang statusnya open atau announced.
- Rata-rata tahunan: rata-rata score_1, score_2, score_3 dari semua bulan dalam 1 tahun.
- Rata-rata semester: rata-rata score_1, score_2, score_3 dari 6 bulan dalam semester terkait.

## Sumber Data
- discipline_scores (per employee_id, month, year).
- employees (jabatan untuk filter pimpinan).
- periods (semester, year).

## Aturan Bisnis
### 1) Filter Pegawai
- Pegawai yang dikecualikan: jabatan mengandung Ketua, Wakil, Panitera, Sekretaris.
- Hakim tetap masuk.

### 2) Periode Perhitungan
- Sementara (release awal): gunakan data 1 tahun penuh untuk tahun yang diminta.
- Setelah berjalan: gunakan data semester sesuai periode.
  - Semester genap: Januari - Juni.
  - Semester ganjil: Juli - Desember.

### 3) Metode Perhitungan
- Untuk setiap pegawai, ambil semua discipline_scores dalam rentang periode.
- Hitung rata-rata:
  - avg_score_1 = rata-rata(score_1)
  - avg_score_2 = rata-rata(score_2)
  - avg_score_3 = rata-rata(score_3)
- total_score = avg_score_1 + avg_score_2 + avg_score_3.
- Ranking berdasarkan total_score tertinggi.

### 4) Data Bulan Kosong
- Jika ada bulan tanpa data, bulan tersebut tidak dihitung (rata-rata hanya dari data yang ada).
- Sistem menampilkan indikator jumlah bulan yang berhasil dihitung.

## UI/UX
### Admin
- Halaman Pegawai Disiplin (otomatis):
  - Info box: total pegawai, rata-rata skor, skor tertinggi, periode dan jumlah bulan.
  - Tabel ranking otomatis.
  - Tombol Generate Ulang (Overwrite).

### Penilai/Peserta
- Saat periode belum diumumkan: tampilkan status menunggu.
- Saat periode diumumkan: tampilkan ranking otomatis (read-only).

## Acceptance Criteria
- [ ] Perhitungan otomatis memakai rata-rata tahunan untuk data awal.
- [ ] Setelah periode berjalan, perhitungan memakai rata-rata semester.
- [ ] Hakim tetap masuk dalam perhitungan.
- [ ] Pimpinan (Ketua, Wakil, Panitera, Sekretaris) tidak masuk perhitungan.
- [ ] UI menampilkan periode dan jumlah bulan yang dihitung.

## Risiko
- Data bulan kosong dapat membuat rata-rata terlihat tinggi jika hanya 1-2 bulan yang ada.
- Tanpa status pegawai, pegawai pindah masih bisa terhitung jika data masih ada.

## Catatan Implementasi
- Perlu fungsi agregasi per pegawai per rentang bulan.
- Generate otomatis harus memakai agregat, bukan 1 bulan terbaru.
- Tampilkan label periode (Tahunan 2025 / Semester Genap 2026) di UI.
