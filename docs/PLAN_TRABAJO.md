# Plan de Trabajo

## Objetivo principal
Convertir `ktransfers` en un instalador util para crear sitios de transporte privado (ej: `nasttransfers.com`, `cancunskytransfers.com`, `usatransfers.com`) sobre una base operativa comun: cotizador, checkout, admin, operacion y RBAC.

## Estado actual resumido
- Ya existe un core funcional en PHP vanilla con instalador, migraciones, buscador, checkout, admin y modulos operativos.
- La home publica ya apunta a un estilo premium/minimalista orientado a turismo de lujo.
- Home Settings ya permite configurar logos, prefijo, colores, canales de contacto y claves de pasarelas.
- El checkout publico registra pagos manuales pendientes y ya puede iniciar Mercado Pago Checkout Pro.
- El registro manual ya incluye agencias, cobro capturado por agencia, operacion aeropuerto/interhotel, asignaciones y work orders.
- Falta cerrar brechas criticas para estabilidad y alineacion con la vision "instalador multi-marca".

## Fase 1: Correcciones criticas (bloqueantes)
1. Corregir alta de rol admin en instalador.
- Unificar `admin` en minusculas en instalador/migraciones para evitar usuarios sin permisos.
- Validar login y permisos reales post-instalacion.

2. Cerrar flujo de pago MVP de checkout.
- Estado actual: al confirmar se crea `booking_payments` con estado `PENDING` y metodo manual seleccionado.
- Estado actual: Mercado Pago actualiza `bookings.payment_status = PAID` y `bookings.status = CONFIRMED` cuando el pago llega como `approved`.
- Pendiente: confirmacion admin para pagos manuales y controles operativos de reintento/cancelacion.
- Mantener consistencia con `Accounting` y `KPIs`: hoy solo cuentan ingresos cuando `booking_payments.status = PAID`.

3. Endurecer validaciones del checkout.
- Validar server-side datos minimos de cliente antes de persistir.
- Devolver errores de forma clara sin perder contexto del formulario.

## Fase 2: Producto instalable de verdad (white-label)
1. Sistema de identidad por sitio.
- Configurar nombre comercial, logo, telefonos, links sociales, colores y tipografia.
- Quitar hardcodes de marca en layout publico.
- Estado actual: logos, prefijo, colores y canales de contacto ya son editables; nombre comercial/copy/secciones aun requieren expansion.

2. Temas/plantillas iniciales.
- Crear presets de interfaz:
  - `classic-transfer`
  - `luxury-minimal`
  - `agency-sales`
- Permitir seleccionar tema desde instalacion o admin.

3. Contenido editable estructurado.
- Mantener editor de home y evolucionarlo para bloques repetibles.
- Separar "estructura UX de transfers" de "copy/branding de cada cliente".

## Fase 3: Escalado del instalador
1. Flujo de instalacion guiado por tipo de negocio.
- Setup de moneda(s), pais, zona base, canales de contacto.
- Seed inicial segun tipo de operacion (solo aeropuerto, aeropuerto + hoteles, rutas largas).

2. Paquete de datos base por destino.
- Semillas por region (ej: Cancun, Riviera Maya, USA corridors).
- Importacion opcional de catalogos de hoteles/rutas.

3. Exportable/reutilizable.
- Checklist de salida para desplegar en hosting compartido.
- Script/documentacion para replicar una nueva instancia rapido.

## Fase 4: Calidad y continuidad
1. Pruebas minimas automatizadas.
- Smoke tests para instalacion, login, cotizacion y checkout.
- Pruebas de permisos clave en admin.

2. Observabilidad y soporte.
- Estandarizar logs de errores de checkout/pago.
- Trazabilidad de cambios de estado de reserva.

3. Criterios de "release candidate".
- Instalacion limpia en entorno nuevo.
- Flujo publico completo sin errores.
- Admin operable por roles (admin, operador, ventas, contabilidad).

## Orden sugerido de ejecucion (sprint corto)
1. Fix instalador + RBAC admin.
2. Fix checkout/pagos (booking_payments + estados).
3. Validaciones y mensajes de error en checkout.
4. Configuracion de branding basica (nombre/logo/contacto).
5. Primer preset de tema (`luxury-minimal`) estable.

## Resultado esperado
Al finalizar estas fases, el proyecto pasa de ser "un sitio de transfers funcional" a ser "una base instalable y personalizable" para multiples marcas de transporte privado, manteniendo UX clara de conversion y una presentacion premium orientada a turismo/luxury.
