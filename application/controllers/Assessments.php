<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assessments extends MY_Controller
{
    private function allowed_cooperative($cooperative_id)
    {
        $user=$this->current_user();
        if (!$this->can('import') && !$this->can('pengguna') && (int)$user['cooperative_id']!==(int)$cooperative_id) show_error('Penilaian hanya untuk koperasi yang terhubung dengan akun Anda.',403);
    }

    private function assessment($id)
    {
        $a=$this->db->select('a.*,c.name AS cooperative_name,c.nik,c.reference_only,m.name AS form_name,v.id AS version_id,v.status AS version_status,v.source_file_name')
            ->from('assessments a')->join('cooperatives c','c.id=a.cooperative_id')->join('master_items m','m.id=a.form_item_id')
            ->join('assessment_form_versions v','v.form_item_id=a.form_item_id AND v.version_code=a.form_version')
            ->where('a.id',(int)$id)->get()->row_array();
        if (!$a)show_404();
        $this->allowed_cooperative($a['cooperative_id']);
        return $a;
    }

    public function index()
    {
        $this->guard('penilaian');$p=$this->page_args();$user=$this->current_user();$own_only=!$this->can('import') && !$this->can('pengguna');
        $this->db->select('a.id,a.fiscal_year,a.revision_no,a.status,a.locked_at,c.name AS cooperative_name,m.name AS form_name')
            ->from('assessments a')->join('cooperatives c','c.id=a.cooperative_id')->join('master_items m','m.id=a.form_item_id');
        if ($own_only)$this->db->where('a.cooperative_id',(int)$user['cooperative_id']);
        if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('c.nik',$p['q'])->group_end();
        if (in_array($p['status'],array('draft','submitted','reviewed','withdrawn'),true))$this->db->where('a.status',$p['status']);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('a.created_at','DESC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('assessments/index',array('title'=>'Penilaian kesehatan','eyebrow'=>'KKPKK · penilaian mandiri','active_nav'=>'penilaian','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/modul/penilaian'));
    }

    public function create($cooperative_id)
    {
        $this->post_only();$this->guard('penilaian','buat');$this->allowed_cooperative($cooperative_id);
        $coop=$this->db->select('c.*,t.code AS type_code,p.code AS pattern_code,k.code AS kuk_code')->from('cooperatives c')
            ->join('master_items t','t.id=c.type_item_id','left')->join('master_items p','p.id=c.pattern_item_id','left')->join('master_items k','k.id=c.kuk_item_id','left')->where('c.id',(int)$cooperative_id)->get()->row_array();
        if (!$coop)show_404();
        if ($coop['reference_only'] || $coop['archived_at'])show_error('Data referensi atau koperasi arsip tidak dapat memulai penilaian kabupaten.',403);
        $year=(int)$this->input->post('fiscal_year',true);
        if ($year<2000 || $year>(int)date('Y')+1)show_error('Tahun buku tidak valid.',422);
        if (!in_array($coop['kuk_code'],array('1','2','3','4'),true) || !in_array($coop['pattern_code'],array('konvensional','syariah'),true) || !$coop['type_code']) {
            $this->session->set_flashdata('error','Lengkapi jenis, pola pengelolaan, dan KUK pada profil koperasi sebelum menilai.');redirect('admin/koperasi/'.$cooperative_id);return;
        }
        $unit_id=(int)$this->input->post('unit_id',true);$unit_type=null;
        if ($unit_id){
            $unit=$this->db->select('u.id,m.code')->from('cooperative_units u')->join('master_items m','m.id=u.unit_type_item_id')->where('u.id',$unit_id)->where('u.cooperative_id',$cooperative_id)->where('u.active',1)->get()->row_array();
            if (!$unit || !in_array($unit['code'],array('usp','uspps'),true))show_error('Unit USP/USPPS tidak sesuai koperasi.',422);
            $unit_type=$unit['code'];
        }
        $group=$unit_type ? ($unit_type==='uspps'?'kspps':'ksp') : ($coop['type_code']==='simpan_pinjam' ? ($coop['pattern_code']==='syariah'?'kspps':'ksp') : ($coop['pattern_code']==='syariah'?'nonksp_syariah':'nonksp'));
        $route=$group.'_'.(in_array($coop['kuk_code'],array('1','2'),true)?'12':'34');
        $form=$this->db->select('m.id,v.id AS version_id,v.version_code,v.status')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->join('assessment_form_versions v','v.form_item_id=m.id')->where('d.slug','paket-kkpkk')->where('m.code',$route)->where('v.version_code','211224')->get()->row_array();
        if (!$form)show_error('Jalur KKPKK belum tersedia untuk data ini.',422);
        $this->db->where('cooperative_id',$cooperative_id)->where('fiscal_year',$year)->where('form_item_id',$form['id']);
        if ($unit_id)$this->db->where('unit_id',$unit_id);else $this->db->where('unit_id IS NULL',null,false);
        $prior=$this->db->order_by('revision_no','DESC')->limit(1)->get('assessments')->row_array();
        if ($prior && $prior['status']!=='withdrawn'){redirect('admin/penilaian/'.$prior['id']);return;}
        $indicators=$this->db->select('indicator_code,prompt,condition_json,scoring_json,guidance,sort_order')->where('form_version_id',$form['version_id'])->order_by('sort_order')->get('assessment_indicators')->result_array();
        $snapshot=json_encode($indicators,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $this->db->trans_start();
        $this->db->insert('assessments',array('cooperative_id'=>$cooperative_id,'unit_id'=>$unit_id?:null,'fiscal_year'=>$year,'form_item_id'=>$form['id'],'form_version'=>$form['version_code'],'revision_no'=>$prior?(int)$prior['revision_no']+1:1,'supersedes_id'=>$prior?$prior['id']:null,'status'=>'draft','created_by'=>$this->session->userdata('user_id')));
        $id=$this->db->insert_id();
        $this->db->insert('assessment_form_snapshots',array('assessment_id'=>$id,'form_version_id'=>$form['version_id'],'snapshot_json'=>$snapshot,'snapshot_sha256'=>hash('sha256',$snapshot)));
        $this->db->trans_complete();$this->audit('assessments',$id,'create',null,array('route'=>$route,'year'=>$year,'version'=>$form['version_code']));
        redirect('admin/penilaian/'.$id);
    }

    public function detail($id)
    {
        $this->guard('penilaian');$a=$this->assessment($id);$p=$this->page_args();
        $this->db->select('i.indicator_code,i.prompt,i.guidance,i.condition_json,aa.answer_json')
            ->from('assessment_indicators i')->join('assessment_answers aa','aa.assessment_id='.(int)$id.' AND aa.indicator_code=i.indicator_code','left')
            ->where('i.form_version_id',$a['version_id']);
        if ($p['q'])$this->db->like('i.prompt',$p['q']);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('i.sort_order')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $all=(int)$this->db->where('form_version_id',$a['version_id'])->count_all_results('assessment_indicators');
        $answered=(int)$this->db->where('assessment_id',$id)->count_all_results('assessment_answers');
        $financial_answered=(int)$this->db->where('assessment_id',$id)->count_all_results('assessment_financial_answers');
        $documents=$this->db->select('d.id,d.original_name,d.verification_status,d.uploaded_at,m.name AS type_name')->from('documents d')->join('master_items m','m.id=d.document_type_item_id','left')->where('d.owner_type','assessment')->where('d.owner_id',$id)->order_by('d.uploaded_at','DESC')->get()->result_array();
        $document_types=$this->db->select('m.id,m.name')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('d.slug','jenis-dokumen')->where('m.active',1)->order_by('m.name')->get()->result_array();
        $this->render_admin('assessments/detail',array('title'=>'Penilaian '.$a['fiscal_year'],'eyebrow'=>'KKPKK / '.$a['cooperative_name'],'active_nav'=>'penilaian','a'=>$a,'rows'=>$rows,'p'=>$p,'total'=>$total,'all'=>$all,'answered'=>$answered,'financial_answered'=>$financial_answered,'path'=>'admin/penilaian/'.$id,'can_edit'=>$this->can('penilaian','ubah'),'can_withdraw'=>$this->can('penilaian','nonaktifkan'),'can_revise'=>$this->can('penilaian','verifikasi'),'documents'=>$documents,'document_types'=>$document_types));
    }

    public function withdraw($id)
    {
        $this->post_only();$this->guard('penilaian','nonaktifkan');$a=$this->assessment($id);
        if ($a['status']!=='draft' || $a['locked_at'])show_error('Hanya draf yang belum dikunci dapat dibatalkan.',409);
        $this->db->where('id',$id)->update('assessments',array('status'=>'withdrawn'));
        $this->audit('assessments',$id,'withdraw',array('status'=>'draft'),array('status'=>'withdrawn'));
        $this->session->set_flashdata('success','Draf dibatalkan dan tetap tersimpan sebagai jejak.');redirect('admin/penilaian/'.$id);
    }

    public function financial($id)
    {
        $this->guard('penilaian');$a=$this->assessment($id);$p=$this->page_args();
        $this->db->select('f.field_code,f.account_code,f.account_name,f.year_offset,f.guidance,f.source_row,fa.amount')->from('assessment_financial_fields f')
            ->join('assessment_financial_answers fa','fa.assessment_id='.(int)$id.' AND fa.field_code=f.field_code','left')->where('f.form_version_id',$a['version_id']);
        if ($p['q'])$this->db->group_start()->like('f.account_name',$p['q'])->or_like('f.account_code',$p['q'])->group_end();
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('f.sort_order')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $all=(int)$this->db->where('form_version_id',$a['version_id'])->count_all_results('assessment_financial_fields');
        $answered=(int)$this->db->where('assessment_id',$id)->count_all_results('assessment_financial_answers');
        $this->render_admin('assessments/financial',array('title'=>'Isian keuangan '.$a['fiscal_year'],'eyebrow'=>'KKPKK / '.$a['cooperative_name'],'active_nav'=>'penilaian','a'=>$a,'rows'=>$rows,'p'=>$p,'total'=>$total,'all'=>$all,'answered'=>$answered,'path'=>'admin/penilaian/'.$id.'/keuangan','can_edit'=>$this->can('penilaian','ubah')));
    }

    private function parse_amount($raw)
    {
        $raw=trim((string)$raw);
        if ($raw==='')return null;
        $raw=str_replace(array(' ',chr(194).chr(160)),'',$raw);
        if (strpos($raw,',')!==false)$raw=str_replace(',','.',str_replace('.','',$raw));
        elseif (preg_match('/^-?\d{1,3}(?:\.\d{3})+$/',$raw))$raw=str_replace('.','',$raw);
        if (!preg_match('/^-?\d{1,18}(?:\.\d{1,2})?$/',$raw))show_error('Nilai keuangan harus berupa angka, maksimal dua desimal. Gunakan 1000000 atau 1.000.000,00.',422);
        return $raw;
    }

    public function save_financial($id)
    {
        $this->post_only();$this->guard('penilaian','ubah');$a=$this->assessment($id);
        if ($a['status']!=='draft' || $a['locked_at'])show_error('Isian keuangan sudah terkunci. Gunakan proses revisi.',409);
        $amounts=(array)$this->input->post('amount',false);if (!$amounts)show_error('Tidak ada isian yang dikirim.',422);
        $codes=array_keys($amounts);$valid=array_column($this->db->select('field_code')->where('form_version_id',$a['version_id'])->where_in('field_code',$codes)->get('assessment_financial_fields')->result_array(),'field_code');
        if (count($valid)!==count($codes))show_error('Kode rekening tidak sesuai versi formulir.',422);
        $parsed=array();foreach ($amounts as $code=>$raw)$parsed[$code]=$this->parse_amount($raw);
        $this->db->trans_start();foreach ($parsed as $code=>$amount){
            if ($amount===null)$this->db->where('assessment_id',$id)->where('field_code',$code)->delete('assessment_financial_answers');
            else $this->db->query('INSERT INTO assessment_financial_answers(assessment_id,field_code,amount) VALUES(?,?,?) ON DUPLICATE KEY UPDATE amount=VALUES(amount)',array($id,$code,$amount));
        }$this->db->trans_complete();$this->audit('assessments',$id,'save_financial',null,array('field_count'=>count($parsed)));
        $this->session->set_flashdata('success','Isian keuangan tersimpan sebagai draf.');redirect('admin/penilaian/'.$id.'/keuangan?page='.(int)$this->input->post('page',true));
    }

    public function save($id)
    {
        $this->post_only();$this->guard('penilaian','ubah');$a=$this->assessment($id);
        if ($a['status']!=='draft' || $a['locked_at'])show_error('Penilaian sudah dikunci. Gunakan proses revisi.',409);
        $answers=(array)$this->input->post('answer',true);if (!$answers)show_error('Tidak ada jawaban yang dikirim.',422);
        $codes=array_keys($answers);
        $indicators=$this->db->select('indicator_code')->where('form_version_id',$a['version_id'])->where_in('indicator_code',$codes)->get('assessment_indicators')->result_array();
        $valid=array_column($indicators,'indicator_code');if (count($valid)!==count($answers))show_error('Kode pertanyaan tidak sesuai versi formulir.',422);
        foreach ($answers as $value) {
            if ($value!=='' && !in_array($value,array('Terpenuhi','Tidak Terpenuhi'),true)) show_error('Jawaban harus Terpenuhi atau Tidak Terpenuhi.',422);
        }
        $this->db->trans_start();
        foreach ($answers as $code=>$value){
            if ($value==='')continue;
            $json=json_encode(array('condition'=>$value),JSON_UNESCAPED_UNICODE);
            $this->db->query('INSERT INTO assessment_answers(assessment_id,indicator_code,answer_json,self_value) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE answer_json=VALUES(answer_json),self_value=VALUES(self_value)',array($id,$code,$json,$value==='Terpenuhi'?1:0));
        }
        $this->db->trans_complete();$this->audit('assessments',$id,'save_answers',null,array('codes'=>$codes));
        $this->session->set_flashdata('success','Jawaban tersimpan sebagai draf.');redirect('admin/penilaian/'.$id.'?page='.(int)$this->input->post('page',true));
    }

    public function submit($id)
    {
        $this->post_only();$this->guard('penilaian','ubah');$a=$this->assessment($id);
        if ($a['status']!=='draft' || $a['locked_at'])show_error('Penilaian sudah dikunci.',409);
        $total=(int)$this->db->where('form_version_id',$a['version_id'])->count_all_results('assessment_indicators');
        $answered=(int)$this->db->where('assessment_id',$id)->count_all_results('assessment_answers');
        if ($answered!==$total){$this->session->set_flashdata('error','Lengkapi seluruh '.($total-$answered).' jawaban yang belum terisi.');redirect('admin/penilaian/'.$id);return;}
        $financial=(int)$this->db->where('assessment_id',$id)->count_all_results('assessment_financial_answers');
        $documents=(int)$this->db->where('owner_type','assessment')->where('owner_id',$id)->count_all_results('documents');
        if (!$financial || !$documents){$this->session->set_flashdata('error','Isi sekurangnya satu rekening keuangan dan unggah dokumen pendukung sebelum mengunci.');redirect('admin/penilaian/'.$id);return;}
        $this->db->where('id',$id)->where('status','draft')->where('locked_at IS NULL',null,false)->update('assessments',array('status'=>'submitted','locked_at'=>date('Y-m-d H:i:s'),'submitted_at'=>date('Y-m-d H:i:s')));
        $this->audit('assessments',$id,'submit_lock',array('status'=>'draft'),array('status'=>'submitted'));
        $this->session->set_flashdata('success','Jawaban indikator dan isian keuangan yang ada telah dikunci. Skor resmi menunggu verifikasi formula dan pemeriksa.');redirect('admin/penilaian/'.$id);
    }

    public function revise($id)
    {
        $this->post_only();$this->guard('penilaian','ubah');$a=$this->assessment($id);
        if (!$this->can('penilaian','verifikasi'))show_error('Revisi hanya dapat dibuka petugas berwenang.',403);
        if ($a['status']!=='submitted')show_error('Hanya penilaian terkunci yang dapat direvisi.',409);
        $reason=trim((string)$this->input->post('reason',true));if (mb_strlen($reason)<10)show_error('Alasan revisi minimal 10 karakter.',422);
        $existing=$this->db->get_where('assessments',array('supersedes_id'=>$id))->row_array();if ($existing){redirect('admin/penilaian/'.$existing['id']);return;}
        $old_snapshot=$this->db->get_where('assessment_form_snapshots',array('assessment_id'=>$id))->row_array();
        $this->db->trans_start();
        $this->db->insert('assessments',array('cooperative_id'=>$a['cooperative_id'],'unit_id'=>$a['unit_id'],'fiscal_year'=>$a['fiscal_year'],'form_item_id'=>$a['form_item_id'],'form_version'=>$a['form_version'],'revision_no'=>$a['revision_no']+1,'supersedes_id'=>$id,'status'=>'draft','created_by'=>$this->session->userdata('user_id')));
        $new_id=$this->db->insert_id();
        $this->db->insert('assessment_form_snapshots',array('assessment_id'=>$new_id,'form_version_id'=>$old_snapshot['form_version_id'],'snapshot_json'=>$old_snapshot['snapshot_json'],'snapshot_sha256'=>$old_snapshot['snapshot_sha256']));
        $this->db->query('INSERT INTO assessment_answers(assessment_id,indicator_code,answer_json,self_value) SELECT ?,indicator_code,answer_json,self_value FROM assessment_answers WHERE assessment_id=?',array($new_id,$id));
        $this->db->query('INSERT INTO assessment_financial_answers(assessment_id,field_code,amount) SELECT ?,field_code,amount FROM assessment_financial_answers WHERE assessment_id=?',array($new_id,$id));
        $this->db->trans_complete();$this->audit('assessments',$new_id,'revision',array('supersedes_id'=>$id),array('reason'=>$reason));
        redirect('admin/penilaian/'.$new_id);
    }

    public function upload($id)
    {
        $this->post_only();$this->guard('penilaian','ubah');$a=$this->assessment($id);
        if ($a['status']!=='draft' || $a['locked_at'])show_error('Dokumen versi terkunci tidak dapat diganti.',409);
        $type_id=(int)$this->input->post('document_type_item_id',true);
        $type=$this->db->select('m.id')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('m.id',$type_id)->where('d.slug','jenis-dokumen')->where('m.active',1)->get()->row_array();
        if (!$type)show_error('Jenis dokumen tidak valid.',422);
        if (!isset($_FILES['document']) || $_FILES['document']['error']!==UPLOAD_ERR_OK)show_error('Unggah dokumen gagal.',422);
        $file=$_FILES['document'];if ($file['size']<=0 || $file['size']>10*1024*1024)show_error('Dokumen maksimal 10 MB.',422);
        $prefix=file_get_contents($file['tmp_name'],false,null,0,5);$image=@getimagesize($file['tmp_name']);
        $mime=$prefix==='%PDF-'?'application/pdf':($image['mime']??'');
        $ext=array('application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp');
        if (!isset($ext[$mime]))show_error('Gunakan PDF, JPG, PNG, atau WebP.',422);
        $filename=bin2hex(random_bytes(16)).'.'.$ext[$mime];$path=FCPATH.'private_uploads/assessment/'.$filename;
        if (!move_uploaded_file($file['tmp_name'],$path))show_error('Penyimpanan dokumen gagal.',500);
        chmod($path,0640);
        $this->db->insert('documents',array('cooperative_id'=>$a['cooperative_id'],'owner_type'=>'assessment','owner_id'=>$id,'document_type_item_id'=>$type_id,'fiscal_year'=>$a['fiscal_year'],'original_name'=>mb_substr(basename($file['name']),0,255),'storage_path'=>'private_uploads/assessment/'.$filename,'mime_type'=>$mime,'byte_size'=>$file['size'],'sha256'=>hash_file('sha256',$path),'visibility'=>'private','verification_status'=>'pending','uploaded_by'=>$this->session->userdata('user_id')));
        $document_id=$this->db->insert_id();$this->audit('documents',$document_id,'upload',null,array('assessment_id'=>$id,'type_id'=>$type_id));
        $this->session->set_flashdata('success','Dokumen disimpan secara privat untuk verifikasi.');redirect('admin/penilaian/'.$id);
    }
    public function download($id,$document_id)
    {
        $this->guard('penilaian');$this->assessment($id);
        $doc=$this->db->get_where('documents',array('id'=>$document_id,'owner_type'=>'assessment','owner_id'=>$id))->row_array();if (!$doc)show_404();
        $path=FCPATH.$doc['storage_path'];if (!is_file($path))show_404();
        $ext=pathinfo($path,PATHINFO_EXTENSION);header('Content-Type: '.$doc['mime_type']);header('Content-Disposition: attachment; filename="bukti-'.$document_id.'.'.$ext.'"');header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');readfile($path);exit;
    }
    public function approve_document($id,$document_id)
    {
        $this->post_only();$this->guard('penilaian','verifikasi');$this->assessment($id);
        $doc=$this->db->get_where('documents',array('id'=>$document_id,'owner_type'=>'assessment','owner_id'=>$id))->row_array();if (!$doc)show_404();
        $this->db->where('id',$document_id)->update('documents',array('verification_status'=>'approved'));
        $this->audit('documents',$document_id,'approve',$doc,array('verification_status'=>'approved'));
        $this->session->set_flashdata('success','Dokumen disetujui.');redirect('admin/penilaian/'.$id);
    }
    public function review($id)
    {
        $this->post_only();$this->guard('penilaian','verifikasi');$a=$this->assessment($id);
        if ($a['status']!=='submitted')show_error('Hanya penilaian terkunci yang dapat diverifikasi.',409);
        $note=trim((string)$this->input->post('note',true));if (mb_strlen($note)<10)show_error('Catatan verifikasi minimal 10 karakter.',422);
        $documents=(int)$this->db->where('owner_type','assessment')->where('owner_id',$id)->count_all_results('documents');
        $pending=(int)$this->db->where('owner_type','assessment')->where('owner_id',$id)->where('verification_status !=','approved')->count_all_results('documents');
        if (!$documents || $pending)show_error('Minimal satu dokumen harus diunggah dan seluruh dokumen harus disetujui.',422);
        $this->db->where('id',$id)->update('assessments',array('status'=>'reviewed','reviewed_at'=>date('Y-m-d H:i:s')));
        $this->audit('assessments',$id,'review',array('status'=>'submitted'),array('status'=>'reviewed','note'=>$note));
        $this->session->set_flashdata('success','Kelengkapan dan jawaban ditinjau. Skor serta sertifikat resmi belum diterbitkan.');redirect('admin/penilaian/'.$id);
    }
    public function minutes($id)
    {
        $this->guard('penilaian');$a=$this->assessment($id);
        $binary=$this->db->select('COUNT(*) AS total,SUM(self_value=1) AS met,SUM(self_value=0) AS unmet')->where('assessment_id',$id)->get('assessment_answers')->row_array();
        $financial=(int)$this->db->where('assessment_id',$id)->count_all_results('assessment_financial_answers');
        $docs=$this->db->select('d.original_name,d.verification_status,m.name AS type_name')->from('documents d')->join('master_items m','m.id=d.document_type_item_id','left')->where('d.owner_type','assessment')->where('d.owner_id',$id)->order_by('d.uploaded_at')->get()->result_array();
        $review=$this->db->select('e.after_json,e.occurred_at AS created_at,u.full_name')->from('audit_events e')->join('users u','u.id=e.actor_user_id','left')->where('e.entity_type','assessments')->where('e.entity_id',$id)->where('e.action_code','review')->order_by('e.id','DESC')->limit(1)->get()->row_array();
        $review_note=$review?json_decode($review['after_json'],true):array();
        $this->load->view('assessments/minutes',array('a'=>$a,'binary'=>$binary,'financial'=>$financial,'docs'=>$docs,'review'=>$review,'review_note'=>$review_note));
    }
}
