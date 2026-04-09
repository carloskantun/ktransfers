# Proyecto KTransfers

Objetivo: que cada paso sea “cerrar un ciclo completo” y no quedarte con páginas sueltas.

# A) Frontend público (buscador → reserva → ticket)
# A1) Datos mínimos que debes tener cargados (seed manual desde admin o SQL)

Antes de que el buscador funcione, en DB deben existir:

zones (al menos 2)

places (hoteles/airbnb ligados a zone)

service_types (Regular, VIP…)

pax_ranges (1-4, 5-7…)

rate_rules (para cada zone + service_type + pax_range + currency, OW/RT)

Si no hay esto, el buscador no puede cotizar.

# A2) Rutas públicas (Router)

Archivo: ktransfer/app/Core/App.php
Agrega (o confirma) estas rutas:

GET / → Public/SearchController@index

POST /search → Public/SearchController@search

POST /checkout/start → Public/CheckoutController@start

GET /checkout/details → Public/CheckoutController@details

POST /checkout/details → Public/CheckoutController@saveDetails

GET /checkout/payment → Public/CheckoutController@payment

POST /checkout/payment → Public/CheckoutController@pay

GET /checkout/confirmation → Public/CheckoutController@confirmation

Regla: en público, todo lo de checkout debe depender de booking_code en sesión.

# A3) Crear el servicio de cotización (NO en la vista)

Crea archivo: ktransfer/app/Services/RateService.php (si no existe carpeta, créala)

Responsabilidad:

Recibir place_id, resolver zone_id

Calcular total_pax = adults + children

Encontrar pax_range

Buscar en rate_rules el precio correcto por zone_id + pax_range_id + service_type_id + currency + trip_type

Devolver lista de opciones (todas las combinaciones de service_type/pax_range que apliquen)

Si Copilot te intenta meter lógica en results.php, lo frenas: todo sale de RateService.

# A4) SearchController (pantalla + submit)

Archivo: ktransfer/app/Http/Controllers/Public/SearchController.php
Debe hacer:

index()

Cargar:

monedas activas (currencies)

lista inicial de zonas (opcional)

Render:

ktransfer/app/Views/Pages/public/search.php

search()

Validar POST + CSRF

Inputs:

trip_type

direction

place_id

dates (arrival/departure según direction)

adults/children

currency_code

Llamar:

RateService::quote(...)

Guardar en sesión el “search_context” (para no perder datos)

Render:

ktransfer/app/Views/Pages/public/results.php

# A5) Views públicas (archivos exactos)

Crea en: ktransfer/app/Views/Pages/public/

search.php

Form POST a /search

Autocomplete: en MVP simple

Input place_query + hidden place_id

(luego haces endpoint /api/places?q=...)

results.php

Lista de opciones:

servicio (service_type)

pax range

precio

Cada opción tiene un botón POST a /checkout/start

manda service_type_id + quoted_price + currency_code

# A6) CheckoutController (start → details → payment → confirmation)

Archivo: ktransfer/app/Http/Controllers/Public/CheckoutController.php

start()

Validar CSRF

Toma de sesión el search_context

Genera booking_code

Crea booking en DB:

status=PENDING

payment_status=UNPAID

price_total = quoted

guarda trip_type, direction, place_id, zone_id, service_type_id, currency_code, datetimes

crea booking_passengers

Guarda booking_code en sesión

Redirect a /checkout/details

details() (GET)

Cargar booking por booking_code

Render: public/checkout_details.php

saveDetails() (POST)

Validar CSRF

Actualizar campos:

customer_name, email, phone

airline (lookup del nombre), flight_number

pickup_notes

Redirect a /checkout/payment

payment() (GET)

Render: public/checkout_payment.php

pay() (POST)

Validar CSRF

Si “manual”:

insertar booking_payments con method=MANUAL, status=PAID o PENDING (tu decides)

Si marca pagado:

booking payment_status=PAID

booking status=CONFIRMED

historial en booking_status_history

Redirect a /checkout/confirmation

confirmation() (GET)

Render: public/checkout_confirmation.php (muestra ticket + estado)

# A7) Ticket (booking_code)

Regla simple:

Se genera en start()

Ej: KTR-20260224-8F3A (fecha + random)

Se guarda en bookings.booking_code

Se muestra en confirmation y en el admin

# B) Admin (panel completo por módulos, orden correcto)
# B1) Rutas admin (todas protegidas)

En ktransfer/app/Core/App.php agrega rutas admin y aplica middlewares:

/admin dashboard

/admin/bookings

/admin/bookings/edit?id=

/admin/workorders

/admin/catalog/zones

/admin/catalog/places

/admin/catalog/vehicles

