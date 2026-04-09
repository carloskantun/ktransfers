# TECH RULES (Copilot / IA) - KTransfers

## Objetivo
Construir KTransfers (single-company) en PHP vanilla sin frameworks ni Composer, con:
- buscador público
- checkout (details -> payment -> confirmation)
- generación de ticket
- panel admin con RBAC
- operación (orden del día)
- agencias/proveedores externos
- contabilidad y KPIs básicos

## 0) Prohibiciones
- NO usar Laravel, Symfony, CodeIgniter, WordPress para el core.
- NO usar Composer ni dependencias externas obligatorias.
- NO crear archivos PHP sueltos en la raíz del sitio (tipo booking.php).
- NO mezclar SQL con HTML en vistas.
- NO escribir queries sin PDO preparado.

## 1) Estructura fija (NO cambiar)
- Código privado SOLO en: /ktransfer
- Público SOLO en: /public_html
- Punto de entrada único: /public_html/index.php
- Rewrite en /public_html/.htaccess
- Views: /ktransfer/app/Views/Pages
- Layouts: /ktransfer/app/Views/Layouts
- Core: /ktransfer/app/Core
- Controladores: /ktransfer/app/Http/Controllers
- Middlewares: /ktransfer/app/Http/Middlewares
- Migraciones SQL: /ktransfer/database/migrations
- Instalador: /ktransfer/install

## 2) Ruteo y controladores
- TODA URL debe resolverse por Router.
- Un controlador NO imprime HTML; solo:
  - valida request
  - llama modelo/servicio
  - retorna View (render)
- Nombres:
  - Public: App\Http\Controllers\Public\*
  - Admin: App\Http\Controllers\Admin\*

## 3) Seguridad
- POST siempre con CSRF (Core/Csrf.php)
- Login con password_hash / password_verify
- Admin siempre protegido:
  - RequireAuth
  - RequirePermission (RBAC)
- No mostrar errores crudos en producción. Log en /ktransfer/storage/logs.

## 4) Base de datos
- DB.php es el único punto de conexión.
- Usar InnoDB + utf8mb4.
- Mantener Foreign Keys.
- Catálogos NO se borran; se desactivan (is_active=0).
- Bookings NO se borran; cambian status.
- Tabla airlines almacena aerolíneas con código IATA único.
- Los campos airline y flight_number de bookings son informativos.

## 5) Migraciones
- NO editar migraciones ya ejecutadas en entornos reales.
- Cambios nuevos = nueva migración (007_xxx.sql, 008_xxx.sql…).
- migrate.php aplica en orden y registra en tabla migrations.

## 6) Estilo y disciplina
- declare(strict_types=1);
- Namespaces correctos
- Métodos cortos y claros
- Validación server-side obligatoria

## 7) UX (frontend) obligatorio
- Buscador debe ser rápido:
  - Autocomplete de hotel/airbnb (places)
  - Selector de fechas y pax claro
  - Moneda visible
- Checkout:
  - Crear ticket/booking code al iniciar checkout
  - Estado PENDING hasta pago/confirmación
  - Capturar información de vuelo: aerolínea (selector) + número de vuelo
- Admin:
  - Dashboard: últimas reservas + pagada/no pagada
  - Work Orders: orden del día para operadores
  - Accounting: pendientes por pagar/cobrar
  - KPIs: resumen por rango de fechas

## 8) No inventar módulos fuera del alcance
- Primero MVP:
  - zones, places, vehicles, airlines, service_types, pax_ranges, rate_rules
  - bookings + payments (registro, no pasarela compleja)
  - assignments + work_orders
  - RBAC
- Luego extensiones: multi-tenant (otro proyecto separado)