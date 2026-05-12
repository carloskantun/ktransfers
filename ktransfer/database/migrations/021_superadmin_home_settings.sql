INSERT INTO permissions (code, description)
VALUES ('home.manage', 'Administrar configuracion de home/branding')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO roles (code, name, created_at)
VALUES ('superadmin', 'Super Administrator', NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.code = 'home.manage'
WHERE r.code = 'superadmin';

DELETE rp
FROM role_permissions rp
INNER JOIN roles r ON r.id = rp.role_id
INNER JOIN permissions p ON p.id = rp.permission_id
WHERE r.code IN ('admin', 'sales')
  AND p.code IN ('home.manage', 'content.manage');
