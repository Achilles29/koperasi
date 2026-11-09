#!/usr/bin/env python3
"""Idempotent initial seed: official regions, master catalog, roles, and ODS Excel snapshots."""
import csv
import hashlib
import json
import re
import subprocess
import sys
import unicodedata
from collections import Counter
from pathlib import Path

import pymysql
from openpyxl import load_workbook

ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / 'docs'
INITIAL_PASSWORD = 'Rembang26!'
ADMIN_PASSWORD = 'Admin26!'
COOPERATIVE_PASSWORD = 'Koperasi26!'

MASTER_DEFS = [
    ('jenis-koperasi', 'Jenis koperasi', 'Koperasi', 'official'),
    ('bentuk-koperasi', 'Bentuk koperasi', 'Koperasi', 'official'),
    ('pola-pengelolaan', 'Pola pengelolaan', 'Koperasi', 'official'),
    ('kewenangan', 'Kewenangan', 'Koperasi', 'official'),
    ('kelompok-ods', 'Kelompok / alias ODS', 'Koperasi', 'mixed'),
    ('sektor-usaha', 'Sektor usaha', 'Koperasi', 'mixed'),
    ('jenis-unit', 'Jenis unit / jaringan', 'Koperasi', 'official'),
    ('kuk', 'Klasifikasi usaha koperasi', 'Koperasi', 'official'),
    ('status-koperasi', 'Status koperasi', 'Koperasi', 'mixed'),
    ('jenis-dokumen', 'Jenis dokumen', 'Koperasi', 'mixed'),
    ('dasar-hukum', 'Dasar hukum', 'Penilaian', 'official'),
    ('paket-kkpkk', 'Paket / versi KKPKK', 'Penilaian', 'official'),
    ('aspek-kkpkk', 'Aspek dan subaspek', 'Penilaian', 'official'),
    ('indikator-kkpkk', 'Indikator / pertanyaan', 'Penilaian', 'official'),
    ('pilihan-jawaban', 'Pilihan jawaban', 'Penilaian', 'official'),
    ('rumus-ambang', 'Rumus, bobot dan ambang', 'Penilaian', 'official'),
    ('predikat-kesehatan', 'Predikat kesehatan', 'Penilaian', 'official'),
    ('kategori-temuan', 'Kategori temuan', 'Penilaian', 'mixed'),
    ('jenis-layanan', 'Jenis layanan', 'Layanan', 'local'),
    ('persyaratan-layanan', 'Persyaratan layanan', 'Layanan', 'mixed'),
    ('instansi-rujukan', 'Instansi tujuan / rujukan', 'Layanan', 'local'),
    ('kategori-aduan', 'Kategori aduan', 'Layanan', 'local'),
    ('prioritas-sla', 'Prioritas dan SLA', 'Layanan', 'local'),
    ('jenis-tindak-lanjut', 'Jenis tindak lanjut', 'Layanan', 'local'),
    ('status-proses', 'Status dan transisi proses', 'Layanan', 'mixed'),
    ('template-naskah', 'Template naskah', 'Dokumen', 'local'),
    ('format-penomoran', 'Format penomoran', 'Dokumen', 'local'),
    ('tahap-kdkmp', 'Tahap KDKMP', 'KDKMP', 'mixed'),
    ('jenis-fasilitas', 'Jenis fasilitas', 'KDKMP', 'mixed'),
    ('checklist-verval', 'Checklist verval', 'KDKMP', 'mixed'),
    ('komoditas', 'Komoditas', 'KDKMP', 'mixed'),
    ('satuan-ukuran', 'Satuan ukuran', 'KDKMP', 'local'),
    ('jenis-mitra', 'Jenis mitra', 'KDKMP', 'local'),
    ('jenis-lokasi', 'Jenis lokasi', 'GIS', 'local'),
    ('lapisan-peta', 'Lapisan peta', 'GIS', 'local'),
    ('kategori-konten', 'Kategori berita / agenda', 'Konten', 'local'),
    ('unit-kerja', 'Unit kerja dan pejabat', 'Organisasi', 'local'),
    ('jenis-akun', 'Jenis akun / peran', 'Akses', 'local'),
    ('hak-akses', 'Modul, aksi dan hak akses', 'Akses', 'local'),
]

