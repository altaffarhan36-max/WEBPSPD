<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function hasMinimumPasswordLength(string $password, int $minimum = 5): bool
{
    $length = function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password);
    return $length >= $minimum;
}

function requireAdmin(): void
{
    requireLogin();
    if (!in_array(($_SESSION['role'] ?? ''), ['master', 'admin'], true)) {
        http_response_code(403);
        exit('Akses hanya untuk administrator.');
    }
}

function requireApiLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Login diperlukan']);
        exit;
    }
}
