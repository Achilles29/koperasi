<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function h($value)
{
    return html_escape((string) ($value ?? ''));
}

function money_id($value)
{
    if ($value===null || $value==='')return '—';
    $parts=explode('.',(string)$value,2);
    $whole=ltrim($parts[0],'0');if ($whole==='')$whole='0';
    $whole=preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole);
    return 'Rp '.$whole.','.str_pad(substr($parts[1]??'0',0,2),2,'0');
}


function site_profile()
{
    static $profile = null;
    if ($profile !== null) return $profile;
    $ci =& get_instance();
    $profile = $ci->db->get_where('organization_profile', array('id'=>1))->row_array();
    return $profile ?: array();
}

function csrf_input()
{
    $ci =& get_instance();
    return '<input type="hidden" name="'.h($ci->security->get_csrf_token_name()).'" value="'.h($ci->security->get_csrf_hash()).'">';
}

function qs_link($path, $changes = array())
{
    $query = $_GET;
    foreach ($changes as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }
    return site_url($path).($query ? '?'.http_build_query($query) : '');
}

function label_scope($value)
{
    $labels = array('kabupaten'=>'Kewenangan kabupaten','provinsi'=>'Referensi provinsi','nasional'=>'Referensi nasional','belum_ditetapkan'=>'Perlu verifikasi');
    return isset($labels[$value]) ? $labels[$value] : ucfirst(str_replace('_',' ',$value));
}