MODULES = [
    ('dashboard', 'Dashboard'), ('koperasi', 'Data koperasi'), ('master', 'Master data'),
    ('wilayah', 'Master wilayah'), ('import', 'Impor data'), ('penilaian', 'Penilaian kesehatan'),
    ('pengawasan', 'Pengawasan'), ('kdkmp', 'KDKMP'), ('gis', 'GIS'),
    ('aduan', 'Aduan'), ('layanan', 'Layanan izin'), ('pembinaan', 'Pembinaan'),
    ('konten', 'Konten publik'), ('laporan', 'Laporan'), ('laporan_tahunan', 'Laporan tahunan koperasi'), ('pengguna', 'Pengguna dan akses'), ('profil_dinas', 'Profil dinas'),
]

MASTER_SEEDS = {
    'jenis-koperasi': [('produsen','Produsen'),('konsumen','Konsumen'),('pemasaran','Pemasaran'),('jasa','Jasa'),('simpan_pinjam','Simpan Pinjam'),('kdkmp','Koperasi Desa/Kelurahan Merah Putih'),('lainnya','Jenis lain sesuai ketentuan')],
    'bentuk-koperasi': [('primer','Primer'),('sekunder','Sekunder')],
    'pola-pengelolaan': [('konvensional','Konvensional'),('syariah','Syariah')],
    'kewenangan': [('kabupaten','Kabupaten/Kota'),('provinsi','Provinsi'),('nasional','Pusat/Nasional'),('belum_ditetapkan','Belum ditetapkan')],
    'kuk': [(str(i),f'KUK {i}') for i in range(1,5)],
    'status-koperasi': [('aktif','Aktif'),('tidak_aktif','Tidak Aktif'),('belum_verifikasi','Belum diverifikasi')],
    'jenis-unit': [('usp','Unit Simpan Pinjam (USP)'),('uspps','Unit Simpan Pinjam dan Pembiayaan Syariah (USPPS)')],
    'jenis-dokumen': [('akta','Akta dan badan hukum'),('ad_art','Anggaran Dasar/Anggaran Rumah Tangga'),('rat','Dokumen RAT'),('laporan_keuangan','Laporan keuangan'),('nib','Nomor Induk Berusaha'),('izin_usp','Izin USP/USPPS'),('bukti_lain','Bukti pendukung lain')],
    'predikat-kesehatan': [('sehat','Sehat'),('cukup_sehat','Cukup Sehat'),('dalam_pengawasan','Dalam Pengawasan'),('pengawasan_khusus','Dalam Pengawasan Khusus')],
    'paket-kkpkk': [
        ('ksp_12','KSP/USP konvensional · KUK 1–2'),('ksp_34','KSP/USP konvensional · KUK 3–4'),
        ('kspps_12','KSPPS/USPPS · KUK 1–2'),('kspps_34','KSPPS/USPPS · KUK 3–4'),
        ('nonksp_12','Non KSP konvensional · KUK 1–2'),('nonksp_34','Non KSP konvensional · KUK 3–4'),
        ('nonksp_syariah_12','Non KSP syariah · KUK 1–2'),('nonksp_syariah_34','Non KSP syariah · KUK 3–4'),
    ],
    'aspek-kkpkk': [('tata_kelola','Tata Kelola'),('profil_risiko','Profil Risiko'),('keuangan','Kinerja Keuangan'),('permodalan','Permodalan'),('syariah','Prinsip Syariah')],
    'jenis-layanan': [('izin','Pendampingan izin'),('konsultasi','Konsultasi'),('aduan','Aduan'),('pembinaan','Pembinaan')],
    'kategori-aduan': [('layanan','Layanan'),('tata_kelola','Tata Kelola Koperasi'),('simpan_pinjam','Simpan Pinjam'),('lainnya','Lainnya')],
    'tahap-kdkmp': [('legalitas','Legalitas'),('lahan','Lahan'),('fisik','Pembangunan Fisik'),('verval','Verifikasi dan Validasi'),('operasional','Operasional')],
    'jenis-fasilitas': [('gerai','Gerai'),('gudang','Gudang'),('kantor','Kantor'),('lainnya','Fasilitas Lain')],
    'komoditas': [('beras','Beras'),('gula','Gula'),('minyak_goreng','Minyak goreng'),('telur','Telur'),('pupuk','Pupuk'),('lainnya','Lainnya')],
    'satuan-ukuran': [('kg','Kilogram'),('ton','Ton'),('liter','Liter'),('unit','Unit'),('rupiah','Rupiah')],
    'jenis-lokasi': [('kantor','Kantor Koperasi'),('unit','Unit Usaha'),('gerai','Gerai KDKMP'),('gudang','Gudang KDKMP')],
    'kategori-konten': [('berita','Berita'),('agenda','Agenda'),('regulasi','Regulasi'),('pengumuman','Pengumuman'),('galeri','Galeri kegiatan'),('sosial','Kanal sosial')],
}


