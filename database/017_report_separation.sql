ALTER TABLE periodic_reports ADD COLUMN created_by BIGINT UNSIGNED DEFAULT NULL;
ALTER TABLE periodic_reports ADD COLUMN updated_by BIGINT UNSIGNED DEFAULT NULL;
ALTER TABLE periodic_reports ADD CONSTRAINT fk_report_creator FOREIGN KEY (created_by) REFERENCES users(id);
ALTER TABLE periodic_reports ADD CONSTRAINT fk_report_editor FOREIGN KEY (updated_by) REFERENCES users(id);
INSERT IGNORE INTO schema_migrations(version) VALUES ('017_report_separation');
