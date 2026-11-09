# Profil dinas, wilayah berjenjang, dan CRUD lanjutan

**Diterapkan 5 November 2026** pada `https://koperasi.projectpintar.com/`. Tema tetap **Tabler Core 1.7.1 (MIT)** dengan tata letak SIKOP khusus: krem, hijau gelap, aksen terakota, judul editorial, dan teks dengan kontras yang terbaca. Logo memakai PNG transparan dari `/www/wwwroot/pustaka/img/logo.png`, disalin ke `assets/img/pemkab-rembang.png`; blok putih pada JPEG sebelumnya tidak dipakai lagi.

## Alur yang sekarang aktif

1. **Profil dinas:** superadmin/admin membuka `/admin/profil-dinas`, mengubah nama aplikasi, pemerintah daerah, OPD, bidang, judul dan uraian beranda/tentang, alamat, telepon, email, situs, sosial, logo, dan favicon. Simpan memakai CSRF, validasi panjang/URL/gambar, serta audit. Perubahan nama, deskripsi, logo/favicon, dan tautan tampil pada portal/panel. Logo baru menerima JPG/PNG/WebP; favicon baru PNG. Berkas maksimal 2 MB.
2. **Master wilayah:** daftar provinsi, kabupaten/kota, kecamatan, desa/kelurahan memakai pilihan induk yang memuat anak sesuai jenjang dari tabel `regions`. Form tambah/ubah dan filter daftar memakai API `/admin/wilayah/anak/{id}`. Server tetap memeriksa level, status induk, awalan kode Kemendagri, dan sumber keputusan; JavaScript hanya membantu memilih. Anak aktif mencegah induk diarsipkan.
3. **Profil koperasi:** form memakai empat pilihan wilayah berjenjang. Server memeriksa hubungan provinsi→kabupaten/kota→kecamatan→desa. Koperasi di luar Kabupaten Rembang tetap dapat disimpan; `reference_only=1` jika bukan kewenangan kabupaten atau kabupaten/kotanya bukan kode `33.17`. Data awal ODS tidak dihapus.
4. **Akun koperasi:** akun awal ditautkan ke satu profil. Setelah masuk, akun ini dapat melihat/mengubah profilnya dan mengisi penilaian miliknya; profil koperasi lain dan pengaturan admin menghasilkan 403.
5. **Unit usaha:** profil koperasi sekarang punya daftar, pencarian, filter, halaman, tambah, ubah, dan arsip/pulihkan USP/USPPS. Unit aktif langsung muncul sebagai objek penilaian. Akun koperasi dapat mengelola unit miliknya; tidak dapat membuat profil koperasi baru atau membuka unit koperasi lain.
6. **Laporan tahunan/RAT:** akun koperasi mengisi anggota dan angka keuangan per tahun buku, mengunggah RAT/laporan keuangan secara privat, lalu mengirim draf terkunci. Admin lain memverifikasi atau mengembalikan dengan catatan. Pembuat atau penyunting terakhir tidak boleh menjadi verifikator. Dashboard internal menjumlahkan **volume usaha terverifikasi** untuk tahun buku terbaru dari koperasi kewenangan kabupaten; angka itu diberi label jelas dan tidak diklaim sebagai seluruh perputaran uang daerah.
7. **Menu:** sidebar dikelompokkan menjadi Data & Peta, Penilaian & Pengawasan, Layanan & Pembinaan, Publikasi, dan Pengaturan. Setiap tautan tetap mengikuti izin modul. Halaman riwayat impor dihapus dari menu dan rute; `/admin/impor` menghasilkan 404. Tabel asal impor dan skrip impor tetap ada untuk audit dan pembaruan data.

## CRUD yang tersedia menurut hak akses

| Data | Tambah | Lihat/cari/filter/halaman | Ubah | Hapus terkontrol |
|---|---|---|---|---|
| Item semua master tematik | `buat` | `lihat` | `ubah` | arsip/pulihkan dengan `nonaktifkan` |
| Provinsi sampai desa | `buat` | `lihat` dan filter induk berjenjang | `ubah` | arsip/pulihkan dengan `nonaktifkan`, anak aktif dicegah |
| Koperasi | `buat` | `lihat`, termasuk filter arsip | `ubah`; akun koperasi hanya miliknya | arsip/pulihkan dengan `nonaktifkan`; akun aktif dan lokasi KDKMP aktif harus diselesaikan dulu |
| Unit USP/USPPS | `buat` dari profil sendiri | `lihat`, cari/filter/halaman | `ubah` | arsip/pulihkan dengan `nonaktifkan` |
| Lokasi KDKMP | `buat` | `lihat`, termasuk filter arsip | `ubah` progres/lokasi | arsip/pulihkan dengan `nonaktifkan` |
| Komoditas KDKMP per periode | `buat` | `lihat` | `ubah` melalui baris yang ada | hapus dengan `nonaktifkan` dan audit |
| Laporan tahunan/RAT | `buat` | `lihat`, cari/filter/halaman | `ubah` selama draf atau dikembalikan | batalkan/aktifkan draf dengan `nonaktifkan`; setelah dikirim terkunci, verifikasi petugas berbeda |
| Penilaian KKPKK | `buat` | `lihat`, cari/filter/halaman | `ubah` selama draf | batalkan draf dengan `nonaktifkan`; rekaman dikunci tetap tak berubah |
| Pemeriksaan pengawasan | `buat` | `lihat`, cari/filter/halaman | identitas rencana dapat diubah sebelum proses berjalan | batalkan rencana tanpa temuan dengan `nonaktifkan` |
| Temuan pemeriksaan | `buat` | `lihat` | `ubah` sebelum ditutup | hapus sebelum ada tindak lanjut dengan `nonaktifkan` dan audit |
| Tiket aduan/izin/pembinaan | `buat` | `lihat`, cari/filter/halaman | ubah sebelum selesai/batal | batalkan tiket baru dengan `nonaktifkan`; kronologi tetap ada |
| Konten publik dan pengguna/peran/modul | `buat` | `lihat`, cari/filter/halaman | `ubah` | status arsip/nonaktif menurut izin yang ada |