def config():
    content = (ROOT / 'application/config/koperasi_db.php').read_text()
    return {key: re.search(r"'" + key + r"'\s*=>\s*'([^']*)'", content).group(1) for key in ('hostname','username','password','database')}


def normalize(value):
    value = unicodedata.normalize('NFKD', str(value or ''))
    return re.sub(r'[^a-z0-9]', '', value.lower())


def upsert_master(cursor, definition_id, code, name):
    cursor.execute('INSERT IGNORE INTO master_items(definition_id,code,name) VALUES(%s,%s,%s)', (definition_id,code,name))
    cursor.execute('SELECT id FROM master_items WHERE definition_id=%s AND code=%s',(definition_id,code))
    return cursor.fetchone()[0]


def seed_catalog(conn):
    with conn.cursor() as q:
        for i,(slug,title,category,policy) in enumerate(MASTER_DEFS,1):
            q.execute('INSERT INTO master_definitions(slug,title,category,source_policy,sort_order) VALUES(%s,%s,%s,%s,%s) ON DUPLICATE KEY UPDATE title=VALUES(title),category=VALUES(category),source_policy=VALUES(source_policy),sort_order=VALUES(sort_order)',(slug,title,category,policy,i))
        q.execute('SELECT slug,id FROM master_definitions')
        defs=dict(q.fetchall())
        for slug,values in MASTER_SEEDS.items():
            for code,name in values:upsert_master(q,defs[slug],code,name)
        new_roles={}
        for code,name in [('superadmin','Superadmin'),('admin','Admin'),('koperasi','Koperasi')]:
            q.execute('INSERT IGNORE INTO roles(code,name) VALUES(%s,%s)',(code,name))
            new_roles[code]=q.rowcount==1
            upsert_master(q,defs['jenis-akun'],code,name)
        for code,name in MODULES:
            q.execute('INSERT IGNORE INTO modules(code,name) VALUES(%s,%s)',(code,name))
            upsert_master(q,defs['hak-akses'],code,name)
        q.execute('SELECT id FROM roles WHERE code=%s',('superadmin',));super_id=q.fetchone()[0]
        q.execute('SELECT id FROM roles WHERE code=%s',('admin',));admin_id=q.fetchone()[0]
        q.execute('SELECT id FROM roles WHERE code=%s',('koperasi',));cooperative_role_id=q.fetchone()[0]
        q.execute('SELECT id,code FROM modules');modules=dict((code,mid) for mid,code in q.fetchall())
        actions=['lihat','buat','ubah','nonaktifkan','verifikasi','setujui','terbitkan','ekspor','kelola']
        if new_roles['superadmin']:
            for mid in modules.values():
                for action in actions:
                    q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(super_id,mid,action))
        if new_roles['admin']:
            for code in modules:
                if code!='pengguna':
                    for action in ['lihat','buat','ubah','ekspor']:
                        q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(admin_id,modules[code],action))
            for code in ('penilaian','pengawasan','kdkmp','laporan_tahunan'):
                q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(admin_id,modules[code],'verifikasi'))
        if new_roles['koperasi']:
            for code in ('dashboard','koperasi','laporan_tahunan'):
                q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(cooperative_role_id,modules[code],'lihat'))
            q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(cooperative_role_id,modules['koperasi'],'ubah'))
            q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(cooperative_role_id,modules['koperasi'],'buat'))
            for action in ('lihat','buat','ubah'):
                q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(cooperative_role_id,modules['penilaian'],action))
            q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(cooperative_role_id,modules['penilaian'],'nonaktifkan'))
            for action in ('buat','ubah','nonaktifkan'):
                q.execute('INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) VALUES(%s,%s,%s)',(cooperative_role_id,modules['laporan_tahunan'],action))
        password_hash=subprocess.run(['php','-r',f'echo password_hash({json.dumps(INITIAL_PASSWORD)}, PASSWORD_DEFAULT);'],capture_output=True,text=True,check=True).stdout
        q.execute('INSERT IGNORE INTO users(username,full_name,password_hash) VALUES(%s,%s,%s)',('superadmin','Superadmin Koperasi Rembang',password_hash))
        q.execute('SELECT id FROM users WHERE username=%s',('superadmin',));user_id=q.fetchone()[0]
        q.execute('INSERT IGNORE INTO user_roles(user_id,role_id) VALUES(%s,%s)',(user_id,super_id))
        for username,full_name,password,role_id,active in [
            ('admin','Admin Bidang Koperasi',ADMIN_PASSWORD,admin_id,1),
            ('koperasi','Akun Koperasi (belum ditautkan)',COOPERATIVE_PASSWORD,cooperative_role_id,0),
        ]:
            hashed=subprocess.run(['php','-r',f'echo password_hash({json.dumps(password)}, PASSWORD_DEFAULT);'],capture_output=True,text=True,check=True).stdout
            q.execute('INSERT IGNORE INTO users(username,full_name,password_hash,active) VALUES(%s,%s,%s,%s)',(username,full_name,hashed,active))
            q.execute('SELECT id FROM users WHERE username=%s',(username,));new_user_id=q.fetchone()[0]
            q.execute('INSERT IGNORE INTO user_roles(user_id,role_id) VALUES(%s,%s)',(new_user_id,role_id))
    conn.commit()
    return defs


