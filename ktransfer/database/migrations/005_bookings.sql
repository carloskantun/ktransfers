-- Migración bookings
CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,

  booking_code VARCHAR(30) NOT NULL UNIQUE,

  trip_type ENUM('ONE_WAY','ROUND_TRIP') NOT NULL,
  direction ENUM('AIRPORT_TO_DESTINATION','DESTINATION_TO_AIRPORT') NOT NULL,

  service_type_id BIGINT NOT NULL,
  zone_id BIGINT NOT NULL,
  place_id BIGINT NOT NULL,

  currency_code CHAR(3) NOT NULL,
  price_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,

  status ENUM('PENDING','CONFIRMED','CANCELLED','NO_SHOW','COMPLETED') NOT NULL DEFAULT 'PENDING',
  payment_status ENUM('UNPAID','PARTIAL','PAID','REFUNDED') NOT NULL DEFAULT 'UNPAID',

  arrival_datetime DATETIME NULL,
  departure_datetime DATETIME NULL,

  airline VARCHAR(120) NULL,
  flight_number VARCHAR(40) NULL,

  pickup_notes VARCHAR(255) NULL,
  customer_name VARCHAR(120) NOT NULL,
  customer_last_name VARCHAR(120) NULL,
  customer_email VARCHAR(190) NOT NULL,
  customer_phone VARCHAR(60) NULL,

  comments TEXT NULL,

  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,

  CONSTRAINT fk_booking_service FOREIGN KEY (service_type_id) REFERENCES service_types(id),
  CONSTRAINT fk_booking_zone FOREIGN KEY (zone_id) REFERENCES zones(id),
  CONSTRAINT fk_booking_place FOREIGN KEY (place_id) REFERENCES places(id),
  CONSTRAINT fk_booking_currency FOREIGN KEY (currency_code) REFERENCES currencies(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  method ENUM('PAYPAL','CARD','BANK','CASH','MANUAL') NOT NULL,
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