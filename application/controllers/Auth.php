<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function login()
    {
        if ($this->current_user()) redirect('admin');
        $data=array('error'=>'');
        if (strtoupper($this->input->method())==='POST') {
            $username=trim((string)$this->input->post('username',true));
            $password=(string)$this->input->post('password',false);
            $failures=(int)$this->session->userdata('login_failures');
            $locked_until=(int)$this->session->userdata('login_locked_until');
            if ($locked_until > time()) {
                $data['error']='Terlalu banyak percobaan. Coba lagi beberapa menit lagi.';
            } else {
                $user=$this->db->get_where('users',array('username'=>$username,'active'=>1))->row_array();
                if ($user && password_verify($password,$user['password_hash'])) {
                    $this->session->sess_regenerate(true);
                    $this->session->set_userdata(array('user_id'=>$user['id'],'login_failures'=>0,'login_locked_until'=>0));
                    $this->db->where('id',$user['id'])->update('users',array('last_login_at'=>date('Y-m-d H:i:s')));
                    redirect('admin');
                }
                $failures++;
                $this->session->set_userdata('login_failures',$failures);
                if ($failures>=5) $this->session->set_userdata('login_locked_until',time()+300);
                $data['error']='Nama pengguna atau kata sandi tidak sesuai.';
            }
        }
        $this->load->view('auth/login',$data);
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('/');
    }
}
