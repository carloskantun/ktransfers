<?php
declare(strict_types=1);
namespace App\Core;

class View {
    public static function render(string $page, array $data = [], ?string $layout = 'public'): string
    {
        $basePath = dirname(__DIR__) . '/Views';
        $pageFile = $basePath . '/Pages/' . $page . '.php';

        if (!is_file($pageFile)) {
            return 'View not found: ' . $page;
        }

        $publicLocale = I18n::current();
        $publicT = static fn (string $key, string $fallback): string => I18n::translate($key, $publicLocale, $fallback);
        $publicLocalizedUrl = static fn (string $locale): string => I18n::localizedUrl($locale);

        $data = array_merge([
            'public_locale' => $publicLocale,
            'public_t' => $publicT,
            'public_localized_url' => $publicLocalizedUrl,
        ], $data);

        extract($data, EXTR_SKIP);

        ob_start();
        include $pageFile;
        $content = (string) ob_get_clean();

        if ($layout === null) {
            return $content;
        }

        $layoutFile = $basePath . '/Layouts/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            return $content;
        }

        ob_start();
        include $layoutFile;
        return (string) ob_get_clean();
    }
}
