<?php
declare(strict_types=1);

function databaseConnection(): PDO
{
    $localConfigPath = __DIR__ . '/database.local.php';
    $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];
    $host = getenv('CERTIFICATION_DB_HOST') ?: ($localConfig['host'] ?? '127.0.0.1');
    $database = getenv('CERTIFICATION_DB_NAME') ?: ($localConfig['database'] ?? 'certification_db');
    $username = getenv('CERTIFICATION_DB_USER') ?: ($localConfig['username'] ?? 'root');
    $password = getenv('CERTIFICATION_DB_PASSWORD') ?: ($localConfig['password'] ?? '');
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
