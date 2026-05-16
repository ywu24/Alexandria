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
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alexandria - Gestione Prenotazioni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/pages/user-detail.css">
    <link rel="stylesheet" href="../css/pages/footer.css">
    <link rel="stylesheet" href="../css/utilities.css">
</head>
<body class="user-detail">
    <div id="nav-placeholder"><?php $root="../"; require_once("../nav/nav.php"); ?></div>

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

    <script src="../js/dettaglioPrenotazione.js"></script>
    <script src="prenotazioneAdmin.js"></script>
    <?php require_once("../nav/footer.php"); ?>
</body>
</html>