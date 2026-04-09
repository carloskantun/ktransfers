-- Migración catálogo
CREATE TABLE IF NOT EXISTS currencies (
  code CHAR(3) PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  symbol VARCHAR(10) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO currencies(code,name,symbol,is_active) VALUES
('USD','US Dollar','$',1),
('MXN','Mexican Peso','$',1),
('CAD','Canadian Dollar','$',1),
('EUR','Euro','€',1)
ON DUPLICATE KEY UPDATE name=VALUES(name), symbol=VALUES(symbol), is_active=VALUES(is_active);

CREATE TABLE IF NOT EXISTS service_types (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  name_es VARCHAR(120) NOT NULL,
  name_en VARCHAR(120) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vehicles (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  max_pax INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS zones (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL UNIQUE,
  name_es VARCHAR(120) NOT NULL,
  name_en VARCHAR(120) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS places (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  zone_id BIGINT NOT NULL,
  type ENUM('HOTEL','AIRBNB','POINT') NOT NULL DEFAULT 'HOTEL',
  name VARCHAR(190) NOT NULL,
  city VARCHAR(120) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_places_zone FOREIGN KEY (zone_id) REFERENCES zones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_places_zone ON places(zone_id);
CREATE INDEX idx_places_name ON places(name);