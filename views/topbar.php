<?php
declare(strict_types=1);

$toolbarEscape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$toolbarTitle = trim((string) ($toolbarTitle ?? 'PT Star Wagen Indonesia'));
$toolbarUserLabel = trim((string) ($toolbarUserLabel ?? $_SESSION['username'] ?? 'Pengguna'));
$toolbarActions = is_array($toolbarActions ?? null) ? $toolbarActions : [];
?>
<header class="topbar app-topbar">
  <a class="brand app-brand" href="modules.php" aria-label="Beranda modul PT Star Wagen Indonesia">
    <img class="brand-logo" src="assets/star-wagen-logo.jpeg" alt="Star Wagen Indonesia">
  </a>
  <strong class="app-topbar-title"><?= $toolbarEscape($toolbarTitle) ?></strong>
  <div class="app-topbar-account">
    <span class="app-topbar-user"><?= $toolbarEscape($toolbarUserLabel) ?></span>
    <?php if ($toolbarActions !== []): ?>
      <nav class="app-topbar-actions" aria-label="Navigasi halaman">
        <?php foreach ($toolbarActions as $toolbarAction): ?>
          <a class="module-access" href="<?= $toolbarEscape($toolbarAction['href'] ?? '#') ?>"><?= $toolbarEscape($toolbarAction['label'] ?? '') ?></a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
    <form class="app-topbar-logout" action="logout.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= $toolbarEscape($_SESSION['csrf_token'] ?? '') ?>">
      <button type="submit" class="logout">Keluar</button>
    </form>
  </div>
</header>
