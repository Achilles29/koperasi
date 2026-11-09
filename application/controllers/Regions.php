<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Regions extends MY_Controller
{
    private $levels=array('province'=>'Provinsi','regency'=>'Kabupaten/Kota','district'=>'Kecamatan','village'=>'Desa/Kelurahan');

    private function check_level($level)
    {
        if (!isset($this->levels[$level])) show_404();
    }

    public function children($parent_id)
    {
        if (!$this->current_user()) { http_response_code(401);exit; }
        if (!$this->can('wilayah') && !$this->can('koperasi')) { http_response_code(403);exit; }
        $parent=$this->db->get_where('regions',array('id'=>(int)$parent_id,'active'=>1))->row_array();
        if (!$parent || $parent['level']==='village')show_404();
        $rows=$this->db->select('id,code,name,level')->from('regions')->where('parent_id',$parent['id'])->where('active',1)->order_by('code')->get()->result_array();
        header('Content-Type: application/json; charset=utf-8');header('Cache-Control: private, max-age=300');echo json_encode($rows,JSON_UNESCAPED_UNICODE);
    }

    private function ancestry($region)
    {
        $ids=array('province'=>null,'regency'=>null,'district'=>null);
        while ($region) {
            if (array_key_exists($region['level'],$ids))$ids[$region['level']]=$region['id'];
            $region=$region['parent_id']?$this->db->get_where('regions',array('id'=>$region['parent_id']))->row_array():null;
        }
        return $ids;
    }

    public function index()
    {
        $this->guard('wilayah');
        $rows=$this->db->select('level,COUNT(*) AS total',false)->from('regions')->group_by('level')->get()->result_array();
        $counts=array();foreach ($rows as $row)$counts[$row['level']]=$row['total'];
        $this->render_admin('regions/index',array('title'=>'Master wilayah','eyebrow'=>'Kode Kemendagri','active_nav'=>'wilayah','levels'=>$this->levels,'counts'=>$counts));
    }

    public function browse($level)
    {
        $this->check_level($level);$this->guard('wilayah');$p=$this->page_args();
        $parent_id=(int)$this->input->get('parent_id',true);
        $selected_parent=$parent_id?$this->db->get_where('regions',array('id'=>$parent_id))->row_array():null;
        if ($parent_id && ($level==='province' || !$selected_parent || $selected_parent['level']!==array('regency'=>'province','district'=>'regency','village'=>'district')[$level]))$selected_parent=null;
        $this->db->select('r.*,p.code AS parent_code,p.name AS parent_name')->from('regions r')->join('regions p','p.id=r.parent_id','left')->where('r.level',$level);
        if ($p['q'])$this->db->group_start()->like('r.code',$p['q'])->or_like('r.name',$p['q'])->group_end();
        if ($p['status']==='aktif')$this->db->where('r.active',1);
        elseif ($p['status']==='arsip')$this->db->where('r.active',0);
        if ($selected_parent)$this->db->where('r.parent_id',$selected_parent['id']);
        $total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $rows=$this->db->order_by('r.code','ASC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('regions/browse',array('title'=>$this->levels[$level],'eyebrow'=>'Master wilayah / Kemendagri','active_nav'=>'wilayah','level'=>$level,'rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/wilayah/'.$level,'parent_id'=>$selected_parent?$selected_parent['id']:null,'selected_ids'=>$this->ancestry($selected_parent),'provinces'=>$this->db->select('id,code,name')->from('regions')->where('level','province')->where('active',1)->order_by('code')->get()->result_array(),'can_edit'=>$this->can('wilayah','ubah'),'can_create'=>$this->can('wilayah','buat'),'can_archive'=>$this->can('wilayah','nonaktifkan')));
    }

    public function form($level,$id=null)
    {
        $this->check_level($level);$this->guard('wilayah',$id?'ubah':'buat');
        $region=$id?$this->db->get_where('regions',array('id'=>$id,'level'=>$level))->row_array():null;
        if ($id && !$region)show_404();
        $parent=$region && $region['parent_id']?$this->db->get_where('regions',array('id'=>$region['parent_id']))->row_array():null;
        $this->render_admin('regions/form',array('title'=>($id?'Ubah ':'Tambah ').$this->levels[$level],'eyebrow'=>'Master wilayah / Kemendagri','active_nav'=>'wilayah','level'=>$level,'region'=>$region,'parent'=>$parent,'selected_ids'=>$this->ancestry($parent),'provinces'=>$this->db->select('id,code,name')->from('regions')->where('level','province')->where('active',1)->order_by('code')->get()->result_array()));
    }

    public function save($level)
    {
        $this->check_level($level);$this->post_only();$id=(int)$this->input->post('id',true);$this->guard('wilayah',$id?'ubah':'buat');
        if (!$id && !$this->can('wilayah','buat'))show_error('Tidak memiliki izin tambah.',403);
        $old=$id?$this->db->get_where('regions',array('id'=>$id,'level'=>$level))->row_array():null;
        if ($id && !$old)show_404();
        $code=trim((string)$this->input->post('code',true));$name=trim((string)$this->input->post('name',true));
        $parent_id_post=(int)$this->input->post('parent_id',true);$selected_parent=$parent_id_post?$this->db->get_where('regions',array('id'=>$parent_id_post,'active'=>1))->row_array():null;$parent_code=$selected_parent?$selected_parent['code']:'';$source_ref=trim((string)$this->input->post('source_ref',true));
        $valid_patterns=array('province'=>'/^\d{2}$/','regency'=>'^','district'=>'^','village'=>'^');
        $pattern=($level==='province')?'/^\d{2}$/':(($level==='regency')?'/^\d{2}\.\d{2}$/':(($level==='district')?'/^\d{2}\.\d{2}\.\d{2}$/':'/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/'));
        if (!preg_match($pattern,$code) || !$name || !$source_ref) {
            $this->session->set_flashdata('error','Kode, nama dan dasar keputusan/sumber wajib diisi dengan format wilayah yang benar.');redirect('admin/wilayah/'.$level.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        $parent_id=null;
        if ($level!=='province') {
            $expected_parent=substr($code,0,strrpos($code,'.'));
            if ($parent_code!==$expected_parent) {
                $this->session->set_flashdata('error','Kode induk harus sesuai awalan kode wilayah.');redirect('admin/wilayah/'.$level.($id?'/'.$id.'/ubah':'/tambah'));return;
            }
            $parent=$this->db->get_where('regions',array('code'=>$parent_code,'active'=>1))->row_array();
            $expected_level=array('regency'=>'province','district'=>'regency','village'=>'district')[$level];
            if (!$parent || $parent['level']!==$expected_level) {
                $this->session->set_flashdata('error','Wilayah induk tidak ditemukan atau tidak aktif.');redirect('admin/wilayah/'.$level.($id?'/'.$id.'/ubah':'/tambah'));return;
            }
            $parent_id=$parent['id'];
        }
        $this->db->where('code',$code);if ($id)$this->db->where('id !=',$id);
        if ($this->db->count_all_results('regions')) {
            $this->session->set_flashdata('error','Kode wilayah sudah digunakan.');redirect('admin/wilayah/'.$level.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        $source=$this->db->get_where('source_versions',array('kind'=>'regions','reference_no'=>$source_ref))->row_array();
        if (!$source) {
            $this->db->insert('source_versions',array('kind'=>'regions','title'=>'Pemutakhiran wilayah '.$source_ref,'reference_no'=>$source_ref));$source_id=$this->db->insert_id();
        } else $source_id=$source['id'];
        $values=array('code'=>$code,'name'=>$name,'level'=>$level,'parent_id'=>$parent_id,'source_version_id'=>$source_id,'village_type'=>$level==='village'?($this->input->post('village_type',true)==='kelurahan'?'kelurahan':'desa'):null,'active'=>$this->input->post('active')?1:0);
        if ($id)$this->db->where('id',$id)->update('regions',$values);
        else {$this->db->insert('regions',$values);$id=$this->db->insert_id();}
        $this->audit('regions',$id,$old?'update':'create',$old,$values);
        $this->session->set_flashdata('success','Wilayah berhasil disimpan.');redirect('admin/wilayah/'.$level);
    }

    public function toggle($level,$id)
    {
        $this->check_level($level);$this->post_only();$this->guard('wilayah','nonaktifkan');
        $old=$this->db->get_where('regions',array('id'=>$id,'level'=>$level))->row_array();if (!$old)show_404();
        $new=1-(int)$old['active'];
        if (!$new && $this->db->where('parent_id',$id)->where('active',1)->count_all_results('regions')) {
            $this->session->set_flashdata('error','Masih ada wilayah anak aktif; perbarui hierarki lebih dulu.');redirect('admin/wilayah/'.$level);return;
        }
        $this->db->where('id',$id)->update('regions',array('active'=>$new));$this->audit('regions',$id,'status',$old,array('active'=>$new));
        $this->session->set_flashdata('success',$new?'Wilayah diaktifkan.':'Wilayah diarsipkan.');redirect('admin/wilayah/'.$level);
    }
}
