-- Migración operaciones y contabilidad
CREATE TABLE IF NOT EXISTS providers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(60) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asignación del servicio (interno o externo)
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

-- Orden de trabajo (lista operativa por día)
CREATE TABLE IF NOT EXISTS work_orders (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  work_date DATE NOT NULL,
  booking_id BIGINT NOT NULL UNIQUE,
  notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_wo_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_wo_date ON work_orders(work_date);

-- Contabilidad con proveedores (lo que debes pagarles o cobrarles)
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