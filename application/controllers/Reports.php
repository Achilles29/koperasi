<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
    private $datasets=array('koperasi'=>'Direktori koperasi','pengawasan'=>'Pengawasan','kdkmp'=>'Progres KDKMP');
    private function dataset()
    {
        $name=(string)$this->input->get('dataset',true);return isset($this->datasets[$name])?$name:'koperasi';
    }
    private function build_query($dataset,$p)
    {
        if ($dataset==='pengawasan'){
            $this->db->select('i.id,c.name AS cooperative_name,c.nik,i.assignment_number,i.inspection_type,i.status,i.started_on,u.full_name AS lead_name')->from('inspections i')->join('cooperatives c','c.id=i.cooperative_id')->join('users u','u.id=i.lead_user_id','left');
            if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('i.assignment_number',$p['q'])->group_end();
            if (in_array($p['status'],array('planned','in_progress','completed','cancelled'),true))$this->db->where('i.status',$p['status']);
        }elseif ($dataset==='kdkmp'){
            $this->db->select('s.id,c.name AS cooperative_name,c.nik,r.name AS district_name,s.physical_progress,s.verification_status,s.operational_status,s.verified_at')->from('kdkmp_sites s')->where('s.archived_at IS NULL',null,false)->join('cooperatives c','c.id=s.cooperative_id')->join('regions r','r.id=c.district_id','left');
            if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('r.name',$p['q'])->group_end();
            if (in_array($p['status'],array('pending','verified'),true))$this->db->where('s.verification_status',$p['status']);
        }else{
            $this->db->select('c.id,c.name AS cooperative_name,c.nik,c.ods_status,c.authority_scope,c.reference_only,c.latitude,c.longitude,r.name AS district_name,m.name AS type_name')->from('cooperatives c')->where('c.archived_at IS NULL',null,false)->join('regions r','r.id=c.district_id','left')->join('master_items m','m.id=c.type_item_id','left');
            if ($p['q'])$this->db->group_start()->like('c.name',$p['q'])->or_like('c.nik',$p['q'])->group_end();
            if (in_array($p['status'],array('kabupaten','provinsi','nasional','belum_ditetapkan'),true))$this->db->where('c.authority_scope',$p['status']);
        }
    }
    private function rows($dataset,$p,$all=false)
    {
        $this->build_query($dataset,$p);$total=$this->db->count_all_results('',false);
        if (!$all)$this->clamp_page($p,$total);
        $order=$dataset==='koperasi'?'c.name':($dataset==='pengawasan'?'i.created_at':'c.name');
        $this->db->order_by($order,$dataset==='pengawasan'?'DESC':'ASC');
        if (!$all)$this->db->limit($p['per_page'],$p['offset']);
        return array($this->db->get()->result_array(),$total,$p);
    }
    public function index()
    {
        $this->guard('laporan');$dataset=$this->dataset();$p=$this->page_args();list($rows,$total,$p)=$this->rows($dataset,$p);
        $totals=array('cooperatives'=>(int)$this->db->where('archived_at IS NULL',null,false)->count_all_results('cooperatives'),'local'=>(int)$this->db->where('reference_only',0)->where('archived_at IS NULL',null,false)->count_all_results('cooperatives'),'inspections'=>(int)$this->db->count_all('inspections'),'kdkmp'=>(int)$this->db->where('archived_at IS NULL',null,false)->count_all_results('kdkmp_sites'));
        $totals['kdkmp_verified']=(int)$this->db->where('physical_progress',100)->where('verification_status','verified')->where('archived_at IS NULL',null,false)->count_all_results('kdkmp_sites');
        $this->render_admin('reports/index',array('title'=>'Laporan bidang','eyebrow'=>'Rekap data operasional','active_nav'=>'laporan','datasets'=>$this->datasets,'dataset'=>$dataset,'rows'=>$rows,'total'=>$total,'p'=>$p,'path'=>'admin/modul/laporan','totals'=>$totals));
    }
    public function export()
    {
        $this->guard('laporan');$dataset=$this->dataset();$p=$this->page_args();list($rows)=$this->rows($dataset,$p,true);
        $name='sikop-'.$dataset.'-'.date('Ymd-His').'.csv';header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="'.$name.'"');header('Cache-Control: no-store');
        $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
        if ($dataset==='pengawasan')$cols=array('cooperative_name'=>'Koperasi','nik'=>'NIK','assignment_number'=>'Nomor tugas','inspection_type'=>'Jenis','status'=>'Status','started_on'=>'Tanggal mulai','lead_name'=>'Petugas');
        elseif ($dataset==='kdkmp')$cols=array('cooperative_name'=>'KDKMP','nik'=>'NIK','district_name'=>'Kecamatan','physical_progress'=>'Progres fisik (%)','verification_status'=>'Verifikasi','operational_status'=>'Operasional','verified_at'=>'Tanggal verifikasi');
        else $cols=array('cooperative_name'=>'Koperasi','nik'=>'NIK','type_name'=>'Jenis','district_name'=>'Kecamatan','ods_status'=>'Status ODS','authority_scope'=>'Kewenangan','reference_only'=>'Referensi lintas kewenangan','latitude'=>'Lintang','longitude'=>'Bujur');
        fputcsv($out,array_values($cols));foreach ($rows as $row){$line=array();foreach ($cols as $key=>$label){$v=(string)($row[$key]??'');if ($v!=='' && preg_match('/^[=+\-@\t\r]/',$v))$v="'".$v;$line[]=$v;}fputcsv($out,$line);}fclose($out);
    }
    public function monitoring()
    {
        $this->guard('laporan');$template=json_decode(file_get_contents(FCPATH.'database/monitoring_template_2026.json'),true);
        if (!$template || $template['column_count']!==108)show_error('Template monitoring belum tersedia.',500);
        $q=trim((string)$this->input->get('q',true));
        $this->db->select('i.id,i.started_on,c.name,c.legal_number,c.nik,r.name AS district_name,t.name AS type_name,g.name AS group_name,u.full_name AS lead_name')
            ->from('inspections i')->join('cooperatives c','c.id=i.cooperative_id')->join('regions r','r.id=c.district_id','left')->join('master_items t','t.id=c.type_item_id','left')->join('master_items g','g.id=c.group_item_id','left')->join('users u','u.id=i.lead_user_id','left')->where('i.status','completed')->order_by('i.started_on','DESC');
        if ($q)$this->db->group_start()->like('c.name',$q)->or_like('c.nik',$q)->or_like('i.assignment_number',$q)->group_end();
        $inspections=$this->db->get()->result_array();$findings=array();
        if ($inspections){$ids=array_column($inspections,'id');$all=$this->db->select('inspection_id,aspect_code,fact')->where_in('inspection_id',$ids)->order_by('id')->get('findings')->result_array();foreach ($all as $row)$findings[$row['inspection_id']][]=$row;}
        header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="draf-monitoring-koperasi-2026-'.date('Ymd').'.csv"');header('Cache-Control: no-store');
        $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");foreach ($template['header_rows'] as $header)fputcsv($out,$header);
        $blank=array_fill(0,108,'');fputcsv($out,$blank);$n=0;
        foreach ($inspections as $r){$line=$blank;$line[0]=++$n;$line[1]=$r['group_name']??'';$line[2]=$r['name'];$line[3]=$r['legal_number']??'';$line[4]=$r['district_name']??'';$line[5]=$r['type_name']??'';$line[6]=$r['started_on']??'';$line[7]=$r['lead_name']??'';
            $items=$findings[$r['id']]??array();$line[8]=implode('; ',array_unique(array_column($items,'aspect_code')));$line[105]=implode("\n",array_map(function($item){return $item['fact'];},$items));
            foreach ($line as &$v){$v=(string)$v;if ($v!=='' && preg_match('/^[=+\-@\t\r]/',$v))$v="'".$v;}unset($v);fputcsv($out,$line);
        }
        fclose($out);
    }
}
