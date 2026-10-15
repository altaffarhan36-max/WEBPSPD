<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
requireLogin();

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

$role = (string) ($_SESSION['role'] ?? '');
$canAccessManpower = in_array($role, ['master', 'admin'], true);
$toolbarTitle = 'Product Support · People Development';
$toolbarUserLabel = (string) ($_SESSION['username'] ?? 'Pengguna');
$toolbarActions = $canAccessManpower
    ? [['href' => 'manpower.php', 'label' => 'Monitoring Man Power'], ['href' => 'kalkulator-mpp.php', 'label' => 'Kalkulator MPP']]
    : [];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pilih Modul | PT Star Wagen Indonesia</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .module-page { min-height: 100vh; color: #fff; background-image: linear-gradient(180deg, #1014178c, #101417ad), url("assets/Picture1.jpg"); background-position: center; background-size: cover; background-attachment: fixed; }
    .module-page .shell { width: min(1120px, calc(100% - 40px)); }
    .module-page .topbar { padding: 0 18px; border: 0; border-radius: 0 0 12px 12px; background: linear-gradient(100deg, #111 0%, #777 100%); color: #fff; }
    .module-page .topbar .account { color: #fff; }
    .module-page .topbar .logout { border-color: #ffffff70; background: #ffffff18; color: #fff; }
    .module-page .topbar .logout:hover { border-color: #fff; background: #ffffff30; color: #fff; }
    .module-hero { margin: 44px 0 28px; padding: 38px; border: 1px solid #ffffff35; border-radius: 20px; background: #10141770; color: #fff; backdrop-filter: blur(3px); }
    .module-hero .eyebrow { color: #e66b7f; font-size: .78rem; font-weight: 750; letter-spacing: .14em; text-transform: uppercase; }
    .module-hero h1 { margin: 10px 0; font-size: clamp(2rem, 5vw, 3rem); }
    .module-hero p:last-child { margin: 0; color: #ccc; }
    .module-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .module-card { display: flex; min-height: 230px; flex-direction: column; padding: 24px; border: 1px solid #e2e2e2; border-radius: 16px; background: #fff; box-shadow: 0 8px 24px #1717170a; }
    .module-card .module-number { color: #c8102e; font-size: .75rem; font-weight: 800; letter-spacing: .12em; }
    .module-card h2 { margin: 13px 0 8px; font-size: 1.35rem; }
    .module-actions { display: grid; gap: 8px; }
    .module-card p { flex: 1; margin: 0 0 22px; color: #777; font-size: .9rem; line-height: 1.55; }
    .module-card a, .module-card .module-disabled { display: inline-flex; justify-content: center; padding: 11px 14px; border: 1px solid #c8102e; border-radius: 9px; background: #c8102e; color: #fff; font-weight: 700; text-align: center; text-decoration: none; }
    .module-card a:hover { background: #a90d27; }
    .module-card .module-disabled { border-color: #ddd; background: #f4f4f4; color: #888; }
    .account-builder-link { position: fixed; z-index: 20; bottom: 20px; left: 20px; padding: 12px 16px; border: 1px solid #fff8; border-radius: 10px; background: #c8102e; box-shadow: 0 5px 18px #0005; color: #fff; font-size: .88rem; font-weight: 750; text-decoration: none; }
    .account-builder-link:hover { background: #a90d27; }
    @media (max-width: 520px) { .account-builder-link { bottom: 12px; left: 12px; } }
    @media (max-width: 760px) { .module-grid { grid-template-columns: 1fr; } .module-hero { margin-top: 28px; padding: 28px 24px; } .module-card { min-height: 190px; } }
  </style>
  <link rel="stylesheet" href="assets/css/toolbar.css?v=20261015">
</head>
<body class="module-page">
  <main class="shell">
    <?php require __DIR__ . '/views/topbar.php'; ?>
    <section class="module-hero">
      <p class="eyebrow">People Development System</p>
      <h1>Pilih Modul</h1>
      <p>Pilih sub bab yang ingin Anda buka.</p>
    </section>
    <section class="module-grid" aria-label="Pilihan modul">
      <article class="module-card">
        <span class="module-number">01</span><h2>Man Power</h2>
        <p>Monitoring data manpower dan perencanaan kebutuhan tenaga kerja.</p>
        <?php if ($canAccessManpower): ?><div class="module-actions"><a href="manpower.php">Monitoring Man Power</a><a href="kalkulator-mpp.php">Kalkulator MPP</a></div><?php else: ?><span class="module-disabled">Akses Master / Admin</span><?php endif; ?>
      </article>
      <article class="module-card">
        <span class="module-number">02</span><h2>Training</h2>
        <p>Lihat nilai training pada dashboard penilaian mekanik.</p>
        <a href="dashboard.php#training">Buka Training</a>
      </article>
      <article class="module-card">
        <span class="module-number">03</span><h2>Certification</h2>
        <p>Pantau hasil penilaian dan rekap sertifikasi mekanik.</p>
        <a href="dashboard.php#certification">Buka Certification</a>
      </article>
    </section>
    <?php if ($role === 'master'): ?><a class="account-builder-link" href="account-management.php">Account Management</a><?php endif; ?>
    <footer>PT Star Wagen Indonesia · People Development</footer>
  </main>
</body>
</html>

