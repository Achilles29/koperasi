### ABAIKAN INI CATATAN SAYA

Directory koperasi
disini saya ingin membuat project aplikasi berbasis web, CI3, untuk bidang koperasi dinas Perdagangan, Koperasi dan UKM

fitur mentah awal yang diinginkan antara lain

Dashboard mungkin profil bidang kopersi, foto2 kegiatan, data koperasi aktif, update jml kdkmp terbangun 100%, data perputaran uang sektor koperasi

Di halaman berikutnya penilaian mandiri kesehatan koperasi, upload dok kelengkapan - verifikasi opd - hasil sertifikat penilaian

Halaman berikutnya tentang KDKMP, kumpulan peraturan, titik koordinat KDKMP termasuk foto2 KDKMP, hasil verval KDKMP, Komoditi tiap kdkmp

 berikutnya tentang layanan pengajuan perijinan koperasi simpan pinjam/Unit simpan pinjam koperasi baru yg wilayah kerjanya di wilayah Kabupaten Rembang (mungkin bisa link ke oss jg)

 selanjutnya event2, cs dan layanan aduan, link sosmed


modul diatas masih sangat simple sekali dan belum layak untuk disebut sistem informasi.

kembangkan lagi dengan modul modul yang mungkin diperlukan untuk sistem sesuai tupoksi bidang koperasi. yang sesuai dengan ketentuan perundang undangan.

ingat aplikasi dibuat untuk memudahkan user, bukan memperusulit.

buatkan gambaran umum aplikasi dan proses bisnisnya dulu agar terlihat mahal

modul GIS jangan lupa



tambahan:

form link pengawasan / monitoring koperasi:
https://docs.google.com/spreadsheets/d/1JRYIsUpQXDCUChQBTs2wpsgxX16r2VVHjXk-BE0NPsc/edit?gid=0#gid=0

form penilaian kesehatan koperasi dan daftar koperasi binaan ada di /www/wwwroot/koperasi/docs
beda jenis koperasi beda jenis formnya

hasil penilaian harusnya sinkron dengan form pengawasan

buatkan master jenis koperasi sesuai ketentuan


Penilaian kesehatan koperasi berdasarkan :
- jenis koperasi
-  ⁠permenkop 9/2020
- juklak juknis terkait
Kunci setelah isi, biar tidak diubah

berita acara hasil penilaian otomatis ,jadi buatkan template form nya

admin bisa Tambah koperasi manual dari ods

modul Aduan , tindak lanjut, Berita acara

modul Hak akses, hak akses bisa diatur berdasarkan jenis akun dan modul oleh superadmin.
akun pertama : Superadmin, admin, koperasi, bisa ditambah oleh superadmin 



modul pengembangan lain sangat disarankan untuk menunjang kinerja bidang koperasi



================

- semua halaman data harusnya ada CRUD nya sesuai hak akses
- master wilayah harusnya berjenjang, dan sinkron dari provinsi sampai desa
- buat halaman pengaturan profil dinas, untuk mengatur deskripsi deskripsi yang bisa diubah, foto logo, nama pemkab, nama opd, dan deskripsi lain, jadi jangan hardcode

- hapus halaman riwayat import
- tautkan user koperasi. tuliskan username dan password semua di docs, ini kan sementara tidak apa ditulis nanti saya ganti
- tata ulang menu di sidebar agar rapi dan serumpun
- logo pemkab blok putihnya masih keliatan, perbaiki fotonya
- lanjutkan pengembangan berikutnya