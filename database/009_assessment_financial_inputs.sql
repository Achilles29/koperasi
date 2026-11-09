-- Isian keuangan mengikuti sel input tiap versi workbook; nilai contoh tidak diimpor.
CREATE TABLE IF NOT EXISTS assessment_financial_fields (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_version_id BIGINT UNSIGNED NOT NULL,
  field_code VARCHAR(40) NOT NULL,
  account_code VARCHAR(40) NOT NULL,
  account_name VARCHAR(255) NOT NULL,
  year_offset SMALLINT NOT NULL,
  source_sheet VARCHAR(40) NOT NULL DEFAULT 'Input',
  source_row INT UNSIGNED NOT NULL,
  source_column CHAR(1) NOT NULL,
  guidance TEXT DEFAULT NULL,
  sort_order INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_financial_field (form_version_id,field_code),
  KEY ix_financial_fields_order (form_version_id,sort_order),
  CONSTRAINT fk_financial_field_version FOREIGN KEY (form_version_id) REFERENCES assessment_form_versions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS assessment_financial_answers (
  assessment_id BIGINT UNSIGNED NOT NULL,
  field_code VARCHAR(40) NOT NULL,
  amount DECIMAL(20,2) NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (assessment_id,field_code),
  CONSTRAINT fk_financial_answer_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO schema_migrations(version) VALUES ('009_assessment_financial_inputs');
