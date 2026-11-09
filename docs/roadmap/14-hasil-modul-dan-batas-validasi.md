# Hasil modul operasional, tema, dan gerbang validasi — 5 November 2026

Dokumen ini mencatat **yang benar-benar berjalan di domain** `https://koperasi.projectpintar.com/` dan membedakannya dari keputusan resmi yang belum dapat diterbitkan. Rincian akun awal ada di [10-status-implementasi.md](10-status-implementasi.md).

## Antarmuka dan tema

- **Tema dasar gratis:** Tabler Core 1.7.1, lisensi MIT, disimpan lokal di `assets/vendor/tabler/` bersama berkas lisensinya. Tata letak SIKOP tetap rancangan khusus: sidebar kerja, judul editorial, kartu dan tabel sesuai fungsi. Tidak bergantung pada CDN untuk CSS/JS inti.
- Beranda mengambil koperasi aktif kabupaten dari status ODS, KDKMP 100% dari progres dan verifikasi bukti, serta menampilkan berita/foto yang sudah diterbitkan. Nilai perputaran uang tetap kosong sampai ada laporan per periode yang sah.
- **Warna:** hijau gelap `#17312e`, latar krem `#f4f3ee`, putih, dan aksen terakota `#b35331`. Teks isi memakai hijau gelap; teks pada panel gelap memakai putih/krem. Rasio kontras pasangan utama yang dihitung: 12,47:1 (hijau/krem), 12,91:1 (judul krem/hijau), dan 5,65:1 (label terakota/krem). Ukuran teks dasar 16 px, menu 14 px, tabel 14 px, label penting sekurangnya 12–13 px. Status tidak dibedakan lewat warna saja; ada kata statusnya.
- Lambang Pemkab `assets/img/pemkab-rembang.jpeg` dan `favicon.ico` diambil dari direktori `/www/wwwroot/pustaka`; gambar diberi ukuran tetap. Pemeriksaan tangkapan layar beranda dan login menemukan serta memperbaiki lambang yang sempat terlalu besar dan judul login yang sempat kurang kontras.
- Cari, filter status/kategori, pilihan 10/25/50/100 baris, reset, keadaan kosong, dan pagination dipakai berulang pada tabel utama. Admin memakai sidebar berbasis izin modul.

## Modul yang sudah aktif

| Modul | Hasil yang dapat dipakai | URL utama |
|---|---|---|
| Penilaian mandiri KKPKK | Delapan paket berversi dari workbook lokal; 1.384 indikator biner dan 2.098 definisi sel keuangan di halaman bertahap; pilih jalur berdasarkan jenis, pola, KUK, dan unit USP/USPPS; simpan draf, kunci, revisi beralasan, unggah/verifikasi dokumen privat, berita acara pemeriksaan kelengkapan otomatis **berstatus draf** | `/admin/modul/penilaian` |
| Pengawasan | Rencana pemeriksaan, nomor tugas/PIC, kaitkan penilaian terkunci, tampilkan jawaban yang tidak terpenuhi, catat temuan dengan kode indikator opsional, tindak lanjut dan penutupan temuan, berita acara **draf**, rekap | `/admin/modul/pengawasan` |
| KDKMP | 294 profil awal; isi progres fisik, bukti foto, persetujuan bukti, verifikasi 100%, status operasional, komoditas dan riwayat progres. Angka 100% hanya naik setelah bukti dan verifikasi; perubahan data lokasi/progres mengembalikan status ke antrean verifikasi | `/admin/modul/kdkmp` |
| Aduan, izin, pembinaan | Tiket dengan nomor, PIC, status, riwayat tindak lanjut, dan berita acara draf. Publik dapat mengirim aduan dan melacak status dengan nomor serta kode akses pribadi; catatan internal tidak terlihat. Pendampingan izin mengarah ke OSS sebagai kanal resmi | `/aduan`, `/aduan/lacak`, `/admin/modul/aduan`, `/layanan`, `/pembinaan` |
| Konten publik | CRUD berita, agenda, regulasi, pengumuman, galeri, kanal sosial; foto JPG/PNG/WebP, draf/tayang/arsip; daftar dan detail publik | `/admin/modul/konten`, `/informasi` |
| Laporan | Rekap koperasi, pemeriksaan, KDKMP; cari/filter/pagination dan CSV sesuai filter. Draf monitoring pengawasan 108 kolom memakai header Google Sheet yang dibekukan; CSV menetralkan sel yang dapat dieksekusi spreadsheet | `/admin/modul/laporan` |
| Fondasi data | 557 koperasi, 91.599 wilayah, 39 definisi master tematik dengan CRUD, pengguna/peran/modul/izin, impor ODS dan GIS internal dua lapis (titik kantor koperasi dan titik lokasi KDKMP) | `/admin` |

