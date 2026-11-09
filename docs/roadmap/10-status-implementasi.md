# Status implementasi aplikasi — 5 November 2026

## Alamat dan cakupan yang benar-benar aktif

Aplikasi CI3 berjalan pada **https://koperasi.projectpintar.com/**. Kode berada di `/www/wwwroot/koperasi`, dokumen kerja di `/www/wwwroot/koperasi/docs/roadmap`. Server: PHP 8.1, MariaDB 10.11, Nginx. Rewrite mengarahkan URL tanpa `index.php` ke CI3; HTTPS sudah dapat diakses. Direktori `docs`, `application`, `system`, `database`, dan `scripts` ditutup dari HTTP.

| Bagian | Status 5 Nov 2026 | Halaman |
|---|---|---|
| Portal publik awal | Aktif: profil singkat, koperasi aktif menurut ODS, KDKMP 100% terverifikasi, berita/foto terbit, pintu masuk aduan | `/` |
| Masuk/keluar, sesi, CSRF, hash sandi | Aktif | `/login`, `/logout` |
| Dashboard admin | Aktif: 557 data, 524 daftar kabupaten, 33 referensi, 294 KDKMP, kualitas padanan desa | `/admin` |
| Direktori koperasi | Aktif: cari, filter lingkup/jenis/kecamatan/status/arsip, 10/25/50/100 per halaman, profil, CRUD unit USP/USPPS, tambah/ubah/arsip/pulihkan, audit | `/admin/koperasi` |
| Master wilayah | Aktif: 4 tingkat, pilihan berjenjang provinsi–desa, cari/filter/halaman, tambah/ubah/arsip, validasi induk dan sumber | `/admin/wilayah` |
| 39 master tematik | Aktif: katalog, daftar/cari/filter/halaman, tambah/ubah/arsip, catatan sumber resmi, audit. Dua master akses memakai layar khusus | `/admin/master` |
| Pengguna, peran, izin | Aktif: pengguna, peran, modul, matriks aksi; hanya superadmin mengelola | `/admin/pengguna`, `/admin/peran`, `/admin/modul-akses` |
| Profil dinas | Aktif: nama pemerintah, OPD, bidang, deskripsi beranda, logo/favicon, kontak, dan tautan sosial dapat diubah | `/admin/profil-dinas` |
| GIS internal | Aktif: peta interaktif dua lapis koperasi dan KDKMP, daftar sudah/belum bertitik, cari/filter/halaman, isi koordinat dari profil | `/admin/modul/gis` |
| Penilaian mandiri KKPKK | Aktif: 8 jalur, 1.384 indikator biner, 2.098 definisi sel keuangan kosong awal, draf/kunci/revisi, dokumen privat, verifikasi kelengkapan dan berita acara draf; **skor dan sertifikat belum diaktifkan** | `/admin/modul/penilaian` |
| Pengawasan | Aktif: penugasan, tautan penilaian, temuan, tindak lanjut, berita acara draf | `/admin/modul/pengawasan` |
| KDKMP | Aktif: 294 profil, tambah/ubah/arsip/pulihkan lokasi, progres, foto bukti, verifikasi, tambah/ubah/hapus komoditas | `/admin/modul/kdkmp` |
| Aduan, layanan izin, pembinaan | Aktif: tiket, PIC, status, tindak lanjut, berita acara draf; aduan publik dan pelacakan dengan kode akses; izin mengarah ke OSS | `/aduan`, `/aduan/lacak`, `/admin/modul/aduan`, `/layanan`, `/pembinaan` |
| Konten publik | Aktif: berita, agenda, regulasi, galeri, kanal sosial dan portal informasi | `/admin/modul/konten`, `/informasi` |
| Laporan tahunan/RAT | Aktif: draf angka, dokumen privat, kirim/kunci, kembali/verifikasi terpisah, arsip draf, rekap volume usaha terverifikasi di dashboard internal | `/admin/modul/laporan-tahunan` |
| Laporan | Aktif: rekap koperasi, pengawasan, KDKMP, filter dan CSV; draf monitoring 108 kolom | `/admin/modul/laporan` |

Pengelolaan master saat ini berfokus pada daftar, formulir, arsip, dan audit basis data. Layar detail/riwayat perubahan per master, impor versi master resmi, ekspor, serta penilaian dampak perubahan masih backlog. Status modul dan batas validasi dijelaskan rinci dalam [14-hasil-modul-dan-batas-validasi.md](14-hasil-modul-dan-batas-validasi.md).

## Akun awal

| Nama pengguna | Kata sandi awal | Keadaan | Tugas |
|---|---|---|---|
| `superadmin` | `Rembang26!` | Aktif | Semua modul dan matriks izin |
| `admin` | `Admin26!` | Aktif | Operasional dan verifikasi penilaian/pengawasan/KDKMP; tidak mengelola pengguna/peran |
| `koperasi` | `Koperasi26!` | Aktif | Terhubung ke **Koperasi Unit Desa Sumber Rejeki** (ID 2, NIK 3317010010001); hanya profil, unit USP/USPPS, penilaian, dan laporan tahunan miliknya. |

