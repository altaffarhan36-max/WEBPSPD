# Dashboard Sertifikasi Mekanik

Dashboard membaca data mekanik, hasil TLU, status tiap kriteria, dan standar passing grade dari MySQL. Sepuluh data teknisi dummy pada schema mengikuti workbook `Data_Dummy_TLU_10_Teknisi.xlsx`.

## Menjalankan di XAMPP

1. Untuk instalasi baru, di phpMyAdmin impor `database/schema.sql`. Schema membuat database `certification_db`, tabel, standar penilaian, akun pengguna, dan sepuluh contoh hasil sertifikasi.
   Jika database sertifikasi lama sudah ada, impor `database/migration_join_date.sql` satu kali. Migrasi mempertahankan data penilaian, memindahkan masa golongan lama ke tanggal join perkiraan, lalu menghapus kolom tenure yang kini dihitung otomatis.
2. Untuk koneksi MySQL selain akun `root` lokal tanpa password, salin `api/database.local.example.php` menjadi `api/database.local.php`, lalu masukkan kredensial database Anda.
3. Buka `http://localhost/trial/setup.php` untuk membuat akun login pertama.
4. Masuk melalui `http://localhost/trial/`.

Import berulang aman untuk sepuluh nama teknisi contoh: nilai contoh diperbarui, tidak dibuat sebagai baris duplikat. Schema tidak menghapus database AutoLube yang lama.

Dashboard menampilkan ringkasan Lulus/Tidak Lulus/Belum dinilai, pencarian nama, filter hasil, dan detail nilai serta status tiap kriteria. Masa golongan dan kategori golongan dihitung ulang memakai waktu MySQL saat halaman dibuka.

Untuk database yang sudah berjalan, impor `database/migration_mechanic_login.sql` satu kali agar akun memiliki role dan tautan mekanik. Akun yang sudah ada tetap menjadi Master. Impor juga `database/migration_behavior_assessments.sql` untuk tabel penilaian perilaku. Master membuat login per mekanik dari panel **Buat akses login mekanik** di dashboard; satu mekanik hanya dapat ditautkan ke satu akun.

Master memberi skor 1–4 pada 12 butir matriks. Sistem menghitung rata-rata seluruh butir dan menyatakan LULUS jika rata-rata minimal 2,00. Riwayat dan nilai per butir hanya ditampilkan pada dashboard Master. Login mekanik hanya menampilkan status LOLOS/TIDAK LOLOS terbaru dan waktu penilaian.

Workbook dummy hanya memberi masa golongan dalam satuan tahun, bukan tanggal join sebenarnya. Karena itu tanggal join contoh dibuat sebagai perkiraan. Ganti dengan tanggal bergabung aktual sebelum memakai data produksi.
