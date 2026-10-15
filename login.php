<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/api/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: modules.php');
    exit;
}

$error = '';
$username = '';
try {
    $database = databaseConnection();
    if ((int) $database->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        header('Location: setup.php');
        exit;
    }
} catch (Throwable $exception) {
    error_log('AutoLube login database error: ' . $exception->getMessage());
    $error = 'Tidak dapat terhubung ke database. Periksa konfigurasi MySQL.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Sesi formulir berakhir. Muat ulang halaman dan coba lagi.';
    } else {
        $query = $database->prepare('SELECT id, username, password_hash, role, mechanic_id FROM users WHERE username = :username LIMIT 1');
        $query->execute([':username' => $username]);
        $user = $query->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['mechanic_id'] = $user['mechanic_id'] === null ? null : (int) $user['mechanic_id'];
            header('Location: modules.php');
            exit;
        }
        $error = 'Username atau password salah.';
    }
}

$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk | PT Star Wagen Indonesia</title>
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;grid-template-columns:1fr 1fr;background:#fff;color:#171717;font:16px Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.welcome{position:relative;display:flex;min-height:100vh;flex-direction:column;justify-content:center;padding:8.3%;overflow:hidden;background:#191919;color:#f7f7f7}.welcome:after{position:absolute;top:0;right:0;width:6px;height:100%;background:#c8102e;content:""}.company{margin:0 0 17px;color:#aaa;font-size:.8rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase}.welcome h1{max-width:650px;margin:0;font-size:clamp(2.3rem,3.6vw,3.8rem);line-height:1.12;letter-spacing:-.035em}.welcome h1 span{display:block;font-size:clamp(1rem,2.1vw,2rem);line-height:1.25;letter-spacing:-.02em}.welcome .subtitle{margin:8px 0 30px;color:#d01838;font-size:clamp(2.3rem,3.6vw,3.8rem);line-height:1.12;letter-spacing:-.035em;font-weight:700}.features{display:grid;gap:11px;margin:0;padding:0;list-style:none}.features li{font-size:1.1rem;font-weight:650}.features li:before{display:inline-block;width:8px;height:8px;margin:0 13px 1px 0;background:#c8102e;content:""}.welcome-foot{position:absolute;bottom:34px;left:8.3%;color:#777;font-size:.8rem}.stripes{width:84px;height:24px;margin-bottom:28px;border-top:4px solid #c8102e;border-bottom:4px solid #c8102e;position:relative}.stripes:after{position:absolute;top:7px;left:0;width:58px;border-top:4px solid #c8102e;content:""}.login-side{display:grid;place-items:center;padding:36px}.panel{width:min(360px,100%);padding:0}.logo-mark{display:block;width:180px;height:180px;margin:0 auto 22px;object-fit:contain}.panel h2{margin:0 0 4px;font-size:1.55rem;font-weight:550}.panel p{margin:0 0 24px;color:#777;font-size:.87rem;line-height:1.5}label{display:block;margin:18px 0 7px;color:#666;font-size:.84rem}input{width:100%;height:44px;padding:10px 14px;border:1px solid #c7c7c7;border-radius:13px;background:#fff;color:#171717;font:inherit;box-shadow:0 0 0 2px #c8102e1a}input:focus{outline:2px solid #c8102e;outline-offset:1px}.error{margin:0 0 16px;padding:11px 13px;border-left:3px solid #c8102e;background:#f8eaed;color:#9f1026;font-size:.9rem}button{width:100%;height:44px;margin-top:17px;border:0;border-radius:12px;background:#c8102e;color:white;font:inherit;font-weight:700;cursor:pointer}button:hover{background:#a90d27}
    @media(max-width:760px){body{grid-template-columns:1fr}.welcome{min-height:310px;padding:38px 28px 62px}.welcome h1{font-size:2rem}.welcome-foot{bottom:18px;left:28px}.login-side{min-height:calc(100vh - 310px);padding:36px 24px}.welcome:after{top:auto;right:0;bottom:0;width:100%;height:5px}}
  </style>
</head>
<body><section class="welcome"><p class="company">PT Star Wagen Indonesia</p><h1>Product Support People Development</h1><p class="subtitle">Management System</p><ul class="features"><li>Man Power</li><li>Training</li><li>Certification</li></ul><div class="welcome-foot"><div class="stripes" aria-hidden="true"></div>Prototype / Demo · Menggunakan data aplikasi</div></section><section class="login-side"><main class="panel">
  <img class="logo-mark" src="assets/star-wagen-logo.jpeg" alt="Logo Star Wagen Indonesia"><h2>Masuk</h2><p>Gunakan akun Anda untuk mengakses dashboard.</p>
  <?php if ($error !== ''): ?><div class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
  <form method="post" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <label for="username">Username</label><input id="username" name="username" required maxlength="64" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" autofocus placeholder="Masukkan username">
    <label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password">
    <button type="submit">Masuk</button>
  </form>
</main></section></body></html>

