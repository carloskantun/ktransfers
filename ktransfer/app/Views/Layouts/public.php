<?php
declare(strict_types=1);

use App\Services\HomeContentService;

$title = $title ?? 'Express Transfer Cancun';
$content = $content ?? '';
$pageStyles = $pageStyles ?? [];
$pageScripts = $pageScripts ?? [];
$public_locale = $public_locale ?? 'en';
$public_t = $public_t ?? null;
$public_localized_url = $public_localized_url ?? null;
$home_content = $home_content ?? [];
$isHomeContent = is_string($content ?? null) && str_contains((string) $content, 'class="lux-home"');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? rtrim($requestPath, '/') : '';
$isHomeRequest = $requestPath === '';

$publicLayoutMode = (string) ($publicLayoutMode ?? ($isHomeContent || $isHomeRequest ? 'immersive' : 'default'));
$isImmersiveLayout = $publicLayoutMode === 'immersive';
$pageStyles = is_array($pageStyles ?? null) ? $pageStyles : [];
$pageScripts = is_array($pageScripts ?? null) ? $pageScripts : [];
$publicLocale = in_array((string) ($public_locale ?? 'en'), ['en', 'es'], true) ? (string) $public_locale : 'en';
$publicT = is_callable($public_t ?? null) ? $public_t : static fn (string $key, string $fallback): string => $fallback;
$publicLocalizedUrl = is_callable($public_localized_url ?? null) ? $public_localized_url : static fn (string $locale): string => '?lang=' . $locale;

if ($isHomeContent || $isHomeRequest) {
    $pageStyles = array_values(array_unique(array_merge($pageStyles, [
        '/assets/public-home.css',
        '/assets/public-floating-contact.css',
    ])));
    $pageScripts = array_values(array_unique(array_merge($pageScripts, [
        '/assets/public-home.js',
    ])));
}

$documentTitle = $isHomeContent || $isHomeRequest
    ? $publicT('title.home', (string) $title)
    : (string) $title;

$homeContent = is_array($home_content ?? null) ? $home_content : [];
if (empty($homeContent)) {
    $homeContent = (new HomeContentService())->getHomePageContent();
}

$tracking = is_array($homeContent['tracking'] ?? null) ? $homeContent['tracking'] : [];
$gtmContainerId = strtoupper(trim((string) ($tracking['gtm_container_id'] ?? '')));
if (preg_match('/^GTM-[A-Z0-9]{4,20}$/', $gtmContainerId) !== 1) {
    $gtmContainerId = '';
}

$customHeadScript = trim((string) ($tracking['custom_head_script'] ?? ''));
$projectRoot = dirname(__DIR__, 5);
$publicRoot = $projectRoot . '/public_html';

$assetVersion = static function (string $publicAssetPath) use ($publicRoot): string {
    if (preg_match('#^https?://#i', $publicAssetPath) === 1) {
        return $publicAssetPath;
    }

    $relativePath = ltrim($publicAssetPath, '/');
    if ($relativePath === '' || str_contains($relativePath, '..')) {
        return $publicAssetPath;
    }

    $assetFile = $publicRoot . '/' . $relativePath;
    if (!is_file($assetFile)) {
        return $publicAssetPath;
    }

    return $publicAssetPath . '?v=' . (string) filemtime($assetFile);
};

$resolvePublicImage = static function (string $imagePath) use ($publicRoot): ?string {
    $imagePath = trim($imagePath);
    if ($imagePath === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $imagePath) === 1) {
        return $imagePath;
    }

    $candidate = str_starts_with($imagePath, '/') ? $imagePath : '/' . ltrim($imagePath, '/');
    $relativePath = ltrim($candidate, '/');

    if ($relativePath !== '' && !str_contains($relativePath, '..') && is_file($publicRoot . '/' . $relativePath)) {
        return $candidate;
    }

    return null;
};

$homeTheme = in_array((string) ($homeContent['home_theme'] ?? 'day'), ['day', 'night'], true)
    ? (string) $homeContent['home_theme']
    : 'day';
