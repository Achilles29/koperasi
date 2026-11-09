# Peta modul dan hak akses

## Modul inti

| Kelompok | Modul | Kegiatan dan keluaran |
|---|---|---|
| Fondasi | Master koperasi dan ODS | Import snapshot, tambah manual dari ODS, verifikasi identitas, kewenangan, histori perubahan, akun pengurus |
| Fondasi | Master data dan halaman CRUD | Wilayah provinsi–desa/kelurahan sesuai kode Kemendagri, jenis koperasi, bentuk, pola, KUK, unit usaha, versi KKPKK, indikator, bobot, dasar hukum, template dokumen; katalog lengkap pada dokumen 08 |
| Fondasi | Pengguna dan hak akses | Jenis akun, izin per modul/aksi, cakupan koperasi/wilayah, delegasi waktu terbatas, audit |
| Operasi | Laporan tahunan/RAT | Isian per tahun buku, dokumen privat, kirim terkunci, koreksi, verifikasi oleh petugas berbeda, volume usaha terverifikasi |
| Operasi | Profil 360° & dokumen | Legalitas, RAT, pengurus, unit usaha, keragaan, keuangan, lampiran, validitas dokumen |
| Operasi | Penilaian mandiri | Delapan jalur KKPKK, draf, validasi, bukti, penguncian final, revisi terkontrol |
| Operasi | Pengawasan & pemeriksaan | Rencana, penugasan, surat tugas, pemeriksaan rutin/insidental, KKPKK, temuan, BAPK, LHPKK |
| Operasi | Tindak lanjut & pembinaan | Rencana aksi, tenggat, bukti perbaikan, verifikasi penyelesaian, kegiatan pendampingan |
| Operasi | Layanan perizinan | Cek kesiapan KSP/USP/KSPPS/USPPS, panduan dan tautan OSS, status berkas, arsip izin |
| Operasi | Aduan & konsultasi | Tiket, triase, klarifikasi, tindak lanjut, berita acara, rujukan, kepuasan layanan |
| Program | KDKMP | Profil, regulasi, target, progres fisik, foto, verval, operasionalisasi, komoditas, mitra |
| Program | GIS | Titik koperasi/cabang/KDKMP, validasi koordinat, lapisan wilayah, filter dan ekspor peta |
| Informasi | Portal publik & CMS | Profil bidang, berita, foto kegiatan, agenda, regulasi, FAQ, layanan, media sosial |
| Informasi | Dashboard & pelaporan | Indikator bertanggal, rekap per kecamatan/tahun, unduh laporan, daftar anomali |
| Platform | Notifikasi & audit | Pengingat berguna, riwayat keputusan, log akses, arsip snapshot, pencadangan |

## Pengembangan yang menunjang tupoksi

| Prioritas | Modul | Nilai kerja |
|---|---|---|
| Tahap 2 | Kalender RAT dan kepatuhan administrasi | Petugas melihat koperasi terlambat RAT, dokumen kedaluwarsa, dan kebutuhan pendampingan |
| Tahap 2 | Manajemen kegiatan pembinaan | Usulan peserta, undangan, absensi, materi, hasil evaluasi, dan perubahan kapasitas pengurus |
| Tahap 2 | Early warning | Sinyal dari temuan berulang, laporan keuangan, keluhan, atau tidak melaksanakan RAT; hanya untuk prioritas pemeriksaan, bukan vonis otomatis |
| Tahap 3 | Katalog komoditas dan kemitraan KDKMP | Memetakan pasokan, kebutuhan pasar, calon pembeli, dan potensi kerja sama |
| Tahap 3 | Direktori layanan dan pusat pengetahuan | Panduan singkat sesuai jenis koperasi, contoh dokumen, tanya jawab, daftar regulasi versi terbaru |
| Tahap 3 | Evaluasi program dan dampak | Membandingkan sebelum/sesudah pembinaan dengan sumber data dan periode yang sama |
| Tahap 3 | Pertukaran data resmi | Integrasi ODS/OSS/layanan pemerintah hanya bila akses API, dasar, dan kewenangan tersedia |

## Jenis akun awal dan perluasan

Jenis akun pertama sesuai permintaan: **Superadmin, Admin, Koperasi**. Superadmin dapat menambah jenis akun, misalnya **Verifikator, Pengawas, Koordinator, Pimpinan, Petugas Aduan, Operator KDKMP, Editor Konten, Auditor baca-saja**. Nama jenis akun tidak otomatis memberi semua kewenangan: hak diberikan per modul, aksi, dan lingkup data.

| Jenis akun | Lingkup awal | Contoh izin bawaan |
|---|---|---|
| Superadmin | Seluruh sistem | Kelola peran/izin, konfigurasi, master, audit; akses dokumen sensitif tetap dicatat |
| Admin | Koperasi dalam kewenangan yang ditetapkan | Kelola master koperasi, verifikasi akun/berkas, layanan, laporan; tidak dapat mengubah hasil terkunci |
| Koperasi | Koperasi miliknya | Isi draf, unggah berkas, kirim final, lihat catatan/hasil milik sendiri, tanggapi temuan |
| Pengawas (contoh perluasan) | Penugasan yang diberikan | Periksa, buat KKPKK/BAPK/LHPKK draf, usulkan hasil; tidak menetapkan sendiri sertifikat |
| Pimpinan (contoh perluasan) | Wilayah dan unitnya | Lihat laporan, setujui keputusan sesuai pendelegasian/ketentuan |

**Aksi izin standar:** `lihat`, `buat`, `ubah_draf`, `kirim`, `verifikasi`, `tugaskan`, `periksa`, `tandatangani`, `setujui`, `terbitkan`, `ekspor`, `kelola`. Setiap izin dibatasi lagi oleh kepemilikan koperasi, wilayah, penugasan, dan status proses. Superadmin dapat mengonfigurasi matriks melalui antarmuka, namun perubahan hak dan penyalahgunaan hak tetap masuk audit.

## Batas akses penting

- Akun koperasi tidak dapat melihat nilai, dokumen, aduan, atau lokasi privat koperasi lain.
- Pengawas yang belum ditugaskan tidak dapat mengubah berkas pemeriksaan.
- Pembuat laporan tidak menjadi penyetuju tunggal untuk laporan yang sama bila alur memerlukan pemisahan tugas.
- Petugas aduan melihat identitas pelapor hanya jika ditugaskan; identitas tidak disalin ke laporan publik.
- Editor konten tidak mendapat akses ke data keuangan/aduan.
- Hak pada dokumen dan API sama dengan hak pada halaman; menyembunyikan tombol saja tidak cukup.
