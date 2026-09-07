<?php
// Health Check Endpoint for Render / Load Balancers
require_once __DIR__ . '/db.php';

header("Content-Type: application/json; charset=UTF-8");

$dbStatus = 'connected';
$driver = 'unknown';

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $check = $pdo->query("SELECT 1")->fetch();
} catch (\Exception $e) {
    $dbStatus = 'disconnected: ' . $e->getMessage();
    http_response_code(503);
    echo json_encode([
        "status" => "unhealthy",
        "database" => $dbStatus,
        "timestamp" => date('Y-m-d H:i:s'),
    ]);
    exit();
}

http_response_code(200);
echo json_encode([
    "status" => "healthy",
    "service" => "CaffeBook Backend API",
    "database" => $dbStatus,
    "driver" => $driver,
    "environment" => getenv('APP_ENV') ?: 'production',
    "timestamp" => date('Y-m-d H:i:s'),
]);
