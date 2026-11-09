# Gambaran umum produk

## Cerita satu layar

Pagi hari, petugas membuka **Pusat Kendali Koperasi Rembang**. Layar menampilkan koperasi yang perlu pembinaan, penilaian yang menunggu verifikasi, aduan yang mendekati tenggat, dan progres Koperasi Desa/Kelurahan Merah Putih (KDKMP). Dari satu kartu koperasi, petugas melihat legalitas, wilayah kewenangan, RAT, kegiatan usaha, lokasi, dokumen, histori pemeriksaan, layanan izin, aduan, dan komitmen perbaikan. Setiap angka dapat ditelusuri ke periode dan sumbernya.

Pengurus koperasi membuka **Ruang Koperasi**. Identitas dasarnya sudah terisi dari data yang telah diverifikasi. Ia melengkapi profil, mengunggah dokumen sekali, mengisi penilaian mandiri yang sesuai jenis usaha/pola pengelolaan/KUK, lalu melihat status dan catatan perbaikan secara jelas. Setelah mengirim, data terkunci sebagai arsip; bila perlu perbaikan, petugas membuka siklus revisi terkontrol.

Warga membuka **Portal Publik** untuk profil bidang, berita, kegiatan, agenda, panduan, katalog layanan, peta dengan informasi terbatas, kanal konsultasi dan aduan, serta tautan media sosial resmi. Angka agregat mempunyai tanggal pembaruan dan definisi. Warga tidak melihat dokumen pengawasan yang bersifat internal.

## Tiga ruang dalam satu sistem

| Ruang | Pengguna | Keluaran utama |
|---|---|---|
| Portal publik | Masyarakat, anggota, calon koperasi | Informasi terpercaya, panduan layanan, agenda, peta publik, status aduan dengan kode tiket |
| Ruang koperasi | Pengurus yang terverifikasi | Profil, dokumen, RAT dan keragaan, penilaian mandiri, tindak lanjut, permohonan layanan |
| Ruang kerja dinas | Admin, pemeriksa, verifikator, pimpinan, superadmin | Register koperasi, penugasan, KKPKK, BAPK, LHPKK, penerbitan keputusan, monitoring, GIS, laporan |

## Halaman utama yang direncanakan

1. **Beranda publik:** profil bidang, layanan utama, informasi koperasi aktif, agenda, foto kegiatan, pembaruan KDKMP, kanal aduan, dan tautan sosial.
2. **Dashboard pimpinan:** koperasi aktif per jenis/kecamatan, ketepatan RAT, cakupan pemeriksaan, predikat kesehatan, temuan terbuka, layanan perizinan, aduan, dan indikator KDKMP.
3. **Daftar kerja petugas:** pekerjaan hari ini, batas waktu, penugasan, dokumen kurang, temuan belum ditindaklanjuti.
4. **Profil 360° koperasi:** identitas, legalitas, status ODS, usaha/USP, pengurus, keuangan ringkas per tahun, arsip dokumen, peta, riwayat layanan dan pemeriksaan.
5. **Peta koperasi dan KDKMP:** filter kecamatan/desa, jenis, status, KUK, capaian fisik dan verval; klik titik membuka kartu ringkas dan foto yang telah disetujui.
6. **Ruang KDKMP:** tahapan badan hukum, kesiapan operasional, lokasi/gerai/gudang, bukti foto, verval, komoditas, mitra, kendala, dan tindak lanjut.

## Arah tampilan dan navigasi

Tampilan dirancang seperti ruang kerja pelayanan publik yang rapi: tipografi mudah dibaca, warna utama yang selaras identitas dinas, foto kegiatan yang dipilih kurator, dan peta sebagai alat kerja. Setiap kartu angka menampilkan **periode**, **asal data**, dan tautan **lihat rincian**. Status memakai kata yang jelas seperti *Menunggu berkas*, *Sedang diperiksa*, atau *Selesai*, bukan kode internal. Form panjang dibagi menurut pekerjaan pengguna, dengan ringkasan progres yang selalu terlihat.

