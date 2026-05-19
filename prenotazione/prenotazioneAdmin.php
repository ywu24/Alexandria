<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Bookings
 * @file Booking admin management page - displays and filters bookings via API
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\StatsService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../index.php');
}

$root = '..';

// Calcolo statistiche prenotazioni tramite StatsService
$statsService = new StatsService($pdo);
$stats = $statsService->getGlobalBookingStats();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <?php render_head('Alexandria - Gestione Prenotazioni',
        ['css/pages/user-detail.css', 'css/pages/footer.css'],
        ['js/modalUtils.js', 'js/reservationDetail.js', 'js/adminReservation.js'],
        '..'
    ); ?>
</head>
<body class="user-detail">
    <div id="nav-placeholder"><?php require_once("../nav/nav.php"); ?></div>

<div class="container mt-5">
    <div class="mt-5 text-center">
        <h1 class="text-center mb-5 font-weight-extra-bold">Prenotazioni</h1>
        <p class="lead text-muted">Gestione delle prenotazioni, monitoraggio dei prestiti e scadenze</p>
    </div>

    <!-- SEZIONE STATISTICHE -->
    <div class="row mb-5 justify-content-center">
        <div class="col-6 col-md-3 col-lg-2 mb-3">
            <div class="card text-center p-3 shadow-sm">
                <div class="text-muted small font-weight-bold text-uppercase">Totale</div>
                <div class="h3 stats mb-0 font-weight-bold" id="statsTotale"><?php echo $stats['totale'] ?? 0; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-3">
            <div class="card text-center p-3 shadow-sm">
                <div class="text-success small font-weight-bold text-uppercase">Prenotati</div>
                <div class="h3 stats mb-0 font-weight-bold" id="statsPrenotati"><?php echo $stats['prenotati'] ?? 0; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-3">
            <div class="card text-center p-3 shadow-sm">
                <div class="text-warning small font-weight-bold text-uppercase">In Prestito</div>
                <div class="h3 stats mb-0 font-weight-bold" id="statsInPrestito"><?php echo $stats['in_prestito'] ?? 0; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-3">
            <div class="card text-center p-3 shadow-sm">
                <div class="text-danger small font-weight-bold text-uppercase">In Ritardo</div>
                <div class="h3 stats mb-0 font-weight-bold" id="statsInRitardo"><?php echo $stats['in_ritardo'] ?? 0; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-3">
            <div class="card text-center p-3 shadow-sm">
                <div class="text-muted small font-weight-bold text-uppercase">Terminati</div>
                <div class="h3 stats mb-0 font-weight-bold" id="statsTerminati"><?php echo $stats['terminati'] ?? 0; ?></div>
            </div>
        </div>
    </div>

    <!-- BARRA FILTRI -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <form id="filtriForm" class="form-inline justify-content-center p-3 shadow-sm filter-bar">
                <label class="mr-2 small text-muted">Stato:</label>
                <select name="filtro_stato" class="form-control form-control-sm border-0 font-weight-bold filter-select">
                    <option value="tutti">Tutti gli stati</option>
                    <option value="Prenotato">Prenotati</option>
                    <option value="In Prestito">In Prestito</option>
                    <option value="In Ritardo">In Ritardo</option>
                    <option value="Terminato">Terminati</option>
                </select>
                
                <label class="mr-2 small text-muted">Dal:</label>
                <input type="date" name="data_inizio" class="form-control form-control-sm border-0">
                
                <label class="mx-2 small text-muted">Al:</label>
                <input type="date" name="data_fine" class="form-control form-control-sm border-0">
                
                <button type="reset" class="btn btn-link btn-sm text-secondary ml-3">Reset</button>
            </form>
        </div>
    </div>

    <!-- CONTENITORE PRENOTAZIONI (popolato via api/bookings.php) -->
    <div id="bookings-container">
        <div class="text-center py-5 text-muted">
            <small>Caricamento...</small>
        </div>
    </div>

    <div class="text-center my-5">
        <button id="caricaAltro" class="btn btn-outline-primary">Carica Altro...</button>
    </div>
</div>

    <?php require_once("../nav/footer.php"); ?>
</body>
</html>
