<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected function current_user()
    {
        $id = (int) $this->session->userdata('user_id');
        if (!$id) return null;
        return $this->db->get_where('users', array('id'=>$id,'active'=>1))->row_array();
    }

    protected function can($module, $action = 'lihat')
    {
        $user = $this->current_user();
        if (!$user) return false;
        return $this->db->select('1', false)
            ->from('role_permissions rp')
            ->join('user_roles ur','ur.role_id=rp.role_id')
            ->join('roles r','r.id=ur.role_id')
            ->join('modules m','m.id=rp.module_id')
            ->where('ur.user_id',$user['id'])
            ->where('m.code',$module)
            ->where('rp.action_code',$action)
            ->where('m.active',1)
            ->where('r.active',1)
            ->limit(1)->get()->num_rows() > 0;
    }

    protected function guard($module, $action = 'lihat')
    {
        if (!$this->current_user()) {
            redirect('login');
            exit;
        }
        if (!$this->can($module,$action)) show_error('Anda tidak memiliki hak akses untuk halaman ini.',403);
    }

    protected function render_admin($view, $data = array())
    {
        $data['user'] = $this->current_user();
        $data['content_view'] = $view;
        $data['active_nav'] = isset($data['active_nav']) ? $data['active_nav'] : '';
        $data['can_manage_users'] = $this->can('pengguna');
        $data['allowed_nav'] = array();
        foreach (array('dashboard','koperasi','wilayah','master','penilaian','pengawasan','kdkmp','gis','aduan','layanan','pembinaan','laporan','laporan_tahunan','konten','pengguna','profil_dinas') as $module) {
            $data['allowed_nav'][$module] = $this->can($module);
        }
        $this->load->view('layouts/admin',$data);
    }

    protected function page_args()
    {
        $per = (int) $this->input->get('per_page',true);
        if (!in_array($per,array(10,25,50,100),true)) $per=25;
        $page=max(1,(int) $this->input->get('page',true));
        return array('q'=>trim((string)$this->input->get('q',true)),'status'=>trim((string)$this->input->get('status',true)),'page'=>$page,'per_page'=>$per,'offset'=>($page-1)*$per);
    }

    protected function clamp_page(&$p, $total)
    {
        $p['page']=min($p['page'],max(1,(int)ceil($total/$p['per_page'])));
        $p['offset']=($p['page']-1)*$p['per_page'];
    }

    protected function audit($entity, $id, $action, $before = null, $after = null)
    {
        $this->db->insert('audit_events',array(
            'actor_user_id'=>(int)$this->session->userdata('user_id'),
            'entity_type'=>$entity,'entity_id'=>$id,'action_code'=>$action,
            'before_json'=>$before===null?null:json_encode($before,JSON_UNESCAPED_UNICODE),
            'after_json'=>$after===null?null:json_encode($after,JSON_UNESCAPED_UNICODE),
            'ip_address'=>$this->input->ip_address()
        ));
    }

    protected function post_only()
    {
        if (strtoupper($this->input->method()) !== 'POST') show_404();
    }
}
