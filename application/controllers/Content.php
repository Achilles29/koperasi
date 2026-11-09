<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Content extends MY_Controller
{
    private $statuses=array('draft'=>'Draf','published'=>'Tayang','archived'=>'Arsip');
    private function categories()
    {
        return $this->db->select('m.id,m.code,m.name')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('d.slug','kategori-konten')->where('m.active',1)->order_by('m.name')->get()->result_array();
    }
    private function entry($id)
    {
        $row=$this->db->get_where('content_entries',array('id'=>(int)$id))->row_array();if (!$row)show_404();return $row;
    }
    public function index()
    {
        $this->guard('konten');$p=$this->page_args();$category=(int)$this->input->get('category',true);$categories=$this->categories();
        $this->db->select('e.id,e.title,e.slug,e.status,e.published_at,e.created_at,m.name AS category_name')->from('content_entries e')->join('master_items m','m.id=e.category_item_id');
        if ($p['q'])$this->db->group_start()->like('e.title',$p['q'])->or_like('e.summary',$p['q'])->group_end();
        if (isset($this->statuses[$p['status']]))$this->db->where('e.status',$p['status']);
        if ($category && in_array($category,array_column($categories,'id'))) $this->db->where('e.category_item_id',$category);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('e.created_at','DESC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('content/index',array('title'=>'Konten publik','eyebrow'=>'Berita · agenda · regulasi','active_nav'=>'konten','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/modul/konten','categories'=>$categories,'category'=>$category,'statuses'=>$this->statuses,'can_create'=>$this->can('konten','buat')));
    }
    public function form($id=0)
    {
        $this->guard('konten',$id?'ubah':'buat');$row=$id?$this->entry($id):null;
        $this->render_admin('content/form',array('title'=>$id?'Ubah konten':'Tulis konten','eyebrow'=>'Konten publik','active_nav'=>'konten','row'=>$row,'categories'=>$this->categories(),'statuses'=>$this->statuses));
    }
    public function save($id=0)
    {
        $this->post_only();$this->guard('konten',$id?'ubah':'buat');$old=$id?$this->entry($id):null;
        $title=trim((string)$this->input->post('title',true));$summary=trim((string)$this->input->post('summary',true));$body=trim((string)$this->input->post('body',true));
        $category=(int)$this->input->post('category_item_id',true);$valid=array_column($this->categories(),'id');$status=(string)$this->input->post('status',true);
        $url=trim((string)$this->input->post('external_url',true));$event_on=trim((string)$this->input->post('event_on',true));
        if (mb_strlen($title)<3 || mb_strlen($title)>220 || !in_array($category,$valid) || !isset($this->statuses[$status]))show_error('Periksa judul, kategori, dan status.',422);
        if ($url && (!filter_var($url,FILTER_VALIDATE_URL) || stripos($url,'https://')!==0 || mb_strlen($url)>1000))show_error('Tautan harus menggunakan HTTPS.',422);
        if ($event_on && (!($date=DateTime::createFromFormat('!Y-m-d',$event_on)) || $date->format('Y-m-d')!==$event_on))show_error('Tanggal kegiatan tidak valid.',422);
        if ($status==='published' && !$body && !$url)show_error('Konten tayang harus memuat isi atau tautan resmi.',422);
        $path=$old?$old['image_path']:null;
        if (isset($_FILES['image']) && $_FILES['image']['error']!==UPLOAD_ERR_NO_FILE){
            if ($_FILES['image']['error']!==UPLOAD_ERR_OK || $_FILES['image']['size']>5*1024*1024 || $_FILES['image']['size']<=0)show_error('Foto maksimal 5 MB.',422);
            $info=@getimagesize($_FILES['image']['tmp_name']);$types=array(IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_WEBP=>'webp');
            if (!$info || !isset($types[$info[2]]))show_error('Gunakan foto JPG, PNG, atau WebP.',422);
            $name=bin2hex(random_bytes(16)).'.'.$types[$info[2]];$path='assets/uploads/content/'.$name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'],FCPATH.$path))show_error('Foto gagal disimpan.',500);
        }
        $values=array('category_item_id'=>$category,'title'=>$title,'summary'=>$summary?:null,'body'=>$body?:null,'external_url'=>$url?:null,'event_on'=>$event_on?:null,'image_path'=>$path,'status'=>$status,'updated_by'=>$this->session->userdata('user_id'));
        if ($status==='published')$values['published_at']=$old && $old['published_at']?$old['published_at']:date('Y-m-d H:i:s');
        if ($id){$this->db->where('id',$id)->update('content_entries',$values);$this->audit('content_entries',$id,'update',array('status'=>$old['status']),array('status'=>$status,'title'=>$title));}
        else{$slug=trim(preg_replace('/[^a-z0-9]+/','-',strtolower(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$title))),'-');$values['slug']=substr($slug?:'konten',0,195).'-'.bin2hex(random_bytes(3));$values['created_by']=$this->session->userdata('user_id');$this->db->insert('content_entries',$values);$id=$this->db->insert_id();$this->audit('content_entries',$id,'create',null,array('title'=>$title,'status'=>$status));}
        $this->session->set_flashdata('success','Konten tersimpan.');redirect('admin/konten/'.$id.'/ubah');
    }
    public function public_index()
    {
        $p=$this->page_args();$categories=$this->categories();$category=(int)$this->input->get('category',true);
        $this->db->select('e.id,e.title,e.slug,e.summary,e.image_path,e.event_on,e.external_url,e.published_at,m.name AS category_name')->from('content_entries e')->join('master_items m','m.id=e.category_item_id')->where('e.status','published')->where('e.published_at <=',date('Y-m-d H:i:s'));
        if ($p['q'])$this->db->group_start()->like('e.title',$p['q'])->or_like('e.summary',$p['q'])->group_end();
        if ($category && in_array($category,array_column($categories,'id'))) $this->db->where('e.category_item_id',$category);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('e.published_at','DESC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->load->view('public/information',array('rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'informasi','categories'=>$categories,'category'=>$category));
    }
    public function public_detail($slug)
    {
        $row=$this->db->select('e.*,m.name AS category_name')->from('content_entries e')->join('master_items m','m.id=e.category_item_id')->where('e.slug',$slug)->where('e.status','published')->where('e.published_at <=',date('Y-m-d H:i:s'))->get()->row_array();
        if (!$row)show_404();$this->load->view('public/information_detail',array('row'=>$row));
    }
}
