-- SIKOP Rembang · migration 002 · definisi formulir kesehatan berversi
-- Tidak mengaktifkan perhitungan/penetapan; hanya menyiapkan kunci dan struktur aturan.
ALTER TABLE assessments
  ADD COLUMN target_unit_key BIGINT UNSIGNED AS (IFNULL(unit_id,0)) PERSISTENT,
  DROP INDEX uq_assessment_revision,
  ADD UNIQUE KEY uq_assessment_revision (cooperative_id,target_unit_key,fiscal_year,form_item_id,revision_no);

CREATE TABLE IF NOT EXISTS assessment_form_versions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_item_id BIGINT UNSIGNED NOT NULL,
  version_code VARCHAR(40) NOT NULL,
  title VARCHAR(220) NOT NULL,
  legal_basis_item_id BIGINT UNSIGNED DEFAULT NULL,
  source_file_name VARCHAR(255) DEFAULT NULL,
  source_sha256 CHAR(64) DEFAULT NULL,
  effective_from DATE DEFAULT NULL,
  effective_to DATE DEFAULT NULL,
  status ENUM('draft','review','approved','retired') NOT NULL DEFAULT 'draft',
  approved_by BIGINT UNSIGNED DEFAULT NULL,
  approved_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_form_version (form_item_id,version_code),
  CONSTRAINT fk_formversion_item FOREIGN KEY (form_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_formversion_law FOREIGN KEY (legal_basis_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_formversion_approver FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_indicators (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_version_id BIGINT UNSIGNED NOT NULL,
  indicator_code VARCHAR(80) NOT NULL,
  parent_indicator_id BIGINT UNSIGNED DEFAULT NULL,
  aspect_item_id BIGINT UNSIGNED DEFAULT NULL,
  prompt TEXT NOT NULL,
  answer_type VARCHAR(40) NOT NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 1,
  weight DECIMAL(10,6) DEFAULT NULL,
  condition_json LONGTEXT DEFAULT NULL,
  scoring_json LONGTEXT DEFAULT NULL,
  guidance TEXT DEFAULT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 100,
  UNIQUE KEY uq_indicator_version (form_version_id,indicator_code),
  KEY ix_indicator_order (form_version_id,sort_order),
  CONSTRAINT fk_indicator_version FOREIGN KEY (form_version_id) REFERENCES assessment_form_versions(id),
  CONSTRAINT fk_indicator_parent FOREIGN KEY (parent_indicator_id) REFERENCES assessment_indicators(id),
  CONSTRAINT fk_indicator_aspect FOREIGN KEY (aspect_item_id) REFERENCES master_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_form_snapshots (
  assessment_id BIGINT UNSIGNED PRIMARY KEY,
  form_version_id BIGINT UNSIGNED NOT NULL,
  snapshot_json LONGTEXT NOT NULL,
  snapshot_sha256 CHAR(64) NOT NULL,
  frozen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_snapshot_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(id),
  CONSTRAINT fk_snapshot_formversion FOREIGN KEY (form_version_id) REFERENCES assessment_form_versions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations(version) VALUES ('002_assessment_rules');
