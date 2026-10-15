<?php
declare(strict_types=1);

// TAG ENDPOINT HTTP: Memvalidasi dan menyimpan pembacaan sensor ke MySQL.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$unitId = $payload['unit_id'] ?? '';
$distance = $payload['distance_cm'] ?? null;
$fillPercent = $payload['fill_percent'] ?? null;
$allowedUnits = ['DT01', 'DT02', 'DT03', 'DT04', 'DT05', 'DT06', 'DT07', 'DT08', 'DT09', 'DT49'];

if (!in_array($unitId, $allowedUnits, true) ||
    !is_numeric($distance) || !is_finite((float) $distance) ||
    (float) $distance < 0 || (float) $distance > 500 ||
    !is_numeric($fillPercent) || !is_finite((float) $fillPercent) ||
    (float) $fillPercent < 0 || (float) $fillPercent > 100) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid sensor values']);
    exit;
}

try {
    require_once __DIR__ . '/database.php';
    $database = databaseConnection();
    $statement = $database->prepare(
        'INSERT INTO sensor_readings (unit_id, distance_cm, fill_percent, measured_at)
         VALUES (:unit_id, :distance_cm, :fill_percent, UTC_TIMESTAMP(3))'
    );
    $statement->execute([
        ':unit_id' => $unitId,
        ':distance_cm' => (float) $distance,
        ':fill_percent' => (float) $fillPercent,
    ]);

    http_response_code(201);
    echo json_encode(['ok' => true, 'id' => (int) $database->lastInsertId()]);
} catch (Throwable $error) {
    error_log('AutoLube database error: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database write failed']);
}
