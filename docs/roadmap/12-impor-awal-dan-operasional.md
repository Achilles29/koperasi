# Impor awal Excel, wilayah, dan operasi aplikasi

## Sumber dan urutan

1. `docs/Daftar Koperasi Binaan 2026-11-04 11.21.16.xlsx` dibaca sebagai snapshot luas, 557 baris. NIK unik menjadi kunci pencocokan. Sebanyak 33 entri yang tidak ada pada snapshot kedua tetap masuk sebagai **data referensi**: 15 primer provinsi dan 18 primer nasional.
2. `docs/Daftar Koperasi Binaan 2026-11-04 11.23.18.xlsx` dibaca sebagai daftar kerja kabupaten, 524 baris. NIK yang sama memperbarui profil, bukan menciptakan duplikat. Keanggotaan snapshot ini menandai `authority_scope='kabupaten'` dan `reference_only=0`; kewenangan hukum tiap kasus tetap harus diperiksa dari dokumen.
3. Lampiran Kepmendagri 300.2.2-2138/2025 diekstrak dengan `scripts/extract_regions.py` menjadi `database/regions_2025.csv`. Skrip memeriksa jumlah 38/514/7.285/83.762 dan rantai kode induk. **Amendemen 300.2.2-2430/2025 belum diterapkan**; proses pembanding versi masuk prioritas berikutnya.
4. `scripts/seed_initial.py` mengisi katalog master, peran, akun awal, wilayah, lalu Excel. Skrip menyimpan hash file dan melewatkan batch yang sama ketika dijalankan ulang.

## Pemetaan kolom Excel

| Kolom Excel | Tujuan basis data | Aturan |
|---|---|---|
| Nomor Induk Koperasi | `cooperatives.nik` | Teks; unik; kunci rekonsiliasi kedua berkas |
| Nama Koperasi | `name`, `name_normalized` | Nama asli tetap utuh; normalisasi untuk pencarian |
| Nomor Badan Hukum | `legal_number` | Teks, tidak dianggap kunci unik karena kualitas sumber perlu diperiksa |
| Bentuk Koperasi | `form_item_id` + `authority_scope` | `Primer/Sekunder` dipisahkan dari Kabupaten/Provinsi/Nasional; label lengkap tetap ada pada JSON asal |
| Jenis Koperasi | `type_item_id` | `Desa Merah Putih` dan `Kelurahan Merah Putih` menjadi jenis KDKMP yang sama; label asal tetap di JSON baris |
| Pola Pengelolaan | `pattern_item_id` | Master konvensional/syariah |
| Status Koperasi | `ods_status` | Status dari ODS, tidak otomatis berarti hasil verifikasi dinas |
| Sektor Usaha | `sector_item_id` | Nilai sumber ditambahkan ke master campuran jika belum ada |
| Kelompok Koperasi | `group_item_id` | Label ODS, bukan jenis hukum |
| Provinsi, Kabupaten/Kota | `province_id`, `regency_id` | Data yang tersedia menunjuk Jawa Tengah/Rembang; diverifikasi dengan identitas wilayah |
| Kecamatan | `district_id` | Padan nama yang dinormalisasi pada 14 kecamatan Rembang |
| Kelurahan, Desa | `village_id`, `ods_village_name` | Coba kolom Desa lalu Kelurahan pada kecamatan terkait; nama mentah disimpan jika tidak cocok |
| Alamat, Kode Pos, Email | `address`, `postal_code`, `email` | Nilai asal tidak dibuat-buat |
| KUK, Grade, Status Akun ODS Mandiri | `kuk_item_id`, `grade`, `ods_account_status` | KUK ODS adalah praisi, bukan penetapan kesehatan |
| Semua 21 kolom | `import_rows.raw_json` | Asal per baris, nomor baris Excel, hasil impor, dan hash batch dapat diaudit |

## Hasil rekonsiliasi awal

| Ukuran | Hasil |
|---|---:|
| Baris snapshot luas / daftar kerja kabupaten | 557 / 524 |
| Entitas unik setelah dua impor | 557 |
| Insert snapshot pertama / update snapshot kedua | 557 / 524 |
| Data kabupaten / data referensi provinsi / data referensi nasional | 524 / 15 / 18 |
| Padan kecamatan / padan desa-kelurahan | 557 / 539 |
| Nama desa-kelurahan perlu tinjau | 18 |
| Profil KDKMP | 294 |

Berkas Excel tidak menyediakan koordinat, bukti progres fisik KDKMP, daftar komoditas, data keuangan terverifikasi, atau hasil penilaian. Nilai tersebut tidak diisi otomatis. Kode wilayah yang tidak cocok tidak diubah paksa menjadi kode lain.

## Menjalankan ulang pada server yang sama

```bash
cd /www/wwwroot/koperasi
python3 scripts/migrate.py
python3 scripts/seed_initial.py
python3 scripts/seed_kkpkk.py
python3 scripts/seed_financial_inputs.py
```

`migrate.py` membaca konfigurasi privat `application/config/koperasi_db.php`; `seed_initial.py` memeriksa SHA-256 sebelum impor. Keduanya idempoten terhadap data yang sudah masuk. **Jangan mengganti file Excel dengan nama sama lalu mengharapkan hasil lama tidak berubah**: hash berubah dan akan menjadi batch baru. Tinjau selisih, kewenangan, dan koreksi manual terlebih dahulu sebelum impor snapshot ODS yang lebih baru, sebab proses awal ini masih memakai upsert kolom ODS. Tahap berikutnya memerlukan pratinjau konflik dan keputusan operator agar koreksi sah tidak ditimpa.

Berkas sumber, skrip, skema, dan konfigurasi database tidak disajikan melalui web. Password database tidak ditulis di roadmap. Cadangan sebelum migrasi/snapshot baru serta uji pemulihan perlu menjadi prosedur operasional Dinas.