| Layar | Susunan utama | Aksi satu langkah |
|---|---|---|
| Beranda publik | Hero layanan, empat indikator bertanggal, peta ringkas, kegiatan terbaru, panduan dan aduan | Cari koperasi / buka layanan |
| Dashboard dinas | Ringkasan kerja hari ini, peta kecamatan, tren pemeriksaan, antrean keputusan, temuan jatuh tempo | Buka kasus prioritas |
| Profil koperasi | Identitas tetap di bagian atas; tab legalitas, keragaan, dokumen, penilaian, izin, peta, riwayat | Perbarui data / mulai penilaian |
| Penilaian mandiri | Progres per aspek, pertanyaan yang relevan, bukti di samping jawaban, ringkasan sebelum kirim | Simpan draf / kirim final |
| Meja pemeriksa | Jawaban mandiri dan bukti, hasil verifikasi, catatan koreksi, pratinjau BAPK/LHPKK | Catat temuan / kirim telaah |

Pola navigasi utama: **Beranda → Direktori Koperasi → Profil → Proses/Layanan → Dokumen Hasil**. Pengguna juga dapat masuk dari daftar tugas atau peta dan tiba di profil koperasi yang sama, sehingga tidak ada identitas ganda pada tiap modul.

## Indikator dengan definisi yang jujur

| Indikator | Definisi awal | Sumber/kendali |
|---|---|---|
| Koperasi aktif | Jumlah koperasi berstatus aktif pada snapshot ODS yang disetujui, ditampilkan menurut kewenangan | Import ODS bertanggal; koreksi manual tercatat |
| KDKMP terbangun 100% | Jumlah lokasi dengan tahap fisik selesai **dan** bukti/verval disetujui dibagi target lokasi yang disahkan | Target tahunan, foto geotag bila tersedia, berita acara verval |
| Perputaran ekonomi koperasi | **Volume usaha agregat** pada tahun buku yang sama dari laporan yang telah diverifikasi; label ini lebih jelas daripada menganggap seluruh pinjaman sebagai perputaran uang | Keragaan/laporan keuangan; tampilkan cakupan data dan periode |
| Cakupan penilaian | Koperasi yang telah memiliki LHPKK sah dalam periode dibagi sasaran pemeriksaan yang ditetapkan | Rencana kerja dan LHPKK |
| Penyelesaian temuan | Temuan yang dinyatakan selesai oleh pemeriksa dibagi temuan dengan tenggat dalam periode | Register tindak lanjut dan bukti |

Angka hanya tampil bila asal data, periode, dan cakupannya jelas. Dashboard membedakan **laporan koperasi**, **hasil verifikasi**, dan **penetapan resmi**.

## Pengalaman yang memudahkan

- Wizard pengisian pendek, simpan otomatis/draf, isi otomatis dari profil yang sah, dan daftar dokumen relevan menurut jenis layanan.
- Pemeriksaan angka sederhana sebelum kirim: neraca seimbang, periode konsisten, angka wajib tidak kosong, lampiran terbaca.
- Satu unggahan dapat dipakai di beberapa proses selama masih berlaku; sistem meminta versi baru hanya jika dokumen berubah atau kedaluwarsa.
- Semua proses memiliki nomor tiket, status dengan bahasa sederhana, petugas penanggung jawab, dan alasan bila dikembalikan.
- Notifikasi ringkas untuk tindakan penting; tidak mengirim pengingat berulang tanpa manfaat.
- Antarmuka ramah ponsel dan tetap dapat dipakai pada jaringan lambat; berkas besar diunggah terpisah dari formulir.

## Ukuran keberhasilan awal

Keberhasilan pilot dinilai dari waktu rata-rata mengisi penilaian, persentase berkas lengkap pada pengiriman pertama, jumlah input ganda yang hilang, kecepatan menemukan riwayat koperasi, ketepatan sinkronisasi laporan monitoring, dan proporsi tindak lanjut yang selesai tepat waktu. Nilai dasar diukur saat pilot, bukan dikarang pada tahap rancangan.