## Ketepatan jalur KKPKK dan batasnya

Sumber: delapan berkas `docs/KKPKK/[1-8]*.xlsx` versi 211224. Kode jalur disimpan sebagai master `paket-kkpkk`; `assessment_form_versions` menyimpan nama dan SHA-256 sumber, `assessment_indicators` menyimpan baris dan rumus biner `Terpenuhi=1`, `assessment_financial_fields` menyimpan sel input rekening yang bukan rumus, dan `assessment_form_snapshots` membekukan daftar pertanyaan pada setiap asesmen. Seluruh jalur berikut diuji lewat alur HTTP buat penilaian; data uji kemudian dihapus.

| Jalur | Kegiatan | Pola | KUK | Indikator biner | Sel keuangan |
|---|---|---|---|---:|---:|
| `ksp_12` | KSP/USP | Konvensional | 1–2 | 145 | 230 |
| `ksp_34` | KSP/USP | Konvensional | 3–4 | 183 | 226 |
| `kspps_12` | KSPPS/USPPS | Syariah | 1–2 | 174 | 288 |
| `kspps_34` | KSPPS/USPPS | Syariah | 3–4 | 212 | 285 |
| `nonksp_12` | Non-KSP | Konvensional | 1–2 | 134 | 240 |
| `nonksp_34` | Non-KSP | Konvensional | 3–4 | 172 | 239 |
| `nonksp_syariah_12` | Non-KSP | Syariah | 1–2 | 163 | 295 |
| `nonksp_syariah_34` | Non-KSP | Syariah | 3–4 | 201 | 295 |

**Belum boleh menghasilkan nilai/predikat/sertifikat resmi.** Sel rekening dari sheet `Input` sudah tersedia dan dimulai kosong; angka contoh workbook tidak disalin. Rumus lintas sheet, bobot, pembulatan, dan penanganan data kosong belum seluruhnya ditranskripsi, diuji terhadap kasus acuan, dan disahkan petugas. Kunci mensyaratkan semua indikator biner terjawab, sekurangnya satu sel rekening terisi, dan satu dokumen terunggah; kunci membekukan **jawaban indikator dan isian keuangan yang telah ada**. OPD kemudian memeriksa kelengkapan dokumen; status `reviewed` bukan penetapan kesehatan. Halaman menyatakan batas ini secara eksplisit.

Permenkop UKM 9/2020 menjadi dasar pengawasan koperasi, sedangkan petunjuk teknis KKPKK yang ada di direktori sumber harus digunakan bersama ketentuan yang berlaku untuk penetapan kesehatan. Tautan rujukan dicatat di [07-dasar-hukum-dan-sumber.md](07-dasar-hukum-dan-sumber.md). Klasifikasi KUK dan kewenangan penetapan tetap harus dicek pada data koperasi dan naskah resmi sebelum sertifikat diaktifkan.

## SQL dan operasi

Jalankan `python3 scripts/migrate.py` dari akar proyek. SQL ada di `database/schema.sql` dan migrasi 002–017; semua versi 001–017 sudah diterapkan. Basis data kini mempunyai **36 tabel**. Pembaruan CRUD/profil/wilayah dijelaskan di dokumen 15. `python3 scripts/seed_initial.py` mengisi master/akun/data awal secara idempoten; `python3 scripts/seed_kkpkk.py` memasukkan delapan paket workbook berversi `review`; `python3 scripts/seed_financial_inputs.py` memasukkan 2.098 sel rekening tanpa nilai contoh. Untuk instalasi baru, urutan: migrasi, seed awal, seed KKPKK, seed isian keuangan. Kredensial basis data disimpan di `application/config/koperasi_db.php` dan tidak disajikan lewat web.

