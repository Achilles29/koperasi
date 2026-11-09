<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller
{
    public function index()
    {
        $data['total']=(int)$this->db->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $data['local']=(int)$this->db->where('reference_only',0)->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $data['active_local']=(int)$this->db->where('reference_only',0)->where('ods_status','Aktif')->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $data['kdkmp']=(int)$this->db->from('cooperatives c')->where('c.archived_at IS NULL',null,false)->join('master_items m','m.id=c.type_item_id')->where('m.code','kdkmp')->where('c.archived_at IS NULL',null,false)->count_all_results();
        $data['kdkmp_verified']=(int)$this->db->where('physical_progress >=',100)->where('verification_status','verified')->where('archived_at IS NULL',null,false)->count_all_results('kdkmp_sites');
        $data['latest_content']=$this->db->select('e.title,e.slug,e.summary,e.image_path,e.published_at,m.name AS category_name')->from('content_entries e')->join('master_items m','m.id=e.category_item_id')->where('e.status','published')->where('e.published_at <=',date('Y-m-d H:i:s'))->order_by('e.published_at','DESC')->limit(3)->get()->result_array();
        $this->load->view('public/home',$data);
    }
}
