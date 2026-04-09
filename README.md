# ktransfers
# KTransfers (PHP Vanilla)

Sistema instalable para empresas de transporte privado (aeropuerto ↔ hotel/airbnb/destino),
con buscador público, checkout, panel administrativo, operación, KPIs y contabilidad.

Este proyecto NO usa Laravel, NO usa Composer y está pensado para:
- Hosting compartido / reseller
- VPS / dedicado

## Objetivo
Crear una base sólida y ordenada (tipo mini-framework propio) basada en el modelo real de producción
(cancunskytransfers.com), manteniendo el flujo:
- Search → Details → Payment → Confirmation
- Admin → Bookings, Rates, Zones, Hotels/Airbnb, Vehicles, Providers/Agencies, KPIs, Accounting
- Roles y permisos (RBAC) para controlar qué puede editar cada usuario (incluye Services/Types).

---

## Estructura del proyecto

- `ktransfer/` (todo lo privado)
  - `app/` núcleo, router, controladores, vistas
  - `config/` configuración
  - `storage/` logs/cache/exports (requiere permisos de escritura)
  - `database/` schema y migraciones SQL
  - `migrate/` runner de migraciones
  - `install/` instalador web (crea config + DB + admin y se bloquea)

- `public_html/` (lo público)
  - `index.php` front controller
  - `.htaccess` rewrite
  - `assets/` css/js/img compilados o estáticos

---

## Instalación (hosting compartido)

### 1) Subir archivos
Sube ambas carpetas al hosting:
- `/ktransfer`
- `/public_html`

Ideal:
- Configura el dominio para que el DocumentRoot sea `public_html/`.

Si NO puedes cambiar el DocumentRoot:
- Puedes colocar el contenido de `public_html/` en la raíz pública
  y mantener `ktransfer/` fuera (en un nivel superior si tu hosting lo permite).

### 2) Permisos
Asegura escritura en:
- `ktransfer/storage/logs`
- `ktransfer/storage/cache`
- `ktransfer/storage/exports`

### 3) Ejecutar instalador
Ir a:
- `https://TU-DOMINIO/install/`

El instalador:
- valida requisitos
- pide datos DB + admin user
- importa schema/migraciones
- crea `ktransfer/config/config.php`
- crea `ktransfer/install/lock.php` para bloquear reinstalación

### 4) Entrar al admin
- `https://TU-DOMINIO/admin`

### Seed base Cancún (catálogos + tarifas iniciales)

Desde esta versión se incluye la migración:
- `ktransfer/database/migrations/008_seed_cancun_catalog.sql`
- `ktransfer/database/migrations/009_seed_cancun_legacy_expansion.sql`
- `ktransfer/database/migrations/010_seed_airlines_catalog_full.sql`
- `ktransfer/database/migrations/011_seed_places_cancun_expanded.sql`
- `ktransfer/database/migrations/012_seed_countries_catalog.sql`
- `ktransfer/database/migrations/013_seed_rate_rules_luxury_and_gaps.sql`
- `ktransfer/database/migrations/014_import_places_from_fzn3_hotels.sql`

Incluye datos iniciales para operar rápido:
- service_types (Regular, VIP, Luxury)
- vehicles (Sedan, SUV, Van, Sprinter)
- pax_ranges (1-3, 4-5, 6-8, 9-12, 13-16)
- zones y places base de Cancún/Riviera Maya
- rate_rules base en USD y MXN

La migración `009` amplía el seed con datos curados desde un SQL legacy:
- más aerolíneas útiles (sin códigos basura)
- más zonas operativas (Playa Mujeres, Puerto Aventuras, Akumal, Maroma)
- hoteles/points representativos por zona
- tarifas base para las zonas nuevas

La migración `010` complementa específicamente el catálogo de aerolíneas
con una cobertura más amplia de códigos IATA usados en operación.

La migración `011` amplía el catálogo de places/hoteles con un subset curado
de Cancún y Riviera Maya para mejorar buscador y panel admin.

La migración `012` agrega tabla/catálogo `countries` (lista base internacional)
para futuras mejoras de checkout/CRM.

La migración `013` completa gaps de `rate_rules`:
- crea tarifas LUXURY faltantes (derivadas de VIP)
- crea tarifas base para zonas activas sin reglas USD
- genera espejo MXN cuando no exista

La migración `014` importa places desde `fzn3_hotels` (cuando esa tabla legacy
existe en la misma base), mapeando zonas históricas a `zones` y evitando
duplicados por `zone + name`.

Notas de aplicación:
- Instalaciones nuevas: el instalador aplica migraciones posteriores al baseline automáticamente.
- Instalaciones ya existentes: ejecutar `ktransfer/migrate/migrate.php` para aplicar migraciones pendientes.

---

## Modelo de negocio (resumen)
Basado en producción:

### Catálogo
- `zones` (zonas geográficas)
- `places` (hoteles/airbnb ligados a zone)
- `vehicles` (tipos de vehículos)
- `airlines` (aerolíneas con código IATA)
- `service_types` (Regular, VIP, Luxury, etc.)

### Pricing
- `pax_ranges` (rangos de pasajeros por servicio)
- `rate_rules` (tarifa por zone + service + pax_range, con OW/RT y multi-moneda)

### Bookings
- reserva con transfer_type (AH/HA/RT), pax, fechas, flight info (airline + flight number), pickup, notas
- payment method + payment status
- asignación a proveedor/vehículo (interno o externo)

### Roles/Permisos (RBAC)
- Control de acceso por permiso (ej: `services.manage`, `rates.manage`, `accounting.view`)
- Menús y endpoints protegidos por middleware `RequirePermission`

---

## Convenciones
- PHP 8.1+ y PDO
- Sin Composer
- SQL migrations simples (archivos .sql en `ktransfer/database/migrations`)
- Vistas en `ktransfer/app/Views/Pages` y layouts en `ktransfer/app/Views/Layouts`

---

## Roadmap (MVP recomendado)
1) Instalador + Login + RBAC
2) CRUD: Zones, Hotels/Airbnb, Services, Vehicles, Airlines, Pax Ranges, Rates
3) Search público + cálculo + creación booking + confirmación
4) Admin Bookings + Work Orders + asignación básica
5) Contabilidad base + KPIs básicos