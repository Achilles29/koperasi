<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Workspace extends MY_Controller
{
    private $pages=array(
        'penilaian'=>array('Penilaian kesehatan','Alur penilaian mandiri dan KKPKK berversi sudah dirancang. Form aktif dibangun setelah verifikasi formula dan indikator dari delapan workbook.'),
        'pengawasan'=>array('Pengawasan','Penugasan, pemeriksaan, BAPK, LHPKK, dan tindak lanjut akan memakai identitas koperasi dan periode yang sama.'),
        'kdkmp'=>array('KDKMP','Progres fisik, hasil verval, komoditas, lokasi, dan bukti foto akan dikelola dalam satu profil KDKMP.'),
        'gis'=>array('GIS koperasi','Titik koperasi dan KDKMP akan divalidasi sebelum tampil di peta publik. Master wilayah Kemendagri sudah tersedia.'),
        'aduan'=>array('Aduan dan konsultasi','Tiket, triase, klarifikasi, berita acara, dan tindak lanjut masuk tahap pengembangan berikutnya.'),
        'layanan'=>array('Layanan perizinan','Pendampingan KSP/USP, pemeriksaan berkas, dan tautan OSS akan dibuat sesuai kewenangan daerah.'),
        'pembinaan'=>array('Pembinaan dan RAT','Kalender RAT, klinik pembinaan, kegiatan dan evaluasi dampak telah masuk prioritas roadmap.'),
        'laporan'=>array('Laporan bidang','Laporan akan memakai snapshot periode dan sumber terverifikasi, termasuk rekap pengawasan dan KDKMP.'),
        'konten'=>array('Konten publik','Berita, agenda, regulasi, foto kegiatan, dan kanal sosial akan dikelola editor berizin.'),
    );

    public function show($slug)
    {
        if (!isset($this->pages[$slug]))show_404();
        $this->guard($slug);
        $page=$this->pages[$slug];
        $this->render_admin('workspace/show',array('title'=>$page[0],'eyebrow'=>'Ruang kerja / tahap berikutnya','active_nav'=>$slug,'message'=>$page[1]));
    }
}
