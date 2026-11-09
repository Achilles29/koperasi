# Data, jenis koperasi, penilaian, dan integrasi

## Temuan dari berkas yang disediakan

| Sumber | Temuan | Pemakaian dalam aplikasi |
|---|---|---|
| `Daftar Koperasi Binaan 2026-11-04 11.21.16.xlsx` | 557 entri unik menurut NIK; 522 primer kabupaten/kota, 2 sekunder kabupaten/kota, 15 primer provinsi, 18 primer nasional | Snapshot pembanding/koordinasi lintas kewenangan |
| `Daftar Koperasi Binaan 2026-11-04 11.23.18.xlsx` | 524 entri unik menurut NIK, semuanya juga ada dalam berkas 557; 522 primer dan 2 sekunder kabupaten/kota | Kandidat daftar kerja kabupaten; kewenangan tetap perlu verifikasi berdasarkan wilayah keanggotaan dan legalitas |
| Delapan berkas `KKPKK/*.xlsx` bernomor 1–8 | Varian KSP, KSPPS, non-KSP konvensional, non-KSP syariah × KUK I–II atau III–IV | Sumber inventaris pertanyaan, lampiran, formula, kategori, dan keluaran; transkripsi perlu uji silang |
| `BAPK - LHPK/BAPKK.docx` dan lampiran | Format berita acara dan tabel temuan/rekomendasi/tanggapan | Dasar template BAPK di dokumen 05 |
| Google Sheet monitoring yang diberikan | Tahun 2026, kelompok koperasi, identitas, tanggal/PIC, kelembagaan, usaha, keuangan, rasio, dan temuan (108 kolom pada ekspor CSV saat diperiksa) | Kamus pemetaan untuk register monitoring; bukan sumber identitas utama |

Data pada dua daftar ODS adalah **snapshot bertanggal 4 November 2026**, bukan data langsung. Perbedaan 33 entri seluruhnya berada pada bentuk primer provinsi/nasional. Nilai jenis dalam daftar meliputi 287 `Desa Merah Putih`, 7 `Kelurahan Merah Putih`, 129/120 konsumen, 63/56 produsen, 56/44 simpan pinjam, 14/9 jasa, dan 1 pemasaran (berurutan untuk daftar 557/524). Angka ini merupakan hasil pembacaan berkas, bukan angka dashboard produksi.

## Master jenis koperasi yang benar

