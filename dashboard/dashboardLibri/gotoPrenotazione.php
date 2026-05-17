<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Redirects from copy ID to user detail page showing the booking
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookingService;
use Alexandria\Services\UserService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

if (!isset($_GET['id'])) {
    redirect('dashboardLibri.php');
}

$bookingService = new BookingService($pdo);
$bookingId = $bookingService->getByCopyId((int) $_GET['id']);

if ($bookingId) {
    $booking = $bookingService->getById($bookingId);
    if ($booking && !empty($booking['Email'])) {
        $userService = new UserService($pdo);
        $user = $userService->getByEmail($booking['Email']);
        if ($user) {
            redirect('../dashboardUtenti/dettaglioUtente.php?id=' . $user['id']);
        }
    }
    flash('error', 'Nessuna prenotazione trovata per questa copia.');
    redirect('dashboardLibri.php');
} else {
    flash('error', 'Nessuna prenotazione trovata per questa copia.');
    redirect('dashboardLibri.php');
}
