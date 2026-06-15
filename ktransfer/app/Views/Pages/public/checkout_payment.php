<?php
declare(strict_types=1);
/** @var string $booking_code */
/** @var string $csrf_token */
/** @var bool $mercado_pago_enabled */
/** @var bool $openpay_enabled */
/** @var string $openpay_public_key */
/** @var string $openpay_merchant_id */
/** @var bool $openpay_sandbox */
/** @var bool $stripe_enabled */
/** @var bool $paypal_enabled */
$mercadoPagoEnabled = !empty($mercado_pago_enabled);
$openPayEnabled     = !empty($openpay_enabled);
$openPayPublicKey   = (string) ($openpay_public_key ?? '');
$openPayMerchantId  = (string) ($openpay_merchant_id ?? '');
$openPaySandbox     = !empty($openpay_sandbox);
$stripeEnabled      = !empty($stripe_enabled);
$payPalEnabled      = !empty($paypal_enabled);

$hasOnlineGateway = $mercadoPagoEnabled || $stripeEnabled || $openPayEnabled;
?>
<div class="flow-shell flow-stack">

    <section class="flow-hero">
        <span class="flow-kicker">Paso 3 de 3</span>
        <h1>Selecciona como pagar.</h1>
        <p>Elige tu metodo preferido y confirma. Solo un paso mas.</p>
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
            <strong>Metodo de pago</strong>
            <span>Elige y confirma.</span>
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
                <strong>Listo para pagar</strong>
            </div>
        </div>
    </section>

    <section class="flow-card" style="padding:0;overflow:hidden;">
        <div style="padding:24px 28px 20px;">
            <span class="card-label">Metodo de pago</span>
            <h2 style="margin:10px 0 4px;font-size:clamp(1.6rem,2.4vw,2.2rem);">¿Como prefieres pagar?</h2>
            <p style="color:var(--muted);margin:0;line-height:1.7;">Selecciona una opcion para continuar.</p>
        </div>

        <div id="pm-list" role="radiogroup" aria-label="Metodos de pago">

            <?php if ($mercadoPagoEnabled): ?>
            <div class="pm-option" data-method="mp" role="radio" aria-checked="false" tabindex="0">
                <div class="pm-option-head">
                    <span class="pm-radio"></span>
                    <span class="pm-icon pm-icon--mp">MP</span>
                    <div class="pm-meta">
                        <strong>Mercado Pago</strong>
                        <span>Tarjeta, transferencia o efectivo via Mercado Pago</span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" hidden>
                    <p style="color:var(--muted);margin:0 0 16px;line-height:1.7;">
                        Seras redirigido a Mercado Pago para completar el pago de forma segura. Al aprobarse, la reserva se confirma automaticamente.
                    </p>
                    <form method="post" action="/checkout/mercado-pago/start">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="pm-cta">Continuar con Mercado Pago &rarr;</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($stripeEnabled): ?>
            <div class="pm-option" data-method="stripe" role="radio" aria-checked="false" tabindex="0">
                <div class="pm-option-head">
                    <span class="pm-radio"></span>
                    <span class="pm-icon pm-icon--stripe">&#9889;</span>
                    <div class="pm-meta">
                        <strong>Stripe</strong>
                        <span>Tarjeta de credito o debito — pago seguro via Stripe</span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" hidden>
                    <p style="color:var(--muted);margin:0 0 16px;line-height:1.7;">
                        Seras redirigido a la pagina de pago de Stripe. Al confirmar, regresaras automaticamente con la reserva lista.
                    </p>
                    <form method="post" action="/checkout/stripe/start">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="pm-cta">Continuar con Stripe &rarr;</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($openPayEnabled): ?>
            <div class="pm-option" data-method="openpay" role="radio" aria-checked="false" tabindex="0">
                <div class="pm-option-head">
                    <span class="pm-radio"></span>
                    <span class="pm-icon pm-icon--openpay">OP</span>
                    <div class="pm-meta">
                        <strong>OpenPay — Tarjeta</strong>
                        <span>Credito o debito — datos cifrados en tu navegador</span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" hidden>
                    <form id="openpay-form" method="post" action="/checkout/openpay/start">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="openpay_token" id="openpay_token_id" value="">

                        <div class="pm-fields">
                            <div class="pm-field pm-field--full">
                                <label for="op_holder_name">Nombre en la tarjeta</label>
                                <input type="text" id="op_holder_name" autocomplete="cc-name" placeholder="Como aparece en la tarjeta">
                            </div>
                            <div class="pm-field pm-field--full">
                                <label for="op_card_number">Numero de tarjeta</label>
                                <input type="text" id="op_card_number" autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="pm-field">
                                <label for="op_exp_month">Mes</label>
                                <input type="text" id="op_exp_month" autocomplete="cc-exp-month" placeholder="MM" maxlength="2">
                            </div>
                            <div class="pm-field">
                                <label for="op_exp_year">Año</label>
                                <input type="text" id="op_exp_year" autocomplete="cc-exp-year" placeholder="AA" maxlength="2">
                            </div>
                            <div class="pm-field">
                                <label for="op_cvv2">CVV</label>
                                <input type="text" id="op_cvv2" autocomplete="cc-csc" placeholder="123" maxlength="4">
                            </div>
                        </div>

                        <div id="openpay-error" class="message-box warn" style="display:none;margin-bottom:14px;"></div>

                        <button type="submit" id="openpay-submit-btn" class="pm-cta">Pagar con OpenPay &rarr;</button>
                    </form>
                    <p style="margin-top:10px;font-size:0.76rem;color:var(--muted);line-height:1.6;">
                        Tus datos de tarjeta se cifran en el navegador antes de enviarse. Nunca pasan por nuestros servidores.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <div class="pm-option <?= !$hasOnlineGateway ? 'pm-option--default' : '' ?>"
                 data-method="manual" role="radio"
                 aria-checked="<?= !$hasOnlineGateway ? 'true' : 'false' ?>"
                 tabindex="0">
                <div class="pm-option-head">
                    <span class="pm-radio"></span>
                    <span class="pm-icon pm-icon--manual">&#128196;</span>
                    <div class="pm-meta">
                        <strong>Pago coordinado por el equipo</strong>
                        <span>Transferencia, efectivo, tarjeta presencial o PayPal<?= $payPalEnabled ? ' (externo)' : '' ?></span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" <?= !$hasOnlineGateway ? '' : 'hidden' ?>>
                    <p style="color:var(--muted);margin:0 0 16px;line-height:1.7;">
                        Envia la solicitud ahora y el equipo te contactara para coordinar el pago. PayPal se registra aparte; transferencia, efectivo y tarjeta presencial se guardan como pago en abordar.
                    </p>
                    <div class="pill-list pm-manual-list" style="margin-bottom:16px;">
                        <?php if ($payPalEnabled): ?>
                        <label class="pill pm-pill-button pm-pill-button--paypal"><input type="radio" name="payment_method" value="PAYPAL" form="confirm-booking-form"> PayPal</label>
                        <?php endif; ?>
                        <label class="pill pm-pill-button"><input type="radio" name="payment_method" value="CARD" form="confirm-booking-form"> Tarjeta presencial</label>
                        <label class="pill pm-pill-button"><input type="radio" name="payment_method" value="BANK" form="confirm-booking-form" <?= !$payPalEnabled ? 'checked' : '' ?>> Transferencia</label>
                        <label class="pill pm-pill-button"><input type="radio" name="payment_method" value="CASH" form="confirm-booking-form" <?= $payPalEnabled ? 'checked' : '' ?>> Efectivo</label>
                    </div>
                    <form id="confirm-booking-form" method="post" action="/checkout/payment">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="pm-cta">Enviar solicitud &rarr;</button>
                    </form>
                    <p style="margin-top:10px;font-size:0.76rem;color:var(--muted);line-height:1.6;">
                        El equipo procesara la reserva manualmente tras recibir la solicitud.
                    </p>
                </div>
            </div>

        </div><!-- /pm-list -->
    </section>

    <div style="display:flex;gap:12px;align-items:center;padding:4px 0;">
        <a class="action-link" href="/checkout/details" style="flex:0 0 auto;min-width:0;">&#8592; Volver a editar datos</a>
    </div>