[Permenkum 13/2025 Pasal 6 ayat (3)](https://peraturan.bpk.go.id/Details/317736/permenkum-no-13-tahun-2025) memuat jenis **produsen, konsumen, pemasaran, jasa, simpan pinjam, Koperasi Desa/Kelurahan Merah Putih, dan jenis lain yang ditetapkan aturan/program pemerintah**. Master awal:

| Kode internal | Jenis hukum | Alias ODS yang dipetakan | Catatan |
|---|---|---|---|
| PRODUSEN | Produsen | `Produsen` | Bukan nama sektor usaha |
| KONSUMEN | Konsumen | `Konsumen` |  |
| PEMASARAN | Pemasaran | `Pemasaran` |  |
| JASA | Jasa | `Jasa` |  |
| SIMPAN_PINJAM | Simpan Pinjam | `Simpan Pinjam` | KSP/KSPPS ditentukan bersama pola dan kegiatan usaha |
| KDKMP | Koperasi Desa/Kelurahan Merah Putih | `Desa Merah Putih`, `Kelurahan Merah Putih` | `desa/kelurahan` disimpan sebagai subtipe lokasi, bukan dua jenis hukum terpisah |
| LAINNYA | Jenis lain berdasar aturan/program | Tidak dipetakan otomatis | Wajib nomor/dasar aturan dan persetujuan admin |

**Dimensi terpisah:** bentuk `primer/sekunder`; wilayah keanggotaan/kewenangan `kabupaten/provinsi/nasional`; pola `konvensional/syariah`; kegiatan `simpan pinjam/non simpan pinjam`; unit `USP/USPPS` bila ada; KUK `1–4`; sektor/KBLI; kelompok/label historis (KUD, KPRI, KSU, dan sebagainya); status ODS; status operasional; status pemeriksaan. Jangan menyamakan kelompok KUD/KPRI/KSU atau USP dengan jenis koperasi menurut master hukum.

KUK mengikuti [Permenkop UKM 9/2020 Pasal 4](https://peraturan.bpk.go.id/Details/160765/permenkop-ukm-no-9-tahun-2020); klasifikasi ditentukan dari capaian tertinggi jumlah anggota, modal sendiri, atau aset. Nilai KUK ODS dapat dipakai untuk praisi, kemudian diverifikasi terhadap data periode pemeriksaan.

## Pemilihan form kesehatan

| Jalur aplikasi | Sumber lokal | Aturan pemilihan |
|---|---|---|
| KSP/USP konvensional, KUK 1–2 | `1. KKPKK KSP KUK I dan II 211224.xlsx` | Usaha simpan pinjam konvensional |
| KSP/USP konvensional, KUK 3–4 | `2. KKPKK KSP KUK III dan IV 211224.xlsx` | Sama, KUK 3–4 |
| KSPPS/USPPS, KUK 1–2 | `3. KKPKK KSPPS KUK I dan II 211224.xlsx` | Simpan pinjam/pembiayaan syariah |
| KSPPS/USPPS, KUK 3–4 | `4. KKPKK KSPPS KUK III dan IV 211224.xlsx` | Sama, KUK 3–4 |
| Non-KSP konvensional, KUK 1–2 | `5. KKPKK Non KSP I dan II 211224.xlsx` | Usaha selain simpan pinjam; periksa apakah ada USP yang memerlukan jalur tersendiri |
| Non-KSP konvensional, KUK 3–4 | `6. KKPKK Non KSP KUK III dan IV 211224.xlsx` | Sama, KUK 3–4 |
| Non-KSP syariah, KUK 1–2 | `7. KKPKK Non KSP Syariah KUK I dan II 211224.xlsx` | Usaha non simpan pinjam berpola syariah |
| Non-KSP syariah, KUK 3–4 | `8. KKPKK Non KSP Syariah KUK III dan IV 211224.xlsx` | Sama, KUK 3–4 |

Penentuan jalur tidak cukup dari kolom `Jenis Koperasi` ODS. Petugas harus mengonfirmasi jenis kegiatan nyata dan keberadaan unit USP/USPPS, pola, serta KUK. Koperasi non-KSP yang mempunyai USP/USPPS dapat memerlukan pemeriksaan pada entitas koperasi dan unitnya; rincian pemisahan keluaran perlu ditetapkan bersama pejabat pengawas berdasarkan KKPKK resmi. Pengembang tidak boleh mengasumsikan satu skor usaha campuran tanpa dasar.

## Mesin formulir yang dapat diaudit

- Setiap versi formulir memuat `kode`, `dasar_hukum`, `berlaku_mulai`, `berlaku_sampai`, `jalur`, `KUK`, `aspek`, `indikator`, `tipe_jawaban`, `bukti`, `rumus`, `bobot`, `ambang`, dan `teks_rekomendasi`.
- Aspek minimum KKPKK: tata kelola, profil risiko, kinerja keuangan, permodalan; jalur syariah menambahkan penerapan prinsip syariah sesuai [Permenkop UKM 9/2020 Pasal 13](https://peraturan.bpk.go.id/Details/160765/permenkop-ukm-no-9-tahun-2020).
- Rumus Excel ditranskripsi ke fungsi yang dapat diuji. Simpan nilai masukan, nilai antara, pembulatan, formula/versi, hasil akhir, dan koreksi pemeriksa. Jangan menghitung dari formula Excel di server produksi.
- Satu pemeriksaan menyimpan snapshot versi formulir. Perubahan master di masa depan tidak mengubah skor pemeriksaan lama.
- Uji pembanding memakai berkas studi kasus dan beberapa skenario batas kategori dari tiap jalur, termasuk pembagi nol dan data tidak lengkap; hasil yang berbeda harus diselesaikan sebelum penerbitan resmi.
- Form publik memakai penjelasan sederhana; petugas dapat membuka rincian teknis dan dasar setiap indikator.

## Model data inti

| Entitas | Kunci dan isi pokok |
|---|---|
| `cooperatives` | ID internal, NIK teks, nomor badan hukum, nama, jenis hukum, bentuk, pola, kewenangan, status, desa/kecamatan, sumber |
| `regions` / `source_versions` | Kode wilayah Kemendagri sebagai teks, jenis dan induk wilayah, sumber keputusan, periode berlaku, status |
| `region_aliases` | Pemetaan nama wilayah dari ODS/berkas lama ke kode resmi yang diverifikasi |
| `cooperative_units` | USP/USPPS/cabang, izin dan lokasi, hubungan ke koperasi induk |
| `periodic_reports` | Tahun buku, RAT, anggota, aset, modal, volume usaha, SHU, status verifikasi |
| `documents` | Jenis berkas, pemilik, periode, versi, checksum, akses, status validasi |
| `assessments` / `assessment_answers` | Jalur dan versi KKPKK, status, jawaban, bukti, skor mandiri dan koreksi petugas |
| `inspections` / `findings` / `actions` | Penugasan, pemeriksaan, temuan, rekomendasi, tenggat, bukti penutupan |
| `official_outputs` | BAPK, LHPKK, keputusan/sertifikat, nomor, penandatangan, versi PDF, hash |
| `service_cases` | Pendampingan izin, aduan, konsultasi, status, PIC dan kronologi |
| `kdkmp_sites` / `commodities` | Lokasi, progres fisik, verval, foto, komoditas, periode |
| `audit_events` / `import_batches` / `import_rows` | Siapa, kapan, aksi, nilai lama/baru, asal impor dan hasil per baris |

## Sinkronisasi dengan monitoring Google Sheets

Sumber yang diperiksa: [lembar monitoring koperasi 2026](https://docs.google.com/spreadsheets/d/1JRYIsUpQXDCUChQBTs2wpsgxX16r2VVHjXk-BE0NPsc/edit?gid=0#gid=0). Kolom utama: A–H identitas/tanggal/PIC; I–AE kelembagaan; AF–AY usaha; AZ–DA keuangan/permodalan/rasio; DB (`106`) temuan. Header bertingkat pada baris 4–8, data mulai baris 10 pada snapshot yang dibaca. Posisi kolom harus dikonfirmasi ulang saat integrasi karena pengguna dapat mengubah struktur lembar.

| Data internal | Tujuan monitoring | Aturan |
|---|---|---|
| Identitas koperasi dan nomor badan hukum | Nama, badan hukum, kecamatan, jenis, kelompok | Cocokkan lewat NIK internal/nomor badan hukum; spreadsheet belum menyediakan NIK sebagai kolom kunci |
| Pemeriksaan disetujui | Tanggal pengawasan, PIC, aspek, temuan | Hanya hasil resmi/ditetapkan; simpan `inspection_id` pada log ekspor |
| Dokumen kelembagaan | Akta, SK, AD/ART, NIB, izin USP, RAT, SOP/SOM | Status dari verifikasi dokumen dengan periode dan bukti |
| Keragaan/keuangan | Anggota, pendapatan, biaya, SHU, pinjaman, NPL, aset, hutang, modal, rasio | Tahun sebelumnya/berjalan dipasangkan secara eksplisit; kosong bukan nol |

**Implementasi bertahap:** tahap awal impor CSV untuk rekonsiliasi dan ekspor laporan berformat monitoring. Integrasi Google Sheets API baca/tulis baru dijalankan dengan akun layanan, izin pemilik, pemetaan kolom yang dibekukan, dan kebijakan konflik. CSV publik yang dapat diunduh saat perencanaan **bukan** mekanisme produksi karena lembar berisi data operasional. Satu arah keluar dari hasil resmi lebih aman untuk tahap awal; perubahan manual di sheet menjadi antrean rekonsiliasi, bukan menimpa data sah.
