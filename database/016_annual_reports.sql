ALTER TABLE periodic_reports ADD COLUMN verified_by BIGINT UNSIGNED DEFAULT NULL;
ALTER TABLE periodic_reports ADD COLUMN review_note TEXT DEFAULT NULL;
ALTER TABLE periodic_reports ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE periodic_reports ADD CONSTRAINT fk_report_verifier FOREIGN KEY (verified_by) REFERENCES users(id);
INSERT IGNORE INTO modules(code,name,active) VALUES ('laporan_tahunan','Laporan tahunan koperasi',1);
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,a.action_code FROM roles r JOIN modules m ON m.code='laporan_tahunan'
JOIN (SELECT 'lihat' AS action_code UNION ALL SELECT 'buat' UNION ALL SELECT 'ubah' UNION ALL SELECT 'nonaktifkan' UNION ALL SELECT 'verifikasi' UNION ALL SELECT 'ekspor') a
WHERE r.code='superadmin';
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,a.action_code FROM roles r JOIN modules m ON m.code='laporan_tahunan'
JOIN (SELECT 'lihat' AS action_code UNION ALL SELECT 'buat' UNION ALL SELECT 'ubah' UNION ALL SELECT 'verifikasi') a
WHERE r.code='admin';
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,a.action_code FROM roles r JOIN modules m ON m.code='laporan_tahunan'
JOIN (SELECT 'lihat' AS action_code UNION ALL SELECT 'buat' UNION ALL SELECT 'ubah' UNION ALL SELECT 'nonaktifkan') a
WHERE r.code='koperasi';
INSERT IGNORE INTO schema_migrations(version) VALUES ('016_annual_reports');
