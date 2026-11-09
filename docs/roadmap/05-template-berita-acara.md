# Template formulir dan dokumen hasil

Template ini adalah **rancangan field dan tampilan**. Nomor naskah, kop, tata naskah dinas, penandatangan, serta format final dicocokkan dengan format resmi dan praktik Dinas Rembang sebelum dipakai. Sumber lokal `BAPKK.docx`, `BAPKK LAMPIRAN.docx`, dan Juknis Deputi 15/2021 menjadi acuan.

## 1. Form persiapan pemeriksaan

| Kelompok | Field | Sumber otomatis / cara isi |
|---|---|---|
| Identitas | Nama, NIK koperasi, nomor/tanggal badan hukum, alamat, jenis, pola, bentuk, KUK, tahun buku | Profil koperasi terverifikasi, masih dapat dikoreksi lewat proses profil |
| Penugasan | Nomor/tanggal surat tugas, pejabat pemberi tugas, ketua/anggota tim, tanggal dan tempat pemeriksaan | Modul penugasan |
| Cakupan | Pemeriksaan rutin/sewaktu-waktu, dasar, unit/cabang yang diperiksa, periode data | Tim pemeriksa |
| Dokumen | AD/ART, izin, RAT, laporan keuangan, daftar anggota, SOM/SOP, bukti indikator khusus | Dokumen profil, status validasi |
| Kegiatan | Pertemuan awal, wawancara, cek lapangan, klarifikasi, pertemuan akhir | Catatan pemeriksa |

## 2. Template Berita Acara Pemeriksaan Kesehatan Koperasi (BAPK)

```text
[KOP DINAS / INSTANSI BERWENANG]
BERITA ACARA PEMERIKSAAN KESEHATAN KOPERASI
Nomor: {{nomor_bapk}}

Pada hari {{hari}}, tanggal {{tanggal_pemeriksaan_akhir}}, bertempat di
{{tempat}}, Tim Pemeriksa berdasarkan Surat Tugas {{nomor_surat_tugas}}
telah melakukan pemeriksaan kesehatan terhadap:

Nama koperasi       : {{nama_koperasi}}
NIK koperasi        : {{nik_koperasi}}
Nomor badan hukum   : {{nomor_badan_hukum}}
Alamat              : {{alamat}}
Jenis / pola / KUK  : {{jenis}} / {{pola}} / {{kuk}}
Tahun buku          : {{tahun_buku}}
Periode pemeriksaan : {{mulai}} s.d. {{selesai}}

Pihak koperasi yang hadir:
{{daftar_pengurus_pengawas_dps_dan_jabatan}}

Tim pemeriksa:
{{daftar_tim_dan_jabatan}}

Ruang lingkup dan metode:
{{ruang_lingkup}}, {{dokumen_diperiksa}}, {{klarifikasi_dan_lokasi}}

Ringkasan temuan per aspek:
1. Tata kelola: {{ringkasan_tata_kelola}}
2. Profil risiko: {{ringkasan_profil_risiko}}
3. Kinerja keuangan: {{ringkasan_keuangan}}
4. Permodalan: {{ringkasan_permodalan}}
5. Prinsip syariah (jika berlaku): {{ringkasan_syariah}}

Rincian temuan, dasar, rekomendasi, tanggapan koperasi, dan target
perbaikan tercantum dalam Lampiran yang menjadi bagian berita acara ini.

Koperasi menyampaikan tanggapan/klarifikasi: {{ringkasan_tanggapan}}.
Dokumen atau informasi tambahan yang belum tersedia: {{daftar_kekurangan}}.

Berita acara dibacakan dan ditandatangani pada {{tanggal_tanda_tangan}}.

Ketua Tim Pemeriksa,              Wakil Koperasi,
{{nama_ketua_tim}}                 {{nama_wakil_koperasi}}
{{identitas_jabatan}}              {{jabatan}}

Anggota Tim: {{daftar_tanda_tangan_anggota}}
Saksi/pihak terkait (bila ada): {{daftar_saksi}}
```

**Lampiran temuan BAPK, satu baris per temuan:**

| Field | Aturan |
|---|---|
| Nomor temuan, aspek, indikator KKPKK | Terhubung ke jawaban dan bukti pemeriksaan |
| Fakta dan bukti | Uraian faktual, tanggal, referensi dokumen; hindari kesimpulan tanpa bukti |
| Kriteria/dasar | Pasal/ayat/peraturan yang berlaku atau standar yang tepat; tidak diisi otomatis dengan pasal rekaan |
| Dampak/risiko | Diisi pemeriksa bila relevan |
| Rekomendasi | Tindakan yang dapat dikerjakan dan diverifikasi |
| Tanggapan koperasi | Dicatat sebagai pernyataan koperasi, bukan ditulis ulang sebagai persetujuan pemeriksa |
| Komitmen, PIC, target tanggal | Menjadi tugas tindak lanjut |
| Status dan bukti penyelesaian | Awalnya terbuka; diperbarui melalui modul tindak lanjut |

