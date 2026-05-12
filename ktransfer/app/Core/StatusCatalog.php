<?php
declare(strict_types=1);

namespace App\Core;

final class StatusCatalog
{
    private const BOOKING_STATUS_LABELS_ES = [
        'PENDING' => 'Pre-reserva pendiente de pago',
        'CONFIRMED' => 'Reserva confirmada',
        'COMPLETED' => 'Servicio completado',
        'NO_SHOW' => 'Cliente no se presento',
        'CANCELLED' => 'Reserva cancelada',
    ];

    private const BOOKING_STATUS_LABELS_BILINGUAL = [
        'PENDING' => 'Pre-reserva pendiente de pago / Booking pending payment',
        'CONFIRMED' => 'Reserva confirmada / Booking confirmed',
        'COMPLETED' => 'Servicio completado / Service completed',
        'NO_SHOW' => 'Cliente no se presento / No-show',
        'CANCELLED' => 'Reserva cancelada / Booking cancelled',
    ];

    private const PAYMENT_STATUS_LABELS_ES = [
        'UNPAID' => 'Pendiente de pago',
        'PARTIAL' => 'Pago parcial',
        'PAID' => 'Pagado total',
        'REFUNDED' => 'Reembolsado',
    ];

    private const PAYMENT_STATUS_LABELS_BILINGUAL = [
        'UNPAID' => 'Pendiente de pago / Payment pending',
        'PARTIAL' => 'Pago parcial / Partially paid',
        'PAID' => 'Pagado total / Paid in full',
        'REFUNDED' => 'Reembolsado / Refunded',
    ];

    private const SERVICE_STATUS_LABELS_ES = [
        'PENDING' => 'Por asignar',
        'ASSIGNED' => 'Asignado',
        'IN_PROGRESS' => 'En ruta',
        'DONE' => 'Finalizado',
        'NO_SHOW' => 'No show',
    ];

    private const SERVICE_STATUS_LABELS_BILINGUAL = [
        'PENDING' => 'Por asignar / Unassigned',
        'ASSIGNED' => 'Asignado / Assigned',
        'IN_PROGRESS' => 'En ruta / In progress',
        'DONE' => 'Finalizado / Completed',
        'NO_SHOW' => 'No show / No-show',
    ];

    public static function bookingMap(bool $bilingual = false): array
    {
        return $bilingual ? self::BOOKING_STATUS_LABELS_BILINGUAL : self::BOOKING_STATUS_LABELS_ES;
    }

    public static function paymentMap(bool $bilingual = false): array
    {
        return $bilingual ? self::PAYMENT_STATUS_LABELS_BILINGUAL : self::PAYMENT_STATUS_LABELS_ES;
    }

    public static function serviceMap(bool $bilingual = false): array
    {
        return $bilingual ? self::SERVICE_STATUS_LABELS_BILINGUAL : self::SERVICE_STATUS_LABELS_ES;
    }

    public static function bookingLabel(string $code, bool $bilingual = false): string
    {
        $map = self::bookingMap($bilingual);
        return $map[$code] ?? $code;
    }

    public static function paymentLabel(string $code, bool $bilingual = false): string
    {
        $map = self::paymentMap($bilingual);
        return $map[$code] ?? $code;
    }

    public static function serviceLabel(string $code, bool $bilingual = false): string
    {
        $map = self::serviceMap($bilingual);
        return $map[$code] ?? $code;
    }
}
