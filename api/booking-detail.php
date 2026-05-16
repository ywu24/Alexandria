<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint for booking detail JSON (admin/librarian view)
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookingService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);

if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID prenotazione mancante']);
    exit;
}

$bookingService = new BookingService($pdo);
$id = (int) $_GET['id'];

try {
    $booking = $bookingService->getById($id);

    if (!$booking) {
        http_response_code(404);
        echo json_encode(['error' => 'Prenotazione non trovata']);
        exit;
    }

    $status = $bookingService->calculateStatus($booking);

    $to_send = [
        'Copertina' => $booking['Copertina'],
        'Email' => $booking['Email'],
        'Color' => $status['color'],
        'Fine' => $status['endDate'],
        'Inizio' => $status['startDate'],
        'Stato' => $status['status'],
    ];

    echo json_encode($to_send);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
