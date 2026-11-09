INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,'nonaktifkan' FROM roles r JOIN modules m ON m.code='penilaian' WHERE r.code='koperasi';
INSERT IGNORE INTO schema_migrations(version) VALUES ('014_draft_permissions');
