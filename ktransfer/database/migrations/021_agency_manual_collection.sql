-- Campos para registrar cobro manual capturado por agencias externas.
-- Idempotente para instalaciones ya actualizadas parcialmente.

SET @add_agency_collected_total_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'bookings'
      AND column_name = 'agency_collected_total'
  ) = 0,
  'ALTER TABLE bookings ADD COLUMN agency_collected_total DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price_total',
  'SELECT 1'
);
PREPARE add_agency_collected_total_stmt FROM @add_agency_collected_total_sql;
EXECUTE add_agency_collected_total_stmt;
DEALLOCATE PREPARE add_agency_collected_total_stmt;

SET @add_agency_collected_at_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'bookings'
      AND column_name = 'agency_collected_at'
  ) = 0,
  'ALTER TABLE bookings ADD COLUMN agency_collected_at DATETIME NULL AFTER agency_collected_total',
  'SELECT 1'
);
PREPARE add_agency_collected_at_stmt FROM @add_agency_collected_at_sql;
EXECUTE add_agency_collected_at_stmt;
DEALLOCATE PREPARE add_agency_collected_at_stmt;
