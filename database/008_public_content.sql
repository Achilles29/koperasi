-- Konten publik terhubung ke master kategori yang dapat dikelola lewat CRUD.
CREATE TABLE IF NOT EXISTS content_entries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_item_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL,
  summary TEXT DEFAULT NULL,
  body LONGTEXT DEFAULT NULL,
  external_url VARCHAR(1000) DEFAULT NULL,
  image_path VARCHAR(255) DEFAULT NULL,
  event_on DATE DEFAULT NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  published_at DATETIME DEFAULT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  updated_by BIGINT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_content_slug (slug),
  KEY ix_content_status (status,published_at,id),
  KEY ix_content_category (category_item_id,status),
  CONSTRAINT fk_content_category FOREIGN KEY (category_item_id) REFERENCES master_items(id),
  CONSTRAINT fk_content_creator FOREIGN KEY (created_by) REFERENCES users(id),
  CONSTRAINT fk_content_updater FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'galeri','Galeri kegiatan',1 FROM master_definitions WHERE slug='kategori-konten';
INSERT IGNORE INTO master_items(definition_id,code,name,active)
SELECT id,'sosial','Kanal sosial',1 FROM master_definitions WHERE slug='kategori-konten';
INSERT IGNORE INTO schema_migrations(version) VALUES ('008_public_content');
