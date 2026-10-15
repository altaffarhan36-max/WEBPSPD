<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/api/database.php';

$error = '';
$username = '';
$setupAvailable = false;

try {
    $database = databaseConnection();
    $lock = (int) $database->query("SELECT GET_LOCK('autolube_first_user_setup', 5)")->fetchColumn();
    if ($lock !== 1) {
        throw new RuntimeException('Tidak bisa mengunci proses setup. Coba lagi.');
    }
    $setupAvailable = (int) $database->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;
} catch (Throwable $exception) {
    error_log('AutoLube setup error: ' . $exception->getMessage());
    $error = 'Database belum siap. Periksa konfigurasi MySQL dan impor database/schema.sql.';
}

if ($setupAvailable && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Sesi formulir berakhir. Muat ulang halaman dan coba lagi.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,64}$/', $username)) {
        $error = 'Username harus 3â€“64 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif (!hasMinimumPasswordLength($password)) {
        $error = 'Password minimal 5 karakter.';
    } elseif ($password !== $confirmation) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        try {
            $insert = $database->prepare('INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, \'master\')');
            $insert->execute([':username' => $username, ':password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $database->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'master';
            $_SESSION['mechanic_id'] = null;
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $exception) {
            error_log('AutoLube first user creation failed: ' . $exception->getMessage());
            $error = 'Akun gagal dibuat. Jika akun sudah ada, silakan login.';
        }
    }
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Buat akun pertama | Sertifikasi Mekanik</title>
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(ellipse at 50% 0%,#193451 0,transparent 55%),#08111e;color:#edf5ff;font:16px system-ui,sans-serif}.panel{width:min(440px,100%);padding:32px;border:1px solid #22364c;border-radius:20px;background:#101e30;box-shadow:0 20px 60px #02081266}h1{margin:0 0 8px;font-size:1.7rem}p{color:#9aadc3;line-height:1.6}label{display:block;margin:18px 0 7px;color:#c7d7e8;font-size:.9rem}input{width:100%;padding:12px 14px;border:1px solid #344b64;border-radius:10px;background:#091523;color:#edf5ff;font:inherit}button{width:100%;margin-top:24px;padding:13px;border:0;border-radius:10px;background:#1685bd;color:white;font:inherit;font-weight:700;cursor:pointer}.error{padding:11px 13px;border-radius:10px;background:#3a2028;color:#ffb6c0;font-size:.9rem}a{color:#76ceff}
  </style>
</head>
<body><main class="panel">
  <h1>Buat akun pertama</h1><p>Akun ini akan digunakan untuk masuk ke dashboard sertifikasi mekanik. Password disimpan sebagai hash di MySQL.</p>
  <?php if ($error !== ''): ?><div class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <?php if ($setupAvailable): ?>
  <form method="post" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <label for="username">Username</label><input id="username" name="username" required minlength="3" maxlength="64" pattern="[A-Za-z0-9_.-]+" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username">
    <label for="password">Password (minimal 5 karakter)</label><input id="password" type="password" name="password" required minlength="5" autocomplete="new-password">
    <label for="password_confirmation">Ulangi password</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="5" autocomplete="new-password">
    <button type="submit">Buat akun dan masuk</button>
  </form>
  <?php elseif ($error === ''): ?><p>Akun pertama sudah dibuat. <a href="login.php">Silakan login</a>.</p><?php endif; ?>
</main></body></html>