</div>

<?php if ($openPayEnabled): ?>
<script src="https://js.openpay.mx/openpay.v1.min.js"></script>
<script src="https://js.openpay.mx/openpay-data.v1.min.js"></script>
<script>
(function () {
    var merchantId = <?= json_encode($openPayMerchantId) ?>;
    var publicKey  = <?= json_encode($openPayPublicKey) ?>;
    var sandbox    = <?= $openPaySandbox ? 'true' : 'false' ?>;
    OpenPay.setId(merchantId);
    OpenPay.setApiKey(publicKey);
    OpenPay.setSandboxMode(sandbox);

    document.getElementById('openpay-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn    = document.getElementById('openpay-submit-btn');
        var errBox = document.getElementById('openpay-error');
        btn.disabled = true;
        errBox.style.display = 'none';

        OpenPay.token.create({
            holder_name:       document.getElementById('op_holder_name').value.trim(),
            card_number:       document.getElementById('op_card_number').value.replace(/\s/g, ''),
            expiration_month:  document.getElementById('op_exp_month').value.trim(),
            expiration_year:   document.getElementById('op_exp_year').value.trim(),
            cvv2:              document.getElementById('op_cvv2').value.trim()
        }, function (res) {
            document.getElementById('openpay_token_id').value = res.data.id;
            document.getElementById('openpay-form').submit();
        }, function (res) {
            btn.disabled = false;
            errBox.textContent = (res.data && res.data.description)
                ? res.data.description
                : 'Error al procesar la tarjeta. Revisa los datos e intenta de nuevo.';
            errBox.style.display = 'block';
        });
    });
})();
</script>
<?php endif; ?>

