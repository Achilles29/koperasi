<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller
{
    private $fields=array('site_name','government_name','opd_name','division_name','hero_title','hero_description','about_title','about_description','address','phone','email','website_url','instagram_url','facebook_url','youtube_url');

    public function index()
    {
        $this->guard('profil_dinas');
        $this->render_admin('profile/form',array('title'=>'Profil dinas','eyebrow'=>'Identitas dan informasi publik','active_nav'=>'profil_dinas','profile'=>site_profile(),'can_edit'=>$this->can('profil_dinas','ubah')));
    }

    private function image_upload($field,$kind)
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error']===UPLOAD_ERR_NO_FILE)return null;
        $file=$_FILES[$field];
        if ($file['error']!==UPLOAD_ERR_OK || $file['size']<1 || $file['size']>2*1024*1024)show_error('Berkas gambar gagal diunggah atau melebihi 2 MB.',422);
        $info=@getimagesize($file['tmp_name']);
        $types=$kind==='logo'?array('image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp'):array('image/png'=>'png');
        if (!$info || !isset($types[$info['mime']]) || $info[0]<32 || $info[1]<32 || $info[0]>4000 || $info[1]>4000)show_error('Logo harus JPG/PNG/WebP dan favicon PNG dengan ukuran 32–4000 piksel.',422);
        $dir=FCPATH.'assets/uploads/branding/';
        if (!is_dir($dir) && !mkdir($dir,0755,true))show_error('Penyimpanan gambar belum siap.',500);
        $name=$kind.'-'.bin2hex(random_bytes(12)).'.'.$types[$info['mime']];
        if (!move_uploaded_file($file['tmp_name'],$dir.$name))show_error('Gagal menyimpan gambar.',500);
        chmod($dir.$name,0644);
        return 'assets/uploads/branding/'.$name;
    }

    public function save()
    {
        $this->post_only();$this->guard('profil_dinas','ubah');
        $old=site_profile();$values=array();
        foreach ($this->fields as $field)$values[$field]=trim((string)$this->input->post($field,true));
        foreach (array('site_name','government_name','opd_name','division_name','hero_title','hero_description','about_title','about_description') as $field)if ($values[$field]==='')show_error('Identitas dan deskripsi utama wajib diisi.',422);
        foreach (array('site_name'=>120,'government_name'=>190,'opd_name'=>190,'division_name'=>160,'hero_title'=>255,'about_title'=>255,'phone'=>80,'email'=>190,'website_url'=>255,'instagram_url'=>255,'facebook_url'=>255,'youtube_url'=>255) as $field=>$max)if (mb_strlen($values[$field])>$max)show_error('Teks identitas atau kontak terlalu panjang.',422);
        if (mb_strlen($values['hero_description'])>10000 || mb_strlen($values['about_description'])>10000 || mb_strlen($values['address'])>10000)show_error('Deskripsi terlalu panjang.',422);
        foreach (array('website_url','instagram_url','facebook_url','youtube_url') as $field)if ($values[$field] && (!filter_var($values[$field],FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($values[$field],PHP_URL_SCHEME)),array('http','https'),true)))show_error('Tautan harus berupa alamat HTTP atau HTTPS yang valid.',422);
        if ($values['email'] && !filter_var($values['email'],FILTER_VALIDATE_EMAIL))show_error('Alamat email tidak valid.',422);
        $logo=$this->image_upload('logo','logo');$favicon=$this->image_upload('favicon','favicon');
        if ($logo)$values['logo_path']=$logo;
        if ($favicon)$values['favicon_path']=$favicon;
        $values['updated_by']=(int)$this->session->userdata('user_id');$values['updated_at']=date('Y-m-d H:i:s');
        $this->db->where('id',1)->update('organization_profile',$values);
        $this->audit('organization_profile',1,'update',$old,$values);
        $this->session->set_flashdata('success','Profil dinas berhasil diperbarui.');redirect('admin/profil-dinas');
    }
}
