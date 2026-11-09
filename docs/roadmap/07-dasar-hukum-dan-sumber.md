# Dasar hukum dan jejak sumber

**Tanggal pemeriksaan sumber:** 5 November 2026. Tautan di bawah adalah acuan rancangan, bukan pengganti pemeriksaan hukum oleh pejabat berwenang saat sistem dioperasikan. Bila aturan berubah, master regulasi dan formulir harus diberi versi baru dengan tanggal berlaku.

## Regulasi utama

| Sumber resmi | Pokok yang dipakai dalam rancangan |
|---|---|
| [Permenkop UKM 9/2020 tentang Pengawasan Koperasi](https://peraturan.bpk.go.id/Details/160765/permenkop-ukm-no-9-tahun-2020) | Kewenangan pengawasan menurut wilayah keanggotaan, KUK, KKPKK, BAPK/LHPKK, empat predikat kesehatan, sertifikat dan tindak lanjut |
| [Juknis Deputi Bidang Perkoperasian 15/2021](https://jdih.kop.go.id/doc/detail/doc-925-v_peraturan) | Pedoman KKPKK, tata cara penilaian mandiri/pemeriksaan, format BAPK/LHPKK dan pemantauan |
| [Permenkum 13/2025 tentang Pengesahan Koperasi](https://peraturan.bpk.go.id/Details/317736/permenkum-no-13-tahun-2025) | Master jenis koperasi, termasuk Koperasi Desa/Kelurahan Merah Putih |
| [Permenkop UKM 8/2023 tentang Usaha Simpan Pinjam oleh Koperasi](https://peraturan.bpk.go.id/Details/260414/permenkop-ukm-no-8-tahun-2023) | Kegiatan dan pengawasan usaha simpan pinjam; digunakan bersama aturan perizinan yang lebih baru |
| [PP 28/2025 tentang Perizinan Berusaha Berbasis Risiko](https://peraturan.bpk.go.id/Details/319773/pp-no-28-tahun-2025) | Kerangka OSS yang berlaku dan mencabut PP 5/2021 |
| [Permenkop 4/2025 tentang Standar Kegiatan Usaha Perizinan Berusaha Sektor Perkoperasian](https://jdih.kop.go.id/doc/detail/doc-1205-v_peraturan) | Standar kegiatan dan perizinan KSP/USP/KSPPS/USPPS serta pengawasan izin |
| [Permen Investasi/Kepala BKPM 5/2025](https://peraturan.bpk.go.id/Details/332573) | Tata cara perizinan melalui OSS berdasarkan PP 28/2025 |
| [Inpres 9/2025 tentang Percepatan Pembentukan KDKMP](https://jdih.kop.go.id/doc/download/file/1747794373_3_Inpres%20No%209%20Tahun%202025_Pembentukan%20KDMP%20%2827%20Maret%202025%29%20.pdf/t/peraturan) | Program pembentukan KDKMP dan koordinasi lintas pihak |
| [Inpres 17/2025 tentang Percepatan Pembangunan Fisik KDKMP](https://jdih.kop.go.id/doc/detail/doc-1210-v_peraturan) | Rujukan program pembangunan gerai, pergudangan, dan kelengkapan fisik |
| [Juklak Menteri Koperasi 1/2025 tentang Pembentukan KDKMP](https://jdih.kop.go.id/doc/download/file/1745222907_JUKLAK%20NOMOR%201%20KOPDES%20MERAH%20PUTIH%20SALNAN.pdf/t/peraturan) | Rujukan tahapan pembentukan |
| [Permenkop 2/2025 tentang Pengembangan Usaha KDKMP](https://jdih.kop.go.id/doc/detail/doc-1198-v_peraturan) | Pengembangan usaha dan potensi lokal/komoditas KDKMP |
| [UU 27/2022 tentang Pelindungan Data Pribadi](https://peraturan.bpk.go.id/Details/229798/uu-no-27-) | Pembatasan akses, pemrosesan dan pelindungan data pengurus/pelapor |
| [Permendagri 58/2021 tentang Kode, Data Wilayah Administrasi Pemerintahan, dan Pulau](https://peraturan.bpk.go.id/Details/196233/permendagri-no-58-tahun-2021) | Kerangka master wilayah provinsi sampai desa/kelurahan |
| [Kepmendagri 300.2.2-2138/2025](https://peraturan.bpk.go.id/Details/322942/keputusan-mendagri-no-nomor-30022-2138-tahun) dan [perubahan 300.2.2-2430/2025](https://peraturan.bpk.go.id/Details/322912/keputusan-mendagri-no-30022-2430-tahun-2025) | Baseline kode dan nama wilayah untuk impor master; perlu cek pemutakhiran terbaru sebelum seed produksi |

**Catatan waktu:** [JDIH Kemenkop pada Agustus 2026](https://jdih.kop.go.id/berita/detail/berita-163-v_berita) masih mencatat harmonisasi rancangan Perpres tata kelola KDKMP. Rancangan tidak boleh diperlakukan sebagai aturan yang telah berlaku. Daftar peraturan KDKMP harus diperiksa kembali ketika modul dibangun.

## Berkas lokal yang telah dibaca

- Dua daftar koperasi binaan di direktori induk `docs/`, masing-masing snapshot 557 dan 524 entri.
- Delapan workbook KKPKK bernomor 1–8 di `docs/KKPKK/`, ditambah `KKPKK Non KSP I dan II - STUDI KASUS.xlsx`.
- `docs/KKPKK/Juknis No. 15 Tahun 2021 ttg Pedoman KKPKK-compressed.pdf`.
- `docs/BAPK - LHPK/BAPKK.docx` dan `BAPKK LAMPIRAN.docx`; berkas LHPKK lama berformat `.doc` tersedia untuk pembandingan tata naskah saat implementasi.
- `docs/SELF ASSESMENT/` memuat panduan pengguna koperasi dan pengawas dari sistem pengawasan kementerian; dipakai sebagai inspirasi langkah pengguna, bukan bukti bahwa aplikasi lokal terhubung dengan sistem pusat.
- [Google Sheet monitoring pengawasan](https://docs.google.com/spreadsheets/d/1JRYIsUpQXDCUChQBTs2wpsgxX16r2VVHjXk-BE0NPsc/edit?gid=0#gid=0), diperiksa melalui ekspor CSV publik pada tanggal di atas. Akses dan struktur lembar dapat berubah.

## Istilah kerja

| Istilah | Arti dalam rancangan |
|---|---|
| ODS | Sumber daftar koperasi binaan yang diberikan, diimpor sebagai snapshot; tidak diasumsikan ada API |
| KKPKK | Kertas Kerja Pemeriksaan Kesehatan Koperasi |
| BAPK | Berita Acara Pemeriksaan Kesehatan Koperasi |
| LHPKK | Laporan Hasil Pemeriksaan Kesehatan Koperasi |
| KUK | Klasifikasi Usaha Koperasi 1–4 |
| KDKMP | Koperasi Desa/Kelurahan Merah Putih |
| OSS | Sistem perizinan berusaha resmi; aplikasi lokal membantu dan merekam layanan sesuai kewenangan |