<script>
(function () {
    var options = document.querySelectorAll('#pm-list .pm-option');

    function activate(el) {
        options.forEach(function (o) {
            var panel  = o.querySelector('.pm-panel');
            var isThis = o === el;
            o.classList.toggle('is-selected', isThis);
            o.setAttribute('aria-checked', isThis ? 'true' : 'false');
            if (panel) panel.hidden = !isThis;
        });
    }

    options.forEach(function (o) {
        // Auto-open first or default
        if (o.getAttribute('aria-checked') === 'true') activate(o);

        o.querySelector('.pm-option-head').addEventListener('click', function () {
            activate(o);
        });
        o.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(o); }
        });
    });

    // If nothing pre-selected, activate first
    var anySelected = document.querySelector('#pm-list .pm-option.is-selected');
    if (!anySelected && options.length > 0) activate(options[0]);
})();
</script>

<style>
/* ── Payment method selector ─────────────────────────────────── */
#pm-list {
    border-top: 1px solid var(--line);
}

.pm-option {
    border-bottom: 1px solid var(--line);
    transition: background 0.15s;
}

.pm-option:last-child {
    border-bottom: none;
}

.pm-option-head {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 28px;
    cursor: pointer;
    user-select: none;
}

.pm-option-head:hover {
    background: color-mix(in srgb, var(--surface-muted) 60%, transparent);
}

.pm-radio {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    border: 2px solid var(--line-strong);
    border-radius: 50%;
    background: var(--surface);
    position: relative;
    transition: border-color 0.15s;
}