**Otomatisasi:** identitas, surat tugas, tanggal, tim, indikator, dan temuan diambil dari data terkunci. Pemeriksa tetap memeriksa narasi, dasar hukum, rekomendasi, dan tanggapan. PDF hanya diterbitkan setelah pratinjau dan persetujuan; simpan nomor dokumen, versi, waktu, penandatangan, serta hash. Bila pihak koperasi tidak menandatangani, alasan dan prosedur pencatatan mengikuti arahan pejabat berwenang, bukan ditandatangani sistem atas nama mereka.

## 3. Ringkasan Laporan Hasil Pemeriksaan Kesehatan Koperasi (LHPKK)

LHPKK dibentuk dari pemeriksaan yang sama, memuat sekurang-kurangnya identitas, tujuan, ruang lingkup, data umum, pokok temuan, rekomendasi, jadwal tindak lanjut, nama dan tanda tangan tim, serta tingkat kesehatan sesuai [Permenkop UKM 9/2020 Pasal 22](https://peraturan.bpk.go.id/Details/160765/permenkop-ukm-no-9-tahun-2020).

| Bagian | Isi |
|---|---|
| Sampul/pengesahan | Nomor laporan, tahun buku, surat tugas, koperasi, penyusun, pejabat penerima |
| Metode | Jalur KKPKK dan versi, periode, sumber data, kunjungan, batas pemeriksaan |
| Hasil | Skor dan predikat per aspek, hasil akhir, koreksi pemeriksa terhadap penilaian mandiri |
| Temuan | Rujukan ke BAPK, bukti, dasar, risiko, tanggapan koperasi |
| Rekomendasi | Tindakan, penanggung jawab, prioritas, tenggat |
| Kesimpulan | Predikat resmi yang diusulkan dan dasar penetapan |
| Lampiran | KKPKK terkunci, BAPK, daftar berkas, bukti terpilih sesuai izin |

Status `draf → ditinjau koordinator → disetujui pejabat`. Perbedaan skor mandiri dan hasil pemeriksaan ditampilkan sebagai selisih beserta alasan; hasil mandiri tidak dihapus.

## 4. Sertifikat kesehatan dan keputusan

Sertifikat hanya dibuat dari LHPKK yang disetujui dan predikat **Sehat/Cukup Sehat**, lalu ditetapkan pejabat berwenang. Field: nomor, identitas koperasi, NIK dan badan hukum, tahun buku/periode pemeriksaan, predikat, dasar LHPKK, tanggal penetapan, pejabat, tanda tangan elektronik/konvensional sesuai kebijakan, kode verifikasi publik, dan status berlaku. Halaman verifikasi publik menampilkan hanya informasi yang disetujui untuk publik.

Predikat **Dalam Pengawasan** atau **Dalam Pengawasan Khusus** masuk alur tindak lanjut dan proses sanksi sesuai kewenangan. Sistem tidak menerbitkan sertifikat untuk predikat tersebut.

## 5. Berita acara penanganan aduan

```text
[KOP DINAS]
BERITA ACARA PENANGANAN ADUAN / KLARIFIKASI
Nomor: {{nomor_ba_aduan}}
Kode tiket: {{kode_tiket}}

Tanggal/tempat        : {{tanggal_tempat}}
Petugas dan pihak     : {{daftar_hadir}}
Objek koperasi        : {{nama_koperasi_jika_ada}}
Ringkasan aduan       : {{ringkasan_aman_tanpa_data_rahasia}}
Dasar penanganan      : {{rujukan_kewenangan_dan_surat_tugas_jika_ada}}
Bukti yang ditelaah   : {{daftar_bukti}}
Klarifikasi para pihak: {{catatan_klarifikasi}}
Fakta yang disepakati : {{fakta}}
Hal yang belum terbukti: {{catatan_ketidakpastian}}
Kesimpulan/tindak lanjut: {{tindakan_pic_tenggat}}
Rujukan instansi lain : {{rujukan_jika_diperlukan}}

Petugas, {{tanda_tangan_petugas}}
Pihak yang hadir, {{tanda_tangan_para_pihak_sesuai_kebutuhan}}
```

Identitas pelapor dan berkas sensitif diletakkan pada lampiran terbatas. Penerimaan aduan tidak otomatis berarti terjadi pelanggaran; keputusan pemeriksaan mengikuti alur pengawasan dan kewenangan yang berlaku.
