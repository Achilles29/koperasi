<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller
{
    public function index()
    {
        $this->guard('dashboard');
        $user=$this->current_user();
        if ($user['cooperative_id'] && !$this->can('import') && !$this->can('pengguna')) {
            redirect('admin/koperasi/'.$user['cooperative_id']);return;
        }
        $data=array('title'=>'Ringkasan kerja','eyebrow'=>'Pusat kendali','active_nav'=>'dashboard');
        $data['total']=(int)$this->db->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $data['local']=(int)$this->db->where('reference_only',0)->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $data['reference']=(int)$this->db->where('reference_only',1)->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $data['kdkmp']=(int)$this->db->from('cooperatives c')->where('c.archived_at IS NULL',null,false)->join('master_items m','m.id=c.type_item_id')->where('m.code','kdkmp')->where('c.archived_at IS NULL',null,false)->count_all_results();
        $data['village_mapped']=(int)$this->db->where('village_id IS NOT NULL',null,false)->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $data['recent']=$this->db->select('c.id,c.name,c.nik,c.authority_scope,c.reference_only,r.name AS district_name')->from('cooperatives c')->where('c.archived_at IS NULL',null,false)->join('regions r','r.id=c.district_id','left')->order_by('c.updated_at','DESC')->limit(8)->get()->result_array();
        $latest=$this->db->select_max('r.fiscal_year')->from('periodic_reports r')->join('cooperatives c','c.id=r.cooperative_id')->where('r.verification_status','verified')->where('c.reference_only',0)->where('c.archived_at IS NULL',null,false)->get()->row_array();
        $data['report_year']=$latest['fiscal_year']?:null;
        $data['verified_volume']=null;$data['verified_report_count']=0;
        if ($data['report_year']) {
            $summary=$this->db->select('SUM(r.business_volume) AS volume,COUNT(*) AS total',false)->from('periodic_reports r')->join('cooperatives c','c.id=r.cooperative_id')->where('r.verification_status','verified')->where('r.fiscal_year',$data['report_year'])->where('c.reference_only',0)->where('c.archived_at IS NULL',null,false)->get()->row_array();
            $data['verified_volume']=$summary['volume'];$data['verified_report_count']=(int)$summary['total'];
        }
        $this->render_admin('admin/dashboard',$data);
    }
}
