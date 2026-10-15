<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/api/database.php';
requireLogin();

$database = databaseConnection();
$identityQuery = $database->prepare('SELECT id, username, role FROM users WHERE id = :id LIMIT 1');
$identityQuery->execute([':id' => (int) $_SESSION['user_id']]);
$identity = $identityQuery->fetch();
if (!$identity) {
    $_SESSION = [];
    session_regenerate_id(true);
    header('Location: login.php');
    exit;
}
$_SESSION['username'] = $identity['username'];
$_SESSION['role'] = $identity['role'];
if ($identity['role'] !== 'master') {
    http_response_code(403);
    exit('Halaman ini hanya dapat diakses oleh Master.');
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
$escape = static fn(mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$lengthOf = static fn(string $value): int => function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
$message = (string) ($_SESSION['account_management_message'] ?? '');
$messageType = (string) ($_SESSION['account_management_message_type'] ?? '');
unset($_SESSION['account_management_message'], $_SESSION['account_management_message_type']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $message = '';
    $messageType = '';
    if (!hash_equals((string) $_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Sesi formulir berakhir. Muat ulang halaman dan coba lagi.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $accountRole = (string) ($_POST['account_role'] ?? '');
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($action !== 'create_user_account') {
            $message = 'Aksi tidak valid.';
        } elseif (!in_array($accountRole, ['mechanic', 'gl'], true)) {
            $message = 'Pilih jenis akun GL atau Mekanik.';
        } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username)) {
            $message = 'Username harus 3-64 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
        } elseif (!hasMinimumPasswordLength($password)) {
            $message = 'Password minimal 5 karakter.';
        } elseif ($accountRole === 'gl') {
            $glName = trim((string) ($_POST['gl_name'] ?? ''));
            $glSite = (string) ($_POST['gl_site'] ?? '');
            if ($glName === '' || $lengthOf($glName) > 120) {
                $message = 'Nama GL wajib diisi (maksimal 120 karakter).';
            } elseif (!in_array($glSite, ['ADRO', 'AMM'], true)) {
                $message = 'Pilih site GL: ADRO atau AMM.';
            } else {
                try {
                    $insert = $database->prepare(
                        'INSERT INTO users (username, password_hash, role, mechanic_id, gl_name, gl_site)
                         VALUES (:username, :password_hash, \'gl\', NULL, :gl_name, :gl_site)'
                    );
                    $insert->execute([
                        ':username' => $username,
                        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        ':gl_name' => $glName,
                        ':gl_site' => $glSite,
                    ]);
                    $message = 'Akun GL berhasil dibuat.';
                    $messageType = 'success';
                } catch (PDOException $exception) {
                    $message = 'Akun GL gagal dibuat. Username mungkin sudah digunakan.';
                }
            }
        } else {
            $mechanicMode = (string) ($_POST['mechanic_mode'] ?? '');
            if ($mechanicMode === 'existing') {
                $mechanicId = filter_var($_POST['mechanic_id'] ?? null, FILTER_VALIDATE_INT);
                if ($mechanicId === false || $mechanicId === null) {
                    $message = 'Pilih mekanik yang akan diberi akun.';
                } else {
                    $available = $database->prepare(
                        'SELECT m.id FROM mechanics AS m
                         LEFT JOIN users AS u ON u.mechanic_id = m.id
                         WHERE m.id = :mechanic_id AND u.id IS NULL LIMIT 1'
                    );
                    $available->execute([':mechanic_id' => $mechanicId]);
                    if (!$available->fetchColumn()) {
                        $message = 'Mekanik tidak ditemukan atau sudah memiliki akun.';
                    } else {
                        try {
                            $insert = $database->prepare(
                                'INSERT INTO users (username, password_hash, role, mechanic_id)
                                 VALUES (:username, :password_hash, \'mechanic\', :mechanic_id)'
                            );
                            $insert->execute([
                                ':username' => $username,
                                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                                ':mechanic_id' => $mechanicId,
                            ]);
                            $message = 'Akun mekanik berhasil dibuat.';
                            $messageType = 'success';
                        } catch (PDOException $exception) {
                            $message = 'Akun mekanik gagal dibuat. Username mungkin sudah digunakan.';
                        }
                    }
                }
            } elseif ($mechanicMode === 'new') {
                $sourceNo = trim((string) ($_POST['source_no'] ?? ''));
                $employeeNo = trim((string) ($_POST['employee_no'] ?? ''));
                $employeeName = trim((string) ($_POST['employee_name'] ?? ''));
                $division = trim((string) ($_POST['division'] ?? ''));
                $positionName = trim((string) ($_POST['position_name'] ?? ''));
                $gradeJis = trim((string) ($_POST['grade_jis'] ?? ''));
                $gradeSwi = trim((string) ($_POST['grade_swi'] ?? ''));
                $company = trim((string) ($_POST['company'] ?? ''));
                $poh = trim((string) ($_POST['poh'] ?? ''));
                $placement = trim((string) ($_POST['placement'] ?? ''));
                $allocation = trim((string) ($_POST['allocation'] ?? ''));
                $onboardDate = trim((string) ($_POST['onboard_date'] ?? ''));
                $employmentStatus = trim((string) ($_POST['employment_status'] ?? ''));
                $contractNo = trim((string) ($_POST['contract_no'] ?? ''));
                $certificationSite = (string) ($_POST['certification_site'] ?? '');
                $parsedJoinDate = DateTimeImmutable::createFromFormat('!Y-m-d', $onboardDate);
                $fieldLimits = [
                    'NIP/NRP' => [$employeeNo, 32], 'Nama' => [$employeeName, 120],
                    'Divisi' => [$division, 120], 'Jabatan' => [$positionName, 120],
                    'Grade JIS' => [$gradeJis, 40], 'Grade SWI' => [$gradeSwi, 40],
                    'Perusahaan' => [$company, 80], 'POH' => [$poh, 120],
                    'Penempatan' => [$placement, 120], 'Alokasi' => [$allocation, 40],
                    'Status' => [$employmentStatus, 80], 'Kontrak' => [$contractNo, 40],
                    'Nomor sumber' => [$sourceNo, 32],
                ];
                $tooLongField = '';
                foreach ($fieldLimits as $fieldName => [$fieldValue, $maxLength]) {
                    if ($lengthOf($fieldValue) > $maxLength) {
                        $tooLongField = $fieldName;
                        break;
                    }
                }
                if ($employeeNo === '') {
                    $message = 'NIP/NRP wajib diisi.';
                } elseif ($employeeName === '') {
                    $message = 'Nama mekanik wajib diisi.';
                } elseif ($tooLongField !== '') {
                    $message = $tooLongField . ' melebihi batas karakter.';
                } elseif (!in_array($certificationSite, ['ADRO', 'AMM'], true)) {
                    $message = 'Pilih site sertifikasi: ADRO atau AMM.';
                } elseif (!$parsedJoinDate || $parsedJoinDate->format('Y-m-d') !== $onboardDate) {
                    $message = 'Tanggal on-board tidak valid.';
                } else {
                    try {
                        $database->beginTransaction();
                        $insertManpower = $database->prepare(
                            'INSERT INTO manpower_base
                                (source_no, employee_no, employee_name, division, position_name, grade_jis, grade_swi,
                                 company, poh, placement, allocation, onboard_date, employment_status, contract_no)
                             VALUES
                                (:source_no, :employee_no, :employee_name, :division, :position_name, :grade_jis, :grade_swi,
                                 :company, :poh, :placement, :allocation, :onboard_date, :employment_status, :contract_no)'
                        );
                        $insertManpower->execute([
                            ':source_no' => $sourceNo === '' ? null : $sourceNo,
                            ':employee_no' => $employeeNo,
                            ':employee_name' => $employeeName,
                            ':division' => $division === '' ? null : $division,
                            ':position_name' => $positionName === '' ? null : $positionName,
                            ':grade_jis' => $gradeJis === '' ? null : $gradeJis,
                            ':grade_swi' => $gradeSwi === '' ? null : $gradeSwi,
                            ':company' => $company === '' ? null : $company,
                            ':poh' => $poh === '' ? null : $poh,
                            ':placement' => $placement === '' ? null : $placement,
                            ':allocation' => $allocation === '' ? null : $allocation,
                            ':onboard_date' => $onboardDate,
                            ':employment_status' => $employmentStatus === '' ? null : $employmentStatus,
                            ':contract_no' => $contractNo === '' ? null : $contractNo,
                        ]);
                        $insertMechanic = $database->prepare(
                            'INSERT INTO mechanics (technician_name, grade_level, site, join_date)
                             VALUES (:technician_name, :grade_level, :site, :join_date)'
                        );
                        $insertMechanic->execute([
                            ':technician_name' => $employeeName,
                            ':grade_level' => $gradeSwi !== '' && strlen($gradeSwi) <= 8 ? $gradeSwi : null,
                            ':site' => $certificationSite,
                            ':join_date' => $onboardDate,
                        ]);
                        $mechanicId = (int) $database->lastInsertId();
                        $insertUser = $database->prepare(
                            'INSERT INTO users (username, password_hash, role, mechanic_id)
                             VALUES (:username, :password_hash, \'mechanic\', :mechanic_id)'
                        );
                        $insertUser->execute([
                            ':username' => $username,
                            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                            ':mechanic_id' => $mechanicId,
                        ]);
                        $database->commit();
                        $message = 'Akun mekanik dan data manpower berhasil dibuat.';
                        $messageType = 'success';
                    } catch (PDOException $exception) {
                        if ($database->inTransaction()) {
                            $database->rollBack();
                        }
                        $message = 'Data atau akun mekanik gagal dibuat. NIP/NRP, nama, atau username mungkin sudah terdaftar.';
                    }
                }
            } else {
                $message = 'Pilih mekanik yang sudah ada atau tambah data mekanik baru.';
            }
        }
    }
    $_SESSION['account_management_message'] = $message;
    $_SESSION['account_management_message_type'] = $messageType;
    header('Location: account-management.php');
    exit;
}

$accounts = $database->query(
    'SELECT u.id, u.username, u.role, u.mechanic_id, u.gl_name, u.gl_site, u.created_at,
            m.technician_name, m.site AS mechanic_site,
            mb.source_no, mb.employee_no, mb.division, mb.position_name, mb.grade_jis, mb.grade_swi,
            mb.company, mb.poh, mb.placement, mb.allocation, mb.onboard_date,
            mb.employment_status, mb.contract_no
     FROM users AS u
     LEFT JOIN mechanics AS m ON m.id = u.mechanic_id
     LEFT JOIN (
         SELECT person.* FROM manpower_base AS person
         INNER JOIN (SELECT employee_name, MAX(id) AS latest_id FROM manpower_base GROUP BY employee_name) AS latest
             ON latest.latest_id = person.id
     ) AS mb ON mb.employee_name = m.technician_name
     ORDER BY u.id DESC'
)->fetchAll();
$unassignedMechanics = $database->query(
    'SELECT m.id, m.technician_name, m.site
     FROM mechanics AS m
     LEFT JOIN users AS u ON u.mechanic_id = m.id
     WHERE u.id IS NULL
     ORDER BY m.technician_name'
)->fetchAll();
$roleLabels = ['master' => 'Master', 'admin' => 'Admin', 'mechanic' => 'Mekanik', 'gl' => 'GL'];
$toolbarTitle = 'Account Management';
$toolbarUserLabel = (string) $identity['username'] . ' · Master';
$toolbarActions = [['href' => 'modules.php', 'label' => 'Kembali ke Pilih Modul']];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Account Management | PT Star Wagen Indonesia</title>
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/account-management.css">
  <link rel="stylesheet" href="assets/css/toolbar.css?v=20261015">
</head>
<body class="account-management-page">
  <main class="shell">
    <?php require __DIR__ . '/views/topbar.php'; ?>

    <section class="intro account-management-intro">
      <p class="eyebrow">Pengelolaan akses</p>
      <h1>Account Management</h1>
      <p>Pantau seluruh akun dan buat akun mekanik atau GL.</p>
    </section>
    <?php if ($message !== ''): ?><div class="account-message <?= $messageType === 'success' ? 'success' : '' ?>" role="status"><?= $escape($message) ?></div><?php endif; ?>

    <section class="account-management-summary" aria-label="Ringkasan akun">
      <article class="summary-card"><span>Total akun</span><strong><?= count($accounts) ?></strong></article>
      <article class="summary-card"><span>Akun mekanik</span><strong><?= count(array_filter($accounts, static fn(array $account): bool => $account['role'] === 'mechanic')) ?></strong></article>
      <article class="summary-card"><span>Akun GL</span><strong><?= count(array_filter($accounts, static fn(array $account): bool => $account['role'] === 'gl')) ?></strong></article>
      <article class="summary-card"><span>Mekanik belum memiliki akun</span><strong><?= count($unassignedMechanics) ?></strong></article>
    </section>

    <section class="account-create-grid" aria-label="Tambah akun">
      <article class="panel account-create-panel account-unified-panel">
        <div class="account-form-heading"><span class="account-form-icon" aria-hidden="true">+</span><div><h2>Tambah akun</h2><p>Pilih jenis akun. Formulir akan menampilkan data yang sesuai.</p></div></div>
        <form id="accountForm" method="post" class="account-form account-management-form" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="create_user_account">
          <label for="accountRole">Jenis akun<select id="accountRole" name="account_role" required><option value="">Pilih GL atau Mekanik</option><option value="gl">GL</option><option value="mechanic">Mekanik</option></select></label>

          <fieldset id="glAccountFields" class="account-field-group" disabled hidden>
            <legend>Data GL</legend>
            <label for="glName">Nama GL<input id="glName" name="gl_name" maxlength="120" required></label>
            <label for="glSite">Site<select id="glSite" name="gl_site" required><option value="">Pilih site</option><option value="ADRO">ADRO</option><option value="AMM">AMM</option></select></label>
          </fieldset>

          <fieldset id="mechanicAccountFields" class="account-field-group" disabled hidden>
            <legend>Data mekanik</legend>
            <label for="mechanicMode">Sumber data<select id="mechanicMode" name="mechanic_mode" required><option value="">Pilih cara penambahan</option><?php if ($unassignedMechanics !== []): ?><option value="existing">Pilih data mekanik yang sudah ada</option><?php endif; ?><option value="new">Tambah data mekanik baru</option></select></label>
            <fieldset id="existingMechanicFields" class="account-field-group account-field-group-nested" disabled hidden>
              <legend>Mekanik tanpa akun</legend>
              <label for="mechanicId">Mekanik<select id="mechanicId" name="mechanic_id" required><option value="">Pilih mekanik</option><?php foreach ($unassignedMechanics as $mechanic): ?><option value="<?= (int) $mechanic['id'] ?>"><?= $escape($mechanic['technician_name']) ?> · <?= $escape($mechanic['site']) ?></option><?php endforeach; ?></select></label>
            </fieldset>
            <fieldset id="newMechanicFields" class="account-field-group account-field-group-nested" disabled hidden>
              <legend>Data manpower mekanik</legend>
              <label>No. sumber<input name="source_no" maxlength="32"></label>
              <label>NIP/NRP<input name="employee_no" maxlength="32" required></label>
              <label>Nama<input name="employee_name" maxlength="120" required></label>
              <label>Divisi<input name="division" maxlength="120"></label>
              <label>Jabatan<input name="position_name" maxlength="120"></label>
              <label>Grade JIS<input name="grade_jis" maxlength="40"></label>
              <label>Grade SWI<input name="grade_swi" maxlength="40"></label>
              <label>Perusahaan<input name="company" maxlength="80"></label>
              <label>POH<input name="poh" maxlength="120"></label>
              <label>Penempatan<input name="placement" maxlength="120"></label>
              <label>Alokasi<input name="allocation" maxlength="40"></label>
              <label>Tanggal on-board<input type="date" name="onboard_date" required></label>
              <label>Status<input name="employment_status" maxlength="80"></label>
              <label>Kontrak<input name="contract_no" maxlength="40"></label>
              <label>Site sertifikasi<select name="certification_site" required><option value="">Pilih site</option><option value="ADRO">ADRO</option><option value="AMM">AMM</option></select></label>
            </fieldset>
          </fieldset>

          <label>Username<input name="username" required minlength="3" maxlength="64" pattern="[A-Za-z0-9_.-]+"></label>
          <label>Password awal<input type="password" name="password" required minlength="5" autocomplete="new-password"></label>
          <button type="submit" class="create-account">Tambah akun</button>
        </form>
      </article>
    </section>

    <section class="panel account-list-panel">
      <div class="panel-heading"><div><h2>Semua akun</h2><p>Daftar username, jenis akses, nama pemilik, dan site.</p></div></div>
      <div class="account-table-wrap">
        <table class="account-table">
          <thead><tr><th>No.</th><th>NIP/NRP</th><th>Nama</th><th>Divisi</th><th>Jabatan</th><th>Grade JIS</th><th>Grade SWI</th><th>Perusahaan</th><th>POH</th><th>Penempatan</th><th>Alokasi</th><th>Tanggal on-board</th><th>Status</th><th>Kontrak</th><th>Site sertifikasi</th><th>Jenis akun</th><th>Username</th></tr></thead>
          <tbody>
          <?php foreach ($accounts as $index => $account): ?>
            <?php $isMechanic = $account['role'] === 'mechanic'; $displayName = $isMechanic ? ($account['technician_name'] ?? '-') : ($account['role'] === 'gl' ? ($account['gl_name'] ?: '-') : '-'); $displayValue = static fn(string $key): mixed => $isMechanic ? ($account[$key] ?? '-') : '-'; $sourceNumber = $isMechanic && !empty($account['source_no']) ? $account['source_no'] : $index + 1; ?>
            <tr>
              <td><?= $escape($sourceNumber) ?></td><td><?= $escape($displayValue('employee_no')) ?></td><td><strong><?= $escape($displayName) ?></strong></td>
              <td><?= $escape($displayValue('division')) ?></td><td><?= $escape($displayValue('position_name')) ?></td><td><?= $escape($displayValue('grade_jis')) ?></td><td><?= $escape($displayValue('grade_swi')) ?></td><td><?= $escape($displayValue('company')) ?></td><td><?= $escape($displayValue('poh')) ?></td><td><?= $escape($displayValue('placement')) ?></td><td><?= $escape($displayValue('allocation')) ?></td><td><?= $escape($displayValue('onboard_date')) ?></td><td><?= $escape($displayValue('employment_status')) ?></td><td><?= $escape($displayValue('contract_no')) ?></td>
              <td><?= $escape($isMechanic ? ($account['mechanic_site'] ?? '-') : ($account['role'] === 'gl' ? ($account['gl_site'] ?? '-') : '-')) ?></td><td><span class="account-role-badge role-<?= $escape($account['role']) ?>"><?= $escape($roleLabels[$account['role']] ?? $account['role']) ?></span></td><td><strong><?= $escape($account['username']) ?></strong></td>
            </tr>
          <?php endforeach; ?>
          <?php if ($accounts === []): ?><tr><td colspan="17" class="account-empty">Belum ada akun.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
    <footer>Account Management - PT Star Wagen Indonesia</footer>
  </main>
  <script>
    const roleSelect = document.getElementById('accountRole');
    const mechanicMode = document.getElementById('mechanicMode');
    const glFields = document.getElementById('glAccountFields');
    const mechanicFields = document.getElementById('mechanicAccountFields');
    const existingFields = document.getElementById('existingMechanicFields');
    const newFields = document.getElementById('newMechanicFields');
    function syncAccountFields() {
      glFields.hidden = roleSelect.value !== 'gl';
      glFields.disabled = glFields.hidden;
      mechanicFields.hidden = roleSelect.value !== 'mechanic';
      mechanicFields.disabled = mechanicFields.hidden;
      existingFields.hidden = mechanicMode.value !== 'existing';
      existingFields.disabled = existingFields.hidden || mechanicFields.disabled;
      newFields.hidden = mechanicMode.value !== 'new';
      newFields.disabled = newFields.hidden || mechanicFields.disabled;
    }
    roleSelect.addEventListener('change', syncAccountFields);
    mechanicMode.addEventListener('change', syncAccountFields);
    syncAccountFields();
  </script>
</body>
</html>
