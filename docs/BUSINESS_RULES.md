Reservas: estados permitidos (mínimos)

status: PENDING → CONFIRMED → COMPLETED

status: PENDING → CANCELLED

status: CONFIRMED → NO_SHOW

Nunca se borra una reserva.

Pagos: estados

payment_status: UNPAID → PAID

PAID → REFUNDED (si lo implementas)

Una reserva puede tener múltiples pagos (manual + online), por eso booking_payments.

Ticket / booking_code

Se genera al iniciar checkout (cuando ya se eligió precio).

Formato recomendado: KTR-YYYYMMDD-XXXX (XXXX random).

Una vez creado, el ticket existe aunque no pague (sirve para seguimiento).

Operación (orden del día)

Un operador NO debería ver contabilidad.

“Orden del día” se basa en:

fecha de servicio (arrival/departure datetime)

y/o work_orders.work_date

Agencias/proveedores externos

Si assignments.mode=PROVIDER:

provider_id obligatorio

genera transacción PAYABLE (lo que debes pagarles) o RECEIVABLE (si te deben)

Esto alimenta contabilidad.