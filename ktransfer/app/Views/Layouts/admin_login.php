<?php
declare(strict_types=1);

use App\Services\HomeContentService;

$homeContent = (new HomeContentService())->getHomePageContent();
$tracking = is_array($homeContent['tracking'] ?? null) ? $homeContent['tracking'] : [];
$gtmContainerId = strtoupper(trim((string) ($tracking['gtm_container_id'] ?? '')));
if (preg_match('/^GTM-[A-Z0-9]{4,20}$/', $gtmContainerId) !== 1) {
    $gtmContainerId = '';
}

$customHeadScript = trim((string) ($tracking['custom_head_script'] ?? ''));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($gtmContainerId !== ''): ?>
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?= htmlspecialchars($gtmContainerId, ENT_QUOTES, 'UTF-8') ?>');
        </script>
    <?php endif; ?>
    <?php if ($customHeadScript !== ''): ?>
        <?= $customHeadScript ?>
    <?php endif; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-box h1 { margin-bottom: 20px; font-size: 24px; }
        .login-box label { display: block; margin-bottom: 5px; font-weight: 600; }
        .login-box input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 4px; }
        .login-box button { width: 100%; padding: 12px; background: #3b82f6; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .login-box button:hover { background: #2563eb; }
        .error { background: #fef2f2; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php if ($gtmContainerId !== ''): ?>
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmContainerId, ENT_QUOTES, 'UTF-8') ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>
    <div class="login-box">
        <?= $content ?>
    </div>
</body>
</html>
