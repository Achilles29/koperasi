-- SIKOP Rembang · migration 005 · lokasi KDKMP awal tanpa klaim progres fisik
INSERT IGNORE INTO kdkmp_sites(cooperative_id,village_id,verification_status)
SELECT c.id,c.village_id,'pending'
FROM cooperatives c JOIN master_items m ON m.id=c.type_item_id
JOIN master_definitions d ON d.id=m.definition_id
WHERE d.slug='jenis-koperasi' AND m.code='kdkmp' AND c.village_id IS NOT NULL;
INSERT IGNORE INTO schema_migrations(version) VALUES ('005_kdkmp_sites');
