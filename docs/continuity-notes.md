# Continuity Notes

Fecha base: 2026-03-20

Actualizacion de contexto: 2026-05-13

Este documento conserva notas historicas de continuidad.
Para estado funcional vigente, revisar primero `docs/CURRENT_STATUS.md` y `docs/changelog.md`.

## Lo que ya se hizo

- Se agregó una base real de control de acceso en código:
  - `App` ahora protege rutas `/admin/*`.
  - Se asignan permisos por ruta.
  - `ACL` entra en modo seguro si los permisos aún no fueron sembrados, para evitar bloqueo antes de correr migraciones.
- Se creó la migración `015_roles_content_operations.sql` para:
  - sembrar permisos;
  - crear roles base (`admin`, `operator`, `sales`, `accounting`);
  - asignar todos los permisos al rol `admin`;
  - asignar permisos operativos/comerciales/contables a roles secundarios;
  - asignar rol `admin` a usuarios existentes sin rol;
  - crear tabla `site_content`.
- Se agregó agenda operativa diaria:
  - ruta `/admin/operations/agenda`;
  - filtros por fecha, operador y estado;
  - edición rápida de operador, estado y nota de work order.
- Se agregó editor de contenido para la home:
  - ruta `/admin/content/home`;
  - contenido persistente en DB por JSON;
  - edición de hero, badges, grids, story, testimonial y CTA.
- Se rediseñó la home pública con una estructura más premium/editorial.
- Se mejoró gestión de usuarios:
  - lista con roles visibles;
  - alta con rol principal;
  - edición de usuario con rol, estado y cambio opcional de contraseña.

## Pendientes importantes

- Correr migraciones en la base real para activar:
  - tabla `site_content`;
  - permisos/roles sembrados;
  - agenda operativa y editor de home completos.
- Revisar si hace falta una pantalla separada de administración de roles/permisos.
- Llevar la misma calidad visual a:
  - resultados;
  - checkout/details;
  - checkout/payment;
  - checkout/confirmation.
- Convertir el editor de home de "bloques de texto" a constructor con items repetibles si se quiere manejar sliders/carruseles reales.
- Definir si los operadores deben tener:
  - solo agenda;
  - agenda + lectura de bookings;
  - agenda + edición limitada.

## Riesgos/observaciones

- El sistema declaraba RBAC desde README, pero no lo ejecutaba realmente en rutas.
- El esquema ya tenía `assignments` y `work_orders`, pero faltaba la interfaz operativa.
- La home original era funcional para cotizar, pero visualmente todavía se sentía como MVP.
- La gestión de roles en usuarios por ahora usa un solo rol principal desde UI, aunque el modelo de datos permite múltiples roles.

## Siguiente paso recomendado

1. Ejecutar migraciones.
2. Validar login y acceso de usuarios existentes.
3. Probar agenda operativa con bookings reales.
4. Ajustar copy y secciones desde `/admin/content/home`.
5. Unificar visual del resto del checkout público.