/admin/catalog/airlines

/admin/catalog/service-types

/admin/catalog/pax-ranges

/admin/pricing/rate-rules

/admin/operations/providers

/admin/operations/assignments

/admin/accounting

/admin/kpis

/admin/users

Regla:

Todos requieren RequireAuth

Cada módulo requiere permiso:

rates.manage, services.manage, etc.

# B2) Layout admin (base)

Archivo: ktransfer/app/Views/Layouts/admin.php
Debe tener:

sidebar (menú)

topbar

área content

Copilot NO debe duplicar sidebar en cada vista.

# B3) Dashboard

Controller: Admin/DashboardController.php
View: admin/dashboard/index.php

Debe listar:

últimas 20 reservas

columnas: booking_code, fecha, customer, total, currency, status, payment_status

Permiso: dashboard.view

# B4) Bookings (admin)

Controller: Admin/BookingsController.php
Views:

admin/bookings/index.php

admin/bookings/edit.php

Index:

filtros: date range, status, payment_status
Edit:

cambiar status (cancel/no-show/confirm)

marcar pagos manuales

ver/crear assignment

Permisos:

bookings.view

bookings.manage

# B5) Work Orders (orden del día operadores)

Controller: Admin/WorkOrdersController.php (si no existe, créalo)
View: admin/workorders/index.php

Debe mostrar por fecha:

booking_code

hora de servicio

pickup/destination

pax

assignment: interno/provider, provider_name, vehicle

botones: Assign / Mark Done / No-show

Permisos:

workorders.view

operations.manage

Este módulo es el “panel del operador”.

# C) Catálogo y Pricing (sin esto no hay negocio)
 # C1) Catálogo CRUD

Zones → zones

Places → places (ligado a zone)

Vehicles → vehicles

Airlines → airlines (código IATA + nombre)

Service Types → service_types

Pax Ranges → pax_ranges

Cada uno:

Controller en Admin/

Views en admin/catalog/...

Permisos:

.view y .manage según módulo

Regla: no borrar, solo is_active=0.

# C2) Rate Rules (pricing)

Controller: Admin/RatesController.php (o RateRulesController)
Views: admin/pricing/rate_rules/*

Debe permitir:

seleccionar zone

service_type

pax_range

currency

OW/RT prices

Permiso:

rates.manage

# D) Agencias externas, contabilidad, KPIs (MVP bien hecho)
 # D1) Providers (agencias externas)

Controller: Admin/ProvidersController.php
Views: admin/operations/providers/*

CRUD simple:

name, email, phone, is_active

Permiso:

providers.manage (o usar operations.manage si no quieres granularidad)

# D2) Assignments (asignación del servicio)

Controller: Admin/AssignmentsController.php
View: admin/operations/assignments/index.php

Acciones:

asignar booking a INTERNAL o PROVIDER

si provider: guardar provider_id

guardar vehicle_id opcional

actualizar service_status

Cuando se asigna a provider:

insertar provider_transactions tipo PAYABLE (lo que le vas a pagar) con amount (puede ser:

el mismo del booking, o

otro campo “cost_to_provider” si lo agregas después)

# D3) Accounting (por transacciones)

Controller: Admin/AccountingController.php
Views:

admin/accounting/index.php (resumen)

admin/accounting/providers.php (estado por proveedor)

Mínimo:

Pagos recibidos: sum de booking_payments status PAID por fecha

Pendiente pagar: por provider: SUM(PAYABLE) - SUM(PAYMENT)

Pendiente cobrar: si usas RECEIVABLE

Permisos:

accounting.view / accounting.manage

# D4) KPIs (queries agregadas)

Controller: Admin/KpisController.php
View: admin/kpis/index.php

Mínimo:

total bookings (rango fechas)

revenue total por moneda (sum payments PAID)

no-shows

top zones / top places

% paid vs unpaid

Permiso:

kpis.view

# E) Checklist que debes exigirle a Copilot por cada módulo

Cada vez que le pidas “haz X”, le exiges que te entregue:

ruta (en ktransfer/app/Core/App.php)

Controller (GET/POST)

permiso requerido (RBAC)

queries PDO (en modelo o servicio)

Views en la carpeta correcta

CSRF en POST

redirect después de acciones

# F) Orden recomendado para que lo termines sin atascarte

Hazlo exactamente en este orden:

RateService + Search + Results (solo cotizar)

Checkout start → details → confirmation (sin pago real)

Admin login + RBAC (mínimo ADMIN)

Admin catálogo (zones/places/service_types/pax_ranges/vehicles/airlines)

Admin rate_rules (pricing)

Admin bookings (ver y editar)

Work Orders (orden del día)

Assignments + Providers

Accounting

KPIs