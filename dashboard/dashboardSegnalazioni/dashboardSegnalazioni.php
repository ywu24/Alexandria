<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Report management dashboard - lists all user reports
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\ReportService;

$authService = new AuthService($pdo);

if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

$reportService = new ReportService($pdo);
$reports = $reportService->getAll(100);

$root = '../..';
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head('Dashboard Segnalazioni - Alexandria\'s Library',
        ['css/pages/dashboard.css', 'css/pages/footer.css', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'],
        [],
        '../..'
    ); ?>
    <link rel="icon" type="image/svg+xml" href="../../img/segnDashFavicon.svg">
</head>

<body class="bg-light reports-dashboard">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>

    <div class="container py-5">
        <!-- Titolo Centrato Uniformato -->
        <div class="mb-5 text-center">
            <h1 class="text-center mb-5 font-weight-extra-bold">Segnalazioni Utenti</h1>
            <p class="lead text-muted">Gestione e monitoraggio delle problematiche riscontrate dai lettori</p>
        </div>

        <!-- Messaggi Flash -->
        <div id="messages">
            <?php render_messages(); ?>
        </div>

        <div class="row">
            <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $row): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-dark text-white font-weight-bold d-flex justify-content-between align-items-center">
                            <span>Segnalazione n° <?php echo (int) $row['idSegnalazione']; ?></span>
                            <i class="fa fa-exclamation-circle text-warning"></i>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3 text-primary font-weight-bold"><?php echo e($row['userEmail']); ?></h6>
                            <p class="card-text text-secondary"><strong>Oggetto:</strong> <?php echo e($row['Oggetto']); ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <a href="dettaglio.php?id=<?php echo (int) $row['idSegnalazione']; ?>" class="btn btn-outline-primary btn-block shadow-none">Visualizza Dettagli</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted font-weight-light">Nessuna segnalazione trovata.</h4>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php require_once('../../nav/footer.php'); ?>
</body>
</html>
