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
