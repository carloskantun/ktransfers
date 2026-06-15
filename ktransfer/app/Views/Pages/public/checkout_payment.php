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
$publicT = is_callable($public_t ?? null) ? $public_t : static fn (string $key, string $fallback): string => $fallback;
$t = static fn (string $key, string $fallback): string => $publicT('checkout.payment.' . $key, $fallback);
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$mercadoPagoEnabled = !empty($mercado_pago_enabled);
$openPayEnabled     = !empty($openpay_enabled);
$openPayPublicKey   = (string) ($openpay_public_key ?? '');
$openPayMerchantId  = (string) ($openpay_merchant_id ?? '');
$openPaySandbox     = !empty($openpay_sandbox);
$stripeEnabled      = !empty($stripe_enabled);
$payPalEnabled      = !empty($paypal_enabled);
$manualDefaultMethod = $payPalEnabled ? 'PAYPAL' : 'CARD';

$hasOnlineGateway = $mercadoPagoEnabled || $stripeEnabled || $openPayEnabled;
?>
<div class="flow-shell flow-stack">

    <section class="flow-hero">
        <span class="flow-kicker"><?= $escape($t('hero.kicker', 'Step 3 of 3')) ?></span>
        <h1><?= $escape($t('hero.title', 'Choose your payment method.')) ?></h1>
        <p><?= $escape($t('hero.description', 'Select your preferred option and confirm in one last step.')) ?></p>
    </section>

    <section class="step-tracker" aria-label="<?= $escape($t('tracker.aria', 'Booking progress')) ?>">
        <article class="step-chip">
            <span class="step-label"><?= $escape($t('tracker.step_1.label', 'Step 1')) ?></span>
            <strong><?= $escape($t('tracker.step_1.title', 'Choose service')) ?></strong>
            <span><?= $escape($t('tracker.step_1.description', 'Service selected.')) ?></span>
        </article>
        <article class="step-chip">
            <span class="step-label"><?= $escape($t('tracker.step_2.label', 'Step 2')) ?></span>
            <strong><?= $escape($t('tracker.step_2.title', 'Enter details')) ?></strong>
            <span><?= $escape($t('tracker.step_2.description', 'Details captured correctly.')) ?></span>
        </article>
        <article class="step-chip is-active">
            <span class="step-label"><?= $escape($t('tracker.step_3.label', 'Step 3')) ?></span>
            <strong><?= $escape($t('tracker.step_3.title', 'Payment method')) ?></strong>
            <span><?= $escape($t('tracker.step_3.description', 'Choose and confirm.')) ?></span>
        </article>
    </section>

    <section class="flow-card flow-stack">
        <div class="summary-grid compact">
            <div class="summary-item">
                <span class="stat-label"><?= $escape($t('summary.code', 'Code')) ?></span>
                <strong><?= $escape($booking_code) ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label"><?= $escape($t('summary.status', 'Status')) ?></span>
                <strong><?= $escape($t('summary.ready', 'Ready to pay')) ?></strong>
            </div>
        </div>
    </section>

    <section class="flow-card flow-card--payment">
        <div class="pm-intro">
            <span class="card-label"><?= $escape($t('section.kicker', 'Payment method')) ?></span>
            <h2 class="pm-title"><?= $escape($t('section.title', 'How would you like to pay?')) ?></h2>
            <p class="pm-subtitle"><?= $escape($t('section.description', 'Select one option to continue.')) ?></p>
        </div>

        <div id="pm-list" role="radiogroup" aria-label="<?= $escape($t('list.aria', 'Payment methods')) ?>">

            <?php if ($mercadoPagoEnabled): ?>
            <div class="pm-option" data-method="mp" role="radio" aria-checked="false" tabindex="0">
                <div class="pm-option-head">
                    <span class="pm-radio"></span>
                    <span class="pm-icon pm-icon--mp">MP</span>
                    <div class="pm-meta">
                        <strong>Mercado Pago</strong>
                        <span><?= $escape($t('mp.meta', 'Card, transfer or cash through Mercado Pago')) ?></span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" hidden>
                    <p class="pm-copy">
                        <?= $escape($t('mp.description', 'You will be redirected to Mercado Pago to complete a secure payment. Once approved, the booking is confirmed automatically.')) ?>
                    </p>
                    <form method="post" action="/checkout/mercado-pago/start">
                        <input type="hidden" name="_csrf" value="<?= $escape($csrf_token) ?>">
                        <button type="submit" class="pm-cta"><?= $escape($t('mp.cta', 'Continue with Mercado Pago')) ?> &rarr;</button>
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
                        <span><?= $escape($t('stripe.meta', 'Credit or debit card - secure payment via Stripe')) ?></span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" hidden>
                    <p class="pm-copy">
                        <?= $escape($t('stripe.description', 'You will be redirected to Stripe checkout. Once confirmed, you will return with your booking ready.')) ?>
                    </p>
                    <form method="post" action="/checkout/stripe/start">
                        <input type="hidden" name="_csrf" value="<?= $escape($csrf_token) ?>">
                        <button type="submit" class="pm-cta"><?= $escape($t('stripe.cta', 'Continue with Stripe')) ?> &rarr;</button>
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
                        <strong><?= $escape($t('openpay.title', 'OpenPay - Card')) ?></strong>
                        <span><?= $escape($t('openpay.meta', 'Credit or debit - encrypted details in your browser')) ?></span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" hidden>
                    <form id="openpay-form" method="post" action="/checkout/openpay/start">
                        <input type="hidden" name="_csrf" value="<?= $escape($csrf_token) ?>">
                        <input type="hidden" name="openpay_token" id="openpay_token_id" value="">

                        <div class="pm-fields">
                            <div class="pm-field pm-field--full">
                                <label for="op_holder_name"><?= $escape($t('openpay.form.holder_name', 'Cardholder name')) ?></label>
                                <input type="text" id="op_holder_name" autocomplete="cc-name" placeholder="<?= $escape($t('openpay.form.holder_name_placeholder', 'As shown on card')) ?>">
                            </div>
                            <div class="pm-field pm-field--full">
                                <label for="op_card_number"><?= $escape($t('openpay.form.card_number', 'Card number')) ?></label>
                                <input type="text" id="op_card_number" autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="pm-field">
                                <label for="op_exp_month"><?= $escape($t('openpay.form.exp_month', 'Month')) ?></label>
                                <input type="text" id="op_exp_month" autocomplete="cc-exp-month" placeholder="MM" maxlength="2">
                            </div>
                            <div class="pm-field">
                                <label for="op_exp_year"><?= $escape($t('openpay.form.exp_year', 'Year')) ?></label>
                                <input type="text" id="op_exp_year" autocomplete="cc-exp-year" placeholder="AA" maxlength="2">
                            </div>
                            <div class="pm-field">
                                <label for="op_cvv2">CVV</label>
                                <input type="text" id="op_cvv2" autocomplete="cc-csc" placeholder="123" maxlength="4">
                            </div>
                        </div>

                        <div
                            id="openpay-error"
                            class="message-box warn pm-openpay-error"
                            style="display:none;"
                            data-fallback="<?= $escape($t('openpay.error', 'Unable to process card. Check your details and try again.')) ?>"
                        ></div>

                        <button type="submit" id="openpay-submit-btn" class="pm-cta"><?= $escape($t('openpay.cta', 'Pay with OpenPay')) ?> &rarr;</button>
                    </form>
                    <p class="pm-note">
                        <?= $escape($t('openpay.note', 'Card details are encrypted in your browser before sending. They never pass through our servers.')) ?>
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
                        <strong><?= $escape($t('manual.title', 'Payment coordinated by our team')) ?></strong>
                        <span>
                            <?= $escape($payPalEnabled
                                ? $t('manual.meta_with_paypal', 'Transfer, cash, in-person card or PayPal (external)')
                                : $t('manual.meta_no_paypal', 'Transfer, cash or in-person card')) ?>
                        </span>
                    </div>
                    <span class="pm-chevron">&#8250;</span>
                </div>
                <div class="pm-panel" <?= !$hasOnlineGateway ? '' : 'hidden' ?>>
                    <p class="pm-copy">
                        <?= $escape($t('manual.description', 'Submit your request now and our team will contact you to coordinate payment and final confirmation.')) ?>
                    </p>
                    <div class="pill-list pm-manual-list">
                        <?php if ($payPalEnabled): ?>
                            <label class="pill pm-pill-button pm-pill-button--paypal"><input type="radio" name="payment_method" value="PAYPAL" form="confirm-booking-form" <?= $manualDefaultMethod === 'PAYPAL' ? 'checked' : '' ?>> PayPal</label>
                        <?php endif; ?>
                        <label class="pill pm-pill-button"><input type="radio" name="payment_method" value="CARD" form="confirm-booking-form" <?= $manualDefaultMethod === 'CARD' ? 'checked' : '' ?>> <?= $escape($t('manual.method.card', 'In-person card')) ?></label>
                        <label class="pill pm-pill-button"><input type="radio" name="payment_method" value="BANK" form="confirm-booking-form"> <?= $escape($t('manual.method.bank', 'Bank transfer')) ?></label>
                        <label class="pill pm-pill-button"><input type="radio" name="payment_method" value="CASH" form="confirm-booking-form"> <?= $escape($t('manual.method.cash', 'Cash')) ?></label>
                    </div>
                    <form id="confirm-booking-form" method="post" action="/checkout/payment">
                        <input type="hidden" name="_csrf" value="<?= $escape($csrf_token) ?>">
                        <button type="submit" class="pm-cta"><?= $escape($t('manual.cta', 'Submit request')) ?> &rarr;</button>
                    </form>
                    <p class="pm-note">
                        <?= $escape($t('manual.note', 'Our team will process the booking manually after receiving your request.')) ?>
                    </p>
                </div>
            </div>

        </div><!-- /pm-list -->
    </section>

    <div class="pm-back-row">
        <a class="action-link" href="/checkout/details">&#8592; <?= $escape($t('back', 'Back to edit details')) ?></a>
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
                : (errBox.getAttribute('data-fallback') || 'Unable to process card. Check your details and try again.');
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
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                activate(o);
                return;
            }

            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                e.preventDefault();
                var next = options[(Array.prototype.indexOf.call(options, o) + 1) % options.length];
                activate(next);
                next.focus();
                return;
            }

            if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                e.preventDefault();
                var idx = Array.prototype.indexOf.call(options, o);
                var prev = options[(idx - 1 + options.length) % options.length];
                activate(prev);
                prev.focus();
            }
        });
    });

    // If nothing pre-selected, activate first
    var anySelected = document.querySelector('#pm-list .pm-option.is-selected');
    if (!anySelected && options.length > 0) activate(options[0]);
})();
</script>

