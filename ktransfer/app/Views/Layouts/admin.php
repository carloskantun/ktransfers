<?php
declare(strict_types=1);

use App\Core\ACL;
use App\Core\Auth;
use App\Core\Csrf;
use App\Services\HomeContentService;

$title = $title ?? 'Panel de control';
$content = $content ?? '';
$currentUser = Auth::user();
$isOperatorOnly = ACL::currentUserHasRole('operator') && !ACL::currentUserHasRole('admin');
$isSuperAdminOnly = ACL::currentUserHasRole('superadmin') && !ACL::currentUserHasRole('admin');
$can = static function (string $permissionCode): bool {
    return ACL::currentUserCan($permissionCode);
};

$projectRoot = dirname(__DIR__, 5);
$publicRoot = $projectRoot . '/public_html';
$adminLogoCandidates = [
    '/assets/expresslogo-300X200.png.webp',
    '/assets/express-logo.svg',
    '/assets/express-logo.png',
    '/assets/logo.svg',
    '/assets/logo.png',
];

$adminLogoPath = null;
$homeContent = (new HomeContentService())->getHomePageContent();
$tracking = is_array($homeContent['tracking'] ?? null) ? $homeContent['tracking'] : [];
$gtmContainerId = strtoupper(trim((string) ($tracking['gtm_container_id'] ?? '')));
if (preg_match('/^GTM-[A-Z0-9]{4,20}$/', $gtmContainerId) !== 1) {
    $gtmContainerId = '';
}

$customHeadScript = trim((string) ($tracking['custom_head_script'] ?? ''));

$normalizeHex = static function ($value, string $fallback): string {
    $value = strtoupper(trim((string) $value));
    if (preg_match('/^#[0-9A-F]{6}$/', $value) === 1) {
        return $value;
    }

    return $fallback;
};

$adminTheme = in_array((string) ($homeContent['home_theme'] ?? 'day'), ['day', 'night'], true)
    ? (string) $homeContent['home_theme']
    : 'day';

$landingTheme = is_array($homeContent['landing_theme'] ?? null) ? $homeContent['landing_theme'] : [];
$landingDay = is_array($landingTheme['day'] ?? null) ? $landingTheme['day'] : [];
$landingNight = is_array($landingTheme['night'] ?? null) ? $landingTheme['night'] : [];

$adminDayBg = $normalizeHex($landingDay['bg'] ?? '', '#F3F6FB');
$adminNightBg = $normalizeHex($landingNight['bg'] ?? '', '#0F172A');
$adminDaySidebar = $normalizeHex($landingDay['header_bg'] ?? '', '#0F1F3A');
$adminNightSidebar = $normalizeHex($landingNight['header_bg'] ?? '', '#0F1F3A');
$adminDaySidebarEnd = $normalizeHex($landingDay['footer_bg'] ?? '', '#122A4D');
$adminNightSidebarEnd = $normalizeHex($landingNight['footer_bg'] ?? '', '#0C1A31');

$adminBg = $adminTheme === 'night' ? $adminNightBg : $adminDayBg;
$adminSidebar = $adminTheme === 'night' ? $adminNightSidebar : $adminDaySidebar;
$adminSidebarEnd = $adminTheme === 'night' ? $adminNightSidebarEnd : $adminDaySidebarEnd;

$brandLogoDark = trim((string) ($homeContent['brand_logo'] ?? ''));
$brandLogoLight = trim((string) ($homeContent['brand_logo_light'] ?? ''));
$customAdminLogo = $adminTheme === 'night'
    ? ($brandLogoDark !== '' ? $brandLogoDark : $brandLogoLight)
    : ($brandLogoLight !== '' ? $brandLogoLight : $brandLogoDark);

if ($customAdminLogo !== '') {
    if (preg_match('#^https?://#i', $customAdminLogo) === 1) {
        // URL absoluta — usarla directamente
        $adminLogoPath = $customAdminLogo;
    } else {
        // Ruta relativa guardada en DB (ej. /uploads/home/logo.png) — confiar en ella sin is_file
        $candidate = str_starts_with($customAdminLogo, '/') ? $customAdminLogo : '/' . ltrim($customAdminLogo, '/');
        if (!str_contains($candidate, '..')) {
            $adminLogoPath = $candidate;
        }
    }
}

