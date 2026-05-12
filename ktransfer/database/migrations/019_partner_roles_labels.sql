-- Nombres operativos y permisos base para usuarios externos/operadores.

INSERT INTO roles (code, name, created_at) VALUES
('admin', 'Administrador', NOW()),
('operator', 'Operador / chofer', NOW()),
('agency', 'Agencia / agente externo', NOW()),
('sales', 'Ventas / reservaciones', NOW()),
('accounting', 'Contabilidad', NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO permissions (code, description) VALUES
('dashboard.view', 'Ver dashboard administrativo'),
('bookings.view', 'Ver listado de reservas'),
('bookings.create', 'Crear reservas propias sin administrar precio ni operacion'),
('bookings.manage', 'Crear y editar reservas'),
('catalog.manage', 'Administrar catalogos, unidades y proveedores'),
('pricing.manage', 'Administrar reglas de precios'),
('operations.view', 'Ver y actualizar orden del dia'),
('accounting.view', 'Ver contabilidad'),
('kpis.view', 'Ver indicadores'),
('users.manage', 'Administrar usuarios y roles'),
('content.manage', 'Editar contenido del sitio')
ON DUPLICATE KEY UPDATE description = VALUES(description);

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

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code IN ('dashboard.view', 'bookings.view', 'operations.view')
WHERE r.code = 'operator';
