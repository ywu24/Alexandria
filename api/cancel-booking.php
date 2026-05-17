<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint for user booking cancellation
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\BookingService;
use Alexandria\Services\AuthService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);
$bookingService = new BookingService($pdo);

if (!$authService->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Accesso negato']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Richiesta non valida']);
    exit;
}

$id = (int) $_POST['id'];
$email = $authService->getCurrentUserEmail();

$booking = $bookingService->getById($id);
if (!$booking) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Prenotazione non trovata']);
    exit;
}

if (!$authService->isAdmin() && !$authService->isLibrarian() && $booking['Email'] !== $email) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accesso negato']);
    exit;
}

try {
    if ($bookingService->cancel($id)) {
        echo json_encode(['success' => true, 'message' => 'Prenotazione eliminata con successo!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore durante l\'eliminazione']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
