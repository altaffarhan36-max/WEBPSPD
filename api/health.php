<?php
declare(strict_types=1);

// TAG ENDPOINT HTTP: Memeriksa apakah endpoint PHP dapat terhubung ke MySQL.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

try {
    require_once __DIR__ . '/database.php';
    databaseConnection()->query('SELECT 1');
    echo json_encode(['ok' => true]);
} catch (Throwable $error) {
    error_log('AutoLube health check failed: ' . $error->getMessage());
    http_response_code(503);
    echo json_encode(['ok' => false]);
}
