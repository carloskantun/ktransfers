-- Vincula usuarios de rol agencia con providers y extiende reservas para scope por agencia.
-- Idempotente para ambientes parcialmente migrados.

SET @add_users_provider_id_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'provider_id'
  ) = 0,
  'ALTER TABLE users ADD COLUMN provider_id BIGINT NULL AFTER password_hash',
  'SELECT 1'
);
PREPARE add_users_provider_id_stmt FROM @add_users_provider_id_sql;
EXECUTE add_users_provider_id_stmt;
DEALLOCATE PREPARE add_users_provider_id_stmt;

SET @add_users_provider_idx_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND index_name = 'idx_users_provider'
  ) = 0,
  'ALTER TABLE users ADD INDEX idx_users_provider (provider_id)',
  'SELECT 1'
);
PREPARE add_users_provider_idx_stmt FROM @add_users_provider_idx_sql;
EXECUTE add_users_provider_idx_stmt;
DEALLOCATE PREPARE add_users_provider_idx_stmt;

SET @add_users_provider_fk_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE constraint_schema = DATABASE()
      AND table_name = 'users'
      AND constraint_name = 'fk_users_provider'
      AND constraint_type = 'FOREIGN KEY'
  ) = 0,
  'ALTER TABLE users ADD CONSTRAINT fk_users_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE add_users_provider_fk_stmt FROM @add_users_provider_fk_sql;
EXECUTE add_users_provider_fk_stmt;
DEALLOCATE PREPARE add_users_provider_fk_stmt;

SET @add_bookings_agency_provider_id_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'bookings'
      AND column_name = 'agency_provider_id'
  ) = 0,
  'ALTER TABLE bookings ADD COLUMN agency_provider_id BIGINT NULL AFTER agency_name',
  'SELECT 1'
);
PREPARE add_bookings_agency_provider_id_stmt FROM @add_bookings_agency_provider_id_sql;
EXECUTE add_bookings_agency_provider_id_stmt;
DEALLOCATE PREPARE add_bookings_agency_provider_id_stmt;

SET @add_bookings_agency_provider_idx_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'bookings'
      AND index_name = 'idx_bookings_agency_provider'
  ) = 0,
  'ALTER TABLE bookings ADD INDEX idx_bookings_agency_provider (agency_provider_id)',
  'SELECT 1'
);
PREPARE add_bookings_agency_provider_idx_stmt FROM @add_bookings_agency_provider_idx_sql;
EXECUTE add_bookings_agency_provider_idx_stmt;
DEALLOCATE PREPARE add_bookings_agency_provider_idx_stmt;

SET @add_bookings_agency_provider_fk_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE constraint_schema = DATABASE()
      AND table_name = 'bookings'
      AND constraint_name = 'fk_bookings_agency_provider'
      AND constraint_type = 'FOREIGN KEY'
  ) = 0,
  'ALTER TABLE bookings ADD CONSTRAINT fk_bookings_agency_provider FOREIGN KEY (agency_provider_id) REFERENCES providers(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE add_bookings_agency_provider_fk_stmt FROM @add_bookings_agency_provider_fk_sql;
EXECUTE add_bookings_agency_provider_fk_stmt;
DEALLOCATE PREPARE add_bookings_agency_provider_fk_stmt;

-- Backfill usuarios agencia por nombre de usuario cuando coincide con proveedor.
UPDATE users u
INNER JOIN user_roles ur ON ur.user_id = u.id
INNER JOIN roles r ON r.id = ur.role_id
INNER JOIN providers p ON LOWER(TRIM(p.name)) = LOWER(TRIM(u.name))
SET u.provider_id = p.id
WHERE r.code = 'agency'
  AND u.provider_id IS NULL;

-- Backfill reservas de agencias usando provider vinculado del usuario creador.
UPDATE bookings b
INNER JOIN users u ON u.id = b.created_by_user_id
INNER JOIN user_roles ur ON ur.user_id = u.id
INNER JOIN roles r ON r.id = ur.role_id
LEFT JOIN providers p ON p.id = u.provider_id
SET
  b.agency_provider_id = u.provider_id,
  b.agency_name = COALESCE(NULLIF(TRIM(b.agency_name), ''), p.name)
WHERE r.code = 'agency'
  AND u.provider_id IS NOT NULL
  AND b.agency_provider_id IS NULL;

-- Backfill adicional por nombre de agencia cuando coincide con proveedor.
UPDATE bookings b
INNER JOIN providers p ON LOWER(TRIM(p.name)) = LOWER(TRIM(b.agency_name))
SET b.agency_provider_id = p.id
WHERE b.agency_provider_id IS NULL
  AND b.agency_name IS NOT NULL
  AND TRIM(b.agency_name) <> '';