GIS dan laporan merupakan tampilan turunan; perubahan dilakukan pada profil koperasi, lokasi KDKMP, dan transaksi sumber. Dokumen bukti yang sudah masuk, tindak lanjut, keluaran yang dikunci, jejak impor, dan audit bersifat rekaman peristiwa: koreksi dibuat sebagai status/revisi baru agar bukti lama tidak hilang. Penghapusan permanen hanya disediakan untuk komoditas dan temuan yang belum memiliki tindak lanjut, dengan audit.

Enam **pilihan awal komoditas** (beras, gula, minyak goreng, telur, pupuk, lainnya) ditambahkan agar form KDKMP dapat langsung dipakai. Ini contoh master lokal, bukan daftar komoditas wajib atau klaim hasil verval; superadmin/admin dapat menyesuaikannya lewat CRUD master.

## Akun sementara untuk serah terima

| Nama pengguna | Kata sandi | Akses awal |
|---|---|---|
| `superadmin` | `Rembang26!` | Semua modul dan pengaturan izin |
| `admin` | `Admin26!` | Operasional, verifikasi, profil dinas; tanpa manajemen pengguna |
| `koperasi` | `Koperasi26!` | **Koperasi Unit Desa Sumber Rejeki**, ID 2, NIK 3317010010001 |

Kata sandi di basis data berbentuk hash; teks di atas hanya ada di dokumen serah terima yang ditutup dari HTTP. Ganti melalui halaman Pengguna setelah serah terima. Akun `koperasi` sudah aktif, bukan contoh kosong.

## Skema dan berkas SQL

- `database/012_profile.sql`: tabel singleton `organization_profile`, data awal identitas, modul/izin profil dinas.
- `database/013_archive_permissions.sql`: `cooperatives.archived_at`, `kdkmp_sites.archived_at`, izin koperasi mengubah profil sendiri.
- `database/014_draft_permissions.sql`: izin koperasi membatalkan draf penilaian miliknya.
- `database/015_cooperative_unit_access.sql`: izin koperasi menambah unit miliknya; pembuatan profil koperasi baru tetap dibatasi oleh pemeriksaan kepemilikan.
- `database/016_annual_reports.sql`: verifikator/catatan/waktu ubah laporan, modul dan izin laporan tahunan.
- `database/017_report_separation.sql`: pembuat dan penyunting terakhir laporan untuk mencegah persetujuan diri sendiri.
- `scripts/migrate.py` menerapkan migrasi 001–017 berurutan. Skema aktif sekarang **36 tabel**. `scripts/seed_initial.py` berisi master, akun, wilayah/ODS awal, dan enam pilihan komoditas untuk pemasangan baru.

Urutan pemasangan baru: `python3 scripts/migrate.py`, `python3 scripts/seed_initial.py`, `python3 scripts/seed_kkpkk.py`, lalu `python3 scripts/seed_financial_inputs.py`. Basis data lama dimigrasikan tanpa impor ulang. Empat level wilayah tetap bersumber dari Kepmendagri 2138/2025; rekonsiliasi perubahan 2430/2025 masuk P0 berikutnya.

## Bukti pemeriksaan dan batas

- Lint PHP pada controller/view lulus. Beranda, login, profil dinas, master wilayah, API anak wilayah, profil koperasi, KDKMP, aduan, dan pengawasan merespons 200 melalui HTTPS. `/admin/impor` merespons 404.
- Login ketiga akun berhasil; akun koperasi memperoleh 200 pada profil miliknya, 403 pada profil lain dan profil dinas. Uji simpan profil dinas berhasil.
- Uji tiket: tambah → ubah → batalkan berhasil. Uji pemeriksaan: buat → ubah → batalkan berhasil. Uji penilaian: buat draf → batalkan → buat revisi baru → batalkan berhasil. Uji komoditas: tambah → ubah → hapus berhasil. Uji temuan: tambah → ubah → hapus berhasil. Uji unit USP: tambah → ubah → arsip berhasil; akun koperasi menerima 403 pada unit koperasi lain dan pembuatan profil koperasi baru. Uji laporan tahunan: simpan draf → unggah dokumen privat → kirim/kunci → verifikasi admin berbeda → angka tampil di dashboard; verifikasi oleh akun koperasi atau pembuat sendiri menghasilkan 403. Semua baris/berkas uji dibersihkan. Baris transaksi uji dibersihkan.
- Gambar beranda 1440×900 diperiksa; logo transparan tidak menampilkan blok putih.
- **Masih perlu diselesaikan:** pengurus berperiode dan kedalaman profil 360°, pratinjau konflik impor ODS, rekonsiliasi wilayah 2430/2025, kelengkapan lampiran/tenggat layanan, riwayat audit di UI, serta formula resmi delapan jalur KKPKK. Nilai/predikat dan sertifikat kesehatan tetap belum diterbitkan sebelum formula dan kewenangan disahkan. Sinkronisasi Google Sheet serta OSS masih pada tahap pemetaan/tautan, bukan integrasi otomatis.
