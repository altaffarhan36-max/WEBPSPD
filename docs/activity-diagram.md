# Activity Diagram — Sistem Sertifikasi Mekanik

![Activity Diagram Sistem Sertifikasi Mekanik](activity-diagram.svg)

```mermaid
flowchart TD
    A([Mulai]) --> B[Pengguna membuka web]
    B --> C[Masukkan username dan password]
    C --> D{Akun ditemukan dan password valid?}
    D -- Tidak --> E[Tampilkan pesan login gagal]
    E --> C
    D -- Ya --> F{Periksa role akun}

    F -- Master --> M1[Tampilkan rekap seluruh mekanik]
    M1 --> M2{Pilih aktivitas}
    M2 -- Lihat rekap --> M3[Lihat grade, data sertifikasi,
    STPR terbaru, dan Detail]
    M3 --> M1
    M2 -- Buat akun Mechanic --> M4[Isi akun dan tautkan
    ke satu mekanik]
    M4 --> DB1[(Simpan hash password
    dan relasi akun di MySQL)]
    DB1 --> M1
    M2 -- Buat akun GL --> M5[Isi username dan password GL]
    M5 --> DB2[(Simpan akun GL
    dengan password hash)]
    DB2 --> M1
    M2 -- Keluar --> Z([Selesai])

    F -- GL --> G1[Tampilkan form penilaian
    dan rekap penilaian milik GL]
    G1 --> G2[Pilih mekanik dan isi
    nilai untuk 12 kriteria]
    G2 --> G3{Semua nilai valid
    pada skala 1–4?}
    G3 -- Tidak --> G4[Tampilkan pesan validasi]
    G4 --> G2
    G3 -- Ya --> G5[Hitung nilai rata-rata]
    G5 --> G6{Rata-rata minimal 2,00?}
    G6 -- Ya --> G7[Tetapkan hasil LULUS]
    G6 -- Tidak --> G8[Tetapkan hasil TIDAK LULUS]
    G7 --> DB3[(Simpan penilaian,
    skor, GL, dan waktu di MySQL)]
    G8 --> DB3
    DB3 --> G9[Tampilkan rekap penilaian
    yang dibuat akun GL ini]
    G9 --> G10[Hasil terbaru tersedia
    pada STPR rekap Master]
    G10 --> G11{Isi penilaian lagi?}
    G11 -- Ya --> G1
    G11 -- Tidak, keluar --> Z

    F -- Mechanic --> K1{Akun tertaut
    ke data mekanik?}
    K1 -- Tidak --> K2[Tampilkan pesan hubungi Master]
    K1 -- Ya --> K3[Ambil data hanya untuk
    mechanic_id akun ini]
    K3 --> K4[Tampilkan masa bergabung,
    passing duration, ATR,
    dan Individual Improvement]
    K4 --> K5[Tampilkan hasil penilaian
    terbaru: LOLOS atau TIDAK LOLOS]
    K5 --> K6{Keluar?}
    K6 -- Tidak --> K4
    K6 -- Ya --> Z
```

## Aturan akses

- **Master** melihat rekap semua mekanik dan dapat membuat akun Mechanic/GL. Form penilaian dan riwayat penilaian tidak ditampilkan untuk Master.
- **GL** mengisi form penilaian dan melihat rekap yang dibuat dari akunnya sendiri.
- **Mechanic** hanya melihat data profil dan hasil penilaian miliknya sendiri.
- Hasil GL menjadi nilai STPR terbaru pada rekap Master. Passing grade penilaian adalah rata-rata **2,00 dari 4,00**.
