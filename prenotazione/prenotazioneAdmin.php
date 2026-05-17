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

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../index.php');
}

$root = '..';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <?php render_head('Alexandria - Gestione Prenotazioni',
        ['css/pages/user-detail.css', 'css/pages/footer.css'],
        ['js/reservationDetail.js', 'js/adminReservation.js'],
        '..'
    ); ?>
</head>
<body class="user-detail">
    <div id="nav-placeholder"><?php require_once("../nav/nav.php"); ?></div>

<div class="container mt-5">
    <h1 class="text-center mb-5 font-weight-extra-bold">Prenotazioni</h1>

    <!-- BARRA FILTRI -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <form id="filtriForm" class="form-inline justify-content-center p-3 bg-white shadow-sm filter-bar">
                <label class="mr-2 small text-muted">Stato:</label>
                <select name="filtro_stato" class="form-control form-control-sm border-0 font-weight-bold filter-select">
                    <option value="tutti">Tutti gli stati</option>
                    <option value="Prenotato">Prenotati</option>
                    <option value="In Prestito">In Prestito</option>
                    <option value="In Ritardo">In Ritardo</option>
                    <option value="Terminato">Terminati</option>
                </select>
                
                <div class="filter-divider mx-3"></div>
                
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