def seed_regions(conn):
    path=ROOT/'database/regions_2025.csv'
    with conn.cursor() as q:
        q.execute('INSERT INTO source_versions(kind,title,reference_no,source_url,file_hash,effective_from) VALUES(%s,%s,%s,%s,%s,%s) ON DUPLICATE KEY UPDATE title=VALUES(title)',('regions','Kode wilayah Kemendagri 2025','300.2.2-2138/2025','https://peraturan.bpk.go.id/Details/322942/keputusan-mendagri-no-nomor-30022-2138-tahun',hashlib.sha256(path.read_bytes()).hexdigest(),'2025-04-25'))
        q.execute('SELECT id FROM source_versions WHERE kind=%s AND reference_no=%s',('regions','300.2.2-2138/2025'));source_id=q.fetchone()[0]
        q.execute('SELECT code,id FROM regions');ids=dict(q.fetchall())
        rows=list(csv.DictReader(path.open(encoding='utf-8')))
        for depth in range(4):
            subset=[r for r in rows if r['code'].count('.')==depth and r['code'] not in ids]
            for row in subset:
                parent_id=ids.get(row['parent_code']) if row['parent_code'] else None
                q.execute('INSERT INTO regions(code,name,level,village_type,parent_id,source_version_id,active,effective_from) VALUES(%s,%s,%s,%s,%s,%s,1,%s)',(row['code'],row['name'],row['level'],row['village_type'] or None,parent_id,source_id,'2025-04-25'))
                ids[row['code']]=q.lastrowid
            conn.commit()
            print('region level',depth,'inserted',len(subset))
    return ids


