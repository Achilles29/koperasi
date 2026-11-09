<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Annual_reports extends MY_Controller
{
    private $statuses=array('draft'=>'Draf','submitted'=>'Menunggu verifikasi','returned'=>'Perlu perbaikan','verified'=>'Terverifikasi','withdrawn'=>'Dibatalkan');

    private function allowed_cooperative($cooperative_id)
    {
        $user=$this->current_user();
        if (!$this->can('import') && !$this->can('pengguna') && (int)$user['cooperative_id']!==(int)$cooperative_id)show_error('Laporan ini bukan milik koperasi Anda.',403);
    }

    private function cooperative($id)
    {
        $row=$this->db->get_where('cooperatives',array('id'=>(int)$id))->row_array();if (!$row)show_404();
        $this->allowed_cooperative($id);return $row;
    }

    private function report($id)
    {
        $row=$this->db->select('r.*,c.name AS cooperative_name,c.nik,c.reference_only,c.archived_at,u.full_name AS verifier_name')->from('periodic_reports r')
            ->join('cooperatives c','c.id=r.cooperative_id')->join('users u','u.id=r.verified_by','left')->where('r.id',(int)$id)->get()->row_array();
        if (!$row)show_404();$this->allowed_cooperative($row['cooperative_id']);return $row;
    }

    private function amount($raw)
    {
        $raw=trim((string)$raw);if ($raw==='')return null;
        $raw=str_replace(array(' ',chr(194).chr(160)),'',$raw);
        if (strpos($raw,',')!==false)$raw=str_replace(',','.',str_replace('.','',$raw));
        elseif (preg_match('/^\d{1,3}(?:\.\d{3})+$/',$raw))$raw=str_replace('.','',$raw);
        if (!preg_match('/^\d{1,18}(?:\.\d{1,2})?$/',$raw))show_error('Nilai keuangan harus angka positif dengan maksimal dua desimal.',422);
        return $raw;
    }

    public function index()
    {
        $this->guard('laporan_tahunan');$p=$this->page_args();$coop_id=(int)$this->input->get('cooperative_id',true);
        $own=!$this->can('import') && !$this->can('pengguna');
        if ($own)$coop_id=(int)$this->current_user()['cooperative_id'];
        if ($own && !$coop_id)show_error('Akun belum terhubung ke koperasi.',403);
        $this->db->select('r.id,r.fiscal_year,r.verification_status,r.business_volume,r.submitted_at,r.verified_at,c.name AS cooperative_name,c.nik')
            ->from('periodic_reports r')->join('cooperatives c','c.id=r.cooperative_id');
        if ($coop_id)$this->db->where('r.cooperative_id',$coop_id);
        if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('c.nik',$p['q'])->or_like('r.fiscal_year',$p['q'])->group_end();
        if (isset($this->statuses[$p['status']]))$this->db->where('r.verification_status',$p['status']);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('r.fiscal_year','DESC')->order_by('r.id','DESC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('annual_reports/index',array('title'=>'Laporan tahunan','eyebrow'=>'RAT dan data usaha','active_nav'=>'laporan_tahunan','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/modul/laporan-tahunan','statuses'=>$this->statuses,'cooperative_id'=>$coop_id,'can_create'=>$this->can('laporan_tahunan','buat')));
    }

    public function form($cooperative_id)
    {
        $this->guard('laporan_tahunan','buat');$coop=$this->cooperative($cooperative_id);
        if ($coop['reference_only'] || $coop['archived_at'])show_error('Laporan kabupaten hanya untuk koperasi aktif dalam kewenangan.',403);
        $this->render_admin('annual_reports/form',array('title'=>'Tambah laporan tahunan','eyebrow'=>'RAT dan data usaha / '.$coop['name'],'active_nav'=>'laporan_tahunan','coop'=>$coop,'row'=>null));
    }

    public function edit($id)
    {
        $this->guard('laporan_tahunan','ubah');$row=$this->report($id);
        if ($row['archived_at'])show_error('Koperasi arsip harus diaktifkan sebelum laporan diubah.',409);
        if (!in_array($row['verification_status'],array('draft','returned'),true))show_error('Laporan yang dikirim atau diverifikasi sudah terkunci.',409);
        $coop=$this->cooperative($row['cooperative_id']);
        $this->render_admin('annual_reports/form',array('title'=>'Ubah laporan tahunan','eyebrow'=>'RAT dan data usaha / '.$coop['name'],'active_nav'=>'laporan_tahunan','coop'=>$coop,'row'=>$row));
    }

    public function save()
    {
        $this->post_only();$id=(int)$this->input->post('id',true);$this->guard('laporan_tahunan',$id?'ubah':'buat');
        $old=$id?$this->report($id):null;$cooperative_id=(int)$this->input->post('cooperative_id',true);
        $coop=$this->cooperative($cooperative_id);
        if ($old && (int)$old['cooperative_id']!==$cooperative_id)show_error('Koperasi pada laporan tidak dapat diganti.',422);
        if ($old && !in_array($old['verification_status'],array('draft','returned'),true))show_error('Laporan sudah terkunci.',409);
        if ($coop['reference_only'] || $coop['archived_at'])show_error('Laporan kabupaten hanya untuk koperasi aktif dalam kewenangan.',403);
        $year=(int)$this->input->post('fiscal_year',true);
        if ($year<2000 || $year>(int)date('Y')+1)show_error('Tahun buku tidak valid.',422);
        $this->db->where('cooperative_id',$cooperative_id)->where('fiscal_year',$year);if ($id)$this->db->where('id !=',$id);
        if ($this->db->count_all_results('periodic_reports'))show_error('Tahun buku ini sudah memiliki laporan. Buka laporan yang ada.',409);
        $rat_date=trim((string)$this->input->post('rat_date',true));
        if ($rat_date){$date=DateTime::createFromFormat('!Y-m-d',$rat_date);if (!$date || $date->format('Y-m-d')!==$rat_date)show_error('Tanggal RAT tidak valid.',422);}
        $members_raw=trim((string)$this->input->post('members_count',true));
        if ($members_raw!=='' && (!ctype_digit($members_raw) || (float)$members_raw>4294967295))show_error('Jumlah anggota harus bilangan bulat tidak negatif.',422);
        $values=array('cooperative_id'=>$cooperative_id,'fiscal_year'=>$year,'rat_date'=>$rat_date?:null,'members_count'=>$members_raw===''?null:(int)$members_raw,'assets'=>$this->amount($this->input->post('assets',true)),'equity'=>$this->amount($this->input->post('equity',true)),'business_volume'=>$this->amount($this->input->post('business_volume',true)),'shu'=>$this->amount($this->input->post('shu',true)),'verification_status'=>'draft','updated_by'=>$this->current_user()['id']);
        if ($old)$this->db->where('id',$id)->update('periodic_reports',$values);
        else {$values['created_by']=$this->current_user()['id'];$this->db->insert('periodic_reports',$values);$id=$this->db->insert_id();}
        $this->audit('periodic_reports',$id,$old?'update':'create',$old,$values);
        $this->session->set_flashdata('success','Laporan tahunan tersimpan sebagai draf.');redirect('admin/laporan-tahunan/'.$id);
    }

    public function detail($id)
    {
        $this->guard('laporan_tahunan');$row=$this->report($id);
        $documents=$this->db->select('d.id,d.original_name,d.mime_type,d.byte_size,d.uploaded_at,m.name AS type_name')->from('documents d')->join('master_items m','m.id=d.document_type_item_id','left')->where('d.owner_type','periodic_report')->where('d.owner_id',$id)->order_by('d.uploaded_at','DESC')->get()->result_array();
        $types=$this->db->select('m.id,m.name')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('d.slug','jenis-dokumen')->where_in('m.code',array('rat','laporan_keuangan'))->where('m.active',1)->order_by('m.name')->get()->result_array();
        $editable=!$row['archived_at'] && in_array($row['verification_status'],array('draft','returned'),true);
        $this->render_admin('annual_reports/detail',array('title'=>'Laporan tahun '.$row['fiscal_year'],'eyebrow'=>'RAT dan data usaha / '.$row['cooperative_name'],'active_nav'=>'laporan_tahunan','row'=>$row,'documents'=>$documents,'types'=>$types,'statuses'=>$this->statuses,'can_edit'=>$editable && $this->can('laporan_tahunan','ubah'),'can_toggle'=>($editable || $row['verification_status']==='withdrawn') && $this->can('laporan_tahunan','nonaktifkan'),'can_verify'=>$row['verification_status']==='submitted' && $this->can('laporan_tahunan','verifikasi')));
    }

    public function upload($id)
    {
        $this->post_only();$this->guard('laporan_tahunan','ubah');$row=$this->report($id);
        if ($row['archived_at'])show_error('Koperasi arsip tidak dapat mengunggah laporan.',409);
        if (!in_array($row['verification_status'],array('draft','returned'),true))show_error('Laporan sudah terkunci.',409);
        $type_id=(int)$this->input->post('document_type_item_id',true);
        $type=$this->db->select('m.id')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('m.id',$type_id)->where('d.slug','jenis-dokumen')->where_in('m.code',array('rat','laporan_keuangan'))->where('m.active',1)->get()->row_array();
        if (!$type)show_error('Jenis dokumen tidak valid.',422);
        if (!isset($_FILES['document']) || $_FILES['document']['error']!==UPLOAD_ERR_OK)show_error('Unggah dokumen gagal.',422);
        $file=$_FILES['document'];if ($file['size']<1 || $file['size']>10*1024*1024)show_error('Dokumen maksimal 10 MB.',422);
        $prefix=file_get_contents($file['tmp_name'],false,null,0,5);$image=@getimagesize($file['tmp_name']);
        $mime=$prefix==='%PDF-'?'application/pdf':($image['mime']??'');
        $ext=array('application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp');
        if (!isset($ext[$mime]))show_error('Gunakan PDF, JPG, PNG, atau WebP.',422);
        $directory=FCPATH.'private_uploads/reports/';
        if (!is_dir($directory) && !mkdir($directory,0750,true))show_error('Penyimpanan dokumen belum siap.',500);
        $filename=bin2hex(random_bytes(16)).'.'.$ext[$mime];$path=$directory.$filename;
        if (!move_uploaded_file($file['tmp_name'],$path))show_error('Penyimpanan dokumen gagal.',500);
        chmod($path,0640);
        $values=array('cooperative_id'=>$row['cooperative_id'],'owner_type'=>'periodic_report','owner_id'=>$id,'document_type_item_id'=>$type_id,'fiscal_year'=>$row['fiscal_year'],'original_name'=>mb_substr(basename($file['name']),0,255),'storage_path'=>'private_uploads/reports/'.$filename,'mime_type'=>$mime,'byte_size'=>$file['size'],'sha256'=>hash_file('sha256',$path),'visibility'=>'private','verification_status'=>'pending','uploaded_by'=>$this->current_user()['id']);
        $this->db->insert('documents',$values);$document_id=$this->db->insert_id();$this->audit('documents',$document_id,'upload',null,array('owner_type'=>'periodic_report','owner_id'=>$id,'sha256'=>$values['sha256']));
        $this->db->where('id',$id)->update('periodic_reports',array('updated_by'=>$this->current_user()['id']));
        $this->session->set_flashdata('success','Dokumen tersimpan secara privat.');redirect('admin/laporan-tahunan/'.$id);
    }

    public function download($id,$document_id)
    {
        $this->guard('laporan_tahunan');$this->report($id);
        $doc=$this->db->get_where('documents',array('id'=>(int)$document_id,'owner_type'=>'periodic_report','owner_id'=>(int)$id))->row_array();if (!$doc)show_404();
        if (strpos($doc['storage_path'],'private_uploads/reports/')!==0)show_404();
        $path=FCPATH.$doc['storage_path'];if (!is_file($path))show_404();
        header('Content-Type: '.$doc['mime_type']);header('Content-Disposition: attachment; filename="laporan-'.$document_id.'.'.pathinfo($path,PATHINFO_EXTENSION).'"');header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');readfile($path);exit;
    }

    public function submit($id)
    {
        $this->post_only();$this->guard('laporan_tahunan','ubah');$row=$this->report($id);
        if ($row['archived_at'])show_error('Koperasi arsip tidak dapat mengirim laporan.',409);
        if (!in_array($row['verification_status'],array('draft','returned'),true))show_error('Laporan sudah dikirim atau diverifikasi.',409);
        if ($row['members_count']===null || $row['assets']===null || $row['equity']===null || $row['business_volume']===null || $row['shu']===null)show_error('Lengkapi anggota, aset, modal, volume usaha, dan SHU sebelum dikirim.',422);
        if (!$this->db->where('owner_type','periodic_report')->where('owner_id',$id)->count_all_results('documents'))show_error('Unggah dokumen RAT atau laporan keuangan sebelum dikirim.',422);
        $values=array('verification_status'=>'submitted','submitted_at'=>date('Y-m-d H:i:s'),'verified_at'=>null,'verified_by'=>null,'updated_by'=>$this->current_user()['id']);
        $this->db->where('id',$id)->update('periodic_reports',$values);$this->audit('periodic_reports',$id,'submit',$row,$values);
        $this->session->set_flashdata('success','Laporan dikunci dan dikirim untuk verifikasi.');redirect('admin/laporan-tahunan/'.$id);
    }

    public function verify($id)
    {
        $this->post_only();$this->guard('laporan_tahunan','verifikasi');$row=$this->report($id);
        if ($row['archived_at'])show_error('Koperasi arsip tidak dapat diverifikasi sebagai laporan aktif.',409);
        if ($row['verification_status']!=='submitted')show_error('Hanya laporan terkirim yang dapat diverifikasi.',409);
        if ((int)$row['created_by']===(int)$this->current_user()['id'] || (int)$row['updated_by']===(int)$this->current_user()['id'])show_error('Pembuat atau penyunting terakhir laporan tidak dapat menjadi verifikatornya.',403);
        $decision=(string)$this->input->post('decision',true);$note=trim((string)$this->input->post('review_note',true));
        if (!in_array($decision,array('verified','returned'),true) || mb_strlen($note)<10 || mb_strlen($note)>5000)show_error('Keputusan dan catatan minimal 10 karakter wajib diisi.',422);
        $values=array('verification_status'=>$decision,'review_note'=>$note,'verified_by'=>$decision==='verified'?$this->current_user()['id']:null,'verified_at'=>$decision==='verified'?date('Y-m-d H:i:s'):null);
        $this->db->where('id',$id)->update('periodic_reports',$values);$this->audit('periodic_reports',$id,'review',$row,$values);
        $this->session->set_flashdata('success',$decision==='verified'?'Laporan diverifikasi.':'Laporan dikembalikan untuk perbaikan.');redirect('admin/laporan-tahunan/'.$id);
    }

    public function toggle($id)
    {
        $this->post_only();$this->guard('laporan_tahunan','nonaktifkan');$row=$this->report($id);
        if (!in_array($row['verification_status'],array('draft','returned','withdrawn'),true))show_error('Laporan yang sudah dikirim atau diverifikasi tidak dapat dibatalkan.',409);
        $new=$row['verification_status']==='withdrawn'?'draft':'withdrawn';
        $this->db->where('id',$id)->update('periodic_reports',array('verification_status'=>$new));$this->audit('periodic_reports',$id,$new==='withdrawn'?'withdraw':'restore',$row,array('verification_status'=>$new));
        $this->session->set_flashdata('success',$new==='withdrawn'?'Draf dibatalkan.':'Draf diaktifkan kembali.');redirect('admin/laporan-tahunan/'.$id);
    }
}
