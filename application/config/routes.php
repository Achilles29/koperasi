<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['admin'] = 'admin/index';
$route['admin/koperasi'] = 'cooperatives/index';
$route['admin/koperasi/tambah'] = 'cooperatives/form';
$route['admin/koperasi/(:num)'] = 'cooperatives/detail/$1';
$route['admin/koperasi/(:num)/ubah'] = 'cooperatives/form/$1';
$route['admin/koperasi/(:num)/status'] = 'cooperatives/toggle/$1';
$route['admin/koperasi/(:num)/unit'] = 'cooperatives/unit_index/$1';
$route['admin/koperasi/(:num)/unit/tambah'] = 'cooperatives/unit_form/$1';
$route['admin/koperasi/(:num)/unit/(:num)/ubah'] = 'cooperatives/unit_form/$1/$2';
$route['admin/koperasi/(:num)/unit/simpan'] = 'cooperatives/unit_save/$1';
$route['admin/koperasi/(:num)/unit/(:num)/status'] = 'cooperatives/unit_toggle/$1/$2';
$route['admin/koperasi/simpan'] = 'cooperatives/save';
$route['admin/profil-dinas'] = 'profile/index';
$route['admin/profil-dinas/simpan'] = 'profile/save';
$route['admin/master'] = 'masters/index';
$route['admin/master/(:any)'] = 'masters/items/$1';
$route['admin/master/(:any)/tambah'] = 'masters/form/$1';
$route['admin/master/(:any)/(:num)/ubah'] = 'masters/form/$1/$2';
$route['admin/master/(:any)/simpan'] = 'masters/save/$1';
$route['admin/master/(:any)/(:num)/status'] = 'masters/toggle/$1/$2';
$route['admin/wilayah'] = 'regions/index';
$route['admin/wilayah/anak/(:num)'] = 'regions/children/$1';
$route['admin/wilayah/(:any)'] = 'regions/browse/$1';
$route['admin/wilayah/(:any)/tambah'] = 'regions/form/$1';
$route['admin/wilayah/(:any)/(:num)/ubah'] = 'regions/form/$1/$2';
$route['admin/wilayah/(:any)/simpan'] = 'regions/save/$1';
$route['admin/wilayah/(:any)/(:num)/status'] = 'regions/toggle/$1/$2';
$route['admin/pengguna'] = 'users/index';
$route['admin/pengguna/tambah'] = 'users/form';
$route['admin/pengguna/(:num)/ubah'] = 'users/form/$1';
$route['admin/pengguna/simpan'] = 'users/save';
$route['admin/peran'] = 'users/roles';
$route['admin/peran/tambah'] = 'users/role_form';
$route['admin/peran/(:num)/ubah'] = 'users/role_form/$1';
$route['admin/peran/simpan'] = 'users/role_save';
$route['admin/modul-akses'] = 'users/modules';
$route['admin/modul-akses/tambah'] = 'users/module_form';
$route['admin/modul-akses/(:num)/ubah'] = 'users/module_form/$1';
$route['admin/modul-akses/simpan'] = 'users/module_save';
$route['admin/modul/gis'] = 'gis/index';
$route['admin/modul/penilaian'] = 'assessments/index';
$route['admin/modul/aduan'] = 'cases/index/aduan';
$route['admin/modul/layanan'] = 'cases/index/layanan';
$route['admin/modul/pembinaan'] = 'cases/index/pembinaan';
$route['admin/modul/kdkmp'] = 'kdkmp/index';
$route['admin/modul/pengawasan'] = 'inspections/index';
$route['admin/modul/konten'] = 'content/index';
$route['admin/modul/laporan'] = 'reports/index';
$route['admin/modul/laporan-tahunan'] = 'annual_reports/index';
$route['admin/laporan-tahunan/tambah/(:num)'] = 'annual_reports/form/$1';
$route['admin/laporan-tahunan/(:num)'] = 'annual_reports/detail/$1';
$route['admin/laporan-tahunan/(:num)/ubah'] = 'annual_reports/edit/$1';
$route['admin/laporan-tahunan/simpan'] = 'annual_reports/save';
$route['admin/laporan-tahunan/(:num)/dokumen'] = 'annual_reports/upload/$1';
$route['admin/laporan-tahunan/(:num)/dokumen/(:num)'] = 'annual_reports/download/$1/$2';
$route['admin/laporan-tahunan/(:num)/kirim'] = 'annual_reports/submit/$1';
$route['admin/laporan-tahunan/(:num)/verifikasi'] = 'annual_reports/verify/$1';
$route['admin/laporan-tahunan/(:num)/status'] = 'annual_reports/toggle/$1';
$route['admin/laporan/ekspor'] = 'reports/export';
$route['admin/laporan/monitoring'] = 'reports/monitoring';
$route['admin/konten/tambah'] = 'content/form';
$route['admin/konten/simpan'] = 'content/save';
$route['admin/konten/(:num)/ubah'] = 'content/form/$1';
$route['admin/konten/(:num)/simpan'] = 'content/save/$1';
$route['informasi'] = 'content/public_index';
$route['informasi/(:any)'] = 'content/public_detail/$1';
$route['aduan'] = 'public_complaints/index';
$route['aduan/kirim'] = 'public_complaints/save';
$route['aduan/lacak'] = 'public_complaints/track';
$route['admin/pengawasan/tambah'] = 'inspections/form';
$route['admin/pengawasan/(:num)/ubah'] = 'inspections/form/$1';
$route['admin/pengawasan/simpan'] = 'inspections/save';
$route['admin/pengawasan/(:num)'] = 'inspections/detail/$1';
$route['admin/pengawasan/(:num)/temuan'] = 'inspections/finding/$1';
$route['admin/pengawasan/(:num)/temuan/(:num)/hapus'] = 'inspections/delete_finding/$1/$2';
$route['admin/pengawasan/(:num)/temuan/(:num)/tindaklanjut'] = 'inspections/action/$1/$2';
$route['admin/pengawasan/(:num)/berita-acara'] = 'inspections/minutes/$1';
$route['admin/pengawasan/(:num)/selesai'] = 'inspections/finish/$1';
$route['admin/pengawasan/(:num)/batalkan'] = 'inspections/cancel/$1';
$route['admin/kdkmp/(:num)'] = 'kdkmp/detail/$1';
$route['admin/kdkmp/tambah'] = 'kdkmp/form';
$route['admin/kdkmp/buat'] = 'kdkmp/create';
$route['admin/kdkmp/(:num)/status'] = 'kdkmp/toggle/$1';
$route['admin/kdkmp/(:num)/simpan'] = 'kdkmp/save/$1';
$route['admin/kdkmp/(:num)/foto'] = 'kdkmp/upload/$1';
$route['admin/kdkmp/(:num)/foto/(:num)'] = 'kdkmp/photo/$1/$2';
$route['admin/kdkmp/(:num)/foto/(:num)/setujui'] = 'kdkmp/approve_photo/$1/$2';
$route['admin/kdkmp/(:num)/verifikasi'] = 'kdkmp/verify/$1';
$route['admin/kdkmp/(:num)/komoditas'] = 'kdkmp/commodity/$1';
$route['admin/kdkmp/(:num)/komoditas/(:num)/hapus'] = 'kdkmp/delete_commodity/$1/$2';
$route['admin/kasus/(:any)/tambah'] = 'cases/form/$1';
$route['admin/kasus/(:any)/(:num)/ubah'] = 'cases/form/$1/$2';
$route['admin/kasus/(:any)/simpan'] = 'cases/save/$1';
$route['admin/kasus/(:any)/(:num)'] = 'cases/detail/$1/$2';
$route['admin/kasus/(:any)/(:num)/tindaklanjut'] = 'cases/event/$1/$2';
$route['admin/kasus/(:any)/(:num)/batalkan'] = 'cases/cancel/$1/$2';
$route['admin/kasus/(:any)/(:num)/berita-acara'] = 'cases/minutes/$1/$2';
$route['admin/penilaian/tambah/(:num)'] = 'assessments/create/$1';
$route['admin/penilaian/(:num)'] = 'assessments/detail/$1';
$route['admin/penilaian/(:num)/simpan'] = 'assessments/save/$1';
$route['admin/penilaian/(:num)/keuangan'] = 'assessments/financial/$1';
$route['admin/penilaian/(:num)/keuangan/simpan'] = 'assessments/save_financial/$1';
$route['admin/penilaian/(:num)/kunci'] = 'assessments/submit/$1';
$route['admin/penilaian/(:num)/batalkan'] = 'assessments/withdraw/$1';
$route['admin/penilaian/(:num)/revisi'] = 'assessments/revise/$1';
$route['admin/penilaian/(:num)/dokumen'] = 'assessments/upload/$1';
$route['admin/penilaian/(:num)/dokumen/(:num)'] = 'assessments/download/$1/$2';
$route['admin/penilaian/(:num)/dokumen/(:num)/setujui'] = 'assessments/approve_document/$1/$2';
$route['admin/penilaian/(:num)/verifikasi'] = 'assessments/review/$1';
$route['admin/penilaian/(:num)/berita-acara'] = 'assessments/minutes/$1';
$route['admin/modul/(:any)'] = 'workspace/show/$1';
