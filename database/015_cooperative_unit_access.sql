INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,'buat' FROM roles r JOIN modules m ON m.code='koperasi' WHERE r.code='koperasi';
INSERT IGNORE INTO schema_migrations(version) VALUES ('015_cooperative_unit_access');
