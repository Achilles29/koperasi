-- SIKOP Rembang · migration 003 · bentuk primer/sekunder dipisah dari lingkup kewenangan
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'primer','Primer',1 FROM master_definitions WHERE slug='bentuk-koperasi';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'sekunder','Sekunder',1 FROM master_definitions WHERE slug='bentuk-koperasi';

UPDATE cooperatives c
JOIN master_items old_item ON old_item.id=c.form_item_id
JOIN master_definitions d ON d.id=old_item.definition_id AND d.slug='bentuk-koperasi'
JOIN master_items new_item ON new_item.definition_id=d.id AND new_item.code='primer'
SET c.form_item_id=new_item.id
WHERE old_item.code IN ('primer_kabupaten','primer_provinsi','primer_nasional');

UPDATE cooperatives c
JOIN master_items old_item ON old_item.id=c.form_item_id
JOIN master_definitions d ON d.id=old_item.definition_id AND d.slug='bentuk-koperasi'
JOIN master_items new_item ON new_item.definition_id=d.id AND new_item.code='sekunder'
SET c.form_item_id=new_item.id
WHERE old_item.code='sekunder_kabupaten';

UPDATE master_items m
JOIN master_definitions d ON d.id=m.definition_id
SET m.active=0
WHERE d.slug='bentuk-koperasi' AND m.code IN ('primer_kabupaten','primer_provinsi','primer_nasional','sekunder_kabupaten');

INSERT IGNORE INTO schema_migrations(version) VALUES ('003_separate_form_scope');
