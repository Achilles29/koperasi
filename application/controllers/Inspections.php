<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inspections extends MY_Controller
{
    private $statuses=array('planned'=>'Direncanakan','in_progress'=>'Berjalan','completed'=>'Selesai','cancelled'=>'Dibatalkan');
    private function inspection($id)
    {
        $row=$this->db->select('i.*,c.name AS cooperative_name,c.nik,c.reference_only,u.full_name AS lead_name,a.fiscal_year AS assessment_year,m.name AS form_name')
          ->from('inspections i')->join('cooperatives c','c.id=i.cooperative_id')->join('users u','u.id=i.lead_user_id','left')->join('assessments a','a.id=i.assessment_id','left')->join('master_items m','m.id=a.form_item_id','left')->where('i.id',(int)$id)->get()->row_array();
        if (!$row)show_404();return $row;
    }
    public function index()
    {
        $this->guard('pengawasan');$p=$this->page_args();
        $this->db->select('i.id,i.assignment_number,i.inspection_type,i.status,i.started_on,i.created_at,c.name AS cooperative_name,u.full_name AS lead_name')
          ->from('inspections i')->join('cooperatives c','c.id=i.cooperative_id')->join('users u','u.id=i.lead_user_id','left');
        if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('c.nik',$p['q'])->or_like('i.assignment_number',$p['q'])->group_end();
        if (isset($this->statuses[$p['status']]))$this->db->where('i.status',$p['status']);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('i.created_at','DESC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('inspections/index',array('title'=>'Pengawasan koperasi','eyebrow'=>'Pemeriksaan dan tindak lanjut','active_nav'=>'pengawasan','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/modul/pengawasan','statuses'=>$this->statuses,'can_create'=>$this->can('pengawasan','buat')));
    }
    public function form($id=null)
    {
        $this->guard('pengawasan',$id?'ubah':'buat');$row=$id?$this->inspection($id):null;
        if ($row && $row['status']!=='planned')show_error('Identitas pemeriksaan hanya dapat diubah selama direncanakan.',409);
        $cooperatives=$this->db->select('id,name,nik')->where('reference_only',0)->order_by('name')->get('cooperatives')->result_array();
        $staff=$this->db->select('id,full_name')->where('active',1)->order_by('full_name')->get('users')->result_array();
        $assessments=$this->db->select('a.id,a.cooperative_id,a.fiscal_year,a.revision_no,c.name AS cooperative_name')->from('assessments a')->join('cooperatives c','c.id=a.cooperative_id')->where_in('a.status',array('submitted','reviewed'))->order_by('a.created_at','DESC')->get()->result_array();
        $this->render_admin('inspections/form',array('title'=>$id?'Ubah rencana pemeriksaan':'Rencanakan pemeriksaan','eyebrow'=>'Pengawasan','active_nav'=>'pengawasan','row'=>$row,'cooperatives'=>$cooperatives,'staff'=>$staff,'assessments'=>$assessments));
    }
    public function save()
    {
        $this->post_only();$id=(int)$this->input->post('id',true);$this->guard('pengawasan',$id?'ubah':'buat');$old=$id?$this->inspection($id):null;
        if ($old && $old['status']!=='planned')show_error('Identitas pemeriksaan hanya dapat diubah selama direncanakan.',409);
        $coop_id=(int)$this->input->post('cooperative_id',true);$assessment_id=(int)$this->input->post('assessment_id',true);
        $coop=$this->db->get_where('cooperatives',array('id'=>$coop_id,'reference_only'=>0))->row_array();if (!$coop)show_error('Pemeriksaan kabupaten hanya untuk koperasi dalam daftar kewenangan.',422);
        if ($assessment_id){$a=$this->db->get_where('assessments',array('id'=>$assessment_id,'cooperative_id'=>$coop_id))->row_array();if (!$a || !in_array($a['status'],array('submitted','reviewed'),true))show_error('Penilaian harus milik koperasi dan sudah dikunci.',422);}
        $type=$this->input->post('inspection_type',true);if (!in_array($type,array('routine','insidental'),true))show_error('Jenis pemeriksaan tidak valid.',422);
        $lead=(int)$this->input->post('lead_user_id',true);if ($lead && !$this->db->get_where('users',array('id'=>$lead,'active'=>1))->row_array())show_error('Petugas tidak valid.',422);
        $start=$this->input->post('started_on',true);if ($start && !DateTime::createFromFormat('!Y-m-d',$start))show_error('Tanggal tidak valid.',422);
        $values=array('cooperative_id'=>$coop_id,'assessment_id'=>$assessment_id?:null,'inspection_type'=>$type,'assignment_number'=>trim((string)$this->input->post('assignment_number',true))?:null,'lead_user_id'=>$lead?:null,'started_on'=>$start?:null,'status'=>'planned');
        if ($old){unset($values['status']);$this->db->where('id',$id)->update('inspections',$values);}else{$this->db->insert('inspections',$values);$id=$this->db->insert_id();}
        $this->audit('inspections',$id,$old?'update':'create',$old,$values);redirect('admin/pengawasan/'.$id);
    }
    public function detail($id)
    {
        $this->guard('pengawasan');$row=$this->inspection($id);
        $findings=$this->db->where('inspection_id',$id)->order_by('id')->get('findings')->result_array();
        $actions=array();if ($findings){$ids=array_column($findings,'id');$all=$this->db->select('f.*,u.full_name AS owner_name')->from('follow_up_actions f')->join('users u','u.id=f.owner_user_id','left')->where_in('f.finding_id',$ids)->order_by('f.created_at')->get()->result_array();foreach ($all as $a)$actions[$a['finding_id']][]=$a;}
        $unmet=array();if ($row['assessment_id'])$unmet=$this->db->select('aa.indicator_code,i.prompt')->from('assessment_answers aa')->join('assessment_indicators i','i.indicator_code=aa.indicator_code')->join('assessment_form_versions v','v.id=i.form_version_id')->join('assessments a','a.form_item_id=v.form_item_id AND a.form_version=v.version_code')->where('a.id',$row['assessment_id'])->where('aa.assessment_id',$row['assessment_id'])->where('aa.self_value',0)->order_by('i.sort_order')->limit(15)->get()->result_array();
        $open_count=0;foreach ($findings as $f)if ($f['status']!=='closed')$open_count++;
        $this->render_admin('inspections/detail',array('title'=>$row['cooperative_name'],'eyebrow'=>'Pemeriksaan / '.($row['assignment_number']?:'tanpa nomor'),'active_nav'=>'pengawasan','row'=>$row,'findings'=>$findings,'actions'=>$actions,'unmet'=>$unmet,'open_count'=>$open_count,'statuses'=>$this->statuses,'can_create'=>$row['status']!=='cancelled' && $this->can('pengawasan','buat'),'can_edit'=>$row['status']!=='cancelled' && $this->can('pengawasan','ubah'),'can_archive'=>$this->can('pengawasan','nonaktifkan'),'can_finish'=>$row['status']!=='cancelled' && $this->can('pengawasan','verifikasi')));
    }
    public function finding($id)
    {
        $this->post_only();$finding_id=(int)$this->input->post('finding_id',true);$this->guard('pengawasan',$finding_id?'ubah':'buat');$inspection=$this->inspection($id);
        if (in_array($inspection['status'],array('completed','cancelled'),true))show_error('Pemeriksaan selesai atau batal tidak dapat diubah.',409);
        $old=$finding_id?$this->db->get_where('findings',array('id'=>$finding_id,'inspection_id'=>$id))->row_array():null;
        if ($finding_id && !$old)show_404();
        if ($old && $old['status']==='closed')show_error('Temuan yang sudah ditutup tidak dapat diubah.',409);
        $aspect=trim((string)$this->input->post('aspect_code',true));$fact=trim((string)$this->input->post('fact',true));$recommendation=trim((string)$this->input->post('recommendation',true));
        if (!$aspect || mb_strlen($aspect)>80 || mb_strlen($fact)<10)show_error('Aspek dan fakta minimal 10 karakter wajib diisi.',422);
        $indicator=trim((string)$this->input->post('indicator_code',true));
        if ($indicator){
            if (!$inspection['assessment_id'])show_error('Pemeriksaan ini belum ditautkan ke penilaian.',422);
            $found=$this->db->select('i.indicator_code')->from('assessment_indicators i')->join('assessment_form_versions v','v.id=i.form_version_id')->join('assessments a','a.form_item_id=v.form_item_id AND a.form_version=v.version_code')->where('a.id',$inspection['assessment_id'])->where('i.indicator_code',$indicator)->get()->row_array();
            if (!$found)show_error('Indikator tidak ada dalam paket penilaian terkait.',422);
        }
        $due=$this->input->post('due_on',true);if ($due && (!($date=DateTime::createFromFormat('!Y-m-d',$due)) || $date->format('Y-m-d')!==$due))show_error('Tanggal tindak lanjut tidak valid.',422);
        $values=array('inspection_id'=>$id,'indicator_code'=>$indicator?:null,'aspect_code'=>$aspect,'fact'=>$fact,'legal_basis'=>trim((string)$this->input->post('legal_basis',true)),'recommendation'=>$recommendation,'cooperative_response'=>trim((string)$this->input->post('cooperative_response',true)),'due_on'=>$due?:null);
        if ($old)$this->db->where('id',$finding_id)->update('findings',$values);
        else {$values['status']='open';$this->db->insert('findings',$values);$finding_id=$this->db->insert_id();}
        $this->audit('findings',$finding_id,$old?'update':'create',$old,$values);
        $this->db->where('id',$id)->update('inspections',array('status'=>'in_progress'));
        $this->session->set_flashdata('success','Temuan disimpan dan akan muncul pada berita acara draf.');redirect('admin/pengawasan/'.$id);
    }
    public function delete_finding($id,$finding_id)
    {
        $this->post_only();$this->guard('pengawasan','nonaktifkan');$inspection=$this->inspection($id);
        if (in_array($inspection['status'],array('completed','cancelled'),true))show_error('Pemeriksaan selesai atau batal tidak dapat diubah.',409);
        $old=$this->db->get_where('findings',array('id'=>(int)$finding_id,'inspection_id'=>(int)$id))->row_array();if (!$old)show_404();
        if ($old['status']==='closed' || $this->db->where('finding_id',$finding_id)->count_all_results('follow_up_actions'))show_error('Temuan dengan tindak lanjut atau yang sudah ditutup tidak dapat dihapus.',409);
        $this->db->where('id',$finding_id)->delete('findings');$this->audit('findings',$finding_id,'delete',$old,null);
        $this->session->set_flashdata('success','Temuan dihapus dan jejak perubahannya disimpan.');redirect('admin/pengawasan/'.$id);
    }
    public function action($id,$finding_id)
    {
        $this->post_only();$this->guard('pengawasan','ubah');$inspection=$this->inspection($id);
        if (in_array($inspection['status'],array('completed','cancelled'),true))show_error('Pemeriksaan selesai atau batal tidak dapat diubah.',409);
        $finding=$this->db->get_where('findings',array('id'=>$finding_id,'inspection_id'=>$id))->row_array();if (!$finding)show_404();
        $note=trim((string)$this->input->post('action_text',true));if (mb_strlen($note)<5)show_error('Catatan tindak lanjut minimal 5 karakter.',422);
        $status=$this->input->post('status',true);if (!in_array($status,array('open','submitted','verified'),true))show_error('Status tindak lanjut tidak valid.',422);
        $values=array('finding_id'=>$finding_id,'owner_user_id'=>$this->session->userdata('user_id'),'action_text'=>$note,'status'=>$status);
        if ($status==='verified'){$values['verified_by']=$this->session->userdata('user_id');$values['verified_at']=date('Y-m-d H:i:s');}
        $this->db->insert('follow_up_actions',$values);$action_id=$this->db->insert_id();
        if ($status==='verified')$this->db->where('id',$finding_id)->update('findings',array('status'=>'closed'));
        $this->audit('follow_up_actions',$action_id,'create',null,$values);$this->session->set_flashdata('success','Tindak lanjut temuan tersimpan.');redirect('admin/pengawasan/'.$id);
    }
    public function minutes($id)
    {
        $this->guard('pengawasan');$row=$this->inspection($id);$findings=$this->db->where('inspection_id',$id)->order_by('id')->get('findings')->result_array();
        $this->load->view('inspections/minutes',array('row'=>$row,'findings'=>$findings));
    }
    public function finish($id)
    {
        $this->post_only();$this->guard('pengawasan','verifikasi');$row=$this->inspection($id);
        if (in_array($row['status'],array('completed','cancelled'),true))show_error('Pemeriksaan sudah selesai atau dibatalkan.',409);
        if (!$row['assignment_number'] || !$row['lead_user_id'] || !$row['started_on'])show_error('Lengkapi surat tugas, petugas, dan tanggal mulai sebelum menyelesaikan.',422);
        $open=(int)$this->db->where('inspection_id',$id)->where('status !=','closed')->count_all_results('findings');
        if ($open)show_error('Masih ada temuan terbuka.',422);
        $this->db->where('id',$id)->update('inspections',array('status'=>'completed','ended_on'=>date('Y-m-d')));
        $this->audit('inspections',$id,'complete',array('status'=>$row['status']),array('status'=>'completed'));
        $this->session->set_flashdata('success','Pemeriksaan selesai. Berita acara tetap berstatus draf sampai disahkan.');redirect('admin/pengawasan/'.$id);
    }
    public function cancel($id)
    {
        $this->post_only();$this->guard('pengawasan','nonaktifkan');$row=$this->inspection($id);
        if ($row['status']!=='planned' || $this->db->where('inspection_id',$id)->count_all_results('findings'))show_error('Hanya rencana tanpa temuan yang dapat dibatalkan.',409);
        $this->db->where('id',$id)->update('inspections',array('status'=>'cancelled'));
        $this->audit('inspections',$id,'cancel',$row,array('status'=>'cancelled'));
        $this->session->set_flashdata('success','Rencana dibatalkan; jejaknya tetap tersimpan.');redirect('admin/pengawasan/'.$id);
    }
}
