CREATE TABLE IF NOT EXISTS site_content (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  content_key VARCHAR(80) NOT NULL UNIQUE,
  content_json LONGTEXT NOT NULL,
  updated_by BIGINT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_site_content_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (code, description) VALUES
('dashboard.view', 'Ver dashboard administrativo'),
('bookings.view', 'Ver listado de reservas'),
('bookings.create', 'Crear reservas propias sin administrar precio ni operacion'),
('bookings.manage', 'Crear y editar reservas'),
('catalog.manage', 'Administrar catálogos'),
('pricing.manage', 'Administrar reglas de precios'),
('operations.view', 'Ver y actualizar agenda operativa'),
('accounting.view', 'Ver contabilidad'),
('kpis.view', 'Ver indicadores'),
('users.manage', 'Administrar usuarios y roles'),
('content.manage', 'Editar contenido del sitio')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO roles (code, name, created_at) VALUES
('admin', 'Administrator', NOW()),
('operator', 'Operator', NOW()),
('agency', 'Agencia externa', NOW()),
('sales', 'Sales', NOW()),
('accounting', 'Accounting', NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p
WHERE r.code = 'admin';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('dashboard.view', 'bookings.view', 'operations.view')
WHERE r.code = 'operator';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('dashboard.view', 'bookings.view', 'bookings.create')
WHERE r.code = 'agency';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('dashboard.view', 'bookings.view', 'bookings.create', 'bookings.manage', 'content.manage')
WHERE r.code = 'sales';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('dashboard.view', 'accounting.view', 'kpis.view')
WHERE r.code = 'accounting';

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
INNER JOIN roles r ON r.code = 'admin'
LEFT JOIN user_roles ur ON ur.user_id = u.id
WHERE ur.user_id IS NULL;
