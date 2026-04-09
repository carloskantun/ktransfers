<?php
/** @var array $service_types */
/** @var array $zones */
/** @var array $places */
/** @var array $errors */
/** @var array $form */
?>
<div class="page-header">
    <h1>Nueva Reserva Manual</h1>
    <a href="/admin/bookings" class="btn btn-secondary">← Volver a Bookings</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <form method="post" action="/admin/bookings/create">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::token() ?>">

        <div class="form-group">
            <label for="customer_name">Nombre</label>
            <input id="customer_name" name="customer_name" required value="<?= htmlspecialchars((string) ($form['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="customer_last_name">Apellido</label>
            <input id="customer_last_name" name="customer_last_name" value="<?= htmlspecialchars((string) ($form['customer_last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="customer_email">Email</label>
            <input id="customer_email" type="email" name="customer_email" required value="<?= htmlspecialchars((string) ($form['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="customer_phone">Teléfono</label>
            <input id="customer_phone" name="customer_phone" value="<?= htmlspecialchars((string) ($form['customer_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="trip_type">Tipo de viaje</label>
            <select id="trip_type" name="trip_type" required>
                <option value="ONE_WAY" <?= ($form['trip_type'] ?? '') === 'ONE_WAY' ? 'selected' : '' ?>>One Way</option>
                <option value="ROUND_TRIP" <?= ($form['trip_type'] ?? '') === 'ROUND_TRIP' ? 'selected' : '' ?>>Round Trip</option>
            </select>
        </div>

        <div class="form-group">
            <label for="direction">Dirección</label>
            <select id="direction" name="direction" required>
                <option value="AIRPORT_TO_DESTINATION" <?= ($form['direction'] ?? '') === 'AIRPORT_TO_DESTINATION' ? 'selected' : '' ?>>Airport → Destination</option>
                <option value="DESTINATION_TO_AIRPORT" <?= ($form['direction'] ?? '') === 'DESTINATION_TO_AIRPORT' ? 'selected' : '' ?>>Destination → Airport</option>
            </select>
        </div>

        <div class="form-group">
            <label for="service_type_id">Servicio</label>
            <select id="service_type_id" name="service_type_id" required>
                <?php foreach ($service_types as $serviceType): ?>
                    <?php $serviceTypeId = (int) ($serviceType['id'] ?? 0); ?>
                    <option value="<?= $serviceTypeId ?>" <?= (int) ($form['service_type_id'] ?? 0) === $serviceTypeId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($serviceType['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="zone_id">Zona</label>
            <select id="zone_id" name="zone_id" required>
                <?php foreach ($zones as $zone): ?>
                    <?php $zoneId = (int) ($zone['id'] ?? 0); ?>
                    <option value="<?= $zoneId ?>" <?= (int) ($form['zone_id'] ?? 0) === $zoneId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($zone['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="place_id">Hotel / Place</label>
            <select id="place_id" name="place_id" required>
                <option value="">Selecciona...</option>
                <?php foreach ($places as $place): ?>
                    <?php $placeId = (int) ($place['id'] ?? 0); ?>
                    <option
                        value="<?= $placeId ?>"
                        data-zone-id="<?= (int) ($place['zone_id'] ?? 0) ?>"
                        <?= (int) ($form['place_id'] ?? 0) === $placeId ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars((string) ($place['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) ($place['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="arrival_datetime">Llegada</label>
            <input id="arrival_datetime" type="datetime-local" name="arrival_datetime" value="<?= htmlspecialchars((string) ($form['arrival_datetime'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="departure_datetime">Salida</label>
            <input id="departure_datetime" type="datetime-local" name="departure_datetime" value="<?= htmlspecialchars((string) ($form['departure_datetime'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="airline">Aerolínea</label>
            <input id="airline" name="airline" value="<?= htmlspecialchars((string) ($form['airline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="flight_number">Vuelo</label>
            <input id="flight_number" name="flight_number" value="<?= htmlspecialchars((string) ($form['flight_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="pickup_notes">Notas pickup</label>
            <input id="pickup_notes" name="pickup_notes" value="<?= htmlspecialchars((string) ($form['pickup_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="adults">Adults</label>
            <input id="adults" type="number" min="1" name="adults" required value="<?= htmlspecialchars((string) ($form['adults'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="children">Children</label>
            <input id="children" type="number" min="0" name="children" required value="<?= htmlspecialchars((string) ($form['children'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="currency_code">Moneda</label>
            <select id="currency_code" name="currency_code" required>
                <?php foreach (['USD', 'MXN', 'CAD', 'EUR'] as $currencyCode): ?>
                    <option value="<?= $currencyCode ?>" <?= strtoupper((string) ($form['currency_code'] ?? 'USD')) === $currencyCode ? 'selected' : '' ?>>
                        <?= $currencyCode ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="price_total">Total</label>
            <input id="price_total" type="number" min="0" step="0.01" name="price_total" required value="<?= htmlspecialchars((string) ($form['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="status">Estado reserva</label>
            <select id="status" name="status" required>
                <?php foreach (['PENDING', 'CONFIRMED', 'COMPLETED', 'NO_SHOW', 'CANCELLED'] as $status): ?>
                    <option value="<?= $status ?>" <?= ($form['status'] ?? 'PENDING') === $status ? 'selected' : '' ?>>
                        <?= $status ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_status">Estado pago</label>
            <select id="payment_status" name="payment_status" required>
                <?php foreach (['UNPAID', 'PARTIAL', 'PAID', 'REFUNDED'] as $paymentStatus): ?>
                    <option value="<?= $paymentStatus ?>" <?= ($form['payment_status'] ?? 'UNPAID') === $paymentStatus ? 'selected' : '' ?>>
                        <?= $paymentStatus ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="comments">Comentarios</label>
            <textarea id="comments" name="comments" rows="4"><?= htmlspecialchars((string) ($form['comments'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Reserva</button>
            <a href="/admin/bookings" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
    (function () {
        var zoneSelect = document.getElementById('zone_id');
        var placeSelect = document.getElementById('place_id');
        if (!zoneSelect || !placeSelect) {
            return;
        }

        function filterPlacesByZone() {
            var currentZoneId = zoneSelect.value;
            var hasVisibleSelected = false;

            for (var index = 0; index < placeSelect.options.length; index++) {
                var option = placeSelect.options[index];
                if (option.value === '') {
                    option.hidden = false;
                    continue;
                }

                var optionZoneId = option.getAttribute('data-zone-id');
                var isVisible = optionZoneId === currentZoneId;
                option.hidden = !isVisible;

                if (isVisible && option.selected) {
                    hasVisibleSelected = true;
                }
            }

            if (!hasVisibleSelected) {
                placeSelect.value = '';
            }
        }

        zoneSelect.addEventListener('change', filterPlacesByZone);
        filterPlacesByZone();
    })();
</script>