$darkLogoPath = $resolvePublicImage((string) ($homeContent['brand_logo'] ?? ''));
$lightLogoPath = $resolvePublicImage((string) ($homeContent['brand_logo_light'] ?? ''));
$brandLogoDayPath = $lightLogoPath ?? $darkLogoPath;
$brandLogoNightPath = $darkLogoPath ?? $lightLogoPath;
$brandLogoPath = $homeTheme === 'night' ? $brandLogoNightPath : $brandLogoDayPath;

$normalizeHex = static function ($value, string $fallback): string {
    $value = strtoupper(trim((string) $value));
    if (preg_match('/^#[0-9A-F]{6}$/', $value) === 1) {
        return $value;
    }

    return $fallback;
};

$landingTheme = is_array($homeContent['landing_theme'] ?? null) ? $homeContent['landing_theme'] : [];
$landingDay = is_array($landingTheme['day'] ?? null) ? $landingTheme['day'] : [];
$landingNight = is_array($landingTheme['night'] ?? null) ? $landingTheme['night'] : [];

$landingDayBg = $normalizeHex($landingDay['bg'] ?? '', '#FFFDF8');
$landingDayText = $normalizeHex($landingDay['text'] ?? '', '#101820');
$landingDayAccent = $normalizeHex($landingDay['accent'] ?? '', '#0F3F46');
$landingDayAccent2 = $normalizeHex($landingDay['accent_2'] ?? '', '#155D66');
$landingDayGold = $normalizeHex($landingDay['gold'] ?? '', '#C9A46A');
$landingDayHeaderBg = $normalizeHex($landingDay['header_bg'] ?? '', '#000000');
$landingDayFooterBg = $normalizeHex($landingDay['footer_bg'] ?? '', '#000000');

$landingNightBg = $normalizeHex($landingNight['bg'] ?? '', '#071114');
$landingNightText = $normalizeHex($landingNight['text'] ?? '', '#F7FBFC');
$landingNightAccent = $normalizeHex($landingNight['accent'] ?? '', '#4FB3C3');
$landingNightAccent2 = $normalizeHex($landingNight['accent_2'] ?? '', '#7AD4DF');
$landingNightGold = $normalizeHex($landingNight['gold'] ?? '', '#C9A46A');
$landingNightHeaderBg = $normalizeHex($landingNight['header_bg'] ?? '', '#000000');
$landingNightFooterBg = $normalizeHex($landingNight['footer_bg'] ?? '', '#071114');

