<?php
declare(strict_types=1);

use App\Core\ACL;
use App\Core\Auth;
use App\Core\Csrf;

$title = $title ?? 'KTransfers Admin';
$currentUser = Auth::user();
$isOperatorOnly = ACL::currentUserHasRole('operator') && !ACL::currentUserHasRole('admin');
$can = static function (string $permissionCode): bool {
    return ACL::currentUserCan($permissionCode);
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?> - Admin</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --border: #dbe3ef;
            --text: #0f172a;
            --muted: #64748b;
            --sidebar: #0f1f3a;
            --sidebar-soft: #122a4d;
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
            background: radial-gradient(circle at 10% 5%, #d9eafe 0, transparent 35%), var(--bg);
            color: var(--text);
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--sidebar) 0%, #0c1a31 100%);
            color: #fff;
            padding: 18px 14px;
            border-right: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 1.45rem;
            letter-spacing: -0.02em;
            padding: 6px 8px;
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
        .main { flex: 1; padding: 18px; }
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
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>KTransfers</h2>
        <nav>
            <?php if ($can('dashboard.view')): ?>
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
                <a href="/admin/catalog/places">Lugares</a>
                <a href="/admin/catalog/airlines">Aerolineas</a>
            <?php endif; ?>

            <?php if ($can('pricing.manage')): ?>
                <div class="nav-section">Pricing</div>
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

            <?php if ($can('content.manage')): ?>
                <div class="nav-section">Website</div>
                <a href="/admin/content/home">Home</a>
            <?php endif; ?>

            <?php if ($can('users.manage')): ?>
                <div class="nav-section">Admin</div>
                <a href="/admin/users">Usuarios</a>
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