<style>
/* ── Payment method selector ─────────────────────────────────── */
.flow-card--payment {
    padding: 0;
    overflow: hidden;
}

.pm-intro {
    padding: 24px 28px 18px;
}

.pm-title {
    margin: 10px 0 4px;
    font-size: clamp(1.6rem, 2.2vw, 2.2rem);
    line-height: 1.05;
}

.pm-subtitle {
    margin: 0;
    color: var(--muted);
    line-height: 1.7;
}

#pm-list {
    border-top: 1px solid var(--line);
}

.pm-option {
    border-bottom: 1px solid var(--line);
    transition: background 0.15s;
}

.pm-option.is-selected {
    background: color-mix(in srgb, var(--surface-muted) 30%, transparent);
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

.pm-copy {
    margin: 0 0 16px;
    color: var(--muted);
    line-height: 1.7;
}

.pm-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
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
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
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
    text-align: center;
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

.pm-note {
    margin-top: 10px;
    font-size: 0.78rem;
    color: var(--muted);
    line-height: 1.6;
}

.pm-openpay-error {
    margin-bottom: 14px;
}

.pm-back-row {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 4px 0;
}

.pm-back-row .action-link {
    flex: 0 0 auto;
    min-width: 0;
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
    .pm-intro { padding: 18px 18px 16px; }
    .pm-option-head { padding: 16px 18px; gap: 10px; }
    .pm-panel { padding: 0 18px 20px 18px; }
    .pm-fields { grid-template-columns: 1fr 1fr; }
    .pm-field--full { grid-column: 1 / -1; }
    .pm-manual-list { grid-template-columns: 1fr; }
}
</style>
