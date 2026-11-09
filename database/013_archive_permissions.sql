ALTER TABLE cooperatives ADD COLUMN archived_at DATETIME DEFAULT NULL;
ALTER TABLE kdkmp_sites ADD COLUMN archived_at DATETIME DEFAULT NULL;
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,'ubah' FROM roles r JOIN modules m ON m.code='koperasi' WHERE r.code='koperasi';
INSERT IGNORE INTO schema_migrations(version) VALUES ('013_archive_permissions');
