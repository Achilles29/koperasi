-- Admin operasional dapat memverifikasi penilaian, pengawasan, dan bukti KDKMP.
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,'verifikasi' FROM roles r JOIN modules m ON m.code IN ('penilaian','pengawasan','kdkmp')
WHERE r.code='admin';
INSERT IGNORE INTO schema_migrations(version) VALUES ('011_admin_verification');
