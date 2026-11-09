<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Masters extends MY_Controller
{
    private function definition($slug)
    {
        $row=$this->db->get_where('master_definitions',array('slug'=>$slug,'active'=>1))->row_array();
        if (!$row) show_404();
        return $row;
    }

    public function index()
    {
        $this->guard('master');
        $p=$this->page_args();
        $can_access=$this->can('pengguna');
        $this->db->where('active',1);
        if (!$can_access)$this->db->where_not_in('slug',array('jenis-akun','hak-akses'));
        $catalog_count=(int)$this->db->count_all_results('master_definitions');
        $this->db->from('master_definitions d')->where('d.active',1);
        if (!$can_access)$this->db->where_not_in('d.slug',array('jenis-akun','hak-akses'));
        if ($p['q']) $this->db->group_start()->like('d.title',$p['q'])->or_like('d.category',$p['q'])->group_end();
        $total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $rows=$this->db->order_by('d.category','ASC')->order_by('d.sort_order','ASC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('masters/index',array('title'=>'Katalog master','eyebrow'=>'Pengaturan data','active_nav'=>'master','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/master','catalog_count'=>$catalog_count));
    }

    public function items($slug)
    {
        $this->guard('master');
        if (($slug==='jenis-akun' || $slug==='hak-akses') && !$this->can('pengguna'))show_error('Master akses hanya untuk superadmin.',403);
        if ($slug==='jenis-akun') { redirect('admin/peran');return; }
        if ($slug==='hak-akses') { redirect('admin/modul-akses');return; }
        $def=$this->definition($slug);$p=$this->page_args();
        $this->db->from('master_items m')->where('m.definition_id',$def['id']);
        if ($p['q']) $this->db->group_start()->like('m.code',$p['q'])->or_like('m.name',$p['q'])->group_end();
        if ($p['status']==='aktif') $this->db->where('m.active',1);
        elseif ($p['status']==='arsip') $this->db->where('m.active',0);
        $total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $rows=$this->db->order_by('m.active','DESC')->order_by('m.name','ASC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('masters/items',array('title'=>$def['title'],'eyebrow'=>'Master data / '.$def['category'],'active_nav'=>'master','def'=>$def,'rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/master/'.$slug,'can_edit'=>$this->can('master','ubah'),'can_create'=>$this->can('master','buat'),'can_archive'=>$this->can('master','nonaktifkan')));
    }

    public function form($slug,$id=null)
    {
        $this->guard('master',$id?'ubah':'buat');
        if ($slug==='jenis-akun' || $slug==='hak-akses') { redirect($slug==='jenis-akun'?'admin/peran':'admin/modul-akses');return; }
        $def=$this->definition($slug);
        $item=$id?$this->db->get_where('master_items',array('id'=>$id,'definition_id'=>$def['id']))->row_array():null;
        if ($id && !$item) show_404();
        $this->render_admin('masters/form',array('title'=>($id?'Ubah ':'Tambah ').$def['title'],'eyebrow'=>'Master data / '.$def['category'],'active_nav'=>'master','def'=>$def,'item'=>$item));
    }

    public function save($slug)
    {
        $this->post_only();$this->guard('master',(int)$this->input->post('id',true)?'ubah':'buat');
        if ($slug==='jenis-akun' || $slug==='hak-akses') show_404();
        $def=$this->definition($slug);
        $id=(int)$this->input->post('id',true);
        if (!$id && !$this->can('master','buat')) show_error('Tidak memiliki izin tambah.',403);
        $old=$id?$this->db->get_where('master_items',array('id'=>$id,'definition_id'=>$def['id']))->row_array():null;
        if ($id && !$old) show_404();
        $code=trim((string)$this->input->post('code',true));
        $name=trim((string)$this->input->post('name',true));
        $description=trim((string)$this->input->post('description',true));
        $source_note=trim((string)$this->input->post('source_note',true));
        if (!$code || !$name || strlen($code)>100 || mb_strlen($name)>220 || !preg_match('/^[a-zA-Z0-9_.:\/-]+$/',$code)) {
            $this->session->set_flashdata('error','Kode dan nama wajib diisi; kode hanya boleh huruf, angka, titik, garis, garis bawah atau titik dua.');
            redirect('admin/master/'.$slug.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        if ($def['source_policy']==='official' && !$source_note && (!$old || $old['code']!==$code || $old['name']!==$name)) {
            $this->session->set_flashdata('error','Master resmi memerlukan catatan dasar atau sumber perubahan.');
            redirect('admin/master/'.$slug.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        $this->db->where('definition_id',$def['id'])->where('code',$code);
        if ($id) $this->db->where('id !=',$id);
        if ($this->db->count_all_results('master_items')) {
            $this->session->set_flashdata('error','Kode sudah dipakai pada master ini.');
            redirect('admin/master/'.$slug.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        $metadata=$old && $old['metadata_json']?json_decode($old['metadata_json'],true):array();
        if (!is_array($metadata)) $metadata=array();
        if ($source_note) $metadata['source_note']=$source_note;
        $values=array('code'=>$code,'name'=>$name,'description'=>$description,'metadata_json'=>json_encode($metadata,JSON_UNESCAPED_UNICODE),'effective_from'=>$this->input->post('effective_from',true)?:null,'effective_to'=>$this->input->post('effective_to',true)?:null,'active'=>$this->input->post('active')?1:0);
        if ($id) $this->db->where('id',$id)->update('master_items',$values);
        else { $values['definition_id']=$def['id'];$this->db->insert('master_items',$values);$id=$this->db->insert_id(); }
        $this->audit('master_items',$id,$old?'update':'create',$old,$values);
        $this->session->set_flashdata('success','Master berhasil disimpan.');
        redirect('admin/master/'.$slug);
    }

    public function toggle($slug,$id)
    {
        $this->post_only();$this->guard('master','nonaktifkan');
        if ($slug==='jenis-akun' || $slug==='hak-akses') show_404();
        $def=$this->definition($slug);$old=$this->db->get_where('master_items',array('id'=>$id,'definition_id'=>$def['id']))->row_array();
        if (!$old) show_404();
        $new=1-(int)$old['active'];
        $this->db->where('id',$id)->update('master_items',array('active'=>$new));
        $this->audit('master_items',$id,'status',$old,array('active'=>$new));
        $this->session->set_flashdata('success',$new?'Master diaktifkan.':'Master diarsipkan; data lama tetap tersimpan.');
        redirect('admin/master/'.$slug);
    }
}
