# Route Map

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
- `GET /checkout/confirmation`
  - Confirmación final

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
- `GET /admin/bookings/create`
  - Permiso: `bookings.manage`
- `POST /admin/bookings/create`
  - Permiso: `bookings.manage`
- `GET /admin/bookings/edit`
  - Permiso: `bookings.manage`
- `POST /admin/bookings/update`
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
- `GET /admin/catalog/places`
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

### Reportes

- `GET /admin/accounting`
  - Permiso: `accounting.view`
- `GET /admin/kpis`
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
  - Permiso: `content.manage`
- `POST /admin/content/home`
  - Permiso: `content.manage`
