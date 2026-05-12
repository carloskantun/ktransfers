-- Auditoria de ediciones en reservas, solicitudes de borrado y ajustes RBAC.

CREATE TABLE IF NOT EXISTS booking_edit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NOT NULL,
  changed_by_user_id BIGINT NULL,
  actor_role_code VARCHAR(60) NOT NULL,
  old_snapshot_json LONGTEXT NOT NULL,
  new_snapshot_json LONGTEXT NOT NULL,
  changed_fields_json LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_booking_edit_logs_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  CONSTRAINT fk_booking_edit_logs_user FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_booking_edit_logs_booking_created (booking_id, created_at),
  INDEX idx_booking_edit_logs_actor (actor_role_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_delete_requests (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NULL,
  booking_code VARCHAR(30) NULL,
  requested_by_user_id BIGINT NULL,
  reason VARCHAR(255) NULL,
  status ENUM('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  reviewed_by_user_id BIGINT NULL,
  review_note VARCHAR(255) NULL,
  reviewed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_booking_delete_requests_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
  CONSTRAINT fk_booking_delete_requests_requested_by FOREIGN KEY (requested_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_booking_delete_requests_reviewed_by FOREIGN KEY (reviewed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_booking_delete_requests_booking_status (booking_id, status),
  INDEX idx_booking_delete_requests_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  type VARCHAR(80) NOT NULL,
  booking_id BIGINT NULL,
  payload_json LONGTEXT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  read_at DATETIME NULL,
  CONSTRAINT fk_admin_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_admin_notifications_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
  INDEX idx_admin_notifications_user_read (user_id, is_read, created_at),
  INDEX idx_admin_notifications_type (type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permisos para borrar con flujo de solicitud/aprobacion.
INSERT INTO permissions (code, description) VALUES
('bookings.delete.request', 'Solicitar borrado de reservas'),
('bookings.delete.approve', 'Aprobar borrado de reservas')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Admin y superadmin: acceso completo.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p
WHERE r.code IN ('admin', 'superadmin');

-- Ventas: ver reservas, orden del dia y catalogo.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('bookings.view', 'operations.view', 'catalog.manage')
WHERE r.code = 'sales';

-- Contabilidad: acceso a casi todo excepto pricing, home/content y usuarios.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('dashboard.view', 'bookings.view', 'operations.view', 'catalog.manage', 'accounting.view', 'kpis.view')
WHERE r.code = 'accounting';

DELETE rp
FROM role_permissions rp
INNER JOIN roles r ON r.id = rp.role_id
INNER JOIN permissions p ON p.id = rp.permission_id
WHERE r.code = 'accounting'
  AND p.code IN ('pricing.manage', 'home.manage', 'content.manage', 'users.manage', 'bookings.manage');

-- Solicitudes de borrado: pueden hacerlas agencias, ventas, admin y superadmin.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code = 'bookings.delete.request'
WHERE r.code IN ('agency', 'sales', 'admin', 'superadmin');

-- Solo admin/superadmin aprueban borrado definitivo.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code = 'bookings.delete.approve'
WHERE r.code IN ('admin', 'superadmin');
