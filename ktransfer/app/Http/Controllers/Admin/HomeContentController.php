<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Services\BrandingService;
use App\Services\HomeContentService;

class HomeContentController
{
    private const LOGO_UPLOAD_DIR = '/uploads/home';
    private const CONTACT_CHANNEL_TYPES = ['whatsapp', 'call', 'sms', 'telegram', 'email'];
    private const HERO_IMAGE_SLOTS = 6;

    public function edit(Request $request): Response
    {
        $service = new HomeContentService();
        $saved = $request->query('saved', '') === '1';
        $currentContent = $service->getHomePageContent();

        if ($request->method() === 'GET') {
            return Response::view('admin/content/home', [
                'title' => 'Home Settings',
                'csrf_token' => Csrf::token(),
                'saved' => $saved,
                'errors' => [],
                'form' => $this->contentToForm($currentContent),
                'suggested_prefix' => (new BrandingService())->suggestPrefixFromHost((string) ($_SERVER['HTTP_HOST'] ?? '')),
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/content/home');
        }

        $form = $this->formFromRequest($request);
        $heroImageUploadErrors = $this->applyHeroImageUploads($form);
        $uploadError = $this->applyLogoUpload($form, 'brand_logo', 'brand_logo_file', 'header-logo');
        $lightLogoUploadError = $this->applyLogoUpload($form, 'brand_logo_light', 'brand_logo_light_file', 'light-logo');

        $errors = $this->validateForm($form);
        $errors = array_merge($errors, $heroImageUploadErrors);
        if ($uploadError !== null) {
            $errors['brand_logo_upload'] = $uploadError;
        }
        if ($lightLogoUploadError !== null) {
            $errors['brand_logo_light_upload'] = $lightLogoUploadError;
        }

        if (!empty($errors)) {
            return Response::view('admin/content/home', [
                'title' => 'Home Settings',
                'csrf_token' => Csrf::token(),
                'saved' => false,
                'errors' => $errors,
                'form' => $form,
                'suggested_prefix' => (new BrandingService())->suggestPrefixFromHost((string) ($_SERVER['HTTP_HOST'] ?? '')),
            ], 'admin');
        }

        $updatedContent = array_replace_recursive($currentContent, $this->formToContent($form));
        $service->saveHomePageContent($updatedContent, Auth::id());

        return Response::redirect('/admin/content/home?saved=1');
    }

    private function formFromRequest(Request $request): array
    {
        return [
            'brand_logo' => trim((string) $request->post('brand_logo', '')),
            'brand_logo_light' => trim((string) $request->post('brand_logo_light', '')),
            'brand_name' => trim((string) $request->post('brand_name', 'Express Transfers')),
            'home_theme' => trim((string) $request->post('home_theme', 'day')),
            'booking_code_prefix' => strtoupper(trim((string) $request->post('booking_code_prefix', ''))),

            'voucher_primary' => strtoupper(trim((string) $request->post('voucher_primary', '#17679A'))),
            'voucher_secondary' => strtoupper(trim((string) $request->post('voucher_secondary', '#0D4F79'))),
            'voucher_line' => strtoupper(trim((string) $request->post('voucher_line', '#1F2937'))),

            'landing_day_bg' => strtoupper(trim((string) $request->post('landing_day_bg', '#FFFDF8'))),
            'landing_day_text' => strtoupper(trim((string) $request->post('landing_day_text', '#101820'))),
            'landing_day_accent' => strtoupper(trim((string) $request->post('landing_day_accent', '#0F3F46'))),
            'landing_day_accent_2' => strtoupper(trim((string) $request->post('landing_day_accent_2', '#155D66'))),
            'landing_day_gold' => strtoupper(trim((string) $request->post('landing_day_gold', '#C9A46A'))),
            'landing_day_header_bg' => strtoupper(trim((string) $request->post('landing_day_header_bg', '#000000'))),
            'landing_day_footer_bg' => strtoupper(trim((string) $request->post('landing_day_footer_bg', '#000000'))),

            'landing_night_bg' => strtoupper(trim((string) $request->post('landing_night_bg', '#071114'))),
            'landing_night_text' => strtoupper(trim((string) $request->post('landing_night_text', '#F7FBFC'))),
            'landing_night_accent' => strtoupper(trim((string) $request->post('landing_night_accent', '#4FB3C3'))),
            'landing_night_accent_2' => strtoupper(trim((string) $request->post('landing_night_accent_2', '#7AD4DF'))),
            'landing_night_gold' => strtoupper(trim((string) $request->post('landing_night_gold', '#C9A46A'))),
            'landing_night_header_bg' => strtoupper(trim((string) $request->post('landing_night_header_bg', '#000000'))),
            'landing_night_footer_bg' => strtoupper(trim((string) $request->post('landing_night_footer_bg', '#071114'))),

            'payment_mercado_pago_enabled' => $request->post('payment_mercado_pago_enabled') !== null ? '1' : '0',
            'payment_mercado_pago_public_key' => trim((string) $request->post('payment_mercado_pago_public_key', '')),
            'payment_mercado_pago_access_token' => trim((string) $request->post('payment_mercado_pago_access_token', '')),

            'payment_stripe_enabled' => $request->post('payment_stripe_enabled') !== null ? '1' : '0',
            'payment_stripe_public_key' => trim((string) $request->post('payment_stripe_public_key', '')),
            'payment_stripe_secret_key' => trim((string) $request->post('payment_stripe_secret_key', '')),

            'payment_paypal_enabled' => $request->post('payment_paypal_enabled') !== null ? '1' : '0',
            'payment_paypal_client_id' => trim((string) $request->post('payment_paypal_client_id', '')),
            'payment_paypal_client_secret' => trim((string) $request->post('payment_paypal_client_secret', '')),

            'hero_images' => $this->heroImagesFromRequest($request),
            'contact_channels' => $this->contactChannelsFromRequest($request),
        ];
    }

    private function validateForm(array $form): array
    {
        $errors = [];

        if (!in_array($form['home_theme'] ?? '', ['day', 'night'], true)) {
            $errors['home_theme'] = 'Selecciona una version visual valida.';
        }

        $prefix = trim((string) ($form['booking_code_prefix'] ?? ''));
        if ($prefix !== '' && preg_match('/^[A-Z]{3}$/', $prefix) !== 1) {
            $errors['booking_code_prefix'] = 'El prefijo debe tener exactamente 3 letras (A-Z).';
        }

        foreach (['brand_logo', 'brand_logo_light'] as $logoField) {
            $logo = trim((string) ($form[$logoField] ?? ''));
            if ($logo !== '' && preg_match('#^(https?://|/)#i', $logo) !== 1) {
                $errors[$logoField] = 'Usa una URL completa o una ruta publica que empiece con /.';
            }
            if ($logo !== '' && str_contains($logo, '..')) {
                $errors[$logoField] = 'La ruta del logo no es valida.';
            }
        }

        foreach (($form['hero_images'] ?? []) as $index => $heroImage) {
            $heroImage = trim((string) $heroImage);
            if ($heroImage === '') {
                continue;
            }

            if (preg_match('#^(https?://|/)#i', $heroImage) !== 1) {
                $errors['hero_image_' . $index] = 'Imagen de slider ' . ((int) $index + 1) . ': usa una URL completa o una ruta publica que empiece con /.';
                continue;
            }

            if (str_contains($heroImage, '..')) {
                $errors['hero_image_' . $index] = 'Imagen de slider ' . ((int) $index + 1) . ': la ruta no es valida.';
            }
        }

        $colorFields = [
            'voucher_primary',
            'voucher_secondary',
            'voucher_line',
            'landing_day_bg',
            'landing_day_text',
            'landing_day_accent',
            'landing_day_accent_2',
            'landing_day_gold',
            'landing_day_header_bg',
            'landing_day_footer_bg',
            'landing_night_bg',
            'landing_night_text',
            'landing_night_accent',
            'landing_night_accent_2',
            'landing_night_gold',
            'landing_night_header_bg',
            'landing_night_footer_bg',
        ];

        foreach ($colorFields as $field) {
            if (preg_match('/^#[0-9A-F]{6}$/', (string) ($form[$field] ?? '')) !== 1) {
                $errors[$field] = 'Color invalido. Usa formato HEX como #1A2B3C.';
            }
        }

        foreach (($form['contact_channels'] ?? []) as $index => $channel) {
            if (!is_array($channel)) {
                continue;
            }

            $label = 'Canal de contacto ' . ((int) $index + 1);
            $hasAnyValue = trim((string) ($channel['title'] ?? '')) !== ''
                || trim((string) ($channel['value'] ?? '')) !== ''
                || trim((string) ($channel['url'] ?? '')) !== '';

            if (!$hasAnyValue) {
                continue;
            }

            if (!in_array((string) ($channel['type'] ?? ''), self::CONTACT_CHANNEL_TYPES, true)) {
                $errors['contact_channel_' . $index . '_type'] = $label . ': tipo invalido.';
            }

            if (trim((string) ($channel['title'] ?? '')) === '') {
                $errors['contact_channel_' . $index . '_title'] = $label . ': titulo requerido.';
            }

            $url = trim((string) ($channel['url'] ?? ''));
            if ($url !== '' && preg_match('#^(https?://|tel:|sms:|mailto:)#i', $url) !== 1) {
                $errors['contact_channel_' . $index . '_url'] = $label . ': usa https://, tel:, sms: o mailto:.';
            }
        }

        return $errors;
    }

    private function formToContent(array $form): array
    {
        return [
            'brand_logo' => (string) $form['brand_logo'],
            'brand_logo_light' => (string) $form['brand_logo_light'],
            'home_theme' => (string) $form['home_theme'],
            'booking_code_prefix' => (string) $form['booking_code_prefix'],
            'hero_images' => $this->normalizeHeroImages($form['hero_images'] ?? []),
            'voucher_theme' => [
                'primary' => (string) $form['voucher_primary'],
                'secondary' => (string) $form['voucher_secondary'],
                'line' => (string) $form['voucher_line'],
            ],
            'landing_theme' => [
                'day' => [
                    'bg' => (string) $form['landing_day_bg'],
                    'text' => (string) $form['landing_day_text'],
                    'accent' => (string) $form['landing_day_accent'],
                    'accent_2' => (string) $form['landing_day_accent_2'],
                    'gold' => (string) $form['landing_day_gold'],
                    'header_bg' => (string) $form['landing_day_header_bg'],
                    'footer_bg' => (string) $form['landing_day_footer_bg'],
                ],
                'night' => [
                    'bg' => (string) $form['landing_night_bg'],
                    'text' => (string) $form['landing_night_text'],
                    'accent' => (string) $form['landing_night_accent'],
                    'accent_2' => (string) $form['landing_night_accent_2'],
                    'gold' => (string) $form['landing_night_gold'],
                    'header_bg' => (string) $form['landing_night_header_bg'],
                    'footer_bg' => (string) $form['landing_night_footer_bg'],
                ],
            ],
            'contact_channels' => $this->normalizeContactChannels($form['contact_channels'] ?? []),
            'payment_settings' => [
                'mercado_pago' => [
                    'enabled' => $form['payment_mercado_pago_enabled'] === '1',
                    'public_key' => (string) $form['payment_mercado_pago_public_key'],
                    'access_token' => (string) $form['payment_mercado_pago_access_token'],
                ],
                'stripe' => [
                    'enabled' => $form['payment_stripe_enabled'] === '1',
                    'public_key' => (string) $form['payment_stripe_public_key'],
                    'secret_key' => (string) $form['payment_stripe_secret_key'],
                ],
                'paypal' => [
                    'enabled' => $form['payment_paypal_enabled'] === '1',
                    'client_id' => (string) $form['payment_paypal_client_id'],
                    'client_secret' => (string) $form['payment_paypal_client_secret'],
                ],
            ],
        ];
    }

    private function contentToForm(array $content): array
    {
        $defaults = HomeContentService::defaultContent();
        $content = array_replace_recursive($defaults, $content);

        return [
            'brand_logo' => (string) ($content['brand_logo'] ?? ''),
            'brand_logo_light' => (string) ($content['brand_logo_light'] ?? ''),
            'brand_name' => (string) ($content['brand_name'] ?? 'Express Transfers'),
            'home_theme' => (string) ($content['home_theme'] ?? 'day'),
            'booking_code_prefix' => (string) ($content['booking_code_prefix'] ?? 'KTR'),
            'hero_images' => $this->heroImagesToForm($content['hero_images'] ?? []),

            'voucher_primary' => (string) ($content['voucher_theme']['primary'] ?? '#17679A'),
            'voucher_secondary' => (string) ($content['voucher_theme']['secondary'] ?? '#0D4F79'),
            'voucher_line' => (string) ($content['voucher_theme']['line'] ?? '#1F2937'),

            'landing_day_bg' => (string) ($content['landing_theme']['day']['bg'] ?? '#FFFDF8'),
            'landing_day_text' => (string) ($content['landing_theme']['day']['text'] ?? '#101820'),
            'landing_day_accent' => (string) ($content['landing_theme']['day']['accent'] ?? '#0F3F46'),
            'landing_day_accent_2' => (string) ($content['landing_theme']['day']['accent_2'] ?? '#155D66'),
            'landing_day_gold' => (string) ($content['landing_theme']['day']['gold'] ?? '#C9A46A'),
            'landing_day_header_bg' => (string) ($content['landing_theme']['day']['header_bg'] ?? '#000000'),
            'landing_day_footer_bg' => (string) ($content['landing_theme']['day']['footer_bg'] ?? '#000000'),

            'landing_night_bg' => (string) ($content['landing_theme']['night']['bg'] ?? '#071114'),
            'landing_night_text' => (string) ($content['landing_theme']['night']['text'] ?? '#F7FBFC'),
            'landing_night_accent' => (string) ($content['landing_theme']['night']['accent'] ?? '#4FB3C3'),
            'landing_night_accent_2' => (string) ($content['landing_theme']['night']['accent_2'] ?? '#7AD4DF'),
            'landing_night_gold' => (string) ($content['landing_theme']['night']['gold'] ?? '#C9A46A'),
            'landing_night_header_bg' => (string) ($content['landing_theme']['night']['header_bg'] ?? '#000000'),
            'landing_night_footer_bg' => (string) ($content['landing_theme']['night']['footer_bg'] ?? '#071114'),

            'payment_mercado_pago_enabled' => !empty($content['payment_settings']['mercado_pago']['enabled']) ? '1' : '0',
            'payment_mercado_pago_public_key' => (string) ($content['payment_settings']['mercado_pago']['public_key'] ?? ''),
            'payment_mercado_pago_access_token' => (string) ($content['payment_settings']['mercado_pago']['access_token'] ?? ''),

            'payment_stripe_enabled' => !empty($content['payment_settings']['stripe']['enabled']) ? '1' : '0',
            'payment_stripe_public_key' => (string) ($content['payment_settings']['stripe']['public_key'] ?? ''),
            'payment_stripe_secret_key' => (string) ($content['payment_settings']['stripe']['secret_key'] ?? ''),

            'payment_paypal_enabled' => !empty($content['payment_settings']['paypal']['enabled']) ? '1' : '0',
            'payment_paypal_client_id' => (string) ($content['payment_settings']['paypal']['client_id'] ?? ''),
            'payment_paypal_client_secret' => (string) ($content['payment_settings']['paypal']['client_secret'] ?? ''),

            'contact_channels' => $this->contactChannelsToForm($content['contact_channels'] ?? []),
        ];
    }

    private function contactChannelsFromRequest(Request $request): array
    {
        $types = $request->post('contact_channel_type', []);
        $titles = $request->post('contact_channel_title', []);
        $values = $request->post('contact_channel_value', []);
        $urls = $request->post('contact_channel_url', []);

        $types = is_array($types) ? $types : [];
        $titles = is_array($titles) ? $titles : [];
        $values = is_array($values) ? $values : [];
        $urls = is_array($urls) ? $urls : [];

        $channels = [];
        for ($i = 0; $i < 4; $i++) {
            $channels[] = [
                'type' => strtolower(trim((string) ($types[$i] ?? 'whatsapp'))),
                'title' => trim((string) ($titles[$i] ?? '')),
                'value' => trim((string) ($values[$i] ?? '')),
                'url' => trim((string) ($urls[$i] ?? '')),
            ];
        }

        return $channels;
    }

    private function heroImagesFromRequest(Request $request): array
    {
        $images = $request->post('hero_images', []);
        if (!is_array($images)) {
            return [];
        }

        $values = [];
        foreach (array_slice($images, 0, self::HERO_IMAGE_SLOTS) as $image) {
            $values[] = trim((string) $image);
        }

        return $values;
    }

    private function applyHeroImageUploads(array &$form): array
    {
        $errors = [];

        for ($i = 0; $i < self::HERO_IMAGE_SLOTS; $i++) {
            $fileField = 'hero_image_file_' . $i;
            $file = $_FILES[$fileField] ?? null;
            if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $errors['hero_image_' . $i] = 'Imagen de slider ' . ($i + 1) . ': no se pudo subir el archivo.';
                continue;
            }

            $tmpName = (string) ($file['tmp_name'] ?? '');
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                $errors['hero_image_' . $i] = 'Imagen de slider ' . ($i + 1) . ': el archivo subido no es valido.';
                continue;
            }

            $size = (int) ($file['size'] ?? 0);
            if ($size <= 0 || $size > 5 * 1024 * 1024) {
                $errors['hero_image_' . $i] = 'Imagen de slider ' . ($i + 1) . ': peso maximo permitido 5 MB.';
                continue;
            }

            $mime = mime_content_type($tmpName) ?: '';
            $extensions = [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
            ];

            if (!isset($extensions[$mime])) {
                $errors['hero_image_' . $i] = 'Imagen de slider ' . ($i + 1) . ': usa PNG, JPG o WEBP.';
                continue;
            }

            $projectRoot = dirname(__DIR__, 5);
            $publicRoot = $projectRoot . '/public_html';
            $uploadDir = $publicRoot . self::LOGO_UPLOAD_DIR;

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                $errors['hero_image_' . $i] = 'Imagen de slider ' . ($i + 1) . ': no se pudo crear la carpeta de uploads.';
                continue;
            }

            $filename = 'hero-slide-' . ($i + 1) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extensions[$mime];
            $destination = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($tmpName, $destination)) {
                $errors['hero_image_' . $i] = 'Imagen de slider ' . ($i + 1) . ': no se pudo guardar el archivo.';
                continue;
            }

