<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Report detail page - displays a single report with image and delete action
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\ReportService;

$authService = new AuthService($pdo);

if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

$reportService = new ReportService($pdo);

$idSegnalazione = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$report = null;
$error = null;

if ($idSegnalazione <= 0) {
    $error = 'ID segnalazione non valido.';
} else {
    $report = $reportService->getById($idSegnalazione);
    if (!$report) {
        $error = 'Segnalazione non trovata.';
    }
}

$root = '../..';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php render_head('Dettaglio Segnalazione - Alexandria\'s Library',
        ['css/pages/forms.css', 'css/pages/footer.css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'],
        [],
        '../..'
    ); ?>
</head>
<body class="bg-light form-page">
   <div id="nav-placeholder">
    <?php require_once('../../nav/nav.php'); ?>
   </div>

<div class="container py-5">

<?php if ($error): ?>
    <div class="alert alert-warning shadow-sm"><?php echo e($error); ?></div>
<?php else: ?>
    <?php
    $imgHtml = '';
    if (!empty($report['imgSegn'])) {
        $imgHtml = "
        <div class='p-3 bg-white border-top text-center'>
            <p class='text-muted small mb-2'>Allegato:</p>
            <img src='../../img/segnalazioni/" . e($report['imgSegn']) . "' class='img-fluid rounded shadow-sm' alt='Immagine segnalazione' style='max-height: 400px;'>
        </div>";
    }
    ?>

    <!-- Header Uniformato -->
    <div class="text-center mb-5">
        <h1 class="display-4 font-weight-bold">Dettaglio Segnalazione</h1>
        <p class="lead text-muted">Gestione pratica n° <?php echo (int) $report['idSegnalazione']; ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <!-- Header Nero coerente -->
                <div class="card-header bg-dark text-white p-3 font-weight-bold d-flex justify-content-between align-items-center">
                    <span>Mittente: <?php echo e($report['userEmail']); ?></span>
                    <i class="fas fa-info-circle"></i>
                </div>

                <div class="card-body p-4">
                    <h4 class="text-primary font-weight-bold mb-3"><?php echo e($report['Oggetto']); ?></h4>
                    <div class="p-3 bg-light rounded text-secondary" style="min-height: 100px;">
                        <?php echo nl2br(e($report['Messaggio'])); ?>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-2">
                            <a href="dashboardSegnalazioni.php" class="btn btn-outline-secondary btn-block shadow-none">
                                <i class="fas fa-arrow-left mr-2"></i> Torna Indietro
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="eliminaSegnalazione.php?id=<?php echo (int) $report['idSegnalazione']; ?>"
                               class="btn btn-danger btn-block shadow-none"
                               onclick="return confirm('Sei sicuro di voler eliminare questa segnalazione?')">
                               <i class="fas fa-trash-alt mr-2"></i> Elimina Pratica
                            </a>
                        </div>
                    </div>
                </div>
                <?php echo $imgHtml; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

</div>
<?php require_once('../../nav/footer.php'); ?>
</body>
</html>
