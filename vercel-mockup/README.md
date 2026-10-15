# Mockup statis People Development

Folder ini berisi pratinjau frontend untuk deployment statis di Vercel. Alurnya dimulai dari login demo, berlanjut ke menu pemilihan modul, lalu ke halaman Man Power, Training, Certification, atau Kalkulator MPP. Gunakan akun tampilan `admin` / `demo123`; formulir login hanya simulasi dan tidak memverifikasi akun. Halaman tidak memakai PHP, API, atau database. Angka dan nama yang ditampilkan hanya contoh; filter tabel dan kalkulator MPP berjalan di browser.

## Deploy ke Vercel

1. Push folder ini ke repository GitHub.
2. Di Vercel pilih **Add New → Project**, lalu impor `altaffarhan36-max/WEBPSPD`.
3. Pada **Root Directory**, pilih `vercel-mockup`.
4. Gunakan Framework Preset **Other**. Biarkan Build Command dan Output Directory kosong.
5. Klik **Deploy**. Vercel akan menyajikan `index.html` dan file statis di folder ini.

Untuk proyek yang sudah terlanjur dibuat di Vercel, ubah **Settings → Build and Deployment → Root Directory** menjadi `vercel-mockup`, simpan, lalu lakukan redeploy.
