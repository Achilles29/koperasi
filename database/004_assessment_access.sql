-- SIKOP Rembang · migration 004 · hak pengisian KKPKK dan jenis unit
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'usp','Unit Simpan Pinjam (USP)',1 FROM master_definitions WHERE slug='jenis-unit';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'uspps','Unit Simpan Pinjam dan Pembiayaan Syariah (USPPS)',1 FROM master_definitions WHERE slug='jenis-unit';
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,'lihat' FROM roles r JOIN modules m ON m.code='penilaian' WHERE r.code='koperasi';
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,'buat' FROM roles r JOIN modules m ON m.code='penilaian' WHERE r.code='koperasi';
INSERT IGNORE INTO role_permissions(role_id,module_id,action_code)
SELECT r.id,m.id,'ubah' FROM roles r JOIN modules m ON m.code='penilaian' WHERE r.code='koperasi';
INSERT IGNORE INTO schema_migrations(version) VALUES ('004_assessment_access');
