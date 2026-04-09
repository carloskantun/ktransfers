# Changelog

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
