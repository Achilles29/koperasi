<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kdkmp extends MY_Controller
{
    private function site($id)
    {
        $row=$this->db->select('s.*,c.name AS cooperative_name,c.nik,c.address AS cooperative_address,v.name AS village_name,d.name AS district_name')
          ->from('kdkmp_sites s')->join('cooperatives c','c.id=s.cooperative_id')->join('regions v','v.id=s.village_id')->join('regions d','d.id=v.parent_id')->where('s.id',(int)$id)->get()->row_array();
        if (!$row)show_404();return $row;
    }
    private function writable_site($id)
    {
        $row=$this->site($id);
        if ($row['archived_at'])show_error('Lokasi yang diarsipkan tidak dapat diubah.',409);
        return $row;
    }
    private function master_options($slug)
    {
        return $this->db->select('m.id,m.name')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('d.slug',$slug)->where('m.active',1)->order_by('m.name')->get()->result_array();
    }
    public function index()
    {
        $this->guard('kdkmp');$p=$this->page_args();$district=trim((string)$this->input->get('district',true));$record_status=trim((string)$this->input->get('record_status',true));
        $this->db->select('s.id,s.physical_progress,s.verification_status,s.operational_status,s.updated_at,s.archived_at,c.name AS cooperative_name,v.name AS village_name,d.id AS district_id,d.name AS district_name')
          ->from('kdkmp_sites s')->join('cooperatives c','c.id=s.cooperative_id')->join('regions v','v.id=s.village_id')->join('regions d','d.id=v.parent_id');
        if ($record_status==='arsip')$this->db->where('s.archived_at IS NOT NULL',null,false);
        elseif ($record_status!=='semua')$this->db->where('s.archived_at IS NULL',null,false);
        $this->db->where('c.archived_at IS NULL',null,false);
        if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('v.name',$p['q'])->group_end();
        if (in_array($p['status'],array('pending','verified'),true))$this->db->where('s.verification_status',$p['status']);
        if ($district && ctype_digit($district))$this->db->where('d.id',(int)$district);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('d.name')->order_by('v.name')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $all=(int)$this->db->where('archived_at IS NULL',null,false)->count_all_results('kdkmp_sites');
        $completed=(int)$this->db->where('physical_progress >=',100)->where('verification_status','verified')->where('archived_at IS NULL',null,false)->count_all_results('kdkmp_sites');
        $operating=(int)$this->db->where('operational_status','operating')->where('archived_at IS NULL',null,false)->count_all_results('kdkmp_sites');
        $rembang=$this->db->get_where('regions',array('code'=>'33.17'))->row_array();
        $districts=$this->db->select('id,name')->where('parent_id',$rembang['id'])->order_by('code')->get('regions')->result_array();
        $this->render_admin('kdkmp/index',array('title'=>'KDKMP','eyebrow'=>'Gerai, lokasi, dan komoditas','active_nav'=>'kdkmp','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/modul/kdkmp','district'=>$district,'districts'=>$districts,'record_status'=>$record_status,'can_create'=>$this->can('kdkmp','buat'),'all'=>$all,'completed'=>$completed,'operating'=>$operating));
    }
    public function detail($id)
    {
        $this->guard('kdkmp');$site=$this->site($id);
        $photos=$this->db->select('id,original_name,verification_status,uploaded_at')->where('owner_type','kdkmp_site')->where('owner_id',$id)->order_by('uploaded_at','DESC')->get('documents')->result_array();
        $commodities=$this->db->select('kc.*,m.name AS commodity_name,u.name AS unit_name')->from('kdkmp_commodities kc')->join('master_items m','m.id=kc.commodity_item_id')->join('master_items u','u.id=kc.unit_item_id','left')->where('kc.site_id',$id)->order_by('kc.report_period','DESC')->get()->result_array();
        $history=$this->db->select('e.*,u.full_name AS actor_name')->from('kdkmp_progress_events e')->join('users u','u.id=e.actor_user_id')->where('e.site_id',$id)->order_by('e.created_at','DESC')->get()->result_array();
        $this->render_admin('kdkmp/detail',array('title'=>$site['cooperative_name'],'eyebrow'=>'KDKMP / '.$site['village_name'],'active_nav'=>'kdkmp','site'=>$site,'photos'=>$photos,'commodities'=>$commodities,'history'=>$history,'commodity_options'=>$this->master_options('komoditas'),'unit_options'=>$this->master_options('satuan-ukuran'),'can_create'=>!$site['archived_at'] && $this->can('kdkmp','buat'),'can_edit'=>!$site['archived_at'] && $this->can('kdkmp','ubah'),'can_archive'=>$this->can('kdkmp','nonaktifkan'),'can_verify'=>!$site['archived_at'] && $this->can('kdkmp','verifikasi')));
    }
    public function form()
    {
        $this->guard('kdkmp','buat');
        $cooperatives=$this->db->select('c.id,c.name,c.nik,c.village_id')->from('cooperatives c')->join('master_items m','m.id=c.type_item_id')->where('m.code','kdkmp')->where('c.village_id IS NOT NULL',null,false)->where('c.archived_at IS NULL',null,false)->order_by('c.name')->get()->result_array();
        $this->render_admin('kdkmp/form',array('title'=>'Tambah lokasi KDKMP','eyebrow'=>'KDKMP / data lokasi','active_nav'=>'kdkmp','cooperatives'=>$cooperatives));
    }
    public function create()
    {
        $this->post_only();$this->guard('kdkmp','buat');$cooperative_id=(int)$this->input->post('cooperative_id',true);
        $coop=$this->db->select('c.id,c.village_id')->from('cooperatives c')->join('master_items m','m.id=c.type_item_id')->where('c.id',$cooperative_id)->where('m.code','kdkmp')->where('c.archived_at IS NULL',null,false)->get()->row_array();
        if (!$coop || !$coop['village_id'])show_error('Pilih koperasi KDKMP dengan desa/kelurahan yang sudah cocok.',422);
        $existing=$this->db->get_where('kdkmp_sites',array('cooperative_id'=>$cooperative_id,'village_id'=>$coop['village_id']))->row_array();
        if ($existing){redirect('admin/kdkmp/'.$existing['id']);return;}
        $values=array('cooperative_id'=>$cooperative_id,'village_id'=>$coop['village_id'],'location_name'=>trim((string)$this->input->post('location_name',true))?:null,'address'=>trim((string)$this->input->post('address',true))?:null,'verification_status'=>'pending','operational_status'=>'planned');
        $this->db->insert('kdkmp_sites',$values);$id=$this->db->insert_id();$this->audit('kdkmp_sites',$id,'create',null,$values);redirect('admin/kdkmp/'.$id);
    }
    public function toggle($id)
    {
        $this->post_only();$this->guard('kdkmp','nonaktifkan');$old=$this->site($id);$new=$old['archived_at']?null:date('Y-m-d H:i:s');
        $this->db->where('id',$id)->update('kdkmp_sites',array('archived_at'=>$new));$this->audit('kdkmp_sites',$id,$new?'archive':'restore',$old,array('archived_at'=>$new));
        $this->session->set_flashdata('success',$new?'Lokasi diarsipkan.':'Lokasi diaktifkan kembali.');redirect('admin/kdkmp/'.$id);
    }
    public function save($id)
    {
        $this->post_only();$this->guard('kdkmp','ubah');$old=$this->writable_site($id);
        $progress_raw=trim((string)$this->input->post('physical_progress',true));$progress=$progress_raw===''?null:(float)$progress_raw;
        $lat_raw=trim((string)$this->input->post('latitude',true));$lon_raw=trim((string)$this->input->post('longitude',true));
        $status=trim((string)$this->input->post('operational_status',true));$note=trim((string)$this->input->post('note',true));
        if (($progress!==null && (!is_numeric($progress_raw) || $progress<0 || $progress>100)) || !in_array($status,array('planned','building','ready','operating'),true) || mb_strlen($note)<5)show_error('Progres 0–100, status, dan catatan minimal 5 karakter wajib valid.',422);
        if (($lat_raw==='')!==($lon_raw==='') || ($lat_raw!=='' && (!is_numeric($lat_raw)||!is_numeric($lon_raw)||abs((float)$lat_raw)>90||abs((float)$lon_raw)>180)))show_error('Koordinat harus berupa pasangan lintang dan bujur yang valid.',422);
        $values=array('location_name'=>trim((string)$this->input->post('location_name',true)),'address'=>trim((string)$this->input->post('address',true)),'latitude'=>$lat_raw?:null,'longitude'=>$lon_raw?:null,'physical_progress'=>$progress,'operational_status'=>$status);
        foreach ($values as $field=>$new_value) {
            $old_value=$old[$field]??null;
            $changed=in_array($field,array('physical_progress','latitude','longitude'),true)
                ? (($old_value===null)!==($new_value===null) || ($old_value!==null && abs((float)$old_value-(float)$new_value)>0.0000001))
                : ((string)($old_value??'')!==(string)($new_value??''));
            if ($changed) { $values['verification_status']='pending'; break; }
        }
        if ($progress===null || $progress<100)$values['verification_status']='pending';
        $this->db->trans_start();$this->db->where('id',$id)->update('kdkmp_sites',$values);
        $this->db->insert('kdkmp_progress_events',array('site_id'=>$id,'progress'=>$progress,'operational_status'=>$status,'note'=>$note,'actor_user_id'=>$this->session->userdata('user_id')));
        $this->db->trans_complete();$this->audit('kdkmp_sites',$id,'progress',$old,array('values'=>$values,'note'=>$note));
        $this->session->set_flashdata('success','Lokasi dan progres tersimpan. Capaian 100% hanya dihitung sesudah bukti foto disetujui dan diverifikasi.');redirect('admin/kdkmp/'.$id);
    }
    public function upload($id)
    {
        $this->post_only();$this->guard('kdkmp','ubah');$site=$this->writable_site($id);
        if (!isset($_FILES['photo']) || $_FILES['photo']['error']!==UPLOAD_ERR_OK)show_error('Unggahan foto gagal.',422);
        $file=$_FILES['photo'];if ($file['size']<=0 || $file['size']>5*1024*1024)show_error('Ukuran foto maksimal 5 MB.',422);
        $info=@getimagesize($file['tmp_name']);
        $mime=$info['mime']??'';
        $ext=array('image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp');
        if (!isset($ext[$mime]) || !$info)show_error('Berkas harus foto JPG, PNG, atau WebP.',422);
        $filename=bin2hex(random_bytes(16)).'.'.$ext[$mime];$path=FCPATH.'private_uploads/kdkmp/'.$filename;
        if (!move_uploaded_file($file['tmp_name'],$path))show_error('Penyimpanan foto gagal.',500);
        chmod($path,0640);
        $this->db->insert('documents',array('cooperative_id'=>$site['cooperative_id'],'owner_type'=>'kdkmp_site','owner_id'=>$id,'original_name'=>mb_substr(basename($file['name']),0,255),'storage_path'=>'private_uploads/kdkmp/'.$filename,'mime_type'=>$mime,'byte_size'=>$file['size'],'sha256'=>hash_file('sha256',$path),'visibility'=>'internal','verification_status'=>'pending','uploaded_by'=>$this->session->userdata('user_id')));
        $doc_id=$this->db->insert_id();$this->audit('documents',$doc_id,'upload',null,array('owner_type'=>'kdkmp_site','owner_id'=>$id,'sha256'=>hash_file('sha256',$path)));
        $this->session->set_flashdata('success','Foto disimpan secara privat dan menunggu verifikasi.');redirect('admin/kdkmp/'.$id);
    }
    public function photo($id,$document_id)
    {
        $this->guard('kdkmp');$this->site($id);
        $doc=$this->db->get_where('documents',array('id'=>$document_id,'owner_type'=>'kdkmp_site','owner_id'=>$id))->row_array();if (!$doc)show_404();
        $path=FCPATH.$doc['storage_path'];if (!is_file($path))show_404();
        header('Content-Type: '.$doc['mime_type']);header('X-Content-Type-Options: nosniff');header('Cache-Control: private, max-age=300');readfile($path);exit;
    }
    public function approve_photo($id,$document_id)
    {
        $this->post_only();$this->guard('kdkmp','verifikasi');$this->writable_site($id);
        $doc=$this->db->get_where('documents',array('id'=>$document_id,'owner_type'=>'kdkmp_site','owner_id'=>$id))->row_array();if (!$doc)show_404();
        $this->db->where('id',$document_id)->update('documents',array('verification_status'=>'approved'));
        $this->audit('documents',$document_id,'approve',$doc,array('verification_status'=>'approved'));
        $this->session->set_flashdata('success','Foto bukti disetujui.');redirect('admin/kdkmp/'.$id);
    }
    public function verify($id)
    {
        $this->post_only();$this->guard('kdkmp','verifikasi');$site=$this->writable_site($id);
        $approved=(int)$this->db->where('owner_type','kdkmp_site')->where('owner_id',$id)->where('verification_status','approved')->count_all_results('documents');
        if ((float)$site['physical_progress']<100 || !$approved)show_error('Capaian 100% memerlukan progres 100 dan minimal satu foto bukti yang disetujui.',422);
        $this->db->where('id',$id)->update('kdkmp_sites',array('verification_status'=>'verified','verified_at'=>date('Y-m-d H:i:s')));
        $this->audit('kdkmp_sites',$id,'verify',array('verification_status'=>$site['verification_status']),array('verification_status'=>'verified','approved_photos'=>$approved));
        $this->session->set_flashdata('success','Progres 100% KDKMP diverifikasi.');redirect('admin/kdkmp/'.$id);
    }
    public function commodity($id)
    {
        $this->post_only();$commodity_id=(int)$this->input->post('id',true);$this->guard('kdkmp',$commodity_id?'ubah':'buat');$this->writable_site($id);
        $old=$commodity_id?$this->db->get_where('kdkmp_commodities',array('id'=>$commodity_id,'site_id'=>(int)$id))->row_array():null;
        if ($commodity_id && !$old)show_404();
        $commodity=(int)$this->input->post('commodity_item_id',true);$unit=(int)$this->input->post('unit_item_id',true);$period=trim((string)$this->input->post('report_period',true));$capacity=trim((string)$this->input->post('capacity',true));
        $check=function($item_id,$slug){return $item_id && $this->db->select('m.id')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('m.id',$item_id)->where('m.active',1)->where('d.slug',$slug)->get()->row_array();};
        $date=DateTime::createFromFormat('!Y-m-d',$period);
        if (!$check($commodity,'komoditas') || ($unit && !$check($unit,'satuan-ukuran')) || !$date || $date->format('Y-m-d')!==$period || ($capacity!=='' && (!is_numeric($capacity)||(float)$capacity<0)))show_error('Komoditas, satuan, periode, atau kapasitas tidak valid.',422);
        $duplicate=$this->db->where('site_id',$id)->where('commodity_item_id',$commodity)->where('report_period',$period);
        if ($commodity_id)$duplicate->where('id !=',$commodity_id);
        if ($duplicate->count_all_results('kdkmp_commodities'))show_error('Komoditas untuk periode itu sudah tercatat. Buka baris yang ada untuk mengubahnya.',409);
        $values=array('site_id'=>$id,'commodity_item_id'=>$commodity,'unit_item_id'=>$unit?:null,'report_period'=>$period,'capacity'=>$capacity===''?null:$capacity,'notes'=>trim((string)$this->input->post('notes',true)));
        if ($old)$this->db->where('id',$commodity_id)->update('kdkmp_commodities',$values);
        else {$this->db->insert('kdkmp_commodities',$values);$commodity_id=$this->db->insert_id();}
        $this->audit('kdkmp_commodities',$commodity_id,$old?'update':'create',$old,$values);$this->session->set_flashdata('success','Komoditas tersimpan untuk periode tersebut.');redirect('admin/kdkmp/'.$id);
    }
    public function delete_commodity($id,$commodity_id)
    {
        $this->post_only();$this->guard('kdkmp','nonaktifkan');$this->writable_site($id);
        $old=$this->db->get_where('kdkmp_commodities',array('id'=>(int)$commodity_id,'site_id'=>(int)$id))->row_array();if (!$old)show_404();
        $this->db->where('id',$commodity_id)->delete('kdkmp_commodities');
        $this->audit('kdkmp_commodities',$commodity_id,'delete',$old,null);
        $this->session->set_flashdata('success','Laporan komoditas dihapus.');redirect('admin/kdkmp/'.$id);
    }
}
