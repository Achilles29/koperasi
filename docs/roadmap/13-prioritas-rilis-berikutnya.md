# Prioritas penyelesaian setelah modul awal

Status implementasi per 5 November 2026 ada di dokumen 14. Beberapa pekerjaan di bawah sudah memiliki alur awal; butir ini menjelaskan penyelesaian mutu dan penerimaan resmi. Rencana ini mengikuti urutan kebutuhan yang disebutkan: identitas koperasi, penilaian/pengawasan, KDKMP, layanan, aduan, dan informasi publik. Setiap tahap harus memberi pekerjaan yang lebih singkat kepada koperasi dan petugas; satu data dimasukkan sekali dan dipakai ulang.

**Pembaruan 5 November 2026:** identitas dinas kini bisa diubah, pilihan wilayah berjenjang aktif, akun koperasi awal terhubung, serta CRUD inti diperluas. Prioritas di bawah hanya memuat pekerjaan yang masih diperlukan untuk mutu operasional.

## P0 — Tuntaskan mutu data dan kendali dasar

| Pekerjaan | Keluaran yang dapat diuji |
|---|---|
| Rekonsiliasi 18 nama desa ODS dan versi wilayah Kemendagri 2430/2025 | Antrean selisih, pilihan kode resmi, alasan koreksi, asal baris tetap tersimpan; tidak ada perubahan massal tanpa pratinjau |
| Impor ODS berulang melalui UI | Unggah Excel, pratinjau baru/berubah/konflik, cek NIK dan nomor badan hukum, setujui batch, rollback terkontrol; referensi luar kabupaten tetap terpisah |
| Profil koperasi 360° | Pengurus berperiode, pemeriksaan mutu RAT/laporan tahunan, riwayat sumber dalam satu profil; formulir angka dan dokumen privat dasar sudah aktif |
| Hak akses dan audit | Peran koperasi hanya melihat/mengisi profilnya; superadmin dapat tambah peran dan atur aksi per modul; layar riwayat audit dengan filter |
| Pola UI dan kualitas | Selesaikan konsistensi cari/filter/pagination pada subdata dan tambah validasi dekat field, aksesibilitas papan ketik, serta simpan draf untuk formulir panjang |

## P1 — Inti sistem informasi kesehatan dan pengawasan

1. **Paket KKPKK berversi.** Delapan jalur dan 1.384 indikator biner sudah masuk. Isian keuangan sudah tersedia. Lanjutkan transkripsi formula lintas sheet, bobot, pembulatan, serta pembanding hasil untuk kasus studi. Petugas mengesahkan tiap jalur sebelum skor resmi aktif.
2. **Penilaian mandiri.** Jalur, isian biner, unggah bukti, draf, kunci, dan revisi sudah aktif. Tambahkan validasi silang dokumen, ringkasan sebelum kirim, dan petunjuk yang lebih singkat untuk pengurus.
3. **Verifikasi OPD dan pengawasan.** Penugasan, kaitan penilaian, temuan, tanggapan, tenggat, dan tindak lanjut awal sudah aktif. Lengkapi nilai koreksi, lampiran bukti tindak lanjut, pemeriksa jamak, pemeriksaan lapangan, serta register monitoring yang memakai ID temuan yang sama.
4. **Naskah otomatis terkontrol.** Template berita acara pada dokumen 05 menjadi PDF draf dari fakta pemeriksaan, lalu diperiksa pejabat, diberi nomor, disahkan, dan dikunci. Sertifikat hanya untuk hasil sah dengan predikat dan pejabat berwenang; kode verifikasi publik menampilkan informasi minimum.
5. **Monitoring Google Sheet.** Bekukan peta 108 kolom dan hak akses, tampilkan selisih, ekspor hasil yang disahkan. Integrasi dua arah hanya setelah aturan konflik dan izin pemilik lembar ada. Perubahan di Sheet tidak menimpa keputusan resmi.

**Gerbang penerimaan P1:** setiap satu dari delapan jalur cocok dengan workbook acuan untuk kasus normal, batas skor, data kosong, dan pembagi nol; hasil yang terkunci tidak dapat diubah lewat URL/API; BAPK, LHPKK, dan monitoring menunjuk ID pemeriksaan/temuan yang sama.

