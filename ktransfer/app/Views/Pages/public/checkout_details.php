<?php
declare(strict_types=1);
/** @var string $booking_code */
/** @var string $csrf_token */
/** @var array $airlines */
?>
<div class="flow-shell flow-stack">
    <section class="flow-hero">
        <span class="flow-kicker">Paso 2 de 3</span>
        <h1>Completa los datos del viaje.</h1>
        <p>Pedimos solo la informacion necesaria para coordinar la llegada y el contacto. Todo esta organizado en bloques grandes y faciles de completar.</p>
    </section>

    <section class="step-tracker" aria-label="Progreso de reserva">
        <article class="step-chip">
            <span class="step-label">Paso 1</span>
            <strong>Elegir servicio</strong>
            <span>Servicio seleccionado correctamente.</span>
        </article>
        <article class="step-chip is-active">
            <span class="step-label">Paso 2</span>
            <strong>Ingresar datos</strong>
            <span>Captura nombre, vuelo y contacto.</span>
        </article>
        <article class="step-chip">
            <span class="step-label">Paso 3</span>
            <strong>Confirmar reserva</strong>
            <span>Revisamos y cerramos la solicitud.</span>
        </article>
    </section>

    <section class="flow-card flow-stack">
        <div class="summary-grid compact">
            <div class="summary-item">
                <span class="stat-label">Codigo</span>
                <strong><?= htmlspecialchars($booking_code, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Siguiente paso</span>
                <strong>Completar y continuar</strong>
            </div>
        </div>
    </section>

    <form class="flow-stack" method="post" action="/checkout/details">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <section class="flow-card flow-stack">
            <div>
                <span class="card-label">Datos personales</span>
                <h2>Quien viaja</h2>
                <p>Ingresa primero el nombre y el correo. Son los datos principales para identificar y confirmar la reserva.</p>
            </div>

            <div class="field-grid">
                <div class="field-block">
                    <label for="customer_name">Nombre</label>
                    <input id="customer_name" type="text" name="customer_name" required autocomplete="given-name">
                </div>

                <div class="field-block">
                    <label for="customer_last_name">Apellido</label>
                    <input id="customer_last_name" type="text" name="customer_last_name" autocomplete="family-name">
                </div>

                <div class="field-block">
                    <label for="customer_email">Email</label>
                    <input id="customer_email" type="email" name="customer_email" required autocomplete="email">
                </div>

                <div class="field-block">
                    <label for="customer_phone">Telefono</label>
                    <input id="customer_phone" type="tel" name="customer_phone" autocomplete="tel">
                </div>
            </div>
        </section>

        <section class="flow-card flow-stack">
            <div>
                <span class="card-label">Datos del vuelo</span>
                <h2>Informacion de llegada o salida</h2>
                <p>Si ya tienes la aerolinea y el numero de vuelo, agregalos aqui. Esto ayuda a coordinar mejor el servicio.</p>
            </div>

            <div class="field-grid">
                <div class="field-block">
                    <label for="airline_query">Aerolinea</label>
                    <input id="airline_query" type="text" placeholder="Escribe el nombre o codigo de la aerolinea" autocomplete="off">
                    <input type="hidden" name="airline_id" id="airline_id">
                    <ul id="airlines_suggestions" class="places-list" style="display:none;"></ul>
                    <div class="help-text">Selecciona una opcion de la lista para llenar la aerolinea.</div>
                </div>

                <div class="field-block">
                    <label for="flight_number">Numero de vuelo</label>
                    <input id="flight_number" type="text" name="flight_number" placeholder="Ejemplo: AA1234">
                </div>

                <div class="field-block span-2">
                    <label for="pickup_notes">Notas para recogida</label>
                    <textarea id="pickup_notes" name="pickup_notes" rows="4" placeholder="Ejemplo: viajamos con silla de bebe o llegamos con equipaje extra"></textarea>
                </div>
            </div>
        </section>

        <section class="flow-card flow-stack">
            <div class="message-box">
                Revisa que nombre, correo y vuelo esten correctos. Cuando todo este listo, solo haz clic en continuar.
            </div>

            <div class="action-row">
                <button type="submit">Continuar al pago</button>
                <a class="action-link" href="/">Volver al inicio</a>
            </div>
        </section>
    </form>
</div>

<script>
    const airlineQuery = document.getElementById('airline_query');
    const airlineHidden = document.getElementById('airline_id');
    const suggestions = document.getElementById('airlines_suggestions');
    let debounceTimer;

    const fetchAirlines = async () => {
        const q = airlineQuery.value.trim();
        if (q.length < 1) {
            suggestions.style.display = 'none';
            suggestions.innerHTML = '';
            return;
        }

        try {
            const response = await fetch('/api/airlines?q=' + encodeURIComponent(q));
            const data = await response.json();
            const items = Array.isArray(data.items) ? data.items : [];

            suggestions.innerHTML = '';
            if (items.length === 0) {
                suggestions.style.display = 'none';
                return;
            }

            items.forEach((item) => {
                const li = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = item.code + ' - ' + item.name;
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    airlineHidden.value = String(item.id);
                    airlineQuery.value = item.code + ' - ' + item.name;
                    suggestions.style.display = 'none';
                    suggestions.innerHTML = '';
                });
                li.appendChild(button);
                suggestions.appendChild(li);
            });

            suggestions.style.display = 'grid';
        } catch (e) {
            suggestions.style.display = 'none';
            suggestions.innerHTML = '';
        }
    };

    airlineQuery.addEventListener('input', () => {
        airlineHidden.value = '';
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchAirlines, 250);
    });

    document.addEventListener('click', (event) => {
        if (!suggestions.contains(event.target) && event.target !== airlineQuery) {
            suggestions.style.display = 'none';
        }
    });
</script>
