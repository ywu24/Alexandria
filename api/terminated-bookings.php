<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint for terminated bookings with review status
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookingService;
use Alexandria\Services\UserService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);

if (!$authService->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Accesso negato']);
    exit;
}

if (!isset($_POST['idUtente']) && !isset($_POST['email'])) {
    echo json_encode([]);
    exit;
}

$userService = new UserService($pdo);
$bookingService = new BookingService($pdo);

$email = '';
if (isset($_POST['idUtente'])) {
    $user = $userService->getById((int) $_POST['idUtente']);
    if ($user) {
        $email = $user['Email'];
    }
} else {
    $email = $_POST['email'];
}

if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    if ($authService->getCurrentUserEmail() !== $email) {
        http_response_code(403);
        echo json_encode(["error" => "Accesso Negato"]);
        exit;
    }
}

try {
    $terminate = $bookingService->getTerminatedByEmail($email);
    echo json_encode($terminate);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
