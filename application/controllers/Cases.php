<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cases extends MY_Controller
{
    private $kinds=array('aduan'=>array('complaint','Aduan & tindak lanjut','ADU'),'layanan'=>array('license_assistance','Pendampingan perizinan','IZN'),'pembinaan'=>array('coaching','Pembinaan & konsultasi','BIN'));
    private $statuses=array('new'=>'Baru','in_review'=>'Ditelaah','follow_up'=>'Tindak lanjut','closed'=>'Selesai','cancelled'=>'Dibatalkan');
    private function kind($slug){if (!isset($this->kinds[$slug]))show_404();return $this->kinds[$slug];}
    private function own_only(){return !$this->can('import') && !$this->can('pengguna');}
    private function case_row($slug,$id)
    {
        $kind=$this->kind($slug);$row=$this->db->select('s.*,c.name AS cooperative_name,u.full_name AS assignee_name')->from('service_cases s')->join('cooperatives c','c.id=s.cooperative_id','left')->join('users u','u.id=s.assigned_user_id','left')->where('s.id',(int)$id)->where('s.case_type',$kind[0])->get()->row_array();
        if (!$row)show_404();
        if ($this->own_only() && (int)$row['cooperative_id']!==(int)$this->current_user()['cooperative_id'])show_error('Tiket ini bukan milik koperasi Anda.',403);
        return $row;
    }
    public function index($slug)
    {
        $kind=$this->kind($slug);$this->guard($slug);$p=$this->page_args();$own=$this->own_only();$user=$this->current_user();
        $this->db->select('s.id,s.ticket_no,s.title,s.status,s.created_at,s.due_at,c.name AS cooperative_name,u.full_name AS assignee_name')->from('service_cases s')->join('cooperatives c','c.id=s.cooperative_id','left')->join('users u','u.id=s.assigned_user_id','left')->where('s.case_type',$kind[0]);
        if ($own)$this->db->where('s.cooperative_id',(int)$user['cooperative_id']);
        if ($p['q'])$this->db->group_start()->like('s.ticket_no',$p['q'])->or_like('s.title',$p['q'])->or_like('c.name',$p['q'])->group_end();
        if (isset($this->statuses[$p['status']]))$this->db->where('s.status',$p['status']);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('s.created_at','DESC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('cases/index',array('title'=>$kind[1],'eyebrow'=>'Tiket layanan / '.$slug,'active_nav'=>$slug,'rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/modul/'.$slug,'slug'=>$slug,'statuses'=>$this->statuses,'can_create'=>$this->can($slug,'buat')));
    }
    public function form($slug,$id=null)
    {
        $this->kind($slug);$this->guard($slug,$id?'ubah':'buat');$own=$this->own_only();$user=$this->current_user();
        $row=$id?$this->case_row($slug,$id):null;
        if ($row && in_array($row['status'],array('closed','cancelled'),true))show_error('Tiket selesai atau batal tidak dapat diubah.',409);
        $cooperatives=$own?array():$this->db->select('id,name,nik')->order_by('name')->get('cooperatives')->result_array();
        $staff=$own?array():$this->db->select('id,full_name')->where('active',1)->order_by('full_name')->get('users')->result_array();
        $this->render_admin('cases/form',array('title'=>($id?'Ubah':'Buat').' tiket '.$slug,'eyebrow'=>'Tiket layanan','active_nav'=>$slug,'slug'=>$slug,'own'=>$own,'user'=>$user,'row'=>$row,'cooperatives'=>$cooperatives,'staff'=>$staff));
    }
    public function save($slug)
    {
        $kind=$this->kind($slug);$this->post_only();$id=(int)$this->input->post('id',true);$this->guard($slug,$id?'ubah':'buat');$own=$this->own_only();$user=$this->current_user();
        $old=$id?$this->case_row($slug,$id):null;
        if ($old && in_array($old['status'],array('closed','cancelled'),true))show_error('Tiket selesai atau batal tidak dapat diubah.',409);
        $title=trim((string)$this->input->post('title',true));$description=trim((string)$this->input->post('description',true));
        $cooperative_id=$own?(int)$user['cooperative_id']:(int)$this->input->post('cooperative_id',true);
        if (!$title || mb_strlen($title)>255 || mb_strlen($description)<10 || ($cooperative_id && !$this->db->get_where('cooperatives',array('id'=>$cooperative_id))->row_array()))show_error('Periksa judul, uraian minimal 10 karakter, dan koperasi.',422);
        if ($own && !$cooperative_id)show_error('Akun belum terhubung ke koperasi.',403);
        $assignee=$own?null:((int)$this->input->post('assigned_user_id',true)?:null);
        if ($assignee && !$this->db->get_where('users',array('id'=>$assignee,'active'=>1))->row_array())show_error('Petugas tidak valid.',422);
        $ticket=$old?$old['ticket_no']:$kind[2].'-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
        $values=array('cooperative_id'=>$cooperative_id?:null,'title'=>$title,'description'=>$description,'reporter_name'=>$own?$user['full_name']:trim((string)$this->input->post('reporter_name',true)),'reporter_contact'=>trim((string)$this->input->post('reporter_contact',true)),'assigned_user_id'=>$assignee);
        $this->db->trans_start();
        if ($old) $this->db->where('id',$id)->update('service_cases',$values);
        else {$values['ticket_no']=$ticket;$values['case_type']=$kind[0];$values['status']='new';$this->db->insert('service_cases',$values);$id=$this->db->insert_id();}
        $this->db->insert('case_events',array('case_id'=>$id,'actor_user_id'=>$user['id'],'event_type'=>$old?'updated':'created','note'=>$old?'Identitas dan penugasan tiket diperbarui.':'Tiket dibuat.','visibility'=>'internal'));
        $this->db->trans_complete();$this->audit('service_cases',$id,$old?'update':'create',$old,$values);
        redirect('admin/kasus/'.$slug.'/'.$id);
    }
    public function detail($slug,$id)
    {
        $this->guard($slug);$row=$this->case_row($slug,$id);
        $events=$this->db->select('e.*,u.full_name AS actor_name')->from('case_events e')->join('users u','u.id=e.actor_user_id','left')->where('e.case_id',$id)->order_by('e.created_at','DESC')->order_by('e.id','DESC')->get()->result_array();
        if ($this->own_only())$events=array_values(array_filter($events,function($e){return $e['visibility']==='reporter';}));
        $this->render_admin('cases/detail',array('title'=>$row['title'],'eyebrow'=>'Tiket '.$row['ticket_no'],'active_nav'=>$slug,'slug'=>$slug,'row'=>$row,'events'=>$events,'statuses'=>$this->statuses,'can_update'=>$this->can($slug,'ubah'),'can_edit'=>$this->can($slug,'ubah'),'can_cancel'=>$this->can($slug,'nonaktifkan'),'own'=>$this->own_only()));
    }
    public function event($slug,$id)
    {
        $this->post_only();$this->guard($slug,'ubah');$row=$this->case_row($slug,$id);$own=$this->own_only();
        if ($row['status']==='cancelled')show_error('Tiket batal tidak dapat ditindaklanjuti.',409);
        $note=trim((string)$this->input->post('note',true));$status=trim((string)$this->input->post('status',true));$visibility=trim((string)$this->input->post('visibility',true));
        if (mb_strlen($note)<5)show_error('Catatan tindak lanjut minimal 5 karakter.',422);
        if ($own){$status=$row['status'];$visibility='reporter';}
        if (!isset($this->statuses[$status]) || $status==='cancelled')show_error('Status tidak valid.',422);
        if (!in_array($visibility,array('internal','reporter'),true))$visibility='internal';
        $this->db->trans_start();$this->db->insert('case_events',array('case_id'=>$id,'actor_user_id'=>$this->current_user()['id'],'event_type'=>'follow_up','note'=>$note,'visibility'=>$visibility));
        if ($status!==$row['status'])$this->db->where('id',$id)->update('service_cases',array('status'=>$status,'closed_at'=>$status==='closed'?date('Y-m-d H:i:s'):null));
        $this->db->trans_complete();$this->audit('service_cases',$id,'follow_up',array('status'=>$row['status']),array('status'=>$status,'note'=>$note));
        $this->session->set_flashdata('success','Tindak lanjut tersimpan.');redirect('admin/kasus/'.$slug.'/'.$id);
    }
    public function minutes($slug,$id)
    {
        $this->guard($slug);$row=$this->case_row($slug,$id);
        if ($this->own_only())show_error('Berita acara hanya untuk petugas.',403);
        $events=$this->db->select('e.*,u.full_name AS actor_name')->from('case_events e')->join('users u','u.id=e.actor_user_id','left')->where('e.case_id',$id)->order_by('e.created_at')->get()->result_array();
        $this->load->view('cases/minutes',array('row'=>$row,'events'=>$events,'slug'=>$slug));
    }
    public function cancel($slug,$id)
    {
        $this->post_only();$this->guard($slug,'nonaktifkan');$row=$this->case_row($slug,$id);
        if ($row['status']!=='new')show_error('Hanya tiket baru yang belum diproses dapat dibatalkan.',409);
        $this->db->trans_start();$this->db->where('id',$id)->update('service_cases',array('status'=>'cancelled','closed_at'=>date('Y-m-d H:i:s')));
        $this->db->insert('case_events',array('case_id'=>$id,'actor_user_id'=>$this->current_user()['id'],'event_type'=>'cancelled','note'=>'Tiket dibatalkan oleh petugas sebelum diproses.','visibility'=>'reporter'));
        $this->db->trans_complete();$this->audit('service_cases',$id,'cancel',$row,array('status'=>'cancelled'));
        $this->session->set_flashdata('success','Tiket dibatalkan dan riwayatnya tetap tersimpan.');redirect('admin/kasus/'.$slug.'/'.$id);
    }
}
