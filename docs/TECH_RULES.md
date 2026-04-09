# Tech Rules (no romper la estructura)

Este documento define reglas para NO degradar el proyecto hacia un “spaghetti admin”
como el legacy.

## 1) Prohibido
- Laravel, Symfony, CodeIgniter u otros frameworks
- Composer como requisito de instalación
- Mezclar HTML con SQL dentro de la misma vista sin controladores
- `mysqli_*` (solo PDO)
- Queries sin parámetros (SQL injection)

## 2) Versiones
- PHP: 8.1+ (ideal 8.2)
- MySQL/MariaDB compatible con utf8mb4

## 3) Rutas y Front Controller
- Solo `public_html/index.php` recibe la web
- Todo request debe pasar por Router
- `.htaccess` o config equivalente debe mandar a index.php

## 4) Estructura obligatoria
- Código privado: `ktransfer/`
- Público: `public_html/`
- Views: `ktransfer/app/Views/Pages`
- Layouts: `ktransfer/app/Views/Layouts`
- Core: `ktransfer/app/Core`

No se aceptan nuevas páginas sueltas en la raíz tipo `booking.php`.

## 5) Base de datos
- Acceso: `ktransfer/app/Core/DB.php`
- No se permite conexión DB repetida por archivo
- Todo query con PDO preparado

## 6) RBAC obligatorio
- Todo endpoint /admin requiere:
  - sesión
  - y permiso
- Los permisos controlan:
  - Services/Capacities/Rates
  - Accounting
  - Users
  - KPIs

Ejemplo:
- Si no tiene `services.manage` → no puede crear/editar servicios

## 7) Contraseñas
- `password_hash()` y `password_verify()`
- Prohibido MD5/SHA1

## 8) Configuración
- `ktransfer/config/config.php` generado por instalador
- Nunca hardcodear:
  - credenciales DB
  - base_url
  - currency conversions
- Las conversiones (si existen) viven en tabla o config editable

## 9) Formularios
- Todos los POST deben usar CSRF token
- Validación server-side obligatoria

## 10) Logs
- Errores y eventos van a `ktransfer/storage/logs`
- No imprimir stacktraces en producción

## 11) Migrations
- Cambios en DB se hacen con migraciones SQL versionadas
- Nunca modificar schema “a mano” sin registrar migración

## 12) Estilo de código (mínimo)
- `declare(strict_types=1);`
- Namespaces en Core/Controllers/Models
- Funciones pequeñas y claras