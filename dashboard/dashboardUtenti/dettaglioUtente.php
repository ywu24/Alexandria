<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file User detail page with active bookings
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookingService;
use Alexandria\Services\UserService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../lista/lista.php');
}

$userService = new UserService($pdo);
$bookingService = new BookingService($pdo);

$id_utente_get = $_GET['id'] ?? null;
if (!$id_utente_get) {
    redirect('dashboardUtenti.php');
}

$user = $userService->getById((int) $id_utente_get);
if (!$user) {
    redirect('dashboardUtenti.php?errore=3');
}

$propic = !empty($user['propic']) ? $user['propic'] : 'userDashFavicon.svg';

// Fetch active bookings
$bookings = $bookingService->getAll(null, null, null, 'idPrenotazione DESC', 100, 0);
// Filter by user email and active only
$activeBookings = array_filter($bookings, function ($b) use ($user) {
    return $b['email'] === $user['Email'] && empty($b['FinePrestito']);
});

$root = '../..';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Alexandria - Gestione Utente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/user-detail.css">
    <link rel="stylesheet" href="../../css/pages/footer.css">
    <link rel="stylesheet" href="../../css/utilities.css">
    <script src="dettaglioUtente.js"></script>
    <script src="../../js/dettaglioPrenotazione.js"></script>
</head>

<body class="user-detail">
    <div id="nav-placeholder"><?php require_once("../../nav/nav.php"); ?></div>

    <div class="container mt-5">
        <!-- HEADER UTENTE -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-10">
                <div class="user-summary shadow-sm d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="../../img/users/<?php echo e($propic); ?>" class="user-avatar mr-3">
                        <div>
                            <h2 class="h5 font-weight-bold mb-0"><?php echo e($user['Nome'] . ' ' . $user['Cognome']); ?></h2>
                            <p class="small text-muted mb-0"><?php echo e($user['Email']); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="small text-muted">Punteggio</span>
                        <div class="user-score"><?php echo (int) $user['punteggio']; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="messages">
            <?php if (isset($_GET['eliminato'])) echo "<p class='successo'>Prenotazione eliminata con successo</p>"; ?>
        </div>

        <div id="bookings-container">
            <?php foreach ($activeBookings as $row):
                $status = $bookingService->calculateStatus($row);
                $idPreno = $row['idPrenotazione'];
                $inizio = $row['InizioPrestito'] ?? $row['InizioPrenotazione'];
                $fine = $row['FinePrestito'] ?? ($row['InizioPrestito'] ? $row['FineAttesa'] : $row['FinePrenotazione']);
            ?>
                <div class="book-container shadow-sm" id="container-prenotazione-<?php echo $idPreno; ?>">
                    <div class="row no-gutters">
                        <div class="col-md-6 left-panel">
                            <div class="media media-book">
                                <img src="../../img/books/<?php echo e($row['Copertina']); ?>" class="mr-4 shadow-sm book-cover">
                                <div class="media-body">
                                    <h3 class="h5 font-weight-bold book-title"><?php echo e($row['Nome']); ?></h3>
                                    <p class="text-muted mb-1 small"><?php echo e($row['Autore']); ?></p>

                                    <p class="small mb-2 status-badge <?php echo $status['color']; ?>">
                                        ● <?php echo strtoupper($status['status']); ?>
                                    </p>

                                    <div class="small text-muted">
                                        <span>Dal: <?php echo format_date($inizio); ?></span><br>
                                        <span>Al: <?php echo format_date($fine); ?></span>
                                    </div>
                                    <button class="btn btn-dark btn-sm mt-3" onclick="apriDettaglioPrenotazione(<?php echo $idPreno; ?>)">Gestisci</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 right-panel" id="dettaglio-content-<?php echo $idPreno; ?>"></div>

                        <div class="col-md-6 right-panel text-center text-muted" id="placeholder-<?php echo $idPreno; ?>">
                            <small>Seleziona "Gestisci" per azioni</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center my-5">
            <hr>
            <div id="terminate-container">
                <button id="load-terminated" class="btn btn-outline-primary btn-terminated"
                    data-id-utente="<?php echo (int) $id_utente_get; ?>">
                    Mostra prenotazioni terminate
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once("../../nav/footer.php"); ?>
</body>

</html>
