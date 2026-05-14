<?php
declare(strict_types=1);

$errors = $errors ?? [];
$form = $form ?? [];
$saved = (bool) ($saved ?? false);
$suggestedPrefix = (string) ($suggested_prefix ?? '');
$heroImages = is_array($form['hero_images'] ?? null) ? $form['hero_images'] : [];
$contactChannels = is_array($form['contact_channels'] ?? null) ? $form['contact_channels'] : [];
$contactTypes = [
    'whatsapp' => 'WhatsApp',
    'call' => 'Telefono',
    'sms' => 'SMS',
    'telegram' => 'Telegram',
    'email' => 'Email',
];
?>
<style>
    .settings-grid {
        display: grid;
        gap: 16px;
    }
    .settings-card {
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
    }
    .settings-card h2 {
        margin: 0 0 6px;
        font-size: 1.05rem;
    }
    .settings-card p {
        margin: 0 0 14px;
        color: var(--muted);
    }
    .settings-two-col {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .settings-five-col {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }
    .settings-three-col {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .contact-channel-row {
        display: grid;
        grid-template-columns: 140px minmax(140px, 1fr) minmax(160px, 1fr) minmax(220px, 1.3fr);
        gap: 10px;
        align-items: start;
        padding: 12px;
        border: 1px solid #e7edf5;
        border-radius: 12px;
        background: #f8fafc;
    }
    .field-note {
        display: block;
        margin-top: 6px;
        color: var(--muted);
        font-size: 0.84rem;
        line-height: 1.4;
    }
    .switch {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #334155;
    }
    .hero-image-row {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) minmax(220px, 1fr);
        gap: 10px;
        align-items: end;
    }
    @media (max-width: 980px) {
        .settings-two-col,
        .settings-five-col,
        .settings-three-col,
        .contact-channel-row,
        .hero-image-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1>Home Settings</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">Configuracion de marca, prefijo de reserva, colores y pasarelas futuras.</p>
    </div>
    <a href="/" class="btn btn-secondary" target="_blank" rel="noreferrer">Ver sitio</a>
</div>

<?php if ($saved): ?>
    <div class="notice">Configuracion guardada correctamente.</div>
<?php endif; ?>

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
    <form method="post" action="/admin/content/home" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <div class="settings-grid">
            <section class="settings-card">
                <h2>Marca y codigo de reserva</h2>
                <p>Define logos, nombre de marca y prefijo de reserva de 3 letras (ejemplo: ETC-20260508-AB12).</p>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>Logo tema noche (fondo oscuro) / ruta publica</label>
                        <input type="text" name="brand_logo" value="<?= htmlspecialchars((string) ($form['brand_logo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/uploads/home/header-logo.webp">
                    </div>
                    <div class="form-group">
                        <label>Subir logo tema noche</label>
                        <input type="file" name="brand_logo_file" accept="image/png,image/jpeg,image/webp">
                    </div>

                    <div class="form-group">
                        <label>Logo tema dia (fondo claro) / ruta publica</label>
                        <input type="text" name="brand_logo_light" value="<?= htmlspecialchars((string) ($form['brand_logo_light'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/uploads/home/light-logo.webp">
                    </div>
                    <div class="form-group">
                        <label>Subir logo tema dia</label>
                        <input type="file" name="brand_logo_light_file" accept="image/png,image/jpeg,image/webp">
                    </div>
                </div>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>Nombre de marca (fallback si no hay logo)</label>
                        <input type="text" name="brand_name" value="<?= htmlspecialchars((string) ($form['brand_name'] ?? 'Express Transfers'), ENT_QUOTES, 'UTF-8') ?>" placeholder="Express Transfers">
                        <span class="field-note">Se muestra cuando no hay logo disponible y en los atributos del sitio. Ejemplo: "Express Transfers" o "Lujo Cancun".</span>
                    </div>

                    <div class="form-group">
                        <label>Tema visual activo</label>
                        <?php $homeTheme = (string) ($form['home_theme'] ?? 'day'); ?>
                        <select name="home_theme">
                            <option value="day" <?= $homeTheme === 'day' ? 'selected' : '' ?>>Dia</option>
                            <option value="night" <?= $homeTheme === 'night' ? 'selected' : '' ?>>Noche</option>
                        </select>
                    </div>
                </div>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>Prefijo de reserva (3 letras)</label>
                        <input type="text" name="booking_code_prefix" maxlength="3" value="<?= htmlspecialchars((string) ($form['booking_code_prefix'] ?? 'KTR'), ENT_QUOTES, 'UTF-8') ?>" placeholder="ETC" style="text-transform:uppercase;">
                        <span class="field-note">
                            <?php if ($suggestedPrefix !== ''): ?>
                                Sugerido por dominio: <strong><?= htmlspecialchars($suggestedPrefix, ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php else: ?>
                                Usa 3 letras de la marca.
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <h2>Colores de voucher</h2>
                <p>Aplica a orden de servicio y voucher PDF/impresion.</p>

                <div class="settings-three-col">
                    <div class="form-group">
                        <label>Primario</label>
                        <input type="text" name="voucher_primary" value="<?= htmlspecialchars((string) ($form['voucher_primary'] ?? '#17679A'), ENT_QUOTES, 'UTF-8') ?>" placeholder="#17679A">
                    </div>
                    <div class="form-group">
                        <label>Secundario</label>
                        <input type="text" name="voucher_secondary" value="<?= htmlspecialchars((string) ($form['voucher_secondary'] ?? '#0D4F79'), ENT_QUOTES, 'UTF-8') ?>" placeholder="#0D4F79">
                    </div>
                    <div class="form-group">
                        <label>Color de linea</label>
                        <input type="text" name="voucher_line" value="<?= htmlspecialchars((string) ($form['voucher_line'] ?? '#1F2937'), ENT_QUOTES, 'UTF-8') ?>" placeholder="#1F2937">
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <h2>Footer de voucher y orden</h2>
                <p>Configura textos del pie para voucher y orden de servicio, y el QR que se muestra en ambos documentos.</p>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>Voucher - titulo principal</label>
                        <input type="text" name="document_footer_voucher_headline" value="<?= htmlspecialchars((string) ($form['document_footer_voucher_headline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="GRACIAS POR SU PREFERENCIA / THANK YOU FOR YOUR PREFERENCE">
                    </div>
                    <div class="form-group">
                        <label>Orden - titulo principal</label>
                        <input type="text" name="document_footer_service_order_headline" value="<?= htmlspecialchars((string) ($form['document_footer_service_order_headline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="GRACIAS POR SU PREFERENCIA / THANK YOU FOR YOUR PREFERENCE">
                    </div>
                </div>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>Voucher - linea 1</label>
                        <input type="text" name="document_footer_voucher_line_1" value="<?= htmlspecialchars((string) ($form['document_footer_voucher_line_1'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Reservation phone: +52 ... | +52 ...">
                    </div>
                    <div class="form-group">
                        <label>Orden - linea 1</label>
                        <input type="text" name="document_footer_service_order_line_1" value="<?= htmlspecialchars((string) ($form['document_footer_service_order_line_1'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Reservation phone: +52 ... | +52 ...">
                    </div>
                </div>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>Voucher - linea 2</label>
                        <input type="text" name="document_footer_voucher_line_2" value="<?= htmlspecialchars((string) ($form['document_footer_voucher_line_2'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="info@dominio.com | dominio.com">
                    </div>
                    <div class="form-group">
                        <label>Orden - linea 2</label>
                        <input type="text" name="document_footer_service_order_line_2" value="<?= htmlspecialchars((string) ($form['document_footer_service_order_line_2'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="info@dominio.com | dominio.com">
                    </div>
                </div>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>QR (URL o ruta publica)</label>
                        <input type="text" name="document_footer_qr_image" value="<?= htmlspecialchars((string) ($form['document_footer_qr_image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/uploads/home/document-qr.webp">
                        <span class="field-note">Si queda vacio, usa el QR legacy de assets si existe.</span>
                    </div>
                    <div class="form-group">
                        <label>Subir QR</label>
                        <input type="file" name="document_footer_qr_file" accept="image/png,image/jpeg,image/webp">
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <h2>Colores landing - tema dia</h2>
                <div class="settings-five-col">
                    <div class="form-group">
                        <label>Fondo</label>
                        <input type="text" name="landing_day_bg" value="<?= htmlspecialchars((string) ($form['landing_day_bg'] ?? '#FFFDF8'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Texto</label>
                        <input type="text" name="landing_day_text" value="<?= htmlspecialchars((string) ($form['landing_day_text'] ?? '#101820'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Acento</label>
                        <input type="text" name="landing_day_accent" value="<?= htmlspecialchars((string) ($form['landing_day_accent'] ?? '#0F3F46'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Acento 2</label>
                        <input type="text" name="landing_day_accent_2" value="<?= htmlspecialchars((string) ($form['landing_day_accent_2'] ?? '#155D66'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Dorado</label>
                        <input type="text" name="landing_day_gold" value="<?= htmlspecialchars((string) ($form['landing_day_gold'] ?? '#C9A46A'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Header</label>
                        <input type="text" name="landing_day_header_bg" value="<?= htmlspecialchars((string) ($form['landing_day_header_bg'] ?? '#000000'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Footer</label>
                        <input type="text" name="landing_day_footer_bg" value="<?= htmlspecialchars((string) ($form['landing_day_footer_bg'] ?? '#000000'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <h2>Colores landing - tema noche</h2>
                <div class="settings-five-col">
                    <div class="form-group">
                        <label>Fondo</label>
                        <input type="text" name="landing_night_bg" value="<?= htmlspecialchars((string) ($form['landing_night_bg'] ?? '#071114'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Texto</label>
                        <input type="text" name="landing_night_text" value="<?= htmlspecialchars((string) ($form['landing_night_text'] ?? '#F7FBFC'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Acento</label>
                        <input type="text" name="landing_night_accent" value="<?= htmlspecialchars((string) ($form['landing_night_accent'] ?? '#4FB3C3'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Acento 2</label>
                        <input type="text" name="landing_night_accent_2" value="<?= htmlspecialchars((string) ($form['landing_night_accent_2'] ?? '#7AD4DF'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Dorado</label>
                        <input type="text" name="landing_night_gold" value="<?= htmlspecialchars((string) ($form['landing_night_gold'] ?? '#C9A46A'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Header</label>
                        <input type="text" name="landing_night_header_bg" value="<?= htmlspecialchars((string) ($form['landing_night_header_bg'] ?? '#000000'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="form-group">
                        <label>Footer</label>
                        <input type="text" name="landing_night_footer_bg" value="<?= htmlspecialchars((string) ($form['landing_night_footer_bg'] ?? '#071114'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <h2>Slider de fondo (Home)</h2>
                <p>Personaliza las imagenes del hero. Puedes usar URLs HTTPS o rutas publicas como /uploads/home/mi-foto.webp.</p>

                <div class="settings-grid">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <div class="hero-image-row">
                            <div class="form-group">
                                <label>Imagen <?= $i + 1 ?> (URL o ruta)</label>
                                <input type="text" name="hero_images[]" value="<?= htmlspecialchars((string) ($heroImages[$i] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://... o /uploads/home/hero-<?= $i + 1 ?>.webp">
                            </div>
                            <div class="form-group">
                                <label>Subir imagen <?= $i + 1 ?></label>
                                <input type="file" name="hero_image_file_<?= $i ?>" accept="image/png,image/jpeg,image/webp">
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <span class="field-note">Si subes archivo, reemplaza el valor del campo de URL/ruta en ese slot. Recomendado: fotos de sprinters, vans ejecutivas o aeropuerto de Cancun.</span>
            </section>

            <section class="settings-card">
                <h2>Canales de contacto</h2>
                <p>Se muestran en el boton flotante y accesos rapidos de la home publica.</p>

                <div class="settings-grid">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <?php $channel = is_array($contactChannels[$i] ?? null) ? $contactChannels[$i] : ['type' => 'whatsapp', 'title' => '', 'value' => '', 'url' => '']; ?>
                        <div class="contact-channel-row">
                            <div class="form-group">
                                <label>Tipo</label>
                                <?php $selectedType = (string) ($channel['type'] ?? 'whatsapp'); ?>
                                <select name="contact_channel_type[]">
                                    <?php foreach ($contactTypes as $typeValue => $typeLabel): ?>
                                        <option value="<?= htmlspecialchars($typeValue, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedType === $typeValue ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Titulo</label>
                                <input type="text" name="contact_channel_title[]" value="<?= htmlspecialchars((string) ($channel['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="WhatsApp">
                            </div>
                            <div class="form-group">
                                <label>Valor visible</label>
                                <input type="text" name="contact_channel_value[]" value="<?= htmlspecialchars((string) ($channel['value'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="+52 998 123 4567">
                            </div>
                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" name="contact_channel_url[]" value="<?= htmlspecialchars((string) ($channel['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://wa.me/529981234567">
                                <span class="field-note">Opcional si el valor permite construir tel:, sms:, mailto: o Telegram.</span>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </section>

            <section class="settings-card">
                <h2>Pasarelas de pago (solo configuracion)</h2>
                <p>Estos datos aun no procesan cobros; se guardan para preparacion de la implementacion.</p>

                <div class="settings-grid">
                    <div>
                        <label class="switch">
                            <input type="checkbox" name="payment_mercado_pago_enabled" value="1" <?= ($form['payment_mercado_pago_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                            Mercado Pago habilitado
                        </label>
                        <div class="settings-two-col" style="margin-top:10px;">
                            <div class="form-group">
                                <label>Public Key</label>
                                <input type="text" name="payment_mercado_pago_public_key" value="<?= htmlspecialchars((string) ($form['payment_mercado_pago_public_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="form-group">
                                <label>Access Token</label>
                                <input type="text" name="payment_mercado_pago_access_token" value="<?= htmlspecialchars((string) ($form['payment_mercado_pago_access_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="switch">
                            <input type="checkbox" name="payment_stripe_enabled" value="1" <?= ($form['payment_stripe_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                            Stripe habilitado
                        </label>
                        <div class="settings-two-col" style="margin-top:10px;">
                            <div class="form-group">
                                <label>Publishable Key</label>
                                <input type="text" name="payment_stripe_public_key" value="<?= htmlspecialchars((string) ($form['payment_stripe_public_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="form-group">
                                <label>Secret Key</label>
                                <input type="text" name="payment_stripe_secret_key" value="<?= htmlspecialchars((string) ($form['payment_stripe_secret_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="switch">
                            <input type="checkbox" name="payment_paypal_enabled" value="1" <?= ($form['payment_paypal_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                            PayPal habilitado
                        </label>
                        <div class="settings-two-col" style="margin-top:10px;">
                            <div class="form-group">
                                <label>Client ID</label>
                                <input type="text" name="payment_paypal_client_id" value="<?= htmlspecialchars((string) ($form['payment_paypal_client_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="form-group">
                                <label>Client Secret</label>
                                <input type="text" name="payment_paypal_client_secret" value="<?= htmlspecialchars((string) ($form['payment_paypal_client_secret'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <h2>Tracking y scripts globales</h2>
                <p>Configura Google Tag Manager y un script clasico para insertarlo en el &lt;head&gt; de todo el sitio.</p>

                <div class="settings-two-col">
                    <div class="form-group">
                        <label>Google Tag Manager ID</label>
                        <input type="text" name="gtm_container_id" value="<?= htmlspecialchars((string) ($form['gtm_container_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="GTM-ABC1234" style="text-transform:uppercase;">
                        <span class="field-note">Formato esperado: GTM-XXXXXXX. Si lo dejas vacio, no se inyecta GTM.</span>
                    </div>
                    <div class="form-group">
                        <label>Script clasico (head)</label>
                        <textarea name="custom_head_script" rows="8" placeholder="&lt;script&gt;...&lt;/script&gt;"><?= htmlspecialchars((string) ($form['custom_head_script'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <span class="field-note">Pega un script completo (por ejemplo pixel o analytics). Se inserta tal cual en todo el sitio.</span>
                    </div>
                </div>
            </section>
        </div>

        <div class="form-actions" style="margin-top: 16px;">
            <button type="submit" class="btn">Guardar configuracion</button>
        </div>
    </form>
</div>
