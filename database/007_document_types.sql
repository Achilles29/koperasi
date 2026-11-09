-- SIKOP Rembang · migration 007 · jenis bukti awal
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'akta','Akta dan badan hukum',1 FROM master_definitions WHERE slug='jenis-dokumen';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'ad_art','Anggaran Dasar/Anggaran Rumah Tangga',1 FROM master_definitions WHERE slug='jenis-dokumen';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'rat','Dokumen RAT',1 FROM master_definitions WHERE slug='jenis-dokumen';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'laporan_keuangan','Laporan keuangan',1 FROM master_definitions WHERE slug='jenis-dokumen';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'nib','Nomor Induk Berusaha',1 FROM master_definitions WHERE slug='jenis-dokumen';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'izin_usp','Izin USP/USPPS',1 FROM master_definitions WHERE slug='jenis-dokumen';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'bukti_lain','Bukti pendukung lain',1 FROM master_definitions WHERE slug='jenis-dokumen';
INSERT IGNORE INTO schema_migrations(version) VALUES ('007_document_types');
