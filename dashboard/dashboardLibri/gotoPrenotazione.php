<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Redirects from copy ID to booking detail page
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookingService;

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
    redirect('../dashboardUtenti/dettaglioPrenotazione.php?id=' . $bookingId);
} else {
    flash('error', 'Nessuna prenotazione trovata per questa copia.');
    redirect('dashboardLibri.php');
}
