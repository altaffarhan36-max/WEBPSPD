<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/api/database.php';
requireAdmin();

$escape = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? '—'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$toolbarTitle = 'Monitoring Man Power';
$toolbarUserLabel = (string) ($_SESSION['username'] ?? 'Administrator') . ' · Master';
$toolbarActions = [['href' => 'modules.php', 'label' => 'Kembali ke Pilih Modul']];
$view = (string) ($_GET['view'] ?? 'monitoring');
if (!in_array($view, ['monitoring', 'all'], true)) {
    $view = 'monitoring';
}
$databaseError = false;
$summary = ['total' => 0, 'on_site' => 0, 'pending' => 0, 'placements' => 0];
$locations = [];
$people = [];

try {
    $database = databaseConnection();
    $summary = $database->query(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(employment_status = 'ON - SITE'), 0) AS on_site,
                COALESCE(SUM(employment_status = 'PENDING'), 0) AS pending,
                COUNT(DISTINCT NULLIF(placement, '')) AS placements
         FROM manpower_base"
    )->fetch() ?: $summary;
    $locations = $database->query(
        "SELECT COALESCE(NULLIF(placement, ''), 'Belum ditentukan') AS placement,
                COUNT(*) AS total,
                COALESCE(SUM(employment_status = 'ON - SITE'), 0) AS on_site,
                COALESCE(SUM(employment_status = 'PENDING'), 0) AS pending
         FROM manpower_base
         GROUP BY COALESCE(NULLIF(placement, ''), 'Belum ditentukan')
         ORDER BY placement"
    )->fetchAll();
    $people = $database->query(
        'SELECT source_no, employee_no, employee_name, division, position_name, grade_jis,
                grade_swi, company, poh, placement, allocation, onboard_date,
                employment_status, contract_no
         FROM manpower_base ORDER BY employee_name, employee_no'
    )->fetchAll();
} catch (Throwable $exception) {
    error_log('Manpower monitoring database error: ' . $exception->getMessage());
    $databaseError = true;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Monitoring Man Power | PT Star Wagen Indonesia</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/toolbar.css?v=20261015">
</head>
<body class="manpower-page">
  <main class="shell">
    <?php require __DIR__ . '/views/topbar.php'; ?>

    <section class="intro">
      <p class="eyebrow">Data tenaga kerja</p>
      <h1>Monitoring Man Power</h1>
      <p>Modul data dasar terpisah dari dashboard sertifikasi.</p>
    </section>

    <nav class="master-menu" aria-label="Menu monitoring manpower">
      <a class="menu-tab <?= $view === 'monitoring' ? 'active' : '' ?>" href="manpower.php?view=monitoring">Monitoring Man Power</a>
      <a class="menu-tab <?= $view === 'all' ? 'active' : '' ?>" href="manpower.php?view=all">All</a>
    </nav>

    <?php if ($databaseError): ?>
      <div class="notice error" role="alert">Data manpower belum tersedia. Impor database/migration_manpower_base.sql pada database certification_db.</div>
    <?php elseif ($view === 'monitoring'): ?>
      <section class="summary" aria-label="Ringkasan manpower">
        <article class="summary-card metric-total"><span>Total tenaga kerja</span><strong><?= (int) $summary['total'] ?></strong><small>Seluruh data karyawan</small></article>
        <article class="summary-card passed metric-onsite"><span>On-site</span><strong><?= (int) $summary['on_site'] ?></strong><small>Sedang aktif di lokasi</small></article>
        <article class="summary-card pending metric-pending"><span>Pending</span><strong><?= (int) $summary['pending'] ?></strong><small>Menunggu penempatan</small></article>
        <article class="summary-card metric-location"><span>Penempatan</span><strong><?= (int) $summary['placements'] ?></strong><small>Lokasi kerja terdata</small></article>
      </section>
      <section class="panel results-panel">
        <div class="panel-heading"><div><h2>Rekap berdasarkan penempatan</h2><p>Ringkasan semua data dari workbook TRIAL.xlsx.</p></div></div>
        <div class="table-wrap"><table>
          <thead><tr><th>Penempatan</th><th>Total</th><th>On-site</th><th>Pending</th></tr></thead>
          <tbody>
          <?php foreach ($locations as $location): ?>
            <tr><td class="placement-name"><?= $escape($location['placement']) ?></td><td><span class="count-pill"><?= (int) $location['total'] ?></span></td><td><span class="status-pill status-onsite"><?= (int) $location['on_site'] ?> On-site</span></td><td><span class="status-pill status-pending"><?= (int) $location['pending'] ?> Pending</span></td></tr>
          <?php endforeach; ?>
          <?php if (!$locations): ?><tr><td colspan="4" class="empty-cell">Belum ada data manpower.</td></tr><?php endif; ?>
          </tbody>
        </table></div>
      </section>
    <?php else: ?>
      <section class="panel results-panel">
        <div class="panel-heading">
          <div><h2>All manpower</h2><p><?= count($people) ?> data karyawan sebagai base data.</p></div>
          <div class="filters"><input id="manpowerSearch" type="search" placeholder="Cari nama, NIP, atau penempatan" aria-label="Cari data manpower"></div>
        </div>
        <div class="table-wrap"><table>
          <thead><tr><th>No.</th><th>NIP/NRP</th><th>Nama</th><th>Divisi</th><th>Jabatan</th><th>Grade JIS</th><th>Grade SWI</th><th>Perusahaan</th><th>POH</th><th>Penempatan</th><th>Alokasi</th><th>Tanggal on-board</th><th>Status</th><th>Kontrak</th></tr></thead>
          <tbody id="manpowerRows">
          <?php foreach ($people as $person): ?>
            <tr class="manpower-row" data-search="<?= $escape(mb_strtolower(implode(' ', array_filter([$person['employee_no'], $person['employee_name'], $person['placement'], $person['division'], $person['employment_status']])), 'UTF-8')) ?>">
              <td><?= $escape($person['source_no']) ?></td><td><?= $escape($person['employee_no']) ?></td><td class="mechanic-name"><?= $escape($person['employee_name']) ?></td><td><?= $escape($person['division']) ?></td><td><?= $escape($person['position_name']) ?></td><td><?= $escape($person['grade_jis']) ?></td><td><?= $escape($person['grade_swi']) ?></td><td><?= $escape($person['company']) ?></td><td><?= $escape($person['poh']) ?></td><td><?= $escape($person['placement']) ?></td><td><?= $escape($person['allocation']) ?></td><td><?= $escape($person['onboard_date']) ?></td><td><?= $escape($person['employment_status']) ?></td><td><?= $escape($person['contract_no']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$people): ?><tr><td colspan="14" class="empty-cell">Belum ada data manpower.</td></tr><?php endif; ?>
          </tbody>
        </table></div>
      </section>
    <?php endif; ?>

    <footer>Monitoring Man Power · Data dasar</footer>
  </main>
  <?php if (!$databaseError && $view === 'all'): ?><script src="assets/js/manpower.js" defer></script><?php endif; ?>
</body>
</html>