foreach ($adminLogoCandidates as $candidate) {
    if ($adminLogoPath !== null) {
        break;
    }

    $relativePath = ltrim($candidate, '/');

    if (is_file($publicRoot . '/' . $relativePath)) {
        $adminLogoPath = $candidate;
        break;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?> - Admin</title>
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
        :root {
            --bg: <?= htmlspecialchars($adminBg, ENT_QUOTES, 'UTF-8') ?>;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --border: #dbe3ef;
            --text: #0f172a;
            --muted: #64748b;
            --sidebar: <?= htmlspecialchars($adminSidebar, ENT_QUOTES, 'UTF-8') ?>;
            --sidebar-soft: <?= htmlspecialchars($adminSidebarEnd, ENT_QUOTES, 'UTF-8') ?>;
            --primary: #2463eb;
            --primary-hover: #1e52c2;
            --success: #047857;
            --danger: #b91c1c;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
            background: radial-gradient(circle at 10% 5%, rgba(255,255,255,0.18) 0, transparent 35%), var(--bg);
            color: var(--text);
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--sidebar) 0%, var(--sidebar-soft) 100%);
            color: #fff;
            padding: 18px 14px;
            border-right: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-toggle {
            display: none;
        }
        .sidebar-top {
            display: block;
        }
        .sidebar-menu-button {
            display: none;
        }
        .sidebar-brand {
            margin-bottom: 20px;
            padding: 6px 8px;
        }
        .sidebar-brand-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .sidebar-brand-logo {
            display: block;
            width: 170px;
            max-width: 100%;
            height: auto;
        }
        .sidebar-brand-text {
            display: inline-block;
        }
        .sidebar nav a {
            display: block;
            padding: 10px 11px;
            color: #d6e2f3;
            text-decoration: none;
            margin-bottom: 5px;
            border-radius: 9px;
            transition: all 0.18s ease;
        }
        .sidebar nav a:hover {
            background: var(--sidebar-soft);
            color: #fff;
            transform: translateX(1px);
        }
        .nav-section {
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .main {
            flex: 1;
            min-width: 0;
            padding: 18px;
        }
        .topbar {
            background: var(--surface);
            padding: 14px 18px;
            margin: 0 0 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .content {
            background: var(--surface);
            padding: 18px;
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 14px 26px rgba(15, 23, 42, 0.07);
            min-width: 0;
            overflow-x: auto;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            gap: 10px;
        }
        .page-header h1 {
            margin: 0;
            font-size: 1.7rem;
            letter-spacing: -0.02em;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
        }
        table th, table td {
            padding: 11px 10px;
            text-align: left;
            border-bottom: 1px solid #e7edf5;
            font-size: 0.93rem;
        }
        table th {
            background: var(--surface-soft);
            font-weight: 700;
            color: #334155;
        }
        .admin-filter-bar {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .admin-filter-bar > div {
            min-width: 180px;
        }
        .admin-pagination {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        .admin-row-action {
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        .admin-row-action:hover {
            text-decoration: underline;
        }
        .form-group { margin-bottom: 12px; }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #334155;
        }
        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #cfd8e6;
            border-radius: 9px;
            padding: 10px 11px;
            font-size: 0.94rem;
            background: #fff;
            color: var(--text);
        }
        input[type="checkbox"],
        input[type="radio"] {
            width: auto;
            min-width: 18px;
            height: 18px;
            padding: 0;
            accent-color: var(--primary);
        }
        .admin-check {
            display: inline-flex;
            gap: 9px;
            align-items: flex-start;
            color: #334155;
            font-weight: 600;
            line-height: 1.35;
        }
        .admin-page-note {
            margin: 6px 0 0;
            color: var(--muted);
            line-height: 1.45;
        }
        .admin-form-card {
            max-width: 760px;
        }
        .admin-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .admin-form-grid .form-group {
            margin-bottom: 0;
        }
        .admin-form-full {
            grid-column: 1 / -1;
        }
        .admin-meta-line {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 0 0 14px;
            color: var(--muted);
        }
        .admin-meta-line strong {
            color: var(--text);
        }
        .admin-section-title {
            margin: 0 0 12px;
            font-size: 1.05rem;
        }
        .admin-report-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .admin-report-grid .card {
            margin-top: 0 !important;
        }
        .field-note {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.84rem;
            line-height: 1.45;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #8fb8ff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
        }
        .form-actions {
            display: flex;
            gap: 8px;
            margin-top: 6px;
            flex-wrap: wrap;
        }
        .error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--danger);
            border-radius: 9px;
            padding: 10px;
            margin-bottom: 12px;
        }
        .error ul { margin-left: 18px; }
        .btn {
            display: inline-block;
            padding: 9px 14px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.18s ease;
        }
        .btn:hover { background: var(--primary-hover); }
        .btn.btn-secondary {
            background: #e2e8f0;
            color: #1f2937;
        }
        .btn.btn-secondary:hover {
            background: #cfd8e6;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #e8f0ff;
            color: #1e3a8a;
            font-size: 0.84rem;
            font-weight: 700;
            max-width: 100%;
            overflow-wrap: anywhere;
        }
        .notice {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }
        @media (max-width: 920px) {
            .sidebar { width: 210px; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .content { padding: 14px; }
            .topbar { flex-direction: column; align-items: flex-start; }
        }
        @media (max-width: 760px) {
            body {
                display: block;
                min-height: 100vh;
            }
            .sidebar {
                width: 100%;
                padding: 10px 12px;
                border-right: 0;
                border-bottom: 1px solid rgba(255,255,255,0.08);
            }
            .sidebar-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }
            .sidebar-brand {
                margin-bottom: 0;
                padding: 0 2px;
            }
            .sidebar-brand-logo {
                width: 132px;
            }
            .sidebar-menu-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 88px;
                padding: 9px 12px;
                border: 1px solid rgba(255,255,255,0.18);
                border-radius: 9px;
                background: rgba(255,255,255,0.08);
                color: #fff;
                font-weight: 700;
                cursor: pointer;
            }
            .sidebar nav {
                display: none;
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid rgba(255,255,255,0.10);
            }
            .sidebar-toggle:checked ~ nav {
                display: grid;
                gap: 7px;
            }
            .sidebar nav a {
                margin-bottom: 0;
                padding: 11px 12px;
            }
            .sidebar nav a:hover {
                transform: none;
            }
            .nav-section {
                display: block;
                margin-top: 12px;
                margin-bottom: 2px;
            }
            .sidebar nav form {
                margin-top: 0 !important;
            }
            .sidebar nav form .btn {
                width: 100% !important;
            }
            .main {
                padding: 12px;
            }
            .topbar {
                display: none;
            }
            .content {
                padding: 10px;
                border-radius: 10px;
            }
            .page-header h1 {
                font-size: 1.35rem;
            }
            .btn {
                width: 100%;
                text-align: center;
            }
            .form-actions {
                width: 100%;
            }
            .form-actions .btn {
                flex: 1 1 100%;
            }
            .admin-form-grid {
                grid-template-columns: 1fr;
            }
            .admin-form-card {
                max-width: none;
            }
            .admin-report-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .admin-page-note {
                font-size: 0.92rem;
            }
            .admin-card-table,
            .admin-card-table thead,
            .admin-card-table tbody,
            .admin-card-table tr,
            .admin-card-table th,
            .admin-card-table td {
                display: block;
                width: 100%;
            }
            .admin-card-table {
                border-collapse: separate;
                border-spacing: 0;
            }
            .admin-card-table thead {
                display: none;
            }
            .admin-card-table tr {
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 12px;
                margin-bottom: 12px;
                background: #fff;
            }
            .admin-card-table td {
                border-bottom: 0;
                padding: 8px 0;
                overflow-wrap: anywhere;
            }
            .admin-card-table td + td {
                border-top: 1px solid #eef3f8;
            }
            .admin-card-table td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 4px;
                color: var(--muted);
                font-size: 0.76rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }
            .admin-card-table td.admin-empty-row {
                text-align: center;
            }
            .admin-card-table td.admin-empty-row::before {
                display: none;
            }
            .admin-row-action,
            .admin-card-table .btn {
                display: block;
                width: 100%;
                text-align: center;
            }
            .admin-filter-bar {
                display: grid;
                gap: 10px;
            }
            .admin-filter-bar > div {
                min-width: 0;
            }
            .admin-pagination {
                justify-content: stretch;
            }
            .admin-pagination a,
            .admin-pagination span {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php if ($gtmContainerId !== ''): ?>
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmContainerId, ENT_QUOTES, 'UTF-8') ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>
    <aside class="sidebar">
        <input class="sidebar-toggle" type="checkbox" id="admin-menu-toggle" aria-label="Abrir menu admin">
        <div class="sidebar-top">
            <div class="sidebar-brand">
                <a href="/admin" class="sidebar-brand-link" aria-label="Inicio del panel">
                    <?php if (is_string($adminLogoPath) && $adminLogoPath !== ''): ?>
                        <img class="sidebar-brand-logo" src="<?= htmlspecialchars($adminLogoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" width="170" height="113">
                    <?php else: ?>
                        <?php
                            $brandName = trim((string) ($homeContent['brand_name'] ?? $homeContent['brand'] ?? ''));
                            $brandName = $brandName !== '' ? $brandName : 'Admin';
                        ?>
                        <span class="sidebar-brand-text"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <label class="sidebar-menu-button" for="admin-menu-toggle">Menu</label>
        </div>
        <nav>
            <?php if ($can('dashboard.view') && !$isOperatorOnly): ?>
                <a href="/admin">Dashboard</a>
            <?php endif; ?>
            <?php if ($can('bookings.view') && !$isOperatorOnly): ?>
                <a href="/admin/bookings">Reservas</a>
            <?php endif; ?>
            <?php if ($can('operations.view')): ?>
                <a href="/admin/operations/agenda">Orden del dia</a>
            <?php endif; ?>

            <?php if ($can('catalog.manage')): ?>
                <div class="nav-section">Catalogo</div>
                <a href="/admin/catalog/zones">Zonas</a>
                <a href="/admin/catalog/services">Tipos de servicio</a>
                <a href="/admin/catalog/currencies">Monedas</a>
                <a href="/admin/catalog/vehicles">Vehiculos</a>
                <a href="/admin/catalog/providers">Proveedores</a>
                <a href="/admin/catalog/places">Lugares</a>
                <a href="/admin/catalog/airlines">Aerolineas</a>
            <?php endif; ?>

            <?php if ($can('pricing.manage')): ?>
                <div class="nav-section">Precios</div>
                <a href="/admin/pricing/pax-ranges">Rangos de pasajeros</a>
                <a href="/admin/pricing/rate-rules">Tarifas</a>
            <?php endif; ?>

            <?php if ($can('accounting.view') || $can('kpis.view')): ?>
                <div class="nav-section">Reportes</div>
                <?php if ($can('accounting.view')): ?>
                    <a href="/admin/accounting">Contabilidad</a>
                <?php endif; ?>
                <?php if ($can('kpis.view')): ?>
                    <a href="/admin/kpis">KPIs</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($can('users.manage')): ?>
                <div class="nav-section">Admin</div>
                <a href="/admin/users">Usuarios</a>
            <?php endif; ?>

            <?php if ($isSuperAdminOnly || ($can('home.manage') && ACL::currentUserHasRole('superadmin'))): ?>
                <div class="nav-section">Configuración</div>
                <a href="/admin/content/home">Home Settings</a>
            <?php endif; ?>
            
            <form method="post" action="/admin/logout" style="margin-top: 20px;">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn" style="width: 100%;">Logout</button>
            </form>
        </nav>
    </aside>
    <div class="main">
        <div class="topbar">
            <strong><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></strong>
            <?php if (is_array($currentUser)): ?>
                <span class="pill">
                    <?= htmlspecialchars((string) ($currentUser['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    ·
                    <?= htmlspecialchars((string) ($currentUser['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="content">
            <?= $content ?>
        </div>
    </div>
</body>
</html>
