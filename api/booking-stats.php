<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\StatsService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Calcolo statistiche prenotazioni tramite StatsService
$statsService = new StatsService($pdo);
try {
    $stats = $statsService->getGlobalBookingStats();
    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
