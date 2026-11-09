# Roadmap SIKOP Rembang

**Status:** CI3 dan modul transaksi awal aktif, 5 November 2026; penilaian/sertifikat resmi menunggu validasi aturan dan formula. **Alamat:** https://koperasi.projectpintar.com/. **Pemilik proses:** Bidang Koperasi, Dinas Perdagangan, Koperasi dan UKM Kabupaten Rembang. Nama **SIKOP Rembang** adalah nama kerja yang dapat diganti.

## Visi

Satu ruang kerja untuk mengenali setiap koperasi, melayani kebutuhannya, mencatat pembinaan, memeriksa kesehatannya, dan memantau tindak lanjut. Pengurus mengisi data sekali; petugas memakai data yang sama untuk pengawasan, perizinan, peta, pelaporan, dan keputusan. Masyarakat memperoleh informasi yang memang layak dipublikasikan.

## Baca dokumen ini berurutan

| Dokumen | Isi |
|---|---|
| [01-gambaran-produk.md](01-gambaran-produk.md) | Gambaran umum, layar, nilai manfaat, ukuran keberhasilan |
| [02-proses-bisnis.md](02-proses-bisnis.md) | Alur kerja lintas peran, status, dan keputusan |
| [03-modul-dan-hak-akses.md](03-modul-dan-hak-akses.md) | Peta modul, menu, peran dan izin |
| [04-data-penilaian-integrasi.md](04-data-penilaian-integrasi.md) | Master jenis koperasi, delapan form KKPKK, sumber data, sinkronisasi |
| [05-template-berita-acara.md](05-template-berita-acara.md) | Rancangan form BAPK, LHPKK, sertifikat, dan berita acara aduan |
| [06-rencana-implementasi.md](06-rencana-implementasi.md) | Tahapan pembangunan CI3, arsitektur, penerimaan, dan keputusan yang perlu ditetapkan |
| [07-dasar-hukum-dan-sumber.md](07-dasar-hukum-dan-sumber.md) | Jejak dokumen lokal dan sumber regulasi resmi |
| [08-katalog-master-dan-crud.md](08-katalog-master-dan-crud.md) | Semua master dan halaman CRUD, termasuk kode wilayah Kemendagri |
| [09-pengembangan-lanjutan.md](09-pengembangan-lanjutan.md) | Prioritas fitur lanjutan yang menunjang tupoksi |
| [10-status-implementasi.md](10-status-implementasi.md) | Yang sudah aktif di domain, akun awal, hasil verifikasi, dan batas rilis |
| [11-rancangan-basis-data.md](11-rancangan-basis-data.md) | 36 tabel, relasi, kunci, aturan versi, dan migrasi |
| [12-impor-awal-dan-operasional.md](12-impor-awal-dan-operasional.md) | Pemetaan Excel ODS, wilayah Kemendagri, hasil impor, cara menjalankan ulang |
| [13-prioritas-rilis-berikutnya.md](13-prioritas-rilis-berikutnya.md) | Urutan P0–P3, kriteria penerimaan, dan pola UI konsisten |
| [14-hasil-modul-dan-batas-validasi.md](14-hasil-modul-dan-batas-validasi.md) | Modul aktif, delapan jalur KKPKK, tema, SQL, uji, dan batas resmi |
| [15-profil-wilayah-dan-crud-lanjutan.md](15-profil-wilayah-dan-crud-lanjutan.md) | Profil dinas, master wilayah berjenjang, CRUD lanjutan, akun aktif, dan hasil uji |

## Prinsip rancangan

1. **Satu identitas koperasi.** NIK koperasi dan nomor badan hukum dipakai untuk rekonsiliasi; nama dan nomor baris Excel bukan kunci utama.
2. **Satu data, banyak pekerjaan.** Dokumen, angka, lokasi, dan temuan yang sudah sah dapat dipakai ulang dengan asal data dan tahun buku yang jelas.
3. **Pengisian ringan.** Simpan draf, lanjutkan nanti, tampilkan hanya pertanyaan yang berlaku, periksa kelengkapan sebelum pengiriman.
4. **Keputusan dapat ditelusuri.** Setiap pemeriksaan, koreksi, persetujuan, dan penerbitan menyimpan petugas, waktu, dasar, serta versi aturan.
5. **Kewenangan jelas.** Koperasi lintas wilayah tetap dapat dipetakan dan dipantau sesuai kewenangan daerah, tetapi penilaian/keputusan resmi diarahkan kepada instansi berwenang.
6. **Informasi publik terpilah.** Berkas keuangan, aduan, identitas pribadi, dan catatan pengawas berada di ruang privat.

## Hasil saat ini

Fondasi dan transaksi awal sudah berjalan: login/hak akses, profil dinas yang dapat diubah, 557 profil koperasi, 39 master dan 4 tingkat wilayah berjenjang, CRUD unit USP/USPPS, laporan tahunan/RAT dengan verifikasi, GIS, delapan jalur isian KKPKK dengan indikator dan rekening keuangan, pengawasan, KDKMP, tiket layanan termasuk aduan publik, konten, dan laporan. Rincian aktual ada di dokumen 10 dan 14. Koneksi dua arah ODS/OSS/Google Sheets dan sertifikat resmi belum diaktifkan. Rumus lintas sheet KKPKK harus ditranskripsi, diuji, dan disahkan sebelum sistem menghasilkan predikat resmi.