def seed_ods(conn, defs, regions):
    files=sorted(DOCS.glob('Daftar Koperasi Binaan *.xlsx'))
    local_niks=set()
    for path in files:
        if '11.23.18' in path.name:
            wb=load_workbook(path,read_only=True,data_only=True)
            local_niks={str(r[1]).strip("' ") for r in list(wb.active.iter_rows(min_col=1,max_col=21,values_only=True))[1:] if r[1]}
    district_map={}; village_map={}
    with conn.cursor() as q:
        q.execute("SELECT code,name,id,parent_id,level FROM regions WHERE code LIKE '33.17.%'")
        for code,name,rid,parent,level in q.fetchall():
            if level=='district':district_map[normalize(name)]=rid
            elif level=='village':village_map[(parent,normalize(name))]=rid
    with conn.cursor() as q:
        q.execute('SELECT definition_id,code,id FROM master_items')
        item_ids={(definition,code):item_id for definition,code,item_id in q.fetchall()}
        q.execute('SELECT code,id FROM regions WHERE code IN (%s,%s)',('33','33.17'))
        region_ids=dict(q.fetchall())
        for path in files:
            digest=hashlib.sha256(path.read_bytes()).hexdigest()
            q.execute('SELECT id FROM import_batches WHERE source_kind=%s AND sha256=%s',('ODS Excel',digest))
            if q.fetchone():
                print('already imported',path.name);continue
            rows=list(load_workbook(path,read_only=True,data_only=True).active.iter_rows(min_col=1,max_col=21,values_only=True))
            headers=list(rows[0]);data=rows[1:]
            q.execute('INSERT INTO import_batches(source_kind,file_name,sha256,total_rows,notes) VALUES(%s,%s,%s,%s,%s)',('ODS Excel',path.name,digest,len(data),'Snapshot awal; seluruh entri disimpan termasuk referensi di luar kewenangan kabupaten.'))
            batch_id=q.lastrowid
            counts=Counter()
            for row_no,row in enumerate(data,2):
                nik=str(row[1] or '').strip("' ")
                if not nik:
                    counts['needs_review']+=1;continue
                raw={str(headers[i]):row[i] for i in range(min(len(headers),len(row)))}
                raw_json=json.dumps(raw,ensure_ascii=False,default=str)
                name=str(row[2] or '').strip("' ")
                type_raw=str(row[5] or '').strip()
                type_code={'Desa Merah Putih':'kdkmp','Kelurahan Merah Putih':'kdkmp','Simpan Pinjam':'simpan_pinjam'}.get(type_raw,normalize(type_raw))
                form_raw=str(row[4] or '').strip();form_code='primer' if form_raw.startswith('Primer') else ('sekunder' if form_raw.startswith('Sekunder') else None)
                pattern_code=normalize(row[6]);kuk_code=str(row[18] or '').strip()
                group_raw=str(row[9] or '').strip();sector_raw=str(row[8] or '').strip()
                group_code=normalize(group_raw)[:90];sector_code=normalize(sector_raw)[:90]
                if group_code:item_ids[(defs['kelompok-ods'],group_code)]=upsert_master(q,defs['kelompok-ods'],group_code,group_raw)
                if sector_code:item_ids[(defs['sektor-usaha'],sector_code)]=upsert_master(q,defs['sektor-usaha'],sector_code,sector_raw)
                district_id=district_map.get(normalize(row[12]))
                village_id=None
                for candidate in (row[14],row[13]):
                    if district_id and candidate:
                        village_id=village_map.get((district_id,normalize(candidate)))
                        if village_id:break
                authority='kabupaten' if nik in local_niks else ('provinsi' if 'Provinsi' in form_raw else 'nasional')
                reference_only=0 if nik in local_niks else 1
                values=(nik,str(row[3] or '').strip("' "),name,normalize(name),item_ids.get((defs['jenis-koperasi'],type_code)),item_ids.get((defs['bentuk-koperasi'],form_code)),item_ids.get((defs['pola-pengelolaan'],pattern_code)),item_ids.get((defs['kelompok-ods'],group_code)),item_ids.get((defs['sektor-usaha'],sector_code)),item_ids.get((defs['kuk'],kuk_code)),str(row[19] or '').strip(),str(row[7] or '').strip(),str(row[20] or '').strip(),authority,reference_only,region_ids.get('33'),region_ids.get('33.17'),district_id,village_id,str(row[14] or row[13] or '').strip(),str(row[15] or '').strip(),str(row[16] or '').strip(),str(row[17] or '').strip(),batch_id)
                q.execute('SELECT id FROM cooperatives WHERE nik=%s',(nik,));old=q.fetchone()
                q.execute('''INSERT INTO cooperatives(nik,legal_number,name,name_normalized,type_item_id,form_item_id,pattern_item_id,group_item_id,sector_item_id,kuk_item_id,grade,ods_status,ods_account_status,authority_scope,reference_only,province_id,regency_id,district_id,village_id,ods_village_name,address,postal_code,email,source_batch_id,source_updated_at)
                           VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())
                           ON DUPLICATE KEY UPDATE legal_number=VALUES(legal_number),name=VALUES(name),name_normalized=VALUES(name_normalized),type_item_id=VALUES(type_item_id),form_item_id=VALUES(form_item_id),pattern_item_id=VALUES(pattern_item_id),group_item_id=VALUES(group_item_id),sector_item_id=VALUES(sector_item_id),kuk_item_id=VALUES(kuk_item_id),grade=VALUES(grade),ods_status=VALUES(ods_status),ods_account_status=VALUES(ods_account_status),authority_scope=VALUES(authority_scope),reference_only=VALUES(reference_only),province_id=VALUES(province_id),regency_id=VALUES(regency_id),district_id=VALUES(district_id),village_id=VALUES(village_id),ods_village_name=VALUES(ods_village_name),address=VALUES(address),postal_code=VALUES(postal_code),email=VALUES(email),source_batch_id=VALUES(source_batch_id),source_updated_at=NOW()''',values)
                q.execute('SELECT id FROM cooperatives WHERE nik=%s',(nik,));coop_id=q.fetchone()[0]
                result='updated' if old else 'inserted';counts[result]+=1
                q.execute('INSERT INTO import_rows(batch_id,source_row_number,source_key,cooperative_id,result_code,raw_json) VALUES(%s,%s,%s,%s,%s,%s)',(batch_id,row_no,nik,coop_id,result,raw_json))
                if row_no%150==0:conn.commit()
            q.execute('UPDATE import_batches SET inserted_rows=%s,updated_rows=%s,skipped_rows=%s WHERE id=%s',(counts['inserted'],counts['updated'],counts['needs_review'],batch_id))
            conn.commit()
            print('ODS',path.name,dict(counts))


def main():
    cfg=config()
    conn=pymysql.connect(host=cfg['hostname'],user=cfg['username'],password=cfg['password'],database=cfg['database'],charset='utf8mb4',autocommit=False)
    defs=seed_catalog(conn)
    regions=seed_regions(conn)
    seed_ods(conn,defs,regions)
    with conn.cursor() as q:
        q.execute('''INSERT IGNORE INTO kdkmp_sites(cooperative_id,village_id,verification_status)
          SELECT c.id,c.village_id,'pending' FROM cooperatives c JOIN master_items m ON m.id=c.type_item_id
          JOIN master_definitions d ON d.id=m.definition_id WHERE d.slug='jenis-koperasi' AND m.code='kdkmp' AND c.village_id IS NOT NULL''')
        conn.commit()
    with conn.cursor() as q:
        q.execute('SELECT COUNT(*),SUM(reference_only=1) FROM cooperatives');print('cooperatives',q.fetchone())
    conn.close()


if __name__=='__main__':main()
