# Route Map

Actualizado: 2026-05-11

## Notas

- Todas las rutas `/admin/*`, excepto login/logout, pasan por auth.
- Los permisos reales se definen en `App::permissionForPath`.
- El home publico actual usa `Public/SearchController@index` y renderiza `public/home`.
- El checkout manual registra `booking_payments.status = PENDING`.
- Mercado Pago Checkout Pro puede crear preferencia y confirmar pagos aprobados via return/webhook.

## Público

- `GET /`
  - Home pública + buscador de traslados
- `POST /search`
  - Cotización pública
- `GET /api/places`
  - Autocomplete de lugares
- `GET /api/airlines`
  - API de aerolíneas
- `POST /checkout/start`
  - Inicio de checkout
- `GET /checkout/details`
  - Paso de datos del pasajero
- `POST /checkout/details`
  - Guardado de datos del pasajero
- `GET /checkout/payment`
  - Paso de pago
- `POST /checkout/payment`
  - Confirmación/ejecución de pago
- `POST /checkout/mercado-pago/start`
  - Crea/asegura reserva pendiente y preferencia Checkout Pro
- `GET /checkout/mercado-pago/return`
  - Retorno desde Mercado Pago; sincroniza pago si viene `payment_id`
- `GET /checkout/confirmation`
  - Confirmación final
- `GET /checkout/voucher`
  - Voucher imprimible de la reserva confirmada en sesión
- `GET|POST /webhooks/mercado-pago`
  - Webhook de Mercado Pago; sincroniza pagos tipo `payment`

## Admin sin auth

- `GET /admin/login`
  - Pantalla login
- `POST /admin/login`
  - Acción login
- `POST /admin/logout`
  - Logout

## Admin protegido

### Dashboard

- `GET /admin`
  - Permiso: `dashboard.view`

### Bookings

- `GET /admin/bookings`
  - Permiso: `bookings.view`
- `GET /admin/bookings/quote`
  - Permiso: `bookings.create`
  - API interna para cotizar desde registro manual
- `GET /admin/bookings/create`
  - Permiso: `bookings.create`
- `POST /admin/bookings/create`
  - Permiso: `bookings.create`
- `GET /admin/bookings/edit`
  - Permiso: `bookings.create`
- `GET /admin/bookings/service-order`
  - Permiso: `bookings.manage`
- `GET /admin/bookings/voucher`
  - Permiso: `bookings.view`
- `GET /admin/bookings/export`
  - Permiso: `bookings.view`
- `GET /admin/bookings/print`
  - Permiso: `bookings.view`
- `POST /admin/bookings/update`
  - Permiso: `bookings.create`
- `POST /admin/bookings/delete-request`
  - Permiso: `bookings.delete.request`
- `POST /admin/bookings/delete-review`
  - Permiso: `bookings.delete.approve`
- `POST /admin/bookings/delete`
  - Permiso: `bookings.manage`

### Catálogo

- `GET /admin/catalog/zones`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/zones/create`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/zones/create`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/zones/edit`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/zones/edit`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/services`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/services/edit`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/services/edit`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/currencies`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/currencies/create`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/currencies/create`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/currencies/edit`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/currencies/edit`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/vehicles`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/vehicles/create`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/vehicles/create`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/vehicles/edit`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/vehicles/edit`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/providers`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/providers/create`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/providers/create`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/providers/edit`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/providers/edit`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/places`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/places/export`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/places/create`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/places/create`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/places/edit`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/places/edit`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/airlines`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/airlines/export`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/airlines/create`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/airlines/create`
  - Permiso: `catalog.manage`
- `GET /admin/catalog/airlines/edit`
  - Permiso: `catalog.manage`
- `POST /admin/catalog/airlines/edit`
  - Permiso: `catalog.manage`

### Pricing

- `GET /admin/pricing/rate-rules`
  - Permiso: `pricing.manage`
- `GET /admin/pricing/rate-rules/edit`
  - Permiso: `pricing.manage`
- `POST /admin/pricing/rate-rules/edit`
  - Permiso: `pricing.manage`
- `GET /admin/pricing/rate-rules/edit-group`
  - Permiso: `pricing.manage`
- `POST /admin/pricing/rate-rules/edit-group`
  - Permiso: `pricing.manage`
- `GET /admin/pricing/pax-ranges`
  - Permiso: `pricing.manage`
- `GET /admin/pricing/pax-ranges/create`
  - Permiso: `pricing.manage`
- `POST /admin/pricing/pax-ranges/create`
  - Permiso: `pricing.manage`
- `GET /admin/pricing/pax-ranges/edit`
  - Permiso: `pricing.manage`
- `POST /admin/pricing/pax-ranges/edit`
  - Permiso: `pricing.manage`

### Operación

- `GET /admin/operations/agenda`
  - Permiso: `operations.view`
- `POST /admin/operations/agenda`
  - Permiso: `operations.view`
- `GET /admin/operations/agenda/print`
  - Permiso: `operations.view`
- `GET /admin/operations/agenda/export`
  - Permiso: `operations.view`

### Reportes

- `GET /admin/accounting`
  - Permiso: `accounting.view`
- `GET /admin/accounting/export`
  - Permiso: `accounting.view`
- `GET /admin/kpis`
  - Permiso: `kpis.view`
- `GET /admin/kpis/export`
  - Permiso: `kpis.view`

### Usuarios y sitio

- `GET /admin/users`
  - Permiso: `users.manage`
- `GET /admin/users/create`
  - Permiso: `users.manage`
- `POST /admin/users/create`
  - Permiso: `users.manage`
- `GET /admin/users/edit`
  - Permiso: `users.manage`
- `POST /admin/users/edit`
  - Permiso: `users.manage`
- `GET /admin/content/home`
  - Permiso: `home.manage`
- `POST /admin/content/home`
  - Permiso: `home.manage`

## Rutas utilitarias publicas

- `GET /install/`
  - Wrapper publico hacia `ktransfer/install/index.php`
- `GET|POST /migrate/`
  - Migrador web protegido por credenciales admin
- `GET /migrate/check.php`
  - Comprobador/reparador puntual para migraciones de hosting
