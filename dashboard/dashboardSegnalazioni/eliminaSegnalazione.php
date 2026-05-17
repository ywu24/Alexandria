<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Handles report deletion
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\ReportService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

if (!isset($_GET['id'])) {
    flash('error', 'Parametro id mancante nella richiesta');
    redirect('dashboardSegnalazioni.php');
}

$reportService = new ReportService($pdo);
$id = (int) $_GET['id'];

try {
    if ($reportService->delete($id)) {
        flash('success', 'Segnalazione eliminata con successo');
        redirect('dashboardSegnalazioni.php');
    } else {
        flash('error', 'Errore durante l\'eliminazione della segnalazione');
        redirect('dettaglio.php?id=' . $id);
    }
} catch (PDOException $e) {
    flash('error', 'Errore durante l\'esecuzione della query: ' . $e->getMessage());
    redirect('dettaglio.php?id=' . $id);
}
