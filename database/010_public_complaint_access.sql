-- Kode akses acak memungkinkan pelapor melihat tindak lanjut yang memang ditandai publik.
ALTER TABLE service_cases ADD COLUMN public_access_hash CHAR(64) DEFAULT NULL;
INSERT IGNORE INTO schema_migrations(version) VALUES ('010_public_complaint_access');