$brandLogoDayUrl = is_string($brandLogoDayPath) && $brandLogoDayPath !== '' ? $assetVersion($brandLogoDayPath) : '';
$brandLogoNightUrl = is_string($brandLogoNightPath) && $brandLogoNightPath !== '' ? $assetVersion($brandLogoNightPath) : '';
$brandLogoDefaultUrl = is_string($brandLogoPath) && $brandLogoPath !== '' ? $assetVersion($brandLogoPath) : '';
$brandName = trim((string) ($homeContent['brand_name'] ?? 'Express Transfers'));
$brandName = $brandName !== '' ? $brandName : 'Express Transfers';
?>
<!doctype html>
<html lang="<?= htmlspecialchars($publicLocale, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($documentTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($publicT('meta.description', 'Private airport transfers in Cancun, Playa del Carmen & Riviera Maya. Luxury transportation with Express Transfer Cancun.'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($publicT('meta.keywords', 'airport transfer cancun, private transfer, cancun transportation'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion('/assets/design-base.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion('/assets/app.css'), ENT_QUOTES, 'UTF-8') ?>">
    <?php foreach ($pageStyles as $pageStyle): ?>
        <?php $pageStyle = (string) $pageStyle; ?>
        <?php if ($pageStyle === '') { continue; } ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion($pageStyle), ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <style>
        body.home-theme-day .lux-home {
            --home-paper: <?= htmlspecialchars($landingDayBg, ENT_QUOTES, 'UTF-8') ?>;
            --home-ink: <?= htmlspecialchars($landingDayText, ENT_QUOTES, 'UTF-8') ?>;
            --home-blue: <?= htmlspecialchars($landingDayAccent, ENT_QUOTES, 'UTF-8') ?>;
            --home-blue-2: <?= htmlspecialchars($landingDayAccent2, ENT_QUOTES, 'UTF-8') ?>;
            --home-gold: <?= htmlspecialchars($landingDayGold, ENT_QUOTES, 'UTF-8') ?>;
        }

        body.home-theme-day .site-header.is-immersive {
            background: <?= htmlspecialchars($landingDayHeaderBg, ENT_QUOTES, 'UTF-8') ?>;
        }

        body.home-theme-day .site-footer.is-immersive {
            background: <?= htmlspecialchars($landingDayFooterBg, ENT_QUOTES, 'UTF-8') ?>;
        }

        body.home-theme-night {
            background: <?= htmlspecialchars($landingNightBg, ENT_QUOTES, 'UTF-8') ?>;
        }

        body.home-theme-night .lux-home {
            --home-paper: <?= htmlspecialchars($landingNightBg, ENT_QUOTES, 'UTF-8') ?>;
            --home-ink: <?= htmlspecialchars($landingNightText, ENT_QUOTES, 'UTF-8') ?>;
            --home-blue: <?= htmlspecialchars($landingNightAccent, ENT_QUOTES, 'UTF-8') ?>;
            --home-blue-2: <?= htmlspecialchars($landingNightAccent2, ENT_QUOTES, 'UTF-8') ?>;
            --home-gold: <?= htmlspecialchars($landingNightGold, ENT_QUOTES, 'UTF-8') ?>;
        }

        body.home-theme-night .site-header.is-immersive {
            background: <?= htmlspecialchars($landingNightHeaderBg, ENT_QUOTES, 'UTF-8') ?>;
        }

        body.home-theme-night .site-footer.is-immersive {
            background: <?= htmlspecialchars($landingNightFooterBg, ENT_QUOTES, 'UTF-8') ?>;
        }
    </style>
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
</head>
<body class="<?= trim(($isImmersiveLayout ? 'is-immersive ' : '') . 'home-theme-' . $homeTheme) ?>">
    <?php if ($gtmContainerId !== ''): ?>
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmContainerId, ENT_QUOTES, 'UTF-8') ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>
    <main class="page-shell <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
        <section class="site-frame <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
            <header class="site-header <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
                <div class="brand-lockup">
                    <a class="brand-logo-link" href="/" aria-label="<?= htmlspecialchars($brandName . ' home', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (is_string($brandLogoPath) && $brandLogoPath !== ''): ?>
                            <img
                                id="site-brand-logo"
                                class="brand-logo brand-logo--wordmark"
                                src="<?= htmlspecialchars($brandLogoDefaultUrl, ENT_QUOTES, 'UTF-8') ?>"
                                data-logo-day="<?= htmlspecialchars($brandLogoDayUrl, ENT_QUOTES, 'UTF-8') ?>"
                                data-logo-night="<?= htmlspecialchars($brandLogoNightUrl, ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?>"
                                width="300"
                                height="122"
                                loading="eager"
                                decoding="async"
                            >
                        <?php else: ?>
                            <span class="brand-fallback"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="header-actions">
                    <div class="language-switcher" aria-label="Language selector">
                        <a class="<?= $publicLocale === 'en' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($publicLocalizedUrl('en'), ENT_QUOTES, 'UTF-8') ?>" hreflang="en">EN</a>
                        <a class="<?= $publicLocale === 'es' ? 'is-active' : '' ?>" href="<?= htmlspecialchars($publicLocalizedUrl('es'), ENT_QUOTES, 'UTF-8') ?>" hreflang="es">ES</a>
                    </div>
                    <button
                        type="button"
                        class="theme-toggle"
                        id="theme-toggle"
                        aria-label="Toggle dark mode"
                        title="Toggle theme"
                    >
                        <svg class="theme-icon theme-icon--sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m5.08-5.08l4.24-4.24M19 12h6m-6-6v6m0 6v6"/></svg>
                        <svg class="theme-icon theme-icon--moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                    <a class="header-cta" href="/#booking-form"><?= htmlspecialchars($publicT('nav.book_now', 'Book now'), ENT_QUOTES, 'UTF-8') ?></a>
                    <div class="header-menu" data-header-menu>
                        <button
                            type="button"
                            class="header-menu-toggle"
                            aria-label="Open navigation menu"
                            aria-controls="header-menu-panel"
                            aria-expanded="false"
                            data-menu-toggle
                        >
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>

                        <nav id="header-menu-panel" class="header-menu-panel" aria-label="Primary">
                            <a href="/#booking-form"><?= htmlspecialchars($publicT('nav.book', 'Book'), ENT_QUOTES, 'UTF-8') ?></a>
                            <a href="/#experience"><?= htmlspecialchars($publicT('nav.experience', 'Experience'), ENT_QUOTES, 'UTF-8') ?></a>
                            <a href="/#routes"><?= htmlspecialchars($publicT('nav.routes', 'Routes'), ENT_QUOTES, 'UTF-8') ?></a>
                            <a href="/#faq"><?= htmlspecialchars($publicT('nav.faq', 'FAQ'), ENT_QUOTES, 'UTF-8') ?></a>
                            <a href="/#contact"><?= htmlspecialchars($publicT('nav.contact', 'Contact'), ENT_QUOTES, 'UTF-8') ?></a>
                            <a href="/admin" class="nav-admin-link"><?= htmlspecialchars($publicT('nav.admin', 'Admin Panel'), ENT_QUOTES, 'UTF-8') ?></a>
                        </nav>
                    </div>
                </div>
            </header>

            <div class="page-content <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
                <?= $content ?>
            </div>

            <footer class="site-footer <?= $isImmersiveLayout ? 'is-immersive' : '' ?>">
                <div class="site-footer-copy"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></div>
                <nav class="site-footer-nav" aria-label="Footer">
                    <a href="/#booking-form"><?= htmlspecialchars($publicT('nav.book', 'Book'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="/#experience"><?= htmlspecialchars($publicT('nav.experience', 'Experience'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="/#routes"><?= htmlspecialchars($publicT('nav.routes', 'Routes'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="/#faq"><?= htmlspecialchars($publicT('nav.faq', 'FAQ'), ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="/#contact"><?= htmlspecialchars($publicT('nav.contact', 'Contact'), ENT_QUOTES, 'UTF-8') ?></a>
                </nav>
            </footer>
        </section>
    </main>
    <?php foreach ($pageScripts as $pageScript): ?>
        <?php $pageScript = (string) $pageScript; ?>
        <?php if ($pageScript === '') { continue; } ?>
        <script src="<?= htmlspecialchars($assetVersion($pageScript), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php endforeach; ?>
    <script>
        (() => {
            const menuRoots = Array.from(document.querySelectorAll('[data-header-menu]'));

            menuRoots.forEach((menuRoot) => {
                const toggle = menuRoot.querySelector('[data-menu-toggle]');
                const panel = menuRoot.querySelector('.header-menu-panel');

                if (!toggle || !panel) {
                    return;
                }

                const closeMenu = () => {
                    menuRoot.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                };

                const openMenu = () => {
                    menuRoot.classList.add('is-open');
                    toggle.setAttribute('aria-expanded', 'true');
                };

                toggle.addEventListener('click', () => {
                    const isOpen = menuRoot.classList.contains('is-open');
                    if (isOpen) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });

                panel.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', closeMenu);
                });

                document.addEventListener('click', (event) => {
                    if (!menuRoot.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMenu();
                    }
                });
            });
        })();
    </script>
</body>
</html>
