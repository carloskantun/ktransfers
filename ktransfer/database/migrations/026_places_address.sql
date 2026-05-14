-- Direccion opcional para lugares, util en Airbnb y puntos no conocidos.
-- Idempotente para instalaciones ya actualizadas parcialmente.

SET @add_places_address_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'places'
      AND column_name = 'address'
  ) = 0,
  'ALTER TABLE places ADD COLUMN address VARCHAR(255) NULL AFTER name',
  'SELECT 1'
);
PREPARE add_places_address_stmt FROM @add_places_address_sql;
EXECUTE add_places_address_stmt;
DEALLOCATE PREPARE add_places_address_stmt;
