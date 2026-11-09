# Katalog master data dan halaman CRUD

**Status 5 November 2026:** 39 master dan empat tingkat wilayah sudah memiliki daftar/cari/filter/pagination, tambah, ubah, dan arsip. Riwayat tersedia sebagai `audit_events` di basis data; layar detail/riwayat, impor/ekspor versi, serta pratinjau dampak perubahan masih prioritas berikutnya. Master `jenis-akun` dan `hak-akses` membuka layar khusus peran/modul agar tidak menyimpang dari izin yang dipakai aplikasi.

**Keputusan produk:** setiap master dalam katalog ini memiliki halaman tersendiri pada menu **Pengaturan → Master Data**. Tidak ada pilihan tetap yang hanya disimpan di kode program. Setiap halaman menyediakan **daftar/cari/filter, tambah, lihat detail, ubah, nonaktifkan/arsipkan, riwayat perubahan, impor/ekspor bila relevan**, dengan izin per aksi yang dapat diatur superadmin. Hapus permanen hanya untuk entri yang belum pernah dipakai; entri yang sudah dirujuk transaksi dinonaktifkan agar laporan historis tetap utuh.

## 1. Master wilayah sesuai kode Kemendagri

| Halaman CRUD | Isi pokok | Hubungan |
|---|---|---|
| Provinsi | Kode resmi, nama, status, tanggal berlaku, versi sumber | Induk kabupaten/kota |
| Kabupaten/Kota | Kode resmi, nama, tipe kabupaten/kota, provinsi, status, versi sumber | Induk kecamatan |
| Kecamatan | Kode resmi, nama, kabupaten/kota, status, versi sumber | Induk desa/kelurahan |
| Desa/Kelurahan | Kode resmi, nama, tipe desa/kelurahan, kecamatan, status, versi sumber | Dipakai oleh koperasi, lokasi KDKMP, dan GIS |