.pm-option.is-selected .pm-radio {
    border-color: var(--accent, #0f3f46);
}

.pm-option.is-selected .pm-radio::after {
    content: '';
    position: absolute;
    inset: 3px;
    border-radius: 50%;
    background: var(--accent, #0f3f46);
}

.pm-icon {
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 0.68rem;
    font-weight: 900;
    letter-spacing: 0.04em;
    border: 1px solid var(--line);
    background: var(--surface-muted);
    color: var(--ink-soft);
}

.pm-icon--mp     { background: #fff7e6; border-color: #f0c040; color: #5a3e00; }
.pm-icon--stripe { background: #f0f4ff; border-color: #a0b4f0; color: #1a3080; font-size: 1rem; }
.pm-icon--paypal { background: #e8f0ff; border-color: #7baaf0; color: #003087; }
.pm-icon--openpay { background: #e8f8f0; border-color: #6abf8a; color: #1a5c33; }
.pm-icon--manual { background: var(--surface-muted); font-size: 1rem; }

.pm-meta {
    flex: 1;
    min-width: 0;
}

.pm-meta strong {
    display: block;
    font-size: 1rem;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.3;
}

.pm-meta span {
    font-size: 0.84rem;
    color: var(--muted);
    line-height: 1.5;
}

.pm-chevron {
    font-size: 1.4rem;
    color: var(--muted);
    line-height: 1;
    transition: transform 0.2s;
}

.pm-option.is-selected .pm-chevron {
    transform: rotate(90deg);
    color: var(--ink-soft);
}

.pm-panel {
    padding: 0 28px 24px 84px;
}

.pm-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    padding: 14px 24px;
    border: none;
    border-radius: var(--radius-sm-token, 14px);
    background: var(--accent, #0f3f46);
    color: #ffffff;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s;
}

.pm-cta:hover { opacity: 0.88; }
.pm-cta:disabled { opacity: 0.5; cursor: not-allowed; }

.pm-manual-list {
    gap: 12px;
}

.pm-pill-button {
    position: relative;
    min-height: 46px;
    padding: 0;
    overflow: hidden;
    border-radius: 14px;
    background: var(--surface);
}

.pm-pill-button input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.pm-pill-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 16px;
    font-weight: 700;
    color: var(--ink);
    border: 1px solid var(--line);
}

.pm-pill-button:has(input:checked) {
    border-color: var(--accent, #0f3f46);
    background: color-mix(in srgb, var(--accent, #0f3f46) 10%, white);
    color: var(--accent, #0f3f46);
}

.pm-pill-button--paypal {
    background: linear-gradient(135deg, #003087, #0070ba);
    border-color: #003087;
    color: #ffffff;
}

.pm-pill-button--paypal:has(input:checked) {
    background: linear-gradient(135deg, #00256b, #005fa3);
    border-color: #001f59;
    color: #ffffff;
    box-shadow: 0 10px 24px rgba(0, 48, 135, 0.22);
}

/* Card fields grid */
.pm-fields {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
}

.pm-field { display: grid; gap: 6px; }
.pm-field--full { grid-column: 1 / -1; }

.pm-field label {
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--muted);
}

.pm-field input {
    width: 100%;
    box-sizing: border-box;
}

@media (max-width: 600px) {
    .pm-option-head { padding: 16px 18px; gap: 10px; }
    .pm-panel { padding: 0 18px 20px 18px; }
    .pm-fields { grid-template-columns: 1fr 1fr; }
    .pm-field--full { grid-column: 1 / -1; }
}
</style>
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
        <?php if ($mercadoPagoEnabled): ?>
            <article class="flow-card flow-stack">
                <div>
                    <span class="card-label">Pago en linea</span>
                    <h2>Mercado Pago</h2>
                    <p>Paga en sandbox con Mercado Pago. Al aprobarse el pago, la reserva se confirma automaticamente.</p>
                </div>

                <form method="post" action="/checkout/mercado-pago/start">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Pagar con Mercado Pago</button>
                </form>
            </article>
        <?php endif; ?>

        <?php if ($stripeEnabled): ?>
            <article class="flow-card flow-stack">
                <div>
                    <span class="card-label">Pago con tarjeta</span>
                    <h2>Stripe</h2>
                    <p>Paga de forma segura con tarjeta de credito o debito. Seras redirigido a la pagina de pago de Stripe y regresaras automaticamente al confirmar.</p>
                </div>

                <form method="post" action="/checkout/stripe/start">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Pagar con Stripe</button>
                </form>
            </article>
        <?php endif; ?>

        <?php if ($payPalEnabled): ?>
            <article class="flow-card flow-stack">
                <div>
                    <span class="card-label">Pago en linea</span>
                    <h2>PayPal</h2>
                    <p>Paga con tu cuenta PayPal o con tarjeta a traves de PayPal. Seras redirigido a PayPal para completar el pago.</p>
                </div>

                <form method="post" action="/checkout/paypal/start">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit">Pagar con PayPal</button>
                </form>
            </article>
        <?php endif; ?>

        <?php if ($openPayEnabled): ?>
            <article class="flow-card flow-stack" id="openpay-card">
                <div>
                    <span class="card-label">Pago con tarjeta</span>
                    <h2>OpenPay</h2>
                    <p>Paga de forma segura con tu tarjeta de credito o debito. Los datos se cifran directamente en tu navegador antes de enviarse.</p>
                </div>

                <form id="openpay-form" method="post" action="/checkout/openpay/start">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="openpay_token" id="openpay_token_id" value="">

                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="op_holder_name">Nombre en la tarjeta</label>
                        <input type="text" id="op_holder_name" autocomplete="cc-name" placeholder="Como aparece en la tarjeta" style="width:100%;">
                    </div>
                    <div class="form-group" style="margin-bottom:12px;">
                        <label for="op_card_number">Numero de tarjeta</label>
                        <input type="text" id="op_card_number" autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="19" style="width:100%;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                        <div class="form-group">
                            <label for="op_exp_month">Mes (MM)</label>
                            <input type="text" id="op_exp_month" autocomplete="cc-exp-month" placeholder="MM" maxlength="2" style="width:100%;">
                        </div>
                        <div class="form-group">
                            <label for="op_exp_year">Año (YY)</label>
                            <input type="text" id="op_exp_year" autocomplete="cc-exp-year" placeholder="YY" maxlength="2" style="width:100%;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:16px;">
                        <label for="op_cvv2">CVV</label>
                        <input type="text" id="op_cvv2" autocomplete="cc-csc" placeholder="3 o 4 digitos" maxlength="4" style="width:100%;">
                    </div>

                    <div id="openpay-error" class="message-box warn" style="display:none;margin-bottom:12px;"></div>

                    <button type="submit" id="openpay-submit-btn">Pagar con OpenPay</button>
                </form>

                <p style="font-size:0.75rem;color:var(--muted,#777);margin-top:4px;">
                    Procesado por OpenPay &mdash; sus datos de tarjeta nunca tocan nuestros servidores.
                </p>
            </article>

            <script src="https://js.openpay.mx/openpay.v1.min.js"></script>
            <script src="https://js.openpay.mx/openpay-data.v1.min.js"></script>
            <script>
            (function () {
                var merchantId = <?= json_encode($openPayMerchantId) ?>;
                var publicKey  = <?= json_encode($openPayPublicKey) ?>;
                var sandbox    = <?= $openPaySandbox ? 'true' : 'false' ?>;

                OpenPay.setId(merchantId);
                OpenPay.setApiKey(publicKey);
                OpenPay.setSandboxMode(sandbox);

                document.getElementById('openpay-form').addEventListener('submit', function (e) {
                    e.preventDefault();
                    var btn = document.getElementById('openpay-submit-btn');
                    var errBox = document.getElementById('openpay-error');
                    btn.disabled = true;
                    errBox.style.display = 'none';

                    var cardData = {
                        holder_name:  document.getElementById('op_holder_name').value.trim(),
                        card_number:  document.getElementById('op_card_number').value.replace(/\s/g, ''),
                        expiration_month: document.getElementById('op_exp_month').value.trim(),
                        expiration_year:  document.getElementById('op_exp_year').value.trim(),
                        cvv2: document.getElementById('op_cvv2').value.trim()
                    };

                    OpenPay.token.create(cardData, function (response) {
                        document.getElementById('openpay_token_id').value = response.data.id;
                        document.getElementById('openpay-form').submit();
                    }, function (response) {
                        btn.disabled = false;
                        var msg = (response.data && response.data.description)
                            ? response.data.description
                            : 'Error al procesar la tarjeta. Revisa los datos e intenta de nuevo.';
                        errBox.textContent = msg;
                        errBox.style.display = 'block';
                    });
                });
            })();
            </script>
        <?php endif; ?>

        <article class="flow-card flow-stack">
            <div>
                <span class="card-label">Metodo de pago</span>
                <h2>Opciones disponibles</h2>
                <p>Estas son las formas de pago que el equipo puede gestionar para la reserva.</p>
            </div>

            <div class="pill-list">
                <?php if (!$payPalEnabled): ?>
                <label class="pill"><input type="radio" name="payment_method" value="PAYPAL" form="confirm-booking-form"> PayPal</label>
                <?php endif; ?>
                <label class="pill"><input type="radio" name="payment_method" value="CARD" form="confirm-booking-form"> Tarjeta de credito o debito</label>
                <label class="pill"><input type="radio" name="payment_method" value="BANK" form="confirm-booking-form" checked> Transferencia bancaria</label>
                <label class="pill"><input type="radio" name="payment_method" value="CASH" form="confirm-booking-form"> Pago en efectivo</label>
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

            <form id="confirm-booking-form" method="post" action="/checkout/payment">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Confirmar reserva</button>
            </form>

            <a class="action-link" href="/checkout/details">Volver a editar datos</a>
        </aside>
    </section>
</div>
