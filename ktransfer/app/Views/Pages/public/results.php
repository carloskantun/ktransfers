<?php
declare(strict_types=1);

$options = $options ?? [];
$searchContext = $search_context ?? [];

$transferLabels = [
    'ROUND_TRIP' => 'Viaje redondo',
    'ONE_WAY' => 'Solo ida',
];

$directionLabels = [
    'AIRPORT_TO_DESTINATION' => 'Aeropuerto a hotel',
    'DESTINATION_TO_AIRPORT' => 'Hotel a aeropuerto',
];

$totalPax = (int) ($searchContext['total_pax'] ?? 0);
$summaryPills = [
    $transferLabels[(string) ($searchContext['trip_type'] ?? 'ONE_WAY')] ?? 'Viaje',
    $directionLabels[(string) ($searchContext['direction'] ?? 'AIRPORT_TO_DESTINATION')] ?? 'Destino',
    $totalPax . ' pasajero' . ($totalPax !== 1 ? 's' : ''),
    htmlspecialchars((string) ($searchContext['currency_code'] ?? 'USD'), ENT_QUOTES, 'UTF-8'),
];
?>
<div class="flow-shell flow-stack">
    <section class="flow-hero">
        <span class="flow-kicker">Paso 1 de 3</span>
        <h1>Elige el traslado.</h1>
        <p>Mostramos opciones claras para que puedas continuar con un solo clic. Elige el servicio que mejor se adapte a tu grupo y seguimos con los datos del viaje.</p>
    </section>

    <section class="step-tracker" aria-label="Progreso de reserva">
        <article class="step-chip is-active">
            <span class="step-label">Paso 1</span>
            <strong>Elegir servicio</strong>
            <span>Compara opciones disponibles y continúa.</span>
        </article>
        <article class="step-chip">
            <span class="step-label">Paso 2</span>
            <strong>Ingresar datos</strong>
            <span>Captura nombre, vuelo y contacto.</span>
        </article>
        <article class="step-chip">
            <span class="step-label">Paso 3</span>
            <strong>Confirmar reserva</strong>
            <span>Revisamos y dejamos todo listo.</span>
        </article>
    </section>

    <section class="flow-card flow-stack">
        <div>
            <span class="card-label">Resumen de busqueda</span>
            <h2>Resultados disponibles</h2>
        </div>

        <div class="pill-list">
            <?php foreach ($summaryPills as $pill): ?>
                <span class="pill"><?= $pill ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (empty($options)): ?>
        <section class="flow-card flow-stack">
            <div class="message-box">
                No encontramos tarifas disponibles para los criterios seleccionados. Puedes volver al buscador y cambiar destino, fecha o tipo de traslado.
            </div>

            <div class="action-row">
                <a class="action-link" href="/">Volver al buscador</a>
            </div>
        </section>
    <?php else: ?>
        <section class="flow-stack">
            <?php foreach ($options as $option): ?>
                <article class="choice-card">
                    <div class="choice-head">
                        <div>
                            <span class="card-label">Servicio disponible</span>
                            <h3><?= htmlspecialchars((string) ($option['service_type_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                            <div class="choice-meta">
                                Capacidad recomendada: <?= htmlspecialchars((string) ($option['pax_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>

                        <div class="choice-price">
                            <strong>$<?= htmlspecialchars(number_format((float) ($option['quoted_price'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= htmlspecialchars((string) ($option['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>

                    <div class="message-box">
                        Opcion simple y directa para continuar. Al hacer clic pasamos al formulario con los datos del pasajero y del vuelo.
                    </div>

                    <form method="post" action="/checkout/start">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="rate_rule_id" value="<?= (int) ($option['rate_rule_id'] ?? 0) ?>">
                        <input type="hidden" name="service_type_id" value="<?= (int) ($option['service_type_id'] ?? 0) ?>">
                        <input type="hidden" name="quoted_price" value="<?= htmlspecialchars((string) ($option['quoted_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="currency_code" value="<?= htmlspecialchars((string) ($option['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit">Elegir este traslado</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="action-row">
            <a class="action-link" href="/">Cambiar busqueda</a>
        </div>
    <?php endif; ?>
</div>
