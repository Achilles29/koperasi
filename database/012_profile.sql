CREATE TABLE IF NOT EXISTS organization_profile (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  site_name VARCHAR(120) NOT NULL,
  government_name VARCHAR(190) NOT NULL,
  opd_name VARCHAR(190) NOT NULL,
  division_name VARCHAR(160) NOT NULL,
  hero_title VARCHAR(255) NOT NULL,
  hero_description TEXT NOT NULL,
  about_title VARCHAR(255) NOT NULL,
  about_description TEXT NOT NULL,
  address TEXT DEFAULT NULL,
  phone VARCHAR(80) DEFAULT NULL,
  email VARCHAR(190) DEFAULT NULL,
  website_url VARCHAR(255) DEFAULT NULL,
  instagram_url VARCHAR(255) DEFAULT NULL,
  facebook_url VARCHAR(255) DEFAULT NULL,
  youtube_url VARCHAR(255) DEFAULT NULL,
  logo_path VARCHAR(255) NOT NULL,
  favicon_path VARCHAR(255) NOT NULL,
  updated_by BIGINT UNSIGNED DEFAULT NULL,
  updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO organization_profile(id,site_name,government_name,opd_name,division_name,hero_title,hero_description,about_title,about_description,logo_path,favicon_path)
VALUES (1,'SIKOP Rembang','Pemerintah Kabupaten Rembang','Dinas Perdagangan, Koperasi dan UKM','Bidang Koperasi','Koperasi yang tumbuh dari data yang terjaga.','Ruang informasi dan kerja untuk mengenali koperasi, mendampingi pengurus, dan menguatkan ekonomi daerah dengan proses yang jelas.','Satu identitas koperasi. Banyak pekerjaan yang lebih ringan.','Sistem ini menyatukan data wilayah, profil koperasi, penilaian kesehatan, pengawasan, KDKMP, dan layanan. Setiap hasil resmi tetap melalui pemeriksaan dan kewenangan petugas.','assets/img/pemkab-rembang.png','favicon.ico');
INSERT IGNORE INTO modules(code,name,active) VALUES ('profil_dinas','Profil dinas',1);
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code) SELECT r.id,m.id,a.action_code FROM roles r JOIN modules m ON m.code='profil_dinas' JOIN (SELECT 'lihat' AS action_code UNION ALL SELECT 'ubah') a WHERE r.code IN ('superadmin','admin');
INSERT IGNORE INTO schema_migrations(version) VALUES ('012_profile');
