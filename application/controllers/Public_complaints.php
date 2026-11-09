<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Public_complaints extends MY_Controller
{
    private $statuses=array('new'=>'Diterima','in_review'=>'Ditelaah','follow_up'=>'Ditindaklanjuti','closed'=>'Selesai','cancelled'=>'Dibatalkan');
    public function index()
    {
        $this->load->view('public/complaint',array('error'=>'','statuses'=>$this->statuses));
    }
    public function save()
    {
        $this->post_only();
        if (trim((string)$this->input->post('website',true))!=='')show_error('Permintaan tidak dapat diproses.',422);
        $times=array_filter((array)$this->session->userdata('public_complaint_times'),function($t){return (int)$t>time()-900;});
        if (count($times)>=3)show_error('Batas tiga aduan per 15 menit tercapai. Coba kembali beberapa saat lagi.',429);
        $name=trim((string)$this->input->post('reporter_name',true));$contact=trim((string)$this->input->post('reporter_contact',true));$title=trim((string)$this->input->post('title',true));$description=trim((string)$this->input->post('description',true));
        if (mb_strlen($name)<3 || mb_strlen($name)>160 || mb_strlen($contact)<5 || mb_strlen($contact)>190 || mb_strlen($title)<5 || mb_strlen($title)>255 || mb_strlen($description)<20 || mb_strlen($description)>10000 || !$this->input->post('consent'))show_error('Lengkapi nama, kontak, perihal, uraian minimal 20 karakter, dan persetujuan pemrosesan.',422);
        $access=strtoupper(bin2hex(random_bytes(10)));$ticket='ADU-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
        $values=array('ticket_no'=>$ticket,'case_type'=>'complaint','title'=>$title,'description'=>$description,'reporter_name'=>$name,'reporter_contact'=>$contact,'public_access_hash'=>hash('sha256',$access),'status'=>'new');
        $this->db->trans_start();$this->db->insert('service_cases',$values);$id=$this->db->insert_id();
        $this->db->insert('case_events',array('case_id'=>$id,'actor_user_id'=>null,'event_type'=>'created','note'=>'Aduan diterima dan menunggu telaah petugas.','visibility'=>'reporter'));
        $this->db->trans_complete();$times[]=time();$this->session->set_userdata('public_complaint_times',array_values($times));
        $this->load->view('public/complaint_receipt',array('ticket'=>$ticket,'access'=>$access));
    }
    public function track()
    {
        $error='';$row=null;$events=array();
        if (strtoupper($this->input->method())==='POST'){
            $ticket=strtoupper(trim((string)$this->input->post('ticket_no',true)));$access=strtoupper(trim((string)$this->input->post('access_code',true)));
            if (preg_match('/^ADU-[0-9]{8}-[A-F0-9]{6}$/',$ticket) && preg_match('/^[A-F0-9]{20}$/',$access)){
                $candidate=$this->db->get_where('service_cases',array('ticket_no'=>$ticket,'case_type'=>'complaint'))->row_array();
                if ($candidate && $candidate['public_access_hash'] && hash_equals($candidate['public_access_hash'],hash('sha256',$access)))$row=$candidate;
            }
            if ($row)$events=$this->db->select('event_type,note,created_at')->where('case_id',$row['id'])->where('visibility','reporter')->order_by('created_at')->order_by('id')->get('case_events')->result_array();
            else $error='Nomor aduan atau kode akses tidak cocok.';
        }
        $this->load->view('public/complaint_track',array('row'=>$row,'events'=>$events,'error'=>$error,'statuses'=>$this->statuses));
    }
}