Kata sandi disimpan sebagai hash PHP, bukan teks di database. Tabel di atas hanya untuk serah terima awal dan berada di direktori yang tidak dapat diakses melalui web. Setelah serah terima, ganti sandi akun aktif melalui halaman pengguna. Akun koperasi awal sudah ditautkan. Akun koperasi baru dapat dibuat melalui halaman pengguna; aksesnya dibatasi pada profil koperasi terhubung.

## Hasil impor yang sudah diperiksa

- Snapshot ODS 557 baris masuk sebagai 557 identitas unik menurut NIK. Snapshot 524 baris berikutnya **memperbarui** entitas yang sama, tanpa menggandakan. Dari 557, sebanyak 524 berlingkup daftar kerja kabupaten; 15 referensi provinsi dan 18 referensi nasional disimpan sebagai data saja.
- 294 entitas terklasifikasi Koperasi Desa/Kelurahan Merah Putih. **Angka ini jumlah profil, bukan jumlah gerai terbangun atau tervalidasi 100%.** Status fisik/operasional belum tersedia dari berkas awal.
- Master wilayah berisi 38 provinsi, 514 kabupaten/kota, 7.285 kecamatan, dan 83.762 desa/kelurahan dari lampiran 2138/2025. Rembang: 14 kecamatan dan 294 desa/kelurahan.
- Semua 557 koperasi dapat dipadankan sampai kecamatan; 539 sampai desa/kelurahan. Sisa 18 nama desa dari ODS tetap disimpan sebagai teks asal dan perlu rekonsiliasi, bukan dibuang.

## Verifikasi yang dijalankan

- PHP lint pada controller dan view baru.
- Login superadmin, admin, dan koperasi berhasil; akun koperasi hanya membuka profil miliknya.
- URL publik dan seluruh menu admin, 39 halaman katalog master, empat tingkat wilayah, form akses, detail dengan ID nyata, pencarian, filter, dan halaman GIS diuji melalui HTTPS. URL yang tidak ada memang menghasilkan 404; dokumen privat terblokir.
- Alur tambah peran, tambah pengguna, dan ubah pengguna diuji lewat HTTP dengan CSRF. Data uji dibersihkan.
- Akun koperasi uji yang terhubung ke satu profil berhasil masuk hanya ke profilnya; daftar menampilkan satu koperasi, profil lain dan pengelolaan pengguna mengembalikan 403. Akun uji dibersihkan.
- Pemeriksaan ulang `seed_initial.py` tidak membuat koperasi ganda dan tidak mengulang batch impor dengan hash berkas yang sama.
- Instalasi skema 001–003 diuji pada basis data sementara yang bersih; migrasi 001–017 kini diterapkan pada aplikasi utama dan menghasilkan 36 tabel. Migrasi 003 memisahkan bentuk primer/sekunder dari lingkup kewenangan tanpa mengubah 557 identitas.
- GIS diuji dengan titik sementara: marker muncul pada filter "Sudah ada", lalu koordinat asal dipulihkan.
- Delapan jalur KKPKK, unggah dokumen, tiket, pemeriksaan/temuan/tindak lanjut, KDKMP dengan bukti 100%, konten publik dengan foto, laporan, dan CSV diuji melalui HTTPS. Data uji dibersihkan.
- Beranda, login, dan dashboard diperiksa sebagai gambar 1440×900; beranda juga diuji pada 390×844. Ukuran lambang Pemkab, kontras judul pada panel gelap, dan lebar berlebih di ponsel diperbaiki. Tema dasar Tabler Core 1.7.1 (MIT) ditambah rancangan visual SIKOP.

## Batas layanan resmi

Modul transaksi awal sudah berjalan, namun **penilaian kesehatan resmi dan penerbitan sertifikat belum tersedia**. Pertanyaan biner dan sel input keuangan KKPKK sudah tersedia; rumus lintas sheet belum selesai diuji/disahkan. Berita acara masih draf, Google Sheet belum terhubung otomatis, dan OSS hanya tautan kanal resmi. Perputaran uang tidak ditampilkan tanpa data tervalidasi. Urutan penyelesaian ada pada dokumen 13 dan 14.

## Perubahan lanjutan pada hari yang sama

Rincian alur CRUD, hierarki wilayah, profil dinas, penghapusan halaman riwayat impor, dan hasil uji baru ada pada [15-profil-wilayah-dan-crud-lanjutan.md](15-profil-wilayah-dan-crud-lanjutan.md). Data asal impor tetap disimpan untuk audit walau halamannya dihapus.
