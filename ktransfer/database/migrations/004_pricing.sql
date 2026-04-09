-- Migración pricing
CREATE TABLE IF NOT EXISTS pax_ranges (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(30) NOT NULL,      -- "1-4", "5-7"
  min_pax INT NOT NULL,
  max_pax INT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_pax_range (min_pax, max_pax)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reglas de tarifa:
-- - para simplificar instalación: precio por vehicle opcional (NULL = cualquier vehículo)
-- - por zone destino (puedes extender a origen+destino después)
CREATE TABLE IF NOT EXISTS rate_rules (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  zone_id BIGINT NOT NULL,
  service_type_id BIGINT NOT NULL,
  pax_range_id BIGINT NOT NULL,
  vehicle_id BIGINT NULL,

  currency_code CHAR(3) NOT NULL,
  one_way_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  round_trip_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,

  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,

  CONSTRAINT fk_rates_zone FOREIGN KEY (zone_id) REFERENCES zones(id),
  CONSTRAINT fk_rates_service FOREIGN KEY (service_type_id) REFERENCES service_types(id),
  CONSTRAINT fk_rates_pax FOREIGN KEY (pax_range_id) REFERENCES pax_ranges(id),
  CONSTRAINT fk_rates_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
  CONSTRAINT fk_rates_currency FOREIGN KEY (currency_code) REFERENCES currencies(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_rate_lookup ON rate_rules(zone_id, service_type_id, pax_range_id, currency_code, is_active);