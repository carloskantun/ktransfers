# Changelog

## 2026-05-13

### Added

- Archivo `.gitignore` con exclusiones para configuracion sensible y artefactos locales.
- Guia de publicacion segura y enfoque white-label en `README.md`.

### Changed

- `ktransfer/config/config.php` saneado para repositorio (sin credenciales reales).
- `ktransfer/config/config.example.php` normalizado como plantilla completa de instancia.

### Notes

- Antes de publicar al remoto, remover de indice cualquier archivo sensible que ya haya sido trackeado historicamente.
- Evaluar si el dump `docs/u372499129_express.sql` debe mantenerse fuera del repo principal por contener datos de entorno real.

## 2026-05-11

### Added

- Edicion de canales de contacto desde `/admin/content/home`.
- Persistencia de `contact_channels` en `site_content.home_page` para boton flotante y accesos rapidos del home.
- Registro de pago pendiente en `booking_payments` al confirmar checkout publico manual.
- Historial inicial en `booking_status_history` para reservas creadas desde checkout publico.
- Integracion inicial de Mercado Pago Checkout Pro sin Composer.
- Rutas `/checkout/mercado-pago/start`, `/checkout/mercado-pago/return` y `/webhooks/mercado-pago`.
- Migracion `025_mercado_pago_payment_method.sql` para metodo `MERCADO_PAGO`.
- Documento `docs/CURRENT_STATUS.md` con estado real del sistema.

### Changed

- `README.md`, `ARCHITECTURE.md`, `route-map.md` y `PLAN_TRABAJO.md` ahora reflejan mejor el estado actual: home nueva, Home Settings, superadmin, agencias, registro manual avanzado, pagos pendientes y migraciones hasta `024`.

### Notes

- Stripe y PayPal siguen sin logica de cobro real; solo se guardan claves de configuracion.
- Mercado Pago requiere credenciales sandbox/produccion y que el dominio publico reciba el webhook.
- El checkout manual conserva reservas como `PENDING` / `UNPAID` hasta que el equipo confirme manualmente el pago.

## 2026-03-20

### Added

- Protección real de rutas admin con auth + permisos por path.
- Seeds iniciales de permisos y roles mediante migración `015`.
- Agenda operativa diaria con actualización rápida de operador, estado y notas.
- Editor de contenido para la home pública en `/admin/content/home`.
- Servicio `HomeContentService` para persistir bloques editables de la landing.
- Edición de usuarios con rol principal y activación/desactivación.
- Documentación de continuidad y mapa de rutas.

### Changed

- La home pública se rediseñó hacia una estructura más premium/editorial.
- El menú admin ahora muestra secciones según permisos.
- La lista de usuarios ahora expone roles asignados.

### Notes

- Falta ejecutar migraciones en la base real para habilitar completamente los nuevos módulos.
- El modelo soporta múltiples roles por usuario, pero la UI actual maneja un rol principal.
