# Estado Actual del Sistema

Fecha: 2026-05-13

Este documento resume el estado real del proyecto despues de los cambios recientes. Debe leerse junto con `README.md`, `ARCHITECTURE.md` y `route-map.md`.

## Resumen

KTransfers es una base PHP vanilla instalable para sitios de transporte privado. La aplicacion ya cubre buscador publico, checkout manual, panel administrativo, operacion, reportes, RBAC y configuracion white-label basica.

No usa Laravel, Composer ni dependencias externas obligatorias.

## Flujo Publico Actual

1. `GET /`
   - Renderiza `public/home`.
   - El controlador real es `Public/SearchController@index`.
   - Carga monedas, zonas, destinos destacados, tarifas iniciales y `home_content`.

2. `POST /search`
   - Valida CSRF y formulario.
   - Cotiza con `RateService`.
   - Guarda `search_context` y `quote_options` en sesion.
   - Muestra `public/results`.

3. `POST /checkout/start`
   - Valida seleccion de tarifa.
   - Genera `booking_code` con `BrandingService`.
   - Guarda seleccion de checkout en sesion.

4. `GET/POST /checkout/details`
   - Captura datos de cliente, vuelo, terminal y notas.
   - Guarda datos en sesion.

5. `GET/POST /checkout/payment`
   - Muestra opciones manuales.
   - Crea `bookings`, `booking_passengers`, `booking_payments` pendiente y `booking_status_history`.
   - La reserva queda `PENDING` y `UNPAID` hasta confirmacion manual.

6. `GET /checkout/confirmation`
   - Muestra confirmacion.

7. `GET /checkout/voucher`
   - Muestra voucher imprimible usando la vista admin `bookings/printable`.

## Home Publica

La home principal es `ktransfer/app/Views/Pages/public/home.php`.

Datos configurables desde `site_content`:

- `brand_logo`
- `brand_logo_light`
- `booking_code_prefix`
- `voucher_theme`
- `landing_theme`
- `payment_settings`
- `home_theme`
- `contact_channels`

Datos que aun viven principalmente en codigo/traducciones:

- Copy principal de hero, bienvenida, beneficios, testimonios, FAQ y CTA.
- Estructura de secciones.
- Imagenes visuales del home.

## Home Settings

Ruta: `/admin/content/home`

Permiso: `home.manage`

Disponible para `superadmin`.

Actualmente permite editar:

- Logos de marca.
- Prefijo de reserva.
- Tema visual day/night.
- Colores de voucher.
- Colores de landing.
- Canales de contacto del boton flotante.
- Claves de Mercado Pago, Stripe y PayPal.

Mercado Pago ya procesa Checkout Pro mediante API HTTP sin Composer. Stripe y PayPal solo guardan claves por ahora.

## Registro Manual

Ruta: `/admin/bookings/create`

El registro manual ya no es un formulario simple. Soporta:

- Reservas aeropuerto y `INTERHOTEL`.
- One way y round trip.
- Zona, place, origen y destino operativo.
- Cliente, vuelo, terminal y notas.
- Precio, moneda y estado de pago.
- Agencia vinculada a provider.
- Cobro capturado por agencia (`agency_collected_total`).
- Asignacion interna o proveedor externo.
- Operador, unidad, estado operativo y work order.
- Recomendacion de vehiculo por capacidad.

Usuarios con rol `agency` operan con scope limitado: no administran precio final ni asignacion operativa.

## Operacion

Ruta: `/admin/operations/agenda`

La agenda filtra por rango de fechas, operador y estado. Permite actualizar:

- Modo de asignacion.
- Proveedor u operador.
- Vehiculo.
- Estado operativo.
- Fecha y notas de work order.
- Datos visibles en hoja de servicio.

Tambien incluye impresion y export CSV.

## Contabilidad y KPIs

Contabilidad usa:

- `booking_payments` con `status = PAID` para pagos recibidos.
- `provider_transactions` para saldos de proveedores.
- `bookings.agency_collected_total` para resumen de agencias.

KPIs usa:

- Total de reservas.
- Ingresos por pagos pagados.
- No shows.
- Top zonas.
- Reservas pagadas/no pagadas.
- Cobro capturado por agencias.

## Pagos

Estado actual:

- Mercado Pago Checkout Pro: implementado para sandbox/produccion segun credenciales.
- Claves de pasarelas: configurables en Home Settings.
- Checkout publico manual: registra metodo seleccionado y pago `PENDING`.
- Contabilidad: solo cuenta ingresos si hay `booking_payments.status = PAID`.
- Webhook/return de Mercado Pago: pagos `approved` marcan reserva `CONFIRMED` / `PAID`.

Pendiente:

- Stripe.
- PayPal.
- Confirmacion admin para pagos manuales.
- Mejor manejo visual de pagos rechazados/pendientes.

## Migraciones Recientes

- `015`: roles, permisos y `site_content`.
- `016`: campos de hoja operativa.
- `017`: tipo de operacion.
- `018`: rol agencia y propiedad de reservas.
- `019`: labels de roles.
- `020`: contacto de proveedores.
- `021`: cobro manual de agencia.
- `022`: superadmin y Home Settings.
- `023`: vinculo agencia-provider.
- `024`: auditoria, solicitudes de borrado y notificaciones.
- `025`: metodo de pago `MERCADO_PAGO`.

## Pendientes Recomendados

1. Probar Mercado Pago con credenciales sandbox y dominio publico.
2. Definir markup libre y reglas de margen.
3. Expandir Home Settings a editor completo de copy/secciones.
4. Agregar validaciones server-side mas completas al checkout.
5. Crear smoke tests de instalacion, login, cotizacion, checkout y RBAC.

## Publicacion y reutilizacion (GitHub base)

Estado actual para publicar de forma segura:

- `config.php` quedo como plantilla saneada para evitar exponer credenciales.
- `config.example.php` define placeholders genericos por instancia.
- Existe `.gitignore` para evitar commitear configuracion local y archivos de entorno.

Pendiente manual recomendado antes de push:

- Sacar del indice archivos sensibles si alguna vez se versionaron con datos reales.
- Revisar si el dump `docs/u372499129_express.sql` debe quedar fuera del repositorio base.
