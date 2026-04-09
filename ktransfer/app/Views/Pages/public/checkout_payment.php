<?php
declare(strict_types=1);
/** @var string $booking_code */
/** @var string $csrf_token */
?>
<div class="flow-shell flow-stack">
    <section class="flow-hero">
        <span class="flow-kicker">Paso 3 de 3</span>
        <h1>Confirma la reserva.</h1>
        <p>En esta etapa solo revisamos el metodo de pago y confirmamos la solicitud. El objetivo es que puedas terminar en un clic, sin pasos confusos.</p>
    </section>

    <section class="step-tracker" aria-label="Progreso de reserva">
        <article class="step-chip">
            <span class="step-label">Paso 1</span>
            <strong>Elegir servicio</strong>
            <span>Servicio seleccionado.</span>
        </article>
        <article class="step-chip">
            <span class="step-label">Paso 2</span>
            <strong>Ingresar datos</strong>
            <span>Datos capturados correctamente.</span>
        </article>
        <article class="step-chip is-active">
            <span class="step-label">Paso 3</span>
            <strong>Confirmar reserva</strong>
            <span>Ultimo clic para enviar la solicitud.</span>
        </article>
    </section>

    <section class="flow-card flow-stack">
        <div class="summary-grid compact">
            <div class="summary-item">
                <span class="stat-label">Codigo</span>
                <strong><?= htmlspecialchars($booking_code, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Estado</span>
                <strong>Listo para confirmar</strong>
            </div>
        </div>
    </section>

    <section class="split-grid">
        <article class="flow-card flow-stack">
            <div>
                <span class="card-label">Metodo de pago</span>
                <h2>Opciones disponibles</h2>
                <p>Estas son las formas de pago que el equipo puede gestionar para la reserva.</p>
            </div>

            <div class="pill-list">
                <span class="pill">PayPal</span>
                <span class="pill">Tarjeta de credito o debito</span>
                <span class="pill">Transferencia bancaria</span>
                <span class="pill">Pago en efectivo</span>
            </div>

            <div class="message-box warn">
                En esta version MVP, la confirmacion se procesa manualmente por el equipo despues de enviar la solicitud.
            </div>
        </article>

        <aside class="flow-card flow-stack">
            <div>
                <span class="card-label">Accion final</span>
                <h3>Un solo clic</h3>
                <p>Si todo se ve bien, confirma ahora y el equipo continuara con la reserva.</p>
            </div>

            <form method="post" action="/checkout/payment">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Confirmar reserva</button>
            </form>

            <a class="action-link" href="/checkout/details">Volver a editar datos</a>
        </aside>
    </section>
</div>