            $form['hero_images'][$i] = self::LOGO_UPLOAD_DIR . '/' . $filename;
        }

        return $errors;
    }

    private function heroImagesToForm(mixed $images): array
    {
        $defaults = HomeContentService::defaultContent()['hero_images'] ?? [];
        $source = is_array($images) && !empty($images) ? $images : $defaults;
        $formImages = [];

        foreach (array_slice($source, 0, self::HERO_IMAGE_SLOTS) as $image) {
            $formImages[] = trim((string) $image);
        }

        while (count($formImages) < self::HERO_IMAGE_SLOTS) {
            $formImages[] = '';
        }

        return $formImages;
    }

    private function normalizeHeroImages(mixed $images): array
    {
        if (!is_array($images)) {
            return [];
        }

        $normalized = [];
        foreach (array_slice($images, 0, self::HERO_IMAGE_SLOTS) as $image) {
            $value = trim((string) $image);
            if ($value === '') {
                continue;
            }

            if (!in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    private function contactChannelsToForm(mixed $channels): array
    {
        $defaults = HomeContentService::defaultContent()['contact_channels'] ?? [];
        $source = is_array($channels) && !empty($channels) ? $channels : $defaults;
        $formChannels = [];

        foreach (array_slice($source, 0, 4) as $channel) {
            if (!is_array($channel)) {
                continue;
            }

            $formChannels[] = [
                'type' => strtolower(trim((string) ($channel['type'] ?? 'whatsapp'))),
                'title' => trim((string) ($channel['title'] ?? '')),
                'value' => trim((string) ($channel['value'] ?? '')),
                'url' => trim((string) ($channel['url'] ?? '')),
            ];
        }

        while (count($formChannels) < 4) {
            $formChannels[] = ['type' => 'whatsapp', 'title' => '', 'value' => '', 'url' => ''];
        }

        return $formChannels;
    }

    private function normalizeContactChannels(mixed $channels): array
    {
        if (!is_array($channels)) {
            return [];
        }

        $normalized = [];
        foreach (array_slice($channels, 0, 4) as $channel) {
            if (!is_array($channel)) {
                continue;
            }

            $title = trim((string) ($channel['title'] ?? ''));
            $value = trim((string) ($channel['value'] ?? ''));
            $url = trim((string) ($channel['url'] ?? ''));
            if ($title === '' && $value === '' && $url === '') {
                continue;
            }

            $type = strtolower(trim((string) ($channel['type'] ?? 'whatsapp')));
            if (!in_array($type, self::CONTACT_CHANNEL_TYPES, true)) {
                $type = 'whatsapp';
            }

            $normalized[] = [
                'type' => $type,
                'title' => $title,
                'value' => $value,
                'url' => $url,
            ];
        }

        return $normalized;
    }

    private function applyLogoUpload(array &$form, string $formField, string $fileField, string $filenamePrefix): ?string
    {
        $file = $_FILES[$fileField] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return 'No se pudo subir el logo. Intenta de nuevo.';
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return 'El archivo subido no es valido.';
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > 2 * 1024 * 1024) {
            return 'El logo debe pesar maximo 2 MB.';
        }

        $mime = mime_content_type($tmpName) ?: '';
        $extensions = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
        ];

        if (!isset($extensions[$mime])) {
            return 'Sube un logo PNG, JPG o WEBP.';
        }

        $projectRoot = dirname(__DIR__, 5);
        $publicRoot = $projectRoot . '/public_html';
        $uploadDir = $publicRoot . self::LOGO_UPLOAD_DIR;

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return 'No se pudo crear la carpeta de uploads para el logo.';
        }

        $filename = $filenamePrefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extensions[$mime];
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            return 'No se pudo guardar el logo subido.';
        }

        $form[$formField] = self::LOGO_UPLOAD_DIR . '/' . $filename;
        return null;
    }
}
