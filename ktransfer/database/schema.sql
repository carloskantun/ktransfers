-- Esquema completo de la base de datos (MVP KTransfers)
-- Este archivo consolida la estructura base definida en migraciones 001..006.

CREATE TABLE IF NOT EXISTS migrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(190) NOT NULL UNIQUE,
  executed_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NULL,
  action VARCHAR(80) NOT NULL,
  entity VARCHAR(80) NOT NULL,
  entity_id BIGINT NULL,
  meta_json JSON NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  provider_id BIGINT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_users_provider ON users(provider_id);

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL UNIQUE,
  description VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id BIGINT NOT NULL,
  role_id BIGINT NOT NULL,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id BIGINT NOT NULL,
  permission_id BIGINT NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_role_perm_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_perm_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS airlines (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
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
  address VARCHAR(255) NULL,
  city VARCHAR(120) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_places_zone FOREIGN KEY (zone_id) REFERENCES zones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_places_zone ON places(zone_id);
CREATE INDEX idx_places_name ON places(name);

CREATE TABLE IF NOT EXISTS pax_ranges (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(30) NOT NULL,
  min_pax INT NOT NULL,
  max_pax INT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_pax_range (min_pax, max_pax)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_code VARCHAR(30) NOT NULL UNIQUE,
  trip_type ENUM('ONE_WAY','ROUND_TRIP') NOT NULL,
  operation_type ENUM('AIRPORT','INTERHOTEL') NOT NULL DEFAULT 'AIRPORT',
  direction ENUM('AIRPORT_TO_DESTINATION','DESTINATION_TO_AIRPORT') NOT NULL,
  service_type_id BIGINT NOT NULL,
  zone_id BIGINT NOT NULL,
  place_id BIGINT NOT NULL,
  origin_name VARCHAR(190) NULL,
  destination_name VARCHAR(190) NULL,
  currency_code CHAR(3) NOT NULL,
  price_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  agency_collected_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  agency_collected_at DATETIME NULL,
  status ENUM('PENDING','CONFIRMED','CANCELLED','NO_SHOW','COMPLETED') NOT NULL DEFAULT 'PENDING',
  payment_status ENUM('UNPAID','PARTIAL','PAID','REFUNDED') NOT NULL DEFAULT 'UNPAID',
  arrival_datetime DATETIME NULL,
  departure_datetime DATETIME NULL,
  airline VARCHAR(120) NULL,
  flight_number VARCHAR(40) NULL,
  terminal VARCHAR(60) NULL,
  pickup_notes VARCHAR(255) NULL,
  customer_name VARCHAR(120) NOT NULL,
  customer_last_name VARCHAR(120) NULL,
  customer_email VARCHAR(190) NOT NULL,
  customer_phone VARCHAR(60) NULL,
  agency_name VARCHAR(190) NULL,
  agency_provider_id BIGINT NULL,
  comments TEXT NULL,
  created_by_user_id BIGINT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_booking_service FOREIGN KEY (service_type_id) REFERENCES service_types(id),
  CONSTRAINT fk_booking_zone FOREIGN KEY (zone_id) REFERENCES zones(id),
  CONSTRAINT fk_booking_place FOREIGN KEY (place_id) REFERENCES places(id),
  CONSTRAINT fk_booking_currency FOREIGN KEY (currency_code) REFERENCES currencies(code),
  CONSTRAINT fk_bookings_created_by_user FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_bookings_created_by_user ON bookings(created_by_user_id);
CREATE INDEX idx_bookings_agency_provider ON bookings(agency_provider_id);

CREATE TABLE IF NOT EXISTS booking_passengers (
  booking_id BIGINT PRIMARY KEY,
  adults INT NOT NULL DEFAULT 1,
  children INT NOT NULL DEFAULT 0,
  total_pax INT NOT NULL,
  CONSTRAINT fk_bp_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_payments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NOT NULL,
  method ENUM('PAYPAL','CARD','BANK','CASH','MANUAL','MERCADO_PAGO') NOT NULL,
  status ENUM('PENDING','PAID','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  currency_code CHAR(3) NOT NULL,
  reference VARCHAR(190) NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_pay_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_pay_currency FOREIGN KEY (currency_code) REFERENCES currencies(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_status_history (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NOT NULL,
  old_status VARCHAR(30) NULL,
  new_status VARCHAR(30) NOT NULL,
  changed_by BIGINT NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_bsh_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_bsh_user FOREIGN KEY (changed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS providers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  contact_name VARCHAR(190) NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(60) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
  ADD CONSTRAINT fk_users_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE SET NULL;

ALTER TABLE bookings
  ADD CONSTRAINT fk_bookings_agency_provider FOREIGN KEY (agency_provider_id) REFERENCES providers(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS assignments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NOT NULL UNIQUE,
  mode ENUM('INTERNAL','PROVIDER') NOT NULL DEFAULT 'INTERNAL',
  provider_id BIGINT NULL,
  vehicle_id BIGINT NULL,
  operator_user_id BIGINT NULL,
  service_status ENUM('PENDING','ASSIGNED','IN_PROGRESS','DONE','NO_SHOW') NOT NULL DEFAULT 'PENDING',
  assigned_at DATETIME NULL,
  done_at DATETIME NULL,
  CONSTRAINT fk_asg_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_asg_provider FOREIGN KEY (provider_id) REFERENCES providers(id),
  CONSTRAINT fk_asg_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
  CONSTRAINT fk_asg_operator FOREIGN KEY (operator_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS work_orders (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  work_date DATE NOT NULL,
  booking_id BIGINT NOT NULL UNIQUE,
  notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_wo_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_wo_date ON work_orders(work_date);

CREATE TABLE IF NOT EXISTS provider_transactions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  provider_id BIGINT NOT NULL,
  booking_id BIGINT NULL,
  type ENUM('PAYABLE','RECEIVABLE','PAYMENT','CHARGE','ADJUSTMENT') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency_code CHAR(3) NOT NULL,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_pt_provider FOREIGN KEY (provider_id) REFERENCES providers(id),
  CONSTRAINT fk_pt_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
  CONSTRAINT fk_pt_currency FOREIGN KEY (currency_code) REFERENCES currencies(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_pt_provider ON provider_transactions(provider_id, created_at);