## P2 — KDKMP, pelayanan, dan lapangan

| Modul | Proses yang perlu dibangun | Hasil praktis |
|---|---|---|
| KDKMP | Profil 294 koperasi awal, lokasi/fasilitas, foto bukti, checklist verval, verifikator, progres fisik dan operasional, komoditas per periode/satuan/mitra | Angka **terbangun 100%** hanya dari progres yang diverifikasi beserta bukti; bedakan terbangun dari beroperasi |
| GIS | Titik kantor dan KDKMP, validasi koordinat, foto yang disetujui, lapisan per kecamatan, status belum bertitik | Peta kerja internal dan peta publik terpilah; versi dasar peta jelas |
| Aduan dan CS | Form tiket singkat, nomor dan status, prioritas/SLA, kerahasiaan, PIC, kronologi, klarifikasi, tindak lanjut, berita acara, rujukan | Pelapor tidak perlu mengulang cerita; petugas melihat tenggat dan bukti penyelesaian |
| Pendampingan izin KSP/USP | Pemeriksaan kebutuhan, dokumen, status pendampingan, instansi berwenang, tautan resmi OSS, pencatatan nomor izin dari kanal resmi | Tidak membuat izin lokal yang menyaingi OSS; pengguna paham langkah berikutnya |
| Pembinaan dan RAT | Kalender, kewajiban dokumen, undangan/pelatihan, konsultasi, bukti kegiatan, capaian | Petugas bisa melihat yang terlambat dan dampak pembinaan |

## P3 — Portal publik dan pengambilan keputusan

- CMS berita, profil bidang, foto kegiatan, agenda, kumpulan regulasi, kontak, sosial, dan layanan aduan publik; semua publikasi melalui pratinjau dan persetujuan editor.
- Dashboard pimpinan dengan definisi indikator dan periode: koperasi aktif, RAT, volume usaha dan perputaran uang **hanya dari laporan yang terverifikasi**, KDKMP terbangun/beroperasi, penilaian, temuan, aduan, izin, dan kualitas data. Tampilkan sumber dan tanggal pembaruan.
- Laporan standar serta ekspor data agregat dengan metadata; verifikasi dokumen lewat QR; notifikasi status kasus. Analitik risiko dan rekomendasi pembinaan muncul setelah data historis memadai.

## Pola pengalaman yang konsisten

- Sidebar admin dikelompokkan menurut pekerjaan harian; menu hanya muncul bagi peran yang berizin. Judul, breadcrumb, tombol utama, dan umpan balik memakai letak yang sama.
- Pencarian mempertahankan kata kunci ketika filter/halaman berubah. Filter hanya memuat pilihan yang ada dalam data atau master aktif; hasil kosong menjelaskan penyebab dan tombol reset. Halaman di luar rentang menampilkan halaman terakhir.
- Form panjang memakai beberapa langkah, contoh dokumen, simpan draf, dan validasi dekat field. Petugas dapat mengembalikan pengajuan dengan alasan yang dapat diperbaiki pengguna, tanpa meminta seluruh isian ulang.
- Identitas visual menggunakan tipografi judul editorial, warna hijau tua/krem/terakota, variasi komposisi kartu dan tabel sesuai tugas. Hindari dashboard yang terdiri dari kartu seragam tanpa hierarki informasi.

## Ukuran keberhasilan awal

1. Petugas dapat menemukan koperasi dengan nama/NIK dan melihat sumber, kewenangan, dokumen, serta riwayat proses dari satu profil.
2. Koperasi dapat mengirim penilaian tanpa mengisi ulang identitas; hasil final terkunci dan revisi dapat ditelusuri.
3. Satu temuan hanya diinput sekali tetapi muncul konsisten pada berita acara, tindak lanjut, dan monitoring.
4. Angka KDKMP 100% dan perputaran uang mempunyai definisi, bukti, periode, dan verifikator.
5. Aduan dan pendampingan izin memiliki status yang dapat dipahami pengguna serta batas kewenangan yang jelas.
