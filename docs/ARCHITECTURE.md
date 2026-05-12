# Arquitectura KTransfers (PHP Vanilla)

## Estado actual

La aplicacion mantiene la arquitectura PHP vanilla original, pero ya opera como base instalable white-label:

- Front controller unico en `public_html/index.php`.
- Codigo privado en `ktransfer/`.
- Home publica renderizada por `Public/SearchController@index` y `Views/Pages/public/home.php`.
- Configuracion de home/marca persistida en `site_content` mediante `HomeContentService`.
- Panel admin protegido por auth + permisos por ruta.
- Roles actuales: `admin`, `superadmin`, `operator`, `agency`, `sales`, `accounting`.
- Checkout publico manual: crea reserva, pasajeros, pago pendiente e historial.
- Mercado Pago Checkout Pro: crea preferencia, redirige, recibe return/webhook y confirma pagos `approved`.

## Principios
1) Todo lo privado vive en `ktransfer/`
2) Todo lo público vive en `public_html/`
3) Un solo punto de entrada: `public_html/index.php`
4) Router simple + Controllers + Views
5) DB con PDO y consultas parametrizadas
6) RBAC (roles/permisos) obligatorio para el admin

---

## Carpetas principales

## `public_html/`
- `index.php`: Front Controller
- `.htaccess`: Reescritura de rutas hacia `index.php`
- `assets/`: CSS/JS/IMG

## `ktransfer/app/Core`
- `App.php`: boot general, carga rutas
- `Router.php`: GET/POST + dispatch
- `DB.php`: conexión PDO
- `Auth.php`: login/logout/sesión
- `ACL.php`: permisos (RBAC) y helpers
- `I18n.php`: traducciones publicas EN/ES basicas
- `StatusCatalog.php`: labels centralizados de estados
- `Csrf.php`: tokens para forms
- `Request.php/Response.php`: helpers mínimos
- `Validator.php`: validaciones
- `View.php`: render de layouts y pages

## `ktransfer/app/Services`
- `RateService.php`: cotizacion por zona, servicio, pax, moneda y tipo de viaje.
- `HomeContentService.php`: defaults, lectura y escritura de `site_content.home_page`.
- `BrandingService.php`: prefijo y generacion de `booking_code`.

## `ktransfer/app/Http/Controllers`
Separación por módulo:
- `Public/*`: buscador y checkout
- `Admin/*`: dashboard, bookings, rates, zones, services, accounting, kpis, users

## `ktransfer/app/Http/Middlewares`
- `RequireAuth`: exige sesión
- `RequirePermission`: exige permiso específico

## `ktransfer/app/Views`
- `Layouts/`: layouts (admin, public)
- `Pages/`: páginas
  - `Pages/public/*`
  - `Pages/admin/*`

Regla:
- Los controladores NO deben imprimir HTML directo.
- Solo pasan data al View.

---

## Rutas (ejemplo)
- `/` GET → Public/SearchController@index
- `/search` POST → Public/SearchController@search
- `/checkout` GET/POST → Public/CheckoutController@*
- `/admin` GET → Admin/DashboardController@index
- `/admin/bookings` GET → Admin/BookingsController@index
- `/admin/services` GET/POST → Admin/ServicesController@*
- `/admin/rates` GET/POST → Admin/RatesController@*

---

## Seguridad
- Sesiones: cookies httpOnly + regeneración en login
- CSRF en todos los POST (admin y checkout)
- Contraseñas: `password_hash()` (bcrypt/argon2)
- Queries: SIEMPRE preparadas con PDO

---

## Dominio (basado en producción cancunsk_transfers)
Entidades núcleo:
- Service (tipo de servicio)
- Capacity (rango de pax por servicio)
- Zone
- Place (Hotel/Airbnb)
- Vehicle (tipos de vehículos)
- Airline (aerolíneas con código IATA)
- Rate (zone + service + capacity + OW/RT + moneda)
- Booking (reserva con flight info: airline + flight_number)
- Payment (registro pagos)
- Assignment (proveedor/vehículo/agencia)
- Agency (externos)
- User/Role/Permission (RBAC)
- SiteContent (configuracion JSON de home/branding)
- BookingEditLog / BookingDeleteRequest / AdminNotification (auditoria y borrado controlado)

---

## Migraciones
- SQL plano en `ktransfer/database/migrations/*.sql`
- Runner: `ktransfer/migrate/migrate.php`
- Runner web: `public_html/migrate/index.php`
- Tabla `migrations` para registrar ejecutadas

`schema.sql` actua como baseline consolidado para instalaciones nuevas; las migraciones posteriores completan seeds y cambios funcionales. Revisar `docs/CURRENT_STATUS.md` para el estado de migraciones recientes.

---

## Compatibilidad hosting compartido
- Sin composer
- Sin dependencias externas
- PHP nativo + PDO
- Assets estáticos