Dasar kode dan hierarki: [Permendagri 58/2021](https://peraturan.bpk.go.id/Details/196233/permendagri-no-58-tahun-2021), [Kepmendagri 300.2.2-2138/2025](https://peraturan.bpk.go.id/Details/322942/keputusan-mendagri-no-nomor-30022-2138-tahun), serta [perubahannya, Kepmendagri 300.2.2-2430/2025](https://peraturan.bpk.go.id/Details/322912/keputusan-mendagri-no-30022-2430-tahun-2025). Ini menjadi baseline impor; sebelum seed produksi, tim memeriksa lagi apakah ada pemutakhiran berikutnya dari Kemendagri.

**Aturan implementasi wilayah:**

1. Kode disimpan sebagai **teks**, lengkap dengan bentuk resmi dan nol di depan bila ada. Basis data memakai ID internal terpisah; kode resmi unik dalam versi yang berlaku.
2. Setiap baris memuat `kode`, `nama_resmi`, `jenis_wilayah`, `parent_id`, `status`, `berlaku_mulai`, `berlaku_sampai`, `nomor_keputusan`, `versi_dataset`, dan `sumber_berkas`. Alias nama ODS disimpan sebagai tabel pemetaan, tidak mengubah nama resmi.
3. Impor keputusan baru berjalan melalui pratinjau selisih **baru/berubah/nonaktif**, validasi rantai provinsi → kabupaten/kota → kecamatan → desa/kelurahan, lalu persetujuan admin berwenang. Kode lama tidak ditimpa atau dihapus; histori alamat koperasi tetap dapat dibaca.
4. Form alamat memakai pilihan bertingkat. Untuk koperasi binaan Rembang, provinsi dan kabupaten dapat terisi otomatis, tetapi tetap dapat ditampilkan untuk kasus cabang/mitra lintas wilayah.
5. GIS menyimpan koordinat titik tersendiri. **Kode wilayah bukan koordinat** dan tidak boleh dipakai sebagai batas peta. Batas administrasi, jika ditambahkan, harus berasal dari dataset spasial resmi dengan versi dan lisensi yang jelas.
6. CRUD manual pada master wilayah dipakai untuk koreksi/entri sementara yang diberi label `menunggu_verifikasi`. Admin tidak dapat menjadikan kode buatan lokal sebagai kode Kemendagri resmi tanpa dasar keputusan yang sah.

## 2. Katalog seluruh master aplikasi

**Setiap baris adalah satu halaman CRUD**, walaupun beberapa halaman dapat memakai komponen tabel/form yang sama. `Sumber resmi` berarti perubahan inti memerlukan rujukan peraturan atau dokumen sumber; `lokal` berarti dikelola Dinas dengan audit; `campuran` berarti ada nilai awal resmi dan tambahan lokal yang ditandai.

| Area | Halaman master | Isi/contoh | Kendali |
|---|---|---|---|
| Koperasi | Jenis koperasi | Produsen, konsumen, pemasaran, jasa, simpan pinjam, KDKMP, jenis lain berdasar aturan | Sumber resmi |
| Koperasi | Bentuk koperasi | Primer, sekunder | Sumber resmi |
| Koperasi | Pola pengelolaan | Konvensional, syariah | Sumber resmi |
| Koperasi | Kewenangan pembinaan/pengawasan | Kabupaten/kota, provinsi, pusat; dasar wilayah keanggotaan | Sumber resmi + verifikasi kasus |
| Koperasi | Kelompok/alias ODS | KUD, KPRI, KSU, kelompok lain; hanya label, bukan jenis hukum | Campuran |
| Koperasi | Sektor dan KBLI | Kode, uraian, versi KBLI, kegiatan usaha | Sumber resmi berversi |
| Koperasi | Jenis unit/jaringan | USP, USPPS, cabang, cabang pembantu, kantor kas | Sumber resmi |
| Koperasi | KUK dan batas klasifikasi | KUK 1–4, batas anggota/modal/aset, tanggal berlaku | Sumber resmi berversi |
| Koperasi | Status koperasi | Aktif/tidak aktif/penanda lain sesuai sumber dan verifikasi | Campuran; simpan sumber status |
| Koperasi | Jenis dokumen | Akta, SK BH, AD/ART, RAT, laporan keuangan, NIB, izin, surat tugas, bukti foto | Campuran |
| Penilaian | Dasar hukum | Nomor, judul, hierarki, status berlaku, tautan dokumen | Sumber resmi |
| Penilaian | Paket/versi KKPKK | Delapan jalur, periode berlaku, dasar hukum, status publikasi | Sumber resmi + pengesahan internal |
| Penilaian | Aspek dan subaspek | Tata kelola, profil risiko, keuangan, permodalan, syariah bila berlaku | Sumber resmi |
| Penilaian | Indikator/pertanyaan | Kode, teks, tipe jawaban, urutan, kondisi tampil | Sumber resmi |
| Penilaian | Pilihan jawaban | Opsi, nilai, petunjuk, berlaku untuk indikator | Sumber resmi |
| Penilaian | Rumus, bobot, dan ambang | Formula, pembulatan, bobot, batas kategori | Sumber resmi; perubahan melalui versi baru |
| Penilaian | Predikat hasil | Sehat, cukup sehat, dalam pengawasan, dalam pengawasan khusus | Sumber resmi |
| Penilaian | Kategori temuan & rekomendasi | Kode temuan, tingkat risiko, contoh tindak lanjut | Campuran; narasi akhir tetap diperiksa petugas |
| Layanan | Jenis layanan | Pendampingan izin, konsultasi, aduan, verifikasi, pembinaan | Lokal |
| Layanan | Persyaratan layanan | Dokumen wajib, kondisi berlaku, masa berlaku, urutan | Campuran dan berversi |
| Layanan | Instansi tujuan/rujukan | Dinas, DPMPTSP, OSS, provinsi, kementerian, instansi lain | Lokal |
| Layanan | Kategori dan subkategori aduan | Pelayanan, tata kelola, dugaan penyimpangan, lain-lain | Lokal |
| Layanan | Prioritas dan SLA | Urgensi, waktu respons/penanganan, kalender kerja | Lokal; sesuai standar layanan yang ditetapkan |
| Layanan | Jenis tindak lanjut | Klarifikasi, kunjungan, pembinaan, rujukan, pemeriksaan | Lokal |
| Layanan | Status dan transisi proses | Draf, dikirim, verifikasi, dikembalikan, selesai; siapa boleh memindahkan | Campuran; konfigurasi berversi |
| Dokumen | Template naskah | BAPK, lampiran, LHPKK, sertifikat, berita acara aduan, surat tugas | Lokal disahkan pejabat |
| Dokumen | Format penomoran | Kode naskah, unit, tahun, urutan; aturan nomor tidak ganda | Lokal disahkan pejabat |
| KDKMP | Tahap pembangunan/operasional | Legalitas, lahan, gerai, gudang, kelengkapan, operasional | Campuran dan berversi |
| KDKMP | Jenis fasilitas | Gerai, gudang, kantor, sarana lain | Campuran |
| KDKMP | Checklist verval | Kriteria, bukti wajib, penilai, ambang selesai | Campuran dan berversi |
| KDKMP | Komoditas | Nama, kategori, kode bila ada, jenis produk | Campuran |
| KDKMP | Satuan ukuran | Kg, ton, liter, unit, rupiah; aturan konversi bila relevan | Lokal |
| KDKMP | Jenis mitra | Pemasok, pembeli, pembiayaan, logistik, pemerintah | Lokal |
| GIS | Jenis lokasi | Kantor koperasi, unit, gerai KDKMP, gudang, lokasi kegiatan | Lokal |
| GIS | Lapisan peta | Sumber, versi, cakupan, hak tampil publik/internal | Lokal dengan sumber spasial jelas |
| Konten | Kategori berita/agenda | Berita, kegiatan, regulasi, pelatihan, pengumuman | Lokal |
| Organisasi | Unit kerja dan pejabat | Bidang, seksi/tim, jabatan, masa tugas, kewenangan tanda tangan | Lokal dengan dasar penugasan |
| Akses | Jenis akun/peran | Superadmin, admin, koperasi, dan peran tambahan | Lokal; hanya superadmin |
| Akses | Modul, aksi, dan hak akses | Matriks izin `lihat/buat/ubah/verifikasi/setujui/terbitkan/ekspor/kelola` | Lokal; hanya superadmin |

**Bukan master:** koperasi individual, pengurus, anggota, laporan keuangan, aduan, penilaian, temuan, berkas unggahan, progres lokasi, dan kegiatan pembinaan adalah data transaksi/profil. Semuanya tetap mempunyai halaman kerja yang sesuai, tetapi tidak disunting melalui katalog master agar riwayat proses tidak tercampur dengan kamus referensi.

## 3. Pola halaman CRUD yang sama untuk semua master

```text
Pengaturan > Master Data > [Nama master]

[Cari kode/nama] [Filter status/sumber/versi] [Tambah] [Impor*] [Ekspor*]
Tabel: kode | nama | induk/kategori | berlaku sejak | status | sumber | aksi
Aksi: Lihat | Ubah | Nonaktifkan/Arsipkan | Riwayat

Form: field wajib + penjelasan singkat + pemeriksaan duplikat + sumber/dasar
Simpan draf perubahan -> pratinjau dampak -> terapkan sesuai hak akses
```

`*` Impor/ekspor hanya tampil pada master yang membutuhkan dan hanya untuk pengguna berizin. Pada master resmi dan master yang memengaruhi skor, perubahan berlaku sebagai **versi baru**; penilaian yang sudah terkunci tetap memakai versi sebelumnya. UI menyampaikan dampak sebelum mengubah master yang telah digunakan, misalnya jumlah koperasi yang merujuk suatu desa, jenis koperasi, atau indikator.

## 4. Contoh kebutuhan penerimaan

- Superadmin dapat membuka setiap halaman master, menambah entri yang diizinkan, melihat, mengubah, menonaktifkan, dan melihat riwayat; admin biasa hanya mendapat aksi yang diberikan melalui matriks hak akses.
- Kode wilayah yang sama tidak dapat muncul dua kali sebagai entri aktif dalam satu versi. Desa harus mempunyai kecamatan aktif; kecamatan harus mempunyai kabupaten/kota; kabupaten/kota harus mempunyai provinsi.
- Impor versi baru Kemendagri menghasilkan laporan selisih dan tidak menghapus alamat historis koperasi.
- Master jenis/rumus KKPKK yang diubah tidak mengubah hasil penilaian tahun lalu.
- Master yang telah dipakai tidak dapat dihapus permanen; pesan UI menjelaskan jumlah rujukannya dan menawarkan nonaktifkan.
- Pencarian wilayah memakai nama resmi maupun alias ODS, tetapi formulir menyimpan kode resmi yang terverifikasi.
