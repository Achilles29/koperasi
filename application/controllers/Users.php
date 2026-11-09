<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
    private $actions=array('lihat'=>'Lihat','buat'=>'Tambah','ubah'=>'Ubah','nonaktifkan'=>'Arsipkan','verifikasi'=>'Verifikasi','setujui'=>'Setujui','terbitkan'=>'Terbitkan','ekspor'=>'Ekspor','kelola'=>'Kelola');

    public function index()
    {
        $this->guard('pengguna');
        $p=$this->page_args();$role=trim((string)$this->input->get('role',true));
        $this->db->select('u.id,u.username,u.full_name,u.email,u.active,u.last_login_at,c.name AS cooperative_name,GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ", ") AS role_names')
            ->from('users u')->join('cooperatives c','c.id=u.cooperative_id','left')->join('user_roles ur','ur.user_id=u.id','left')->join('roles r','r.id=ur.role_id','left');
        if ($p['q']) $this->db->group_start()->like('u.username',$p['q'])->or_like('u.full_name',$p['q'])->or_like('u.email',$p['q'])->group_end();
        if ($p['status']==='aktif') $this->db->where('u.active',1);
        if ($p['status']==='arsip') $this->db->where('u.active',0);
        if ($role && ctype_digit($role)) $this->db->where('ur.role_id',(int)$role);
        $this->db->group_by('u.id');
        $total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $rows=$this->db->order_by('u.full_name')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $roles=$this->db->order_by('name')->get('roles')->result_array();
        $this->render_admin('users/index',array('title'=>'Pengguna & akses','eyebrow'=>'Pengaturan sistem','active_nav'=>'pengguna','rows'=>$rows,'roles'=>$roles,'role'=>$role,'p'=>$p,'total'=>$total,'path'=>'admin/pengguna'));
    }

    public function form($id=null)
    {
        $this->guard('pengguna',$id?'ubah':'buat');
        $row=$id?$this->db->get_where('users',array('id'=>(int)$id))->row_array():null;
        if ($id && !$row) show_404();
        $selected=$id?$this->db->select('role_id')->get_where('user_roles',array('user_id'=>$id))->result_array():array();
        $role_ids=array_map(function($r){return (int)$r['role_id'];},$selected);
        $roles=$this->db->where('active',1)->order_by('name')->get('roles')->result_array();
        $cooperatives=$this->db->select('id,name,nik')->order_by('name')->get('cooperatives')->result_array();
        $this->render_admin('users/form',array('title'=>$id?'Ubah pengguna':'Tambah pengguna','eyebrow'=>'Pengaturan sistem','active_nav'=>'pengguna','row'=>$row,'roles'=>$roles,'role_ids'=>$role_ids,'cooperatives'=>$cooperatives));
    }

    public function save()
    {
        $this->post_only();$id=(int)$this->input->post('id',true);$this->guard('pengguna',$id?'ubah':'buat');
        $old=$id?$this->db->get_where('users',array('id'=>$id))->row_array():null;
        if ($id && !$old) show_404();
        $username=trim((string)$this->input->post('username',true));
        $name=trim((string)$this->input->post('full_name',true));
        $email=trim((string)$this->input->post('email',true));
        $password=(string)$this->input->post('password',false);
        $role_ids=array_unique(array_map('intval',(array)$this->input->post('roles',true)));
        $cooperative_id=(int)$this->input->post('cooperative_id',true);
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,80}$/',$username) || !$name || mb_strlen($name)>160 || ($email && !filter_var($email,FILTER_VALIDATE_EMAIL)) || (!$id && strlen($password)<8) || ($password && strlen($password)<8) || !$role_ids) {
            $this->session->set_flashdata('error','Periksa nama pengguna, nama lengkap, peran, email, dan kata sandi minimal 8 karakter.');redirect('admin/pengguna'.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        $this->db->where('username',$username);if ($id)$this->db->where('id !=',$id);
        if ($this->db->count_all_results('users')) { $this->session->set_flashdata('error','Nama pengguna sudah dipakai.');redirect('admin/pengguna'.($id?'/'.$id.'/ubah':'/tambah'));return; }
        if ($email) { $this->db->where('email',$email);if ($id)$this->db->where('id !=',$id);if ($this->db->count_all_results('users')) { $this->session->set_flashdata('error','Email sudah dipakai.');redirect('admin/pengguna'.($id?'/'.$id.'/ubah':'/tambah'));return; } }
        $valid=$this->db->select('id,code')->where_in('id',$role_ids)->where('active',1)->get('roles')->result_array();
        if (count($valid)!==count($role_ids)) show_error('Peran tidak valid.',422);
        $koperasi_role=false;foreach ($valid as $r)if ($r['code']==='koperasi')$koperasi_role=true;
        if ($koperasi_role && (!$cooperative_id || !$this->db->get_where('cooperatives',array('id'=>$cooperative_id))->row_array())) { $this->session->set_flashdata('error','Akun koperasi harus terhubung ke data koperasi.');redirect('admin/pengguna'.($id?'/'.$id.'/ubah':'/tambah'));return; }
        if ($id===(int)$this->session->userdata('user_id') && !$this->input->post('active')) { $this->session->set_flashdata('error','Akun yang sedang dipakai tidak dapat dinonaktifkan.');redirect('admin/pengguna/'.$id.'/ubah');return; }
        if ($id===(int)$this->session->userdata('user_id')) {
            $has_super=false;foreach ($valid as $r)if ($r['code']==='superadmin')$has_super=true;
            if (!$has_super) { $this->session->set_flashdata('error','Peran superadmin tidak dapat dilepas dari akun yang sedang dipakai.');redirect('admin/pengguna/'.$id.'/ubah');return; }
        }
        $values=array('username'=>$username,'full_name'=>$name,'email'=>$email?:null,'cooperative_id'=>$cooperative_id?:null,'active'=>$this->input->post('active')?1:0);
        if ($password)$values['password_hash']=password_hash($password,PASSWORD_DEFAULT);
        $this->db->trans_start();
        if ($id)$this->db->where('id',$id)->update('users',$values);
        else {$this->db->insert('users',$values);$id=$this->db->insert_id();}
        $this->db->delete('user_roles',array('user_id'=>$id));
        foreach ($role_ids as $role_id)$this->db->insert('user_roles',array('user_id'=>$id,'role_id'=>$role_id));
        $this->db->trans_complete();
        $audit_values=$values;unset($audit_values['password_hash']);$audit_values['roles']=$role_ids;$audit_values['password_changed']=$password!=='';
        $this->audit('users',$id,$old?'update':'create',$old?array_diff_key($old,array('password_hash'=>true)):null,$audit_values);
        $this->session->set_flashdata('success','Pengguna berhasil disimpan.');redirect('admin/pengguna');
    }

    public function roles()
    {
        $this->guard('pengguna');$p=$this->page_args();
        $this->db->select('r.id,r.code,r.name,r.active,COUNT(DISTINCT ur.user_id) AS user_count')->from('roles r')->join('user_roles ur','ur.role_id=r.id','left');
        if ($p['q'])$this->db->group_start()->like('r.code',$p['q'])->or_like('r.name',$p['q'])->group_end();
        if ($p['status']==='aktif')$this->db->where('r.active',1);
        if ($p['status']==='arsip')$this->db->where('r.active',0);
        $this->db->group_by('r.id');$total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $rows=$this->db->order_by('r.name')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('users/roles',array('title'=>'Peran & izin','eyebrow'=>'Pengaturan sistem','active_nav'=>'pengguna','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/peran'));
    }

    public function role_form($id=null)
    {
        $this->guard('pengguna',$id?'ubah':'buat');
        $row=$id?$this->db->get_where('roles',array('id'=>(int)$id))->row_array():null;
        if ($id && !$row)show_404();
        $modules=$this->db->where('active',1)->order_by('name')->get('modules')->result_array();
        $permissions=array();
        if ($id)foreach ($this->db->get_where('role_permissions',array('role_id'=>$id))->result_array() as $r)$permissions[$r['module_id'].'|'.$r['action_code']]=true;
        $this->render_admin('users/role_form',array('title'=>$id?'Ubah peran':'Tambah peran','eyebrow'=>'Matriks hak akses','active_nav'=>'pengguna','row'=>$row,'modules'=>$modules,'actions'=>$this->actions,'permissions'=>$permissions));
    }

    public function role_save()
    {
        $this->post_only();$id=(int)$this->input->post('id',true);$this->guard('pengguna',$id?'ubah':'buat');
        $old=$id?$this->db->get_where('roles',array('id'=>$id))->row_array():null;if ($id && !$old)show_404();
        $code=trim((string)$this->input->post('code',true));$name=trim((string)$this->input->post('name',true));
        if (!preg_match('/^[a-z0-9_-]{3,60}$/',$code) || !$name || mb_strlen($name)>100) { $this->session->set_flashdata('error','Kode peran minimal 3 karakter dan nama wajib diisi.');redirect('admin/peran'.($id?'/'.$id.'/ubah':'/tambah'));return; }
        $this->db->where('code',$code);if ($id)$this->db->where('id !=',$id);
        if ($this->db->count_all_results('roles')) { $this->session->set_flashdata('error','Kode peran sudah dipakai.');redirect('admin/peran'.($id?'/'.$id.'/ubah':'/tambah'));return; }
        $raw=(array)$this->input->post('permissions',true);
        $modules=$this->db->select('id,code')->where('active',1)->get('modules')->result_array();$module_ids=array();foreach ($modules as $m)$module_ids[(int)$m['id']]=$m['code'];
        $perms=array();foreach ($raw as $key) { $parts=explode('|',(string)$key);if (count($parts)!==2)continue;$mid=(int)$parts[0];$action=$parts[1];if (isset($module_ids[$mid],$this->actions[$action]))$perms[$mid.'|'.$action]=array('role_id'=>$id,'module_id'=>$mid,'action_code'=>$action); }
        if ($old && $old['code']==='superadmin' && ($code!=='superadmin' || !$this->input->post('active') || count($perms)<count($module_ids)*count($this->actions))) { $this->session->set_flashdata('error','Peran superadmin wajib aktif, berkode tetap, dan memiliki seluruh izin.');redirect('admin/peran/'.$id.'/ubah');return; }
        $this->db->trans_start();$values=array('code'=>$code,'name'=>$name,'active'=>$this->input->post('active')?1:0);
        if ($id)$this->db->where('id',$id)->update('roles',$values);else{$this->db->insert('roles',$values);$id=$this->db->insert_id();}
        $this->db->delete('role_permissions',array('role_id'=>$id));foreach ($perms as $perm){$perm['role_id']=$id;$this->db->insert('role_permissions',$perm);}
        $def=$this->db->get_where('master_definitions',array('slug'=>'jenis-akun'))->row_array();
        if ($def){
            if ($old && $old['code']!==$code)$this->db->where('definition_id',$def['id'])->where('code',$old['code'])->update('master_items',array('code'=>$code,'name'=>$name,'active'=>$values['active']));
            else $this->db->query('INSERT INTO master_items(definition_id,code,name,active) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),active=VALUES(active)',array($def['id'],$code,$name,$values['active']));
        }
        $this->db->trans_complete();
        $this->audit('roles',$id,$old?'update':'create',$old,array('role'=>$values,'permissions'=>array_keys($perms)));
        $this->session->set_flashdata('success','Peran dan matriks izin berhasil disimpan.');redirect('admin/peran');
    }

    public function modules()
    {
        $this->guard('pengguna');$p=$this->page_args();
        $this->db->from('modules m');
        if ($p['q'])$this->db->group_start()->like('m.code',$p['q'])->or_like('m.name',$p['q'])->group_end();
        if ($p['status']==='aktif')$this->db->where('m.active',1);
        if ($p['status']==='arsip')$this->db->where('m.active',0);
        $total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $rows=$this->db->order_by('m.name')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('users/modules',array('title'=>'Modul akses','eyebrow'=>'Pengaturan sistem','active_nav'=>'pengguna','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/modul-akses'));
    }

    public function module_form($id=null)
    {
        $this->guard('pengguna',$id?'ubah':'buat');
        $row=$id?$this->db->get_where('modules',array('id'=>(int)$id))->row_array():null;
        if ($id && !$row)show_404();
        $this->render_admin('users/module_form',array('title'=>$id?'Ubah modul akses':'Tambah modul akses','eyebrow'=>'Pengaturan sistem','active_nav'=>'pengguna','row'=>$row));
    }

    public function module_save()
    {
        $this->post_only();$id=(int)$this->input->post('id',true);$this->guard('pengguna',$id?'ubah':'buat');
        $old=$id?$this->db->get_where('modules',array('id'=>$id))->row_array():null;if ($id && !$old)show_404();
        $code=trim((string)$this->input->post('code',true));$name=trim((string)$this->input->post('name',true));
        if (!preg_match('/^[a-z0-9_-]{3,80}$/',$code) || !$name || mb_strlen($name)>120) { $this->session->set_flashdata('error','Kode modul minimal 3 karakter dan nama wajib diisi.');redirect('admin/modul-akses'.($id?'/'.$id.'/ubah':'/tambah'));return; }
        if ($old && in_array($old['code'],array('dashboard','pengguna'),true) && (!$this->input->post('active') || $code!==$old['code'])) { $this->session->set_flashdata('error','Modul inti dashboard dan pengguna wajib aktif dengan kode tetap.');redirect('admin/modul-akses/'.$id.'/ubah');return; }
        $this->db->where('code',$code);if ($id)$this->db->where('id !=',$id);
        if ($this->db->count_all_results('modules')) { $this->session->set_flashdata('error','Kode modul sudah dipakai.');redirect('admin/modul-akses'.($id?'/'.$id.'/ubah':'/tambah'));return; }
        $values=array('code'=>$code,'name'=>$name,'active'=>$this->input->post('active')?1:0);
        $this->db->trans_start();
        if ($id)$this->db->where('id',$id)->update('modules',$values);else{
            $this->db->insert('modules',$values);$id=$this->db->insert_id();
            $super=$this->db->get_where('roles',array('code'=>'superadmin'))->row_array();
            if ($super)foreach (array_keys($this->actions) as $action)$this->db->insert('role_permissions',array('role_id'=>$super['id'],'module_id'=>$id,'action_code'=>$action));
        }
        $def=$this->db->get_where('master_definitions',array('slug'=>'hak-akses'))->row_array();
        if ($def) {
            if ($old && $old['code']!==$code)$this->db->where('definition_id',$def['id'])->where('code',$old['code'])->update('master_items',array('code'=>$code,'name'=>$name,'active'=>$values['active']));
            else $this->db->query('INSERT INTO master_items(definition_id,code,name,active) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),active=VALUES(active)',array($def['id'],$code,$name,$values['active']));
        }
        $this->db->trans_complete();$this->audit('modules',$id,$old?'update':'create',$old,$values);
        $this->session->set_flashdata('success','Modul akses berhasil disimpan.');redirect('admin/modul-akses');
    }
}
