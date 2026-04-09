<?php
declare(strict_types=1);

$title = $title ?? 'KTransfers';
$publicLayoutMode = (string) ($publicLayoutMode ?? 'default');
$isImmersiveLayout = $publicLayoutMode === 'immersive';
$pageStyles = is_array($pageStyles ?? null) ? $pageStyles : [];
$pageScripts = is_array($pageScripts ?? null) ? $pageScripts : [];
$projectRoot = dirname(__DIR__, 5);
$publicRoot = $projectRoot . '/public_html';

$assetVersion = static function (string $publicAssetPath) use ($publicRoot): string {
    $relativePath = ltrim($publicAssetPath, '/');
    if (!str_starts_with($relativePath, 'assets/')) {
        return $publicAssetPath;
    }

    $assetFile = $publicRoot . '/' . $relativePath;
    if (!is_file($assetFile)) {
        return $publicAssetPath;
    }

    return $publicAssetPath . '?v=' . (string) filemtime($assetFile);
};

$assetContents = static function (string $publicAssetPath) use ($publicRoot): string {
    $relativePath = ltrim($publicAssetPath, '/');
    if (!str_starts_with($relativePath, 'assets/')) {
        return '';
    }

    $assetFile = $publicRoot . '/' . $relativePath;
    if (!is_file($assetFile)) {
        return '';
    }

    $content = file_get_contents($assetFile);
    return is_string($content) ? $content : '';
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion('/assets/app.css'), ENT_QUOTES, 'UTF-8') ?>">
    <?php $inlineAppCss = $assetContents('/assets/app.css'); ?>
    <?php if ($inlineAppCss !== ''): ?>
        <style><?= $inlineAppCss ?></style>
    <?php endif; ?>
    <?php foreach ($pageStyles as $pageStyle): ?>
        <?php $pageStyle = (string) $pageStyle; ?>
        <?php if ($pageStyle === '') { continue; } ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion($pageStyle), ENT_QUOTES, 'UTF-8') ?>">
        <?php $inlinePageCss = $assetContents($pageStyle); ?>
        <?php if ($inlinePageCss !== ''): ?>
            <style><?= $inlinePageCss ?></style>
        <?php endif; ?>
    <?php endforeach; ?>
</head>
<body class="<?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
    <main class="page-shell <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
        <section class="site-frame <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
            <header class="site-header <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
                <div class="brand-lockup">
                    <strong class="brand-name">KTransfers</strong>
                    <span class="brand-meta">Private airport transfers for Cancun, Costa Mujeres, Playa del Carmen and Tulum</span>
                </div>

                <nav class="header-nav" aria-label="Primary">
                    <a href="/#booking-form">Book</a>
                    <a href="/#routes">Routes</a>
                    <a href="/#experience">Experience</a>
                    <a href="/#contact-channels">Support</a>
                </nav>

                <div class="header-actions">
                    <span class="brand-note">Luxury hospitality tone with direct-booking clarity</span>
                    <a class="header-cta" href="/#booking-form">Reserve transfer</a>
                </div>
            </header>

            <div class="page-content <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
                <?= $content ?>
            </div>
        </section>
    </main>
    <?php foreach ($pageScripts as $pageScript): ?>
        <?php $pageScript = (string) $pageScript; ?>
        <?php if ($pageScript === '') { continue; } ?>
        <script src="<?= htmlspecialchars($assetVersion($pageScript), ENT_QUOTES, 'UTF-8') ?>" defer></script>
        <?php $inlinePageJs = $assetContents($pageScript); ?>
        <?php if ($inlinePageJs !== ''): ?>
            <script><?= $inlinePageJs ?></script>
        <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>
