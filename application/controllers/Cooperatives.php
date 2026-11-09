<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cooperatives extends MY_Controller
{
    private function options($slug)
    {
        return $this->db->select('m.id,m.code,m.name')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('d.slug',$slug)->where('m.active',1)->order_by('m.name')->get()->result_array();
    }

    private function selected_item($field,$slug)
    {
        $id=(int)$this->input->post($field,true);
        if (!$id)return null;
        $item=$this->db->select('m.id')->from('master_items m')->join('master_definitions d','d.id=m.definition_id')->where('m.id',$id)->where('m.active',1)->where('d.slug',$slug)->get()->row_array();
        if (!$item)show_error('Pilihan master data tidak valid.',422);
        return $id;
    }

    public function index()
    {
        $this->guard('koperasi');$p=$this->page_args();
        $user=$this->current_user();$own_only=$this->is_cooperative_only($user);
        $scope=trim((string)$this->input->get('scope',true));
        $type=trim((string)$this->input->get('type',true));
        $district=trim((string)$this->input->get('district',true));
        $record_status=trim((string)$this->input->get('record_status',true));
        $this->db->select('c.id,c.name,c.nik,c.legal_number,c.ods_status,c.authority_scope,c.reference_only,c.archived_at,t.name AS type_name,r.name AS district_name')
            ->from('cooperatives c')->join('master_items t','t.id=c.type_item_id','left')->join('regions r','r.id=c.district_id','left');
        if ($own_only)$this->db->where('c.id',(int)$user['cooperative_id']);
        if ($record_status==='arsip')$this->db->where('c.archived_at IS NOT NULL',null,false);
        elseif ($record_status!=='semua')$this->db->where('c.archived_at IS NULL',null,false);
        if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('c.nik',$p['q'])->or_like('c.legal_number',$p['q'])->group_end();
        if ($scope==='kabupaten')$this->db->where('c.reference_only',0);
        elseif ($scope==='referensi')$this->db->where('c.reference_only',1);
        if ($type && ctype_digit($type))$this->db->where('c.type_item_id',(int)$type);
        if ($district && ctype_digit($district))$this->db->where('c.district_id',(int)$district);
        if ($p['status'])$this->db->where('c.ods_status',$p['status']);
        $total=$this->db->count_all_results('',false);
        $this->clamp_page($p,$total);
        $rows=$this->db->order_by('c.name','ASC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $rembang=$this->db->select('id')->get_where('regions',array('code'=>'33.17'))->row_array();
        $districts=$this->db->select('id,name')->from('regions')->where('parent_id',(int)$rembang['id'])->where('level','district')->order_by('code')->get()->result_array();
        $this->render_admin('cooperatives/index',array('title'=>'Data koperasi','eyebrow'=>'Direktori dan profil','active_nav'=>'koperasi','rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/koperasi','scope'=>$scope,'type'=>$type,'district'=>$district,'record_status'=>$record_status,'types'=>$this->options('jenis-koperasi'),'districts'=>$districts,'can_edit'=>$this->can('koperasi','ubah'),'can_create'=>!$own_only && $this->can('koperasi','buat')));
    }

    public function detail($id)
    {
        $this->guard('koperasi');
        $user=$this->current_user();if ($this->is_cooperative_only($user) && (int)$user['cooperative_id']!==(int)$id)show_error('Anda hanya dapat membuka profil koperasi sendiri.',403);
        $row=$this->db->select('c.*,t.name AS type_name,f.name AS form_name,p.name AS pattern_name,k.name AS kuk_name,g.name AS group_name,s.name AS sector_name,pr.name AS province_name,rg.name AS regency_name,di.name AS district_name,vi.name AS village_name')
            ->from('cooperatives c')->join('master_items t','t.id=c.type_item_id','left')->join('master_items f','f.id=c.form_item_id','left')->join('master_items p','p.id=c.pattern_item_id','left')->join('master_items k','k.id=c.kuk_item_id','left')->join('master_items g','g.id=c.group_item_id','left')->join('master_items s','s.id=c.sector_item_id','left')->join('regions pr','pr.id=c.province_id','left')->join('regions rg','rg.id=c.regency_id','left')->join('regions di','di.id=c.district_id','left')->join('regions vi','vi.id=c.village_id','left')->where('c.id',(int)$id)->get()->row_array();
        if (!$row)show_404();
        $units=$this->db->select('u.id,u.name,u.active,m.code AS type_code')->from('cooperative_units u')->join('master_items m','m.id=u.unit_type_item_id')->where('u.cooperative_id',$id)->get()->result_array();
        $report_count=(int)$this->db->where('cooperative_id',$id)->count_all_results('periodic_reports');
        $this->render_admin('cooperatives/detail',array('title'=>$row['name'],'eyebrow'=>'Profil koperasi','active_nav'=>'koperasi','row'=>$row,'can_edit'=>$this->can('koperasi','ubah'),'can_archive'=>$this->can('koperasi','nonaktifkan'),'can_assess'=>$this->can('penilaian','buat'),'can_report'=>$this->can('laporan_tahunan'),'report_count'=>$report_count,'units'=>$units));
    }

    public function form($id=null)
    {
        $this->guard('koperasi',$id?'ubah':'buat');
        $user=$this->current_user();if ($this->is_cooperative_only($user) && (int)$user['cooperative_id']!==(int)$id)show_error('Anda hanya dapat mengubah profil koperasi sendiri.',403);
        $row=$id?$this->db->get_where('cooperatives',array('id'=>(int)$id))->row_array():null;
        if ($id && !$row)show_404();
        if ($row && $row['archived_at'])show_error('Koperasi arsip harus diaktifkan kembali sebelum diubah.',409);
        $province=$this->db->get_where('regions',array('code'=>'33'))->row_array();$regency=$this->db->get_where('regions',array('code'=>'33.17'))->row_array();
        $selected_ids=array('province'=>$row['province_id']??$province['id'],'regency'=>$row['regency_id']??$regency['id'],'district'=>$row['district_id']??null,'village'=>$row['village_id']??null);
        $data=array('title'=>$id?'Ubah koperasi':'Tambah koperasi','eyebrow'=>'Profil koperasi','active_nav'=>'koperasi','row'=>$row,'types'=>$this->options('jenis-koperasi'),'forms'=>$this->options('bentuk-koperasi'),'patterns'=>$this->options('pola-pengelolaan'),'kuks'=>$this->options('kuk'),'selected_ids'=>$selected_ids,'provinces'=>$this->db->select('id,code,name')->from('regions')->where('level','province')->where('active',1)->order_by('code')->get()->result_array());
        $this->render_admin('cooperatives/form',$data);
    }

    public function save()
    {
        $this->post_only();$id=(int)$this->input->post('id',true);$this->guard('koperasi',$id?'ubah':'buat');
        $user=$this->current_user();if ($this->is_cooperative_only($user) && (int)$user['cooperative_id']!==$id)show_error('Anda hanya dapat mengubah profil koperasi sendiri.',403);
        $old=$id?$this->db->get_where('cooperatives',array('id'=>$id))->row_array():null;
        if ($id && !$old)show_404();
        if ($old && $old['archived_at'])show_error('Koperasi arsip harus diaktifkan kembali sebelum diubah.',409);
        $name=trim((string)$this->input->post('name',true));
        $nik=trim((string)$this->input->post('nik',true));
        $legal=trim((string)$this->input->post('legal_number',true));
        if (!$name || mb_strlen($name)>255 || ($nik && !preg_match('/^[0-9A-Za-z.-]{8,40}$/',$nik))) {
            $this->session->set_flashdata('error','Nama wajib diisi dan NIK harus valid.');redirect('admin/koperasi'.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        if ($nik) {
            $this->db->where('nik',$nik);if ($id)$this->db->where('id !=',$id);
            if ($this->db->count_all_results('cooperatives')){
                $this->session->set_flashdata('error','NIK sudah dipakai koperasi lain.');redirect('admin/koperasi'.($id?'/'.$id.'/ubah':'/tambah'));return;
            }
        }
        $scope=$this->input->post('authority_scope',true);
        if (!in_array($scope,array('kabupaten','provinsi','nasional','belum_ditetapkan'),true))$scope='belum_ditetapkan';
        $province_id=(int)$this->input->post('province_id',true);$regency_id=(int)$this->input->post('regency_id',true);$district_id=(int)$this->input->post('district_id',true);$village_id=(int)$this->input->post('village_id',true);
        $province=$this->db->get_where('regions',array('id'=>$province_id,'level'=>'province','active'=>1))->row_array();
        $regency=$this->db->get_where('regions',array('id'=>$regency_id,'level'=>'regency','active'=>1))->row_array();
        $district=$district_id?$this->db->get_where('regions',array('id'=>$district_id,'level'=>'district','active'=>1))->row_array():null;
        $village=$village_id?$this->db->get_where('regions',array('id'=>$village_id,'level'=>'village','active'=>1))->row_array():null;
        if (!$province || !$regency || (int)$regency['parent_id']!==$province_id || ($district_id && (!$district || (int)$district['parent_id']!==$regency_id)) || ($village_id && (!$village || !$district || (int)$village['parent_id']!==$district_id))) {
            $this->session->set_flashdata('error','Hierarki provinsi hingga desa tidak sesuai master wilayah aktif.');redirect('admin/koperasi'.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        $lat_raw=trim((string)$this->input->post('latitude',true));$lon_raw=trim((string)$this->input->post('longitude',true));
        if (($lat_raw==='')!==($lon_raw==='') || ($lat_raw!=='' && (!is_numeric($lat_raw) || !is_numeric($lon_raw) || (float)$lat_raw < -90 || (float)$lat_raw > 90 || (float)$lon_raw < -180 || (float)$lon_raw > 180))) {
            $this->session->set_flashdata('error','Koordinat harus berisi pasangan lintang dan bujur yang valid.');redirect('admin/koperasi'.($id?'/'.$id.'/ubah':'/tambah'));return;
        }
        $values=array('name'=>$name,'name_normalized'=>mb_strtolower($name),'nik'=>$nik?:null,'legal_number'=>$legal?:null,'type_item_id'=>$this->selected_item('type_item_id','jenis-koperasi'),'form_item_id'=>$this->selected_item('form_item_id','bentuk-koperasi'),'pattern_item_id'=>$this->selected_item('pattern_item_id','pola-pengelolaan'),'kuk_item_id'=>$this->selected_item('kuk_item_id','kuk'),'authority_scope'=>$scope,'reference_only'=>($scope==='kabupaten' && $regency['code']==='33.17')?0:1,'ods_status'=>$this->input->post('ods_status',true)?:'Belum diverifikasi','province_id'=>$province['id'],'regency_id'=>$regency['id'],'district_id'=>$district_id?:null,'village_id'=>$village_id?:null,'address'=>$this->input->post('address',true),'email'=>$this->input->post('email',true),'latitude'=>$lat_raw!==''?$lat_raw:null,'longitude'=>$lon_raw!==''?$lon_raw:null);
        if ($id)$this->db->where('id',$id)->update('cooperatives',$values);
        else {$this->db->insert('cooperatives',$values);$id=$this->db->insert_id();}
        $this->audit('cooperatives',$id,$old?'update':'create',$old,$values);
        $this->session->set_flashdata('success','Data koperasi berhasil disimpan.');redirect('admin/koperasi/'.$id);
    }

    private function is_cooperative_only($user)
    {
        if (!$user)return false;
        return $this->db->select('1',false)->from('user_roles ur')->join('roles r','r.id=ur.role_id')->where('ur.user_id',$user['id'])->where('r.code','koperasi')->where('r.active',1)->limit(1)->get()->num_rows()>0
            && !$this->can('pengguna') && !$this->can('import');
    }

    public function toggle($id)
    {
        $this->post_only();$this->guard('koperasi','nonaktifkan');
        $old=$this->db->get_where('cooperatives',array('id'=>(int)$id))->row_array();if (!$old)show_404();
        if (!$old['archived_at'] && $this->db->where('cooperative_id',$id)->where('active',1)->count_all_results('users'))show_error('Nonaktifkan akun yang terhubung sebelum mengarsipkan koperasi.',409);
        if (!$old['archived_at'] && $this->db->where('cooperative_id',$id)->where('archived_at IS NULL',null,false)->count_all_results('kdkmp_sites'))show_error('Arsipkan lokasi KDKMP terkait terlebih dahulu.',409);
        $new=$old['archived_at']?null:date('Y-m-d H:i:s');
        $this->db->where('id',$id)->update('cooperatives',array('archived_at'=>$new));
        $this->audit('cooperatives',$id,$new?'archive':'restore',$old,array('archived_at'=>$new));
        $this->session->set_flashdata('success',$new?'Koperasi diarsipkan.':'Koperasi diaktifkan kembali.');redirect('admin/koperasi/'.$id);
    }

    private function unit_cooperative($id)
    {
        $row=$this->db->get_where('cooperatives',array('id'=>(int)$id))->row_array();if (!$row)show_404();
        $user=$this->current_user();if ($this->is_cooperative_only($user) && (int)$user['cooperative_id']!==(int)$id)show_error('Anda hanya dapat membuka unit koperasi sendiri.',403);
        return $row;
    }

    public function unit_index($cooperative_id)
    {
        $this->guard('koperasi');$coop=$this->unit_cooperative($cooperative_id);$p=$this->page_args();
        $this->db->select('u.*,m.name AS type_name')->from('cooperative_units u')->join('master_items m','m.id=u.unit_type_item_id')->where('u.cooperative_id',$cooperative_id);
        if ($p['q'])$this->db->group_start()->like('u.name',$p['q'])->or_like('u.license_number',$p['q'])->group_end();
        if ($p['status']==='aktif')$this->db->where('u.active',1);elseif ($p['status']==='arsip')$this->db->where('u.active',0);
        $total=$this->db->count_all_results('',false);$this->clamp_page($p,$total);
        $rows=$this->db->order_by('u.created_at','DESC')->order_by('u.id','DESC')->limit($p['per_page'],$p['offset'])->get()->result_array();
        $this->render_admin('cooperatives/units',array('title'=>'Unit usaha '.$coop['name'],'eyebrow'=>'Profil koperasi / USP dan USPPS','active_nav'=>'koperasi','coop'=>$coop,'rows'=>$rows,'p'=>$p,'total'=>$total,'path'=>'admin/koperasi/'.$cooperative_id.'/unit','can_create'=>!$coop['archived_at'] && $this->can('koperasi','buat'),'can_edit'=>!$coop['archived_at'] && $this->can('koperasi','ubah'),'can_archive'=>$this->can('koperasi','nonaktifkan')));
    }

    public function unit_form($cooperative_id,$unit_id=null)
    {
        $this->guard('koperasi',$unit_id?'ubah':'buat');$coop=$this->unit_cooperative($cooperative_id);
        if ($coop['archived_at'])show_error('Koperasi arsip harus diaktifkan sebelum unit diubah.',409);
        $unit=$unit_id?$this->db->get_where('cooperative_units',array('id'=>(int)$unit_id,'cooperative_id'=>(int)$cooperative_id))->row_array():null;
        if ($unit_id && !$unit)show_404();
        $this->render_admin('cooperatives/unit_form',array('title'=>$unit_id?'Ubah unit usaha':'Tambah unit usaha','eyebrow'=>'Profil koperasi / '.$coop['name'],'active_nav'=>'koperasi','coop'=>$coop,'unit'=>$unit,'types'=>$this->options('jenis-unit')));
    }

    public function unit_save($cooperative_id)
    {
        $this->post_only();$unit_id=(int)$this->input->post('id',true);$this->guard('koperasi',$unit_id?'ubah':'buat');$coop=$this->unit_cooperative($cooperative_id);
        if ($coop['archived_at'])show_error('Koperasi arsip harus diaktifkan sebelum unit diubah.',409);
        $old=$unit_id?$this->db->get_where('cooperative_units',array('id'=>$unit_id,'cooperative_id'=>(int)$cooperative_id))->row_array():null;
        if ($unit_id && !$old)show_404();
        $name=trim((string)$this->input->post('name',true));$license=trim((string)$this->input->post('license_number',true));$address=trim((string)$this->input->post('address',true));
        if (!$name || mb_strlen($name)>220 || mb_strlen($license)>190 || mb_strlen($address)>10000)show_error('Nama, nomor izin, atau alamat unit tidak valid.',422);
        $values=array('cooperative_id'=>(int)$cooperative_id,'unit_type_item_id'=>$this->selected_item('unit_type_item_id','jenis-unit'),'name'=>$name,'license_number'=>$license?:null,'region_id'=>$coop['regency_id']?:null,'address'=>$address?:null);
        if (!$values['unit_type_item_id'])show_error('Jenis unit wajib dipilih.',422);
        if ($old)$this->db->where('id',$unit_id)->update('cooperative_units',$values);
        else {$this->db->insert('cooperative_units',$values);$unit_id=$this->db->insert_id();}
        $this->audit('cooperative_units',$unit_id,$old?'update':'create',$old,$values);
        $this->session->set_flashdata('success','Unit usaha berhasil disimpan.');redirect('admin/koperasi/'.$cooperative_id.'/unit');
    }

    public function unit_toggle($cooperative_id,$unit_id)
    {
        $this->post_only();$this->guard('koperasi','nonaktifkan');$this->unit_cooperative($cooperative_id);
        $old=$this->db->get_where('cooperative_units',array('id'=>(int)$unit_id,'cooperative_id'=>(int)$cooperative_id))->row_array();if (!$old)show_404();
        $new=1-(int)$old['active'];$this->db->where('id',$unit_id)->update('cooperative_units',array('active'=>$new));
        $this->audit('cooperative_units',$unit_id,$new?'restore':'archive',$old,array('active'=>$new));
        $this->session->set_flashdata('success',$new?'Unit diaktifkan kembali.':'Unit diarsipkan.');redirect('admin/koperasi/'.$cooperative_id.'/unit');
    }
}
