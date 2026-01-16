# Alur Kerja (Workflow) Penilaian Pegawai

Dokumen ini menjelaskan langkah-langkah proses penilaian pegawai dalam sistem, mulai dari login hingga penerbitan sertifikat.

---

## 1. Tahap Autentikasi (Login)

- **Akses Halaman**: Buka URL aplikasi (halaman utama/login).
- **Input Kredensial**: Masukkan email dan password yang terdaftar.
- **Verifikasi 2FA (Opsional)**: Jika fitur keamanan dua faktor aktif, masukkan kode verifikasi.
- **Redirect Dashboard**: Sistem akan mengarahkan user ke dashboard sesuai role (SuperAdmin, Admin, Penilai, atau Peserta).

---

## 2. Tahap Persiapan (Admin/SuperAdmin)

Sebelum penilaian dimulai, Admin harus memastikan data pendukung sudah siap:

- **Manajemen Periode**: Membuat periode penilaian baru (misal: "Penilaian Januari 2026").
- **Manajemen Kategori**: Menentukan kategori penilaian (misal: Kinerja, Perilaku).
- **Manajemen Kriteria**: Menentukan kriteria detail untuk setiap kategori beserta bobotnya (weight).
- **Data Pegawai**: Memastikan daftar pegawai yang akan dinilai sudah lengkap atau melakukan import data pegawai.
- **Import SIKEP (Opsional)**: Melakukan import data kehadiran dari Excel SIKEP untuk perhitungan skor kedisiplinan otomatis.

---

## 3. Tahap Penilaian (Penilai)

Penilai melakukan evaluasi terhadap pegawai:

- **Menu Voting**: Masuk ke menu "Voting" di dashboard Penilai.
- **Pilih Periode & Kategori**: Memilih periode aktif dan kategori yang ingin dinilai.
- **Pemberian Nilai**: Memberikan skor (skala 1-5) untuk setiap pegawai pada kriteria yang tersedia.
- **Submit**: Menyimpan hasil penilaian ke sistem.

---

## 4. Tahap Perhitungan & Penentuan Pemenang (Sistem/Admin)

- **Perhitungan Skor**: Sistem secara otomatis menghitung skor akhir berdasarkan bobot kriteria menggunakan `ScoreCalculationService`.
- **Monitoring**: Admin memantau progress voting melalui dashboard.
- **Penentuan Pemenang**: Sistem menentukan peringkat dan pemenang berdasarkan skor tertinggi di setiap kategori.

---

## 5. Tahap Sertifikasi & Hasil (Admin & Peserta)

- **Generate Sertifikat (Admin)**: Admin melakukan generate sertifikat PDF untuk para pemenang.
- **Lihat Hasil (Peserta)**: Pegawai (Peserta) login untuk melihat hasil penilaian dan peringkat mereka.
- **Download Sertifikat (Peserta)**: Pemenang dapat mengunduh sertifikat ber-QR Code dari menu "Sertifikat".
- **Verifikasi (Publik)**: Pihak luar dapat memverifikasi keaslian sertifikat melalui fitur scan QR Code.
