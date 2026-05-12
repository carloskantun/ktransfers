-- Rol de agencias externas y propiedad de reservas creadas desde admin.
-- Idempotente para hostings donde la base pudo quedar parcialmente actualizada.

SET @add_created_by_user_id_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'bookings'
      AND column_name = 'created_by_user_id'
  ) = 0,
  'ALTER TABLE bookings ADD COLUMN created_by_user_id BIGINT NULL AFTER comments',
  'SELECT 1'
);
PREPARE add_created_by_user_id_stmt FROM @add_created_by_user_id_sql;
EXECUTE add_created_by_user_id_stmt;
DEALLOCATE PREPARE add_created_by_user_id_stmt;

SET @add_created_by_user_idx_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'bookings'
      AND index_name = 'idx_bookings_created_by_user'
  ) = 0,
  'ALTER TABLE bookings ADD INDEX idx_bookings_created_by_user (created_by_user_id)',
  'SELECT 1'
);
PREPARE add_created_by_user_idx_stmt FROM @add_created_by_user_idx_sql;
EXECUTE add_created_by_user_idx_stmt;
DEALLOCATE PREPARE add_created_by_user_idx_stmt;

SET @add_created_by_user_fk_sql := IF(
  (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE constraint_schema = DATABASE()
      AND table_name = 'bookings'
      AND constraint_name = 'fk_bookings_created_by_user'
      AND constraint_type = 'FOREIGN KEY'
  ) = 0,
  'ALTER TABLE bookings ADD CONSTRAINT fk_bookings_created_by_user FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE add_created_by_user_fk_stmt FROM @add_created_by_user_fk_sql;
EXECUTE add_created_by_user_fk_stmt;
DEALLOCATE PREPARE add_created_by_user_fk_stmt;

INSERT INTO permissions (code, description) VALUES
('bookings.create', 'Crear reservas propias sin administrar precio ni operacion')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO roles (code, name, created_at) VALUES
('agency', 'Agencia externa', NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code = 'bookings.create'
WHERE r.code IN ('admin', 'sales');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('dashboard.view', 'bookings.view', 'bookings.create')
WHERE r.code = 'agency';
