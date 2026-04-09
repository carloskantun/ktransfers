<?php
declare(strict_types=1);

$errors = $errors ?? [];
$form = $form ?? [];
$saved = (bool) ($saved ?? false);
?>
<style>
    .content-editor-grid {
        display: grid;
        gap: 18px;
    }
    .content-section {
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff, #f9fbff);
    }
    .content-section h2 {
        margin: 0 0 6px;
        font-size: 1.08rem;
    }
    .content-section p {
        margin: 0 0 16px;
        color: var(--muted);
        line-height: 1.55;
    }
    .content-two-col {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .field-note {
        display: block;
        margin-top: 6px;
        color: var(--muted);
        font-size: 0.84rem;
        line-height: 1.45;
    }
    .toggle-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .toggle-item {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
    }
    .toggle-item input {
        width: auto;
        margin-top: 2px;
    }
    .toggle-item strong {
        display: block;
        margin-bottom: 4px;
    }
    @media (max-width: 900px) {
        .content-two-col {
            grid-template-columns: 1fr;
        }
        .toggle-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1>Editor de Home</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">Ahora puedes definir estructura, estilo de hero, slides y canales rápidos sin tocar la plantilla.</p>
    </div>
    <a href="/" class="btn btn-secondary" target="_blank" rel="noreferrer">Ver sitio</a>
</div>

<?php if ($saved): ?>
    <div class="notice">Los cambios de la home se guardaron correctamente.</div>
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
    <form method="post" action="/admin/content/home">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <div class="content-editor-grid">
            <section class="content-section">
                <h2>Visibilidad de secciones</h2>
                <p>Aquí decides qué bloques se muestran y cuáles se ocultan en la landing principal, sin tocar la plantilla.</p>

                <div class="toggle-grid">
                    <?php
                    $sectionToggles = [
                        'show_social_links' => ['title' => 'Redes en hero', 'text' => 'Muestra o esconde los accesos sociales superiores.'],
                        'show_hero_badges' => ['title' => 'Badges del hero', 'text' => 'Activa los sellos cortos debajo del hero principal.'],
                        'show_booking_panel' => ['title' => 'Buscador principal', 'text' => 'Mantiene visible el panel de booking sobre la landing.'],
                        'show_highlights' => ['title' => 'Bloque de beneficios', 'text' => 'Sección con razones para reservar directo.'],
                        'show_routes' => ['title' => 'Bloque de rutas', 'text' => 'Muestra los destinos y rutas destacadas.'],
                        'show_story' => ['title' => 'Narrativa de marca', 'text' => 'Activa el bloque editorial/testimonial.'],
                        'show_story_points' => ['title' => 'Puntos narrativos', 'text' => 'Lista secundaria de bullets o argumentos.'],
                        'show_support' => ['title' => 'Soporte y contacto', 'text' => 'Muestra canales de ayuda antes de reservar.'],
                        'show_closing_cta' => ['title' => 'CTA final', 'text' => 'Bloque de cierre al final de la página.'],
                        'show_floating_contact' => ['title' => 'Burbujas flotantes', 'text' => 'WhatsApp y canales flotantes en esquina.'],
                    ];
                    ?>
                    <?php foreach ($sectionToggles as $field => $meta): ?>
                        <label class="toggle-item">
                            <input type="checkbox" name="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" value="1" <?= ($form[$field] ?? '0') === '1' ? 'checked' : '' ?>>
                            <span>
                                <strong><?= htmlspecialchars($meta['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="field-note" style="margin-top:0;"><?= htmlspecialchars($meta['text'], ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="content-section">
                <h2>Hero principal</h2>
                <p>Este bloque controla el primer impacto visual. Puedes mantener un hero clásico o cambiar a un slider/carrusel usando las mismas diapositivas configurables.</p>

                <div class="content-two-col">
                    <div class="form-group">
                        <label>Eyebrow</label>
                        <input type="text" name="eyebrow" value="<?= htmlspecialchars((string) ($form['eyebrow'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Modo de hero</label>
                        <select name="hero_mode" required>
                            <?php $heroMode = (string) ($form['hero_mode'] ?? 'editorial'); ?>
                            <option value="editorial" <?= $heroMode === 'editorial' ? 'selected' : '' ?>>Hero editorial</option>
                            <option value="slider" <?= $heroMode === 'slider' ? 'selected' : '' ?>>Hero con slider</option>
                            <option value="carousel" <?= $heroMode === 'carousel' ? 'selected' : '' ?>>Hero con carrusel</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Hero title</label>
                    <textarea name="hero_title" rows="3" required><?= htmlspecialchars((string) ($form['hero_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Hero images</label>
                    <textarea name="hero_images_text" rows="5" placeholder="Una URL por línea"><?= htmlspecialchars((string) ($form['hero_images_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <span class="field-note">Puedes controlar las imágenes del hero/slider sin editar la vista.</span>
                </div>

                <div class="form-group">
                    <label>Hero subtitle</label>
                    <textarea name="hero_subtitle" rows="4" required><?= htmlspecialchars((string) ($form['hero_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="content-two-col">
                    <div class="form-group">
                        <label>CTA primario label</label>
                        <input type="text" name="hero_primary_cta_label" value="<?= htmlspecialchars((string) ($form['hero_primary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>CTA primario href</label>
                        <input type="text" name="hero_primary_cta_href" value="<?= htmlspecialchars((string) ($form['hero_primary_cta_href'] ?? '#booking-form'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>CTA secundario label</label>
                        <input type="text" name="hero_secondary_cta_label" value="<?= htmlspecialchars((string) ($form['hero_secondary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>CTA secundario href</label>
                        <input type="text" name="hero_secondary_cta_href" value="<?= htmlspecialchars((string) ($form['hero_secondary_cta_href'] ?? '#contact-channels'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Badges</label>
                    <textarea name="badges_text" rows="4" placeholder="Una línea por badge"><?= htmlspecialchars((string) ($form['badges_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Slides / tarjetas del hero</label>
                    <textarea name="hero_slides_text" rows="6" placeholder="Título | Texto | Botón | URL"><?= htmlspecialchars((string) ($form['hero_slides_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <span class="field-note">Se usa en modo slider o carrusel. Una línea por slide.</span>
                </div>
            </section>

            <section class="content-section">
                <h2>Buscador y prueba de valor</h2>
                <p>Controla el panel de cotización y las secciones que explican por qué reservar contigo.</p>

                <div class="content-two-col">
                    <div class="form-group">
                        <label>Search panel title</label>
                        <input type="text" name="search_label" value="<?= htmlspecialchars((string) ($form['search_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Posición del buscador</label>
                        <?php $searchPanelLayout = (string) ($form['search_panel_layout'] ?? 'center-horizontal'); ?>
                        <select name="search_panel_layout">
                            <option value="center-horizontal" <?= $searchPanelLayout === 'center-horizontal' ? 'selected' : '' ?>>Centro horizontal (Inspirato)</option>
                            <option value="left-vertical" <?= $searchPanelLayout === 'left-vertical' ? 'selected' : '' ?>>Izquierda vertical</option>
                            <option value="right-vertical" <?= $searchPanelLayout === 'right-vertical' ? 'selected' : '' ?>>Derecha vertical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Search button label</label>
                        <input type="text" name="search_button_label" value="<?= htmlspecialchars((string) ($form['search_button_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Search panel help text</label>
                    <textarea name="search_help" rows="3"><?= htmlspecialchars((string) ($form['search_help'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Highlights grid</label>
                    <textarea name="highlights_text" rows="5" placeholder="Título | Texto"><?= htmlspecialchars((string) ($form['highlights_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Collections grid</label>
                    <textarea name="collections_text" rows="6" placeholder="Título | Texto"><?= htmlspecialchars((string) ($form['collections_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </section>

            <section class="content-section">
                <h2>Contacto, redes y burbujas rápidas</h2>
                <p>Aquí defines los accesos directos visibles en la landing y las burbujas flotantes para WhatsApp, llamada, SMS, Telegram u otros canales.</p>

                <div class="form-group">
                    <label>Canales de contacto</label>
                    <textarea name="contact_channels_text" rows="6" placeholder="tipo | Título | Valor visible | URL"><?= htmlspecialchars((string) ($form['contact_channels_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <span class="field-note">Tipos recomendados: `whatsapp`, `call`, `sms`, `telegram`, `email`. Una línea por canal.</span>
                </div>

                <div class="form-group">
                    <label>Redes sociales</label>
                    <textarea name="social_links_text" rows="4" placeholder="Nombre | URL"><?= htmlspecialchars((string) ($form['social_links_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </section>

            <section class="content-section">
                <h2>Narrativa y prueba social</h2>
                <p>Este bloque sostiene la historia de marca y la credibilidad del servicio.</p>

                <div class="form-group">
                    <label>Story title</label>
                    <input type="text" name="story_title" value="<?= htmlspecialchars((string) ($form['story_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label>Story body</label>
                    <textarea name="story_body" rows="4"><?= htmlspecialchars((string) ($form['story_body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Story bullets</label>
                    <textarea name="story_points_text" rows="4" placeholder="Una línea por punto"><?= htmlspecialchars((string) ($form['story_points_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="content-two-col">
                    <div class="form-group">
                        <label>Testimonial author</label>
                        <input type="text" name="testimonial_author" value="<?= htmlspecialchars((string) ($form['testimonial_author'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Testimonial role</label>
                        <input type="text" name="testimonial_role" value="<?= htmlspecialchars((string) ($form['testimonial_role'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Testimonial quote</label>
                    <textarea name="testimonial_quote" rows="3"><?= htmlspecialchars((string) ($form['testimonial_quote'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </section>

            <section class="content-section">
                <h2>CTA de cierre</h2>
                <p>Controla el bloque final para empujar al usuario al buscador o a un canal de contacto.</p>

                <div class="form-group">
                    <label>CTA title</label>
                    <input type="text" name="cta_title" value="<?= htmlspecialchars((string) ($form['cta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label>CTA body</label>
                    <textarea name="cta_body" rows="3"><?= htmlspecialchars((string) ($form['cta_body'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="content-two-col">
                    <div class="form-group">
                        <label>CTA button label</label>
                        <input type="text" name="cta_button_label" value="<?= htmlspecialchars((string) ($form['cta_button_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>CTA button href</label>
                        <input type="text" name="cta_button_href" value="<?= htmlspecialchars((string) ($form['cta_button_href'] ?? '#booking-form'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </section>
        </div>

        <div class="form-actions" style="margin-top: 18px;">
            <button type="submit" class="btn">Guardar Home</button>
        </div>
    </form>
</div>