Berkas penilaian dan foto bukti KDKMP berada di `private_uploads/` yang ditolak Nginx dan disajikan hanya lewat controller berizin. Foto konten publik berada di `assets/uploads/content/` dengan nama acak dan pemeriksaan format gambar. Perubahan transaksi penting masuk `audit_events`. Peran `admin` mendapat hak verifikasi penilaian, pengawasan, dan bukti KDKMP; pengaturan peran/modul tetap milik superadmin.

## Pemeriksaan yang telah dilakukan

- HTTPS: halaman publik, login, sidebar admin, penilaian, pengawasan, KDKMP, aduan, GIS, konten, laporan, dan ekspor CSV merespons sukses. URL privat tidak terbuka langsung.
- Akun `admin` dapat masuk dan mempunyai aksi verifikasi pada penilaian, pengawasan, serta KDKMP; akses pengelolaan pengguna tetap mengembalikan 403.
- Delapan jalur KKPKK diuji dengan kombinasi jenis/pola/KUK; jumlah indikator sesuai sumber. Uji simpan jawaban, penolakan kunci tidak lengkap, unggah/unduh/verifikasi dokumen dan berita acara otomatis berstatus draf berjalan. Isian keuangan diuji: angka `1.000.000,00` disimpan sebagai `1000000.00`, nol dibedakan dari kosong, nilai bukan angka ditolak; angka contoh workbook tidak muncul. Data uji dihapus.
- Tiket, tindak lanjut, dan berita acara draf; aduan publik dengan kode pelacakan dan catatan internal tersaring; pemeriksaan, temuan, tindak lanjut; progres/foto/verifikasi KDKMP; lapisan titik lokasi KDKMP di GIS; serta publikasi konten dengan foto diuji lalu dibersihkan. Perubahan progres KDKMP dari 100 ke 90 mengembalikan status verifikasi ke `pending`; penyimpanan ulang 100 tanpa perubahan mempertahankan status semula.
- Tangkapan layar beranda, login, dan dashboard diperiksa pada 1440×900; beranda dan formulir aduan diperiksa pada 390×844. Isu ukuran logo, kontras judul di panel gelap, lebar berlebih di ponsel, dan letak tanda wajib isi pada label formulir diperbaiki. Audit aksesibilitas otomatis dan uji perangkat yang lebih luas masih perlu dilakukan.

## Prioritas lanjutan sebelum layanan resmi

1. **P1:** isian keuangan per paket sudah tersedia. Lanjutkan mesin nilai/predikat yang diuji silang dengan workbook dan studi kasus, validasi pembagi nol/pembulatan, serta pengesahan aturan dan template BAPK/LHPKK/sertifikat oleh pejabat. Baru kemudian penerbitan resmi dan QR verifikasi diaktifkan.
2. **P1:** header 108 kolom Google Sheet sudah dibekukan di `database/monitoring_template_2026.json` (SHA-256 snapshot sumber berawalan `c3dc66de903f`). Draf CSV untuk pemeriksaan selesai mengisi identitas, tanggal, PIC, aspek, dan temuan; 99 kolom lain dibiarkan kosong agar tidak mengarang data. Lanjutkan pemetaan keuangan/dokumen, kunci identitas yang aman, uji selisih, dan ekspor satu arah hasil sah. Akses API dua arah memerlukan akun layanan serta aturan konflik dari pemilik data.
3. **P1/P2:** unggah ODS lewat UI dengan pratinjau selisih/konflik, rekonsiliasi 18 desa belum cocok, penyegaran versi wilayah Kepmendagri, pengurus berperiode dan pemeriksaan mutu laporan tahunan, lampiran tindak lanjut, pencarian indikator di pengawasan, serta audit trail yang bisa ditelusuri pengguna berizin.
4. **P2:** peta KDKMP/foto publik yang disetujui, agenda publik terkurasi di beranda, dashboard pimpinan dari data periode yang tervalidasi, serta penyempurnaan kanal kontak dan notifikasi aduan tanpa membuka dokumen privat.

Data **perputaran uang** belum tersedia dari sumber terverifikasi sehingga dashboard/laporan tidak mengarang nilainya. `294` adalah jumlah profil KDKMP, bukan gerai terbangun. Data koperasi luar kewenangan tetap masuk sebagai referensi dan dikecualikan dari keputusan kabupaten.
