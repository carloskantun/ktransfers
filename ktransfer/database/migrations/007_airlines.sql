-- Migración 007: Airlines (Aerolíneas)

CREATE TABLE IF NOT EXISTS airlines (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE COMMENT 'Código IATA de aerolínea (ej: AA, DL, AM)',
  name VARCHAR(120) NOT NULL COMMENT 'Nombre de la aerolínea',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar aerolíneas principales
INSERT INTO airlines (code, name, is_active, created_at) VALUES
('AA', 'American Airlines', 1, NOW()),
('DL', 'Delta Air Lines', 1, NOW()),
('UA', 'United Airlines', 1, NOW()),
('AM', 'Aeroméxico', 1, NOW()),
('Y4', 'Volaris', 1, NOW()),
('VB', 'VivaAerobus', 1, NOW()),
('AC', 'Air Canada', 1, NOW()),
('WS', 'WestJet', 1, NOW()),
('F9', 'Frontier Airlines', 1, NOW()),
('NK', 'Spirit Airlines', 1, NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), is_active = VALUES(is_active);
