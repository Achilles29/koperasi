-- SIKOP Rembang · baseline 001 · MariaDB 10.11 / utf8mb4
-- Semua tabel transaksional memakai ID internal; kode luar tetap sebagai teks.
CREATE TABLE IF NOT EXISTS schema_migrations (
  version VARCHAR(40) PRIMARY KEY, applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS source_versions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kind VARCHAR(40) NOT NULL,
  title VARCHAR(255) NOT NULL,
  reference_no VARCHAR(120) DEFAULT NULL,
  source_url TEXT DEFAULT NULL,
  file_hash CHAR(64) DEFAULT NULL,
  effective_from DATE DEFAULT NULL,
  imported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_source_kind_ref (kind, reference_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS regions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(24) NOT NULL,
  name VARCHAR(160) NOT NULL,
  level ENUM('province','regency','district','village') NOT NULL,
  village_type ENUM('desa','kelurahan') DEFAULT NULL,
  parent_id BIGINT UNSIGNED DEFAULT NULL,
  source_version_id BIGINT UNSIGNED DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  effective_from DATE DEFAULT NULL,
  effective_to DATE DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_region_code (code),
  KEY ix_region_parent (parent_id,active,name),
  KEY ix_region_level (level,active,name),
  CONSTRAINT fk_region_parent FOREIGN KEY (parent_id) REFERENCES regions(id),
  CONSTRAINT fk_region_source FOREIGN KEY (source_version_id) REFERENCES source_versions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS region_aliases (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  region_id BIGINT UNSIGNED NOT NULL,
  alias_name VARCHAR(160) NOT NULL,
  source_name VARCHAR(60) NOT NULL,
  UNIQUE KEY uq_region_alias (region_id,alias_name,source_name),
  CONSTRAINT fk_alias_region FOREIGN KEY (region_id) REFERENCES regions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS master_definitions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  title VARCHAR(120) NOT NULL,
  category VARCHAR(60) NOT NULL,
  source_policy ENUM('official','local','mixed') NOT NULL DEFAULT 'local',
  fields_json LONGTEXT DEFAULT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS master_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  definition_id INT UNSIGNED NOT NULL,
  code VARCHAR(100) NOT NULL,
  name VARCHAR(220) NOT NULL,
  description TEXT DEFAULT NULL,
  parent_item_id BIGINT UNSIGNED DEFAULT NULL,
  metadata_json LONGTEXT DEFAULT NULL,
  source_version_id BIGINT UNSIGNED DEFAULT NULL,
  effective_from DATE DEFAULT NULL,
  effective_to DATE DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_master_code (definition_id,code),
  KEY ix_master_lookup (definition_id,active,name),
  CONSTRAINT fk_item_definition FOREIGN KEY (definition_id) REFERENCES master_definitions(id),
  CONSTRAINT fk_item_parent FOREIGN KEY (parent_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_item_source FOREIGN KEY (source_version_id) REFERENCES source_versions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id INT UNSIGNED NOT NULL,
  module_id INT UNSIGNED NOT NULL,
  action_code VARCHAR(40) NOT NULL,
  PRIMARY KEY (role_id,module_id,action_code),
  CONSTRAINT fk_permission_role FOREIGN KEY (role_id) REFERENCES roles(id),
  CONSTRAINT fk_permission_module FOREIGN KEY (module_id) REFERENCES modules(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  full_name VARCHAR(160) NOT NULL,
  email VARCHAR(190) DEFAULT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  cooperative_id BIGINT UNSIGNED DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id,role_id),
  CONSTRAINT fk_user_role_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_user_role_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS import_batches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_kind VARCHAR(60) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  sha256 CHAR(64) NOT NULL,
  imported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_rows INT UNSIGNED NOT NULL DEFAULT 0,
  inserted_rows INT UNSIGNED NOT NULL DEFAULT 0,
  updated_rows INT UNSIGNED NOT NULL DEFAULT 0,
  skipped_rows INT UNSIGNED NOT NULL DEFAULT 0,
  notes TEXT DEFAULT NULL,
  UNIQUE KEY uq_import_file (source_kind,sha256)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cooperatives (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nik VARCHAR(40) DEFAULT NULL UNIQUE,
  legal_number VARCHAR(190) DEFAULT NULL,
  name VARCHAR(255) NOT NULL,
  name_normalized VARCHAR(255) NOT NULL,
  type_item_id BIGINT UNSIGNED DEFAULT NULL,
  form_item_id BIGINT UNSIGNED DEFAULT NULL,
  pattern_item_id BIGINT UNSIGNED DEFAULT NULL,
  group_item_id BIGINT UNSIGNED DEFAULT NULL,
  sector_item_id BIGINT UNSIGNED DEFAULT NULL,
  kuk_item_id BIGINT UNSIGNED DEFAULT NULL,
  grade VARCHAR(20) DEFAULT NULL,
  ods_status VARCHAR(60) DEFAULT NULL,
  ods_account_status VARCHAR(120) DEFAULT NULL,
  authority_scope ENUM('kabupaten','provinsi','nasional','belum_ditetapkan') NOT NULL DEFAULT 'belum_ditetapkan',
  reference_only TINYINT(1) NOT NULL DEFAULT 0,
  province_id BIGINT UNSIGNED DEFAULT NULL,
  regency_id BIGINT UNSIGNED DEFAULT NULL,
  district_id BIGINT UNSIGNED DEFAULT NULL,
  village_id BIGINT UNSIGNED DEFAULT NULL,
  ods_village_name VARCHAR(160) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  postal_code VARCHAR(20) DEFAULT NULL,
  email VARCHAR(190) DEFAULT NULL,
  latitude DECIMAL(10,7) DEFAULT NULL,
  longitude DECIMAL(10,7) DEFAULT NULL,
  source_batch_id BIGINT UNSIGNED DEFAULT NULL,
  source_updated_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_coop_name (name_normalized),
  KEY ix_coop_scope (authority_scope,reference_only,ods_status),
  KEY ix_coop_region (district_id,village_id),
  KEY ix_coop_type (type_item_id,pattern_item_id,kuk_item_id),
  CONSTRAINT fk_coop_type FOREIGN KEY (type_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_coop_form FOREIGN KEY (form_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_coop_pattern FOREIGN KEY (pattern_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_coop_group FOREIGN KEY (group_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_coop_sector FOREIGN KEY (sector_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_coop_kuk FOREIGN KEY (kuk_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_coop_province FOREIGN KEY (province_id) REFERENCES regions(id),
  CONSTRAINT fk_coop_regency FOREIGN KEY (regency_id) REFERENCES regions(id),
  CONSTRAINT fk_coop_district FOREIGN KEY (district_id) REFERENCES regions(id),
  CONSTRAINT fk_coop_village FOREIGN KEY (village_id) REFERENCES regions(id),
  CONSTRAINT fk_coop_batch FOREIGN KEY (source_batch_id) REFERENCES import_batches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kunci relasi pengguna ke koperasi ditambahkan setelah tabel koperasi tersedia.
SET @fk_user_coop_sql = IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
   WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND CONSTRAINT_NAME = 'fk_user_cooperative') = 0,
  'ALTER TABLE users ADD CONSTRAINT fk_user_cooperative FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id)',
  'SELECT 1'
);
PREPARE fk_user_coop_stmt FROM @fk_user_coop_sql;
EXECUTE fk_user_coop_stmt;
DEALLOCATE PREPARE fk_user_coop_stmt;

CREATE TABLE IF NOT EXISTS import_rows (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  batch_id BIGINT UNSIGNED NOT NULL,
  source_row_number INT UNSIGNED NOT NULL,
  source_key VARCHAR(120) DEFAULT NULL,
  cooperative_id BIGINT UNSIGNED DEFAULT NULL,
  result_code ENUM('inserted','updated','unchanged','needs_review','error') NOT NULL,
  message VARCHAR(500) DEFAULT NULL,
  raw_json LONGTEXT NOT NULL,
  UNIQUE KEY uq_import_row (batch_id,source_row_number),
  CONSTRAINT fk_importrow_batch FOREIGN KEY (batch_id) REFERENCES import_batches(id),
  CONSTRAINT fk_importrow_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cooperative_units (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cooperative_id BIGINT UNSIGNED NOT NULL,
  unit_type_item_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(220) NOT NULL,
  license_number VARCHAR(190) DEFAULT NULL,
  region_id BIGINT UNSIGNED DEFAULT NULL,
  address TEXT DEFAULT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_unit_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
  CONSTRAINT fk_unit_type FOREIGN KEY (unit_type_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_unit_region FOREIGN KEY (region_id) REFERENCES regions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS periodic_reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cooperative_id BIGINT UNSIGNED NOT NULL,
  fiscal_year SMALLINT UNSIGNED NOT NULL,
  rat_date DATE DEFAULT NULL,
  members_count INT UNSIGNED DEFAULT NULL,
  assets DECIMAL(20,2) DEFAULT NULL,
  equity DECIMAL(20,2) DEFAULT NULL,
  business_volume DECIMAL(20,2) DEFAULT NULL,
  shu DECIMAL(20,2) DEFAULT NULL,
  verification_status VARCHAR(40) NOT NULL DEFAULT 'draft',
  submitted_at DATETIME DEFAULT NULL,
  verified_at DATETIME DEFAULT NULL,
  UNIQUE KEY uq_report_period (cooperative_id,fiscal_year),
  CONSTRAINT fk_report_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cooperative_id BIGINT UNSIGNED DEFAULT NULL,
  owner_type VARCHAR(50) NOT NULL,
  owner_id BIGINT UNSIGNED DEFAULT NULL,
  document_type_item_id BIGINT UNSIGNED DEFAULT NULL,
  fiscal_year SMALLINT UNSIGNED DEFAULT NULL,
  original_name VARCHAR(255) NOT NULL,
  storage_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  byte_size BIGINT UNSIGNED NOT NULL,
  sha256 CHAR(64) NOT NULL,
  visibility ENUM('private','internal','public') NOT NULL DEFAULT 'private',
  verification_status VARCHAR(40) NOT NULL DEFAULT 'pending',
  uploaded_by BIGINT UNSIGNED DEFAULT NULL,
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_document_owner (owner_type,owner_id),
  CONSTRAINT fk_doc_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
  CONSTRAINT fk_doc_type FOREIGN KEY (document_type_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_doc_user FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cooperative_id BIGINT UNSIGNED NOT NULL,
  unit_id BIGINT UNSIGNED DEFAULT NULL,
  fiscal_year SMALLINT UNSIGNED NOT NULL,
  form_item_id BIGINT UNSIGNED NOT NULL,
  form_version VARCHAR(40) NOT NULL,
  revision_no SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  supersedes_id BIGINT UNSIGNED DEFAULT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'draft',
  self_score DECIMAL(8,3) DEFAULT NULL,
  official_score DECIMAL(8,3) DEFAULT NULL,
  official_predicate_item_id BIGINT UNSIGNED DEFAULT NULL,
  locked_at DATETIME DEFAULT NULL,
  submitted_at DATETIME DEFAULT NULL,
  reviewed_at DATETIME DEFAULT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_assessment_revision (cooperative_id,unit_id,fiscal_year,form_item_id,revision_no),
  KEY ix_assessment_status (status,fiscal_year),
  CONSTRAINT fk_assessment_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
  CONSTRAINT fk_assessment_unit FOREIGN KEY (unit_id) REFERENCES cooperative_units(id),
  CONSTRAINT fk_assessment_form FOREIGN KEY (form_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_assessment_prev FOREIGN KEY (supersedes_id) REFERENCES assessments(id),
  CONSTRAINT fk_assessment_pred FOREIGN KEY (official_predicate_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_assessment_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessment_answers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  indicator_code VARCHAR(80) NOT NULL,
  answer_json LONGTEXT DEFAULT NULL,
  self_value DECIMAL(18,6) DEFAULT NULL,
  verified_value DECIMAL(18,6) DEFAULT NULL,
  evidence_document_id BIGINT UNSIGNED DEFAULT NULL,
  reviewer_note TEXT DEFAULT NULL,
  UNIQUE KEY uq_answer_indicator (assessment_id,indicator_code),
  CONSTRAINT fk_answer_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(id),
  CONSTRAINT fk_answer_document FOREIGN KEY (evidence_document_id) REFERENCES documents(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspections (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cooperative_id BIGINT UNSIGNED NOT NULL,
  assessment_id BIGINT UNSIGNED DEFAULT NULL,
  inspection_type ENUM('routine','insidental') NOT NULL DEFAULT 'routine',
  assignment_number VARCHAR(120) DEFAULT NULL,
  lead_user_id BIGINT UNSIGNED DEFAULT NULL,
  started_on DATE DEFAULT NULL,
  ended_on DATE DEFAULT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'planned',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_inspection_coop (cooperative_id,status),
  CONSTRAINT fk_inspection_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
  CONSTRAINT fk_inspection_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(id),
  CONSTRAINT fk_inspection_lead FOREIGN KEY (lead_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS findings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspection_id BIGINT UNSIGNED NOT NULL,
  indicator_code VARCHAR(80) DEFAULT NULL,
  aspect_code VARCHAR(80) NOT NULL,
  fact TEXT NOT NULL,
  legal_basis TEXT DEFAULT NULL,
  recommendation TEXT DEFAULT NULL,
  cooperative_response TEXT DEFAULT NULL,
  due_on DATE DEFAULT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'open',
  CONSTRAINT fk_finding_inspection FOREIGN KEY (inspection_id) REFERENCES inspections(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS follow_up_actions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  finding_id BIGINT UNSIGNED NOT NULL,
  owner_user_id BIGINT UNSIGNED DEFAULT NULL,
  action_text TEXT NOT NULL,
  evidence_document_id BIGINT UNSIGNED DEFAULT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'open',
  verified_by BIGINT UNSIGNED DEFAULT NULL,
  verified_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_action_finding FOREIGN KEY (finding_id) REFERENCES findings(id),
  CONSTRAINT fk_action_owner FOREIGN KEY (owner_user_id) REFERENCES users(id),
  CONSTRAINT fk_action_document FOREIGN KEY (evidence_document_id) REFERENCES documents(id),
  CONSTRAINT fk_action_verifier FOREIGN KEY (verified_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS official_outputs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspection_id BIGINT UNSIGNED NOT NULL,
  output_type ENUM('BAPK','LHPKK','SERTIFIKAT','SANKSI','ADDENDUM') NOT NULL,
  document_number VARCHAR(160) DEFAULT NULL,
  revision_no SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  status VARCHAR(40) NOT NULL DEFAULT 'draft',
  document_id BIGINT UNSIGNED DEFAULT NULL,
  issued_by BIGINT UNSIGNED DEFAULT NULL,
  issued_at DATETIME DEFAULT NULL,
  verification_code CHAR(36) DEFAULT NULL UNIQUE,
  CONSTRAINT fk_output_inspection FOREIGN KEY (inspection_id) REFERENCES inspections(id),
  CONSTRAINT fk_output_document FOREIGN KEY (document_id) REFERENCES documents(id),
  CONSTRAINT fk_output_issuer FOREIGN KEY (issued_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_cases (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_no VARCHAR(40) NOT NULL UNIQUE,
  case_type ENUM('complaint','consultation','license_assistance','coaching') NOT NULL,
  cooperative_id BIGINT UNSIGNED DEFAULT NULL,
  category_item_id BIGINT UNSIGNED DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  reporter_name VARCHAR(160) DEFAULT NULL,
  reporter_contact VARCHAR(190) DEFAULT NULL,
  assigned_user_id BIGINT UNSIGNED DEFAULT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  due_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at DATETIME DEFAULT NULL,
  KEY ix_case_work (case_type,status,due_at),
  CONSTRAINT fk_case_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
  CONSTRAINT fk_case_category FOREIGN KEY (category_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_case_assignee FOREIGN KEY (assigned_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS case_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  case_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED DEFAULT NULL,
  event_type VARCHAR(50) NOT NULL,
  note TEXT DEFAULT NULL,
  visibility ENUM('internal','reporter') NOT NULL DEFAULT 'internal',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_case FOREIGN KEY (case_id) REFERENCES service_cases(id),
  CONSTRAINT fk_event_user FOREIGN KEY (actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kdkmp_sites (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cooperative_id BIGINT UNSIGNED NOT NULL,
  village_id BIGINT UNSIGNED NOT NULL,
  location_name VARCHAR(220) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  latitude DECIMAL(10,7) DEFAULT NULL,
  longitude DECIMAL(10,7) DEFAULT NULL,
  physical_progress DECIMAL(5,2) DEFAULT NULL,
  verification_status VARCHAR(40) NOT NULL DEFAULT 'pending',
  verified_at DATETIME DEFAULT NULL,
  operational_status VARCHAR(40) DEFAULT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kdkmp_coop_village (cooperative_id,village_id),
  CONSTRAINT fk_site_coop FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
  CONSTRAINT fk_site_village FOREIGN KEY (village_id) REFERENCES regions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kdkmp_commodities (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  site_id BIGINT UNSIGNED NOT NULL,
  commodity_item_id BIGINT UNSIGNED NOT NULL,
  unit_item_id BIGINT UNSIGNED DEFAULT NULL,
  report_period DATE NOT NULL,
  capacity DECIMAL(18,3) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  UNIQUE KEY uq_site_commodity_period (site_id,commodity_item_id,report_period),
  CONSTRAINT fk_commodity_site FOREIGN KEY (site_id) REFERENCES kdkmp_sites(id),
  CONSTRAINT fk_commodity_item FOREIGN KEY (commodity_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_commodity_unit FOREIGN KEY (unit_item_id) REFERENCES master_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED DEFAULT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED DEFAULT NULL,
  action_code VARCHAR(50) NOT NULL,
  before_json LONGTEXT DEFAULT NULL,
  after_json LONGTEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_audit_entity (entity_type,entity_id,occurred_at),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_migrations(version) VALUES ('001_initial');
