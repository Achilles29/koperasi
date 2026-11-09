<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gis extends MY_Controller
{
    public function index()
    {
        $this->guard('gis');$p=$this->page_args();
        $mapping=trim((string)$this->input->get('mapping',true));
        $district=trim((string)$this->input->get('district',true));
        $type=trim((string)$this->input->get('type',true));
        $this->db->select('c.id,c.name,c.nik,c.address,c.latitude,c.longitude,c.reference_only,r.name AS district_name,t.name AS type_name')
            ->from('cooperatives c')->where('c.archived_at IS NULL',null,false)->join('regions r','r.id=c.district_id','left')->join('master_items t','t.id=c.type_item_id','left');
        if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('c.nik',$p['q'])->group_end();
        if ($mapping==='mapped')$this->db->where('c.latitude IS NOT NULL',null,false)->where('c.longitude IS NOT NULL',null,false);
        elseif ($mapping==='unmapped')$this->db->group_start()->where('c.latitude IS NULL',null,false)->or_where('c.longitude IS NULL',null,false)->group_end();
        if ($district && ctype_digit($district))$this->db->where('c.district_id',(int)$district);
        if ($type && ctype_digit($type))$this->db->where('c.type_item_id',(int)$type);
        $total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $base_sql=$this->db->get_compiled_select('',false);
        $rows=$this->db->order_by('c.name')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $mapped=$this->db->query('SELECT id,name,latitude,longitude FROM ('.$base_sql.') AS selected_points WHERE latitude IS NOT NULL AND longitude IS NOT NULL')->result_array();
        $this->db->select('s.id,c.name,s.latitude,s.longitude')->from('kdkmp_sites s')->where('s.archived_at IS NULL',null,false)->join('cooperatives c','c.id=s.cooperative_id')
            ->where('s.latitude IS NOT NULL',null,false)->where('s.longitude IS NOT NULL',null,false);
        if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('c.nik',$p['q'])->group_end();
        if ($district && ctype_digit($district))$this->db->where('c.district_id',(int)$district);
        if ($type && ctype_digit($type))$this->db->where('c.type_item_id',(int)$type);
        if ($mapping==='unmapped')$this->db->where('1=0',null,false);
        $kdkmp_points=$this->db->get()->result_array();
        $rembang=$this->db->get_where('regions',array('code'=>'33.17'))->row_array();
        $districts=$this->db->select('id,name')->where('parent_id',$rembang['id'])->order_by('code')->get('regions')->result_array();
        $types=$this->db->select('m.id,m.name')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('d.slug','jenis-koperasi')->where('m.active',1)->order_by('m.name')->get()->result_array();
        $all_mapped=(int)$this->db->where('latitude IS NOT NULL',null,false)->where('longitude IS NOT NULL',null,false)->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $all_total=(int)$this->db->where('archived_at IS NULL',null,false)->count_all_results('cooperatives');
        $this->render_admin('gis/index',array('title'=>'GIS koperasi','eyebrow'=>'Peta dan kualitas lokasi','active_nav'=>'gis','rows'=>$rows,'mapped'=>$mapped,'kdkmp_points'=>$kdkmp_points,'all_mapped'=>$all_mapped,'all_total'=>$all_total,'p'=>$p,'total'=>$total,'path'=>'admin/modul/gis','mapping'=>$mapping,'district'=>$district,'type'=>$type,'districts'=>$districts,'types'=>$types));
    }
}
