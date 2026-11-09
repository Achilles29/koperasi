-- SIKOP Rembang · migration 006 · jejak progres KDKMP
CREATE TABLE IF NOT EXISTS kdkmp_progress_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  site_id BIGINT UNSIGNED NOT NULL,
  progress DECIMAL(5,2) DEFAULT NULL,
  operational_status VARCHAR(40) DEFAULT NULL,
  note TEXT NOT NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_progress_site (site_id,created_at),
  CONSTRAINT fk_progress_site FOREIGN KEY (site_id) REFERENCES kdkmp_sites(id),
  CONSTRAINT fk_progress_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations(version) VALUES ('006_kdkmp_progress');
