<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file User bookings page with active loans and history
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\BookingService;

$bookingService = new BookingService($pdo);

$root = '..';

// Access control
$utenza = $_SESSION['utenza'] ?? null;
$email = $_SESSION['email'] ?? null;

if ($utenza == 1 || $utenza == 2) {
    redirect('prenotazioneAdmin.php');
}

if ($email === null) {
    redirect('../index.php');
}

// Fetch user bookings
$bookings = $bookingService->getByUser($email);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head('Le Mie Prenotazioni | Alexandria', ['css/pages/library.css', 'css/pages/footer.css'], ['js/utils.js', 'prenotazione/prenotazione.js'], '..', <<<CSS
        .book-container {
            background: var(--color-surface);
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--color-border);
            overflow: hidden;
        }
        .left-panel {
            border-right: 1px solid var(--color-border);
            padding: 20px;
            display: flex;
        }
        .book-cover {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .status-dot {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .info-meta {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-bottom: 10px;
        }
        .delete-button {
            border: none;
            background: none;
            padding: 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .delete-button:hover {
            transform: scale(1.1);
        }
        .icon-trash {
            width: 22px;
            height: 22px;
            color: var(--color-danger);
            opacity: 0.7;
        }
CSS
    ); ?>
</head>

<body class="prenotazioni-page">

    <div id="nav-placeholder">
        <?php require_once('../nav/nav.php'); ?>
    </div>

    <div id="messages" class="container mt-3">
        <?php render_legacy_messages(); ?>
    </div>

    <div class="container mt-5">
        <div class="text-center mb-5">
            <h1 class="display-4 font-weight-bold">Le Mie Prenotazioni</h1>
            <p class="lead text-muted">Gestisci i tuoi prestiti e visualizza lo storico</p>
        </div>

        <div id="bookings-container">
            <?php
            $numero_prenotazioni = 0;

            foreach ($bookings as $row) {
                $statusInfo = $bookingService->calculateStatus($row);
                $stato = $statusInfo['status'];
                $color_class = $statusInfo['color'];
                $inizio = format_date($statusInfo['startDate']);
                $fine = format_date($statusInfo['endDate']);

                // Skip expired and terminated bookings from the main list
                if ($stato === 'Scaduto' || $stato === 'Terminato') {
                    continue;
                }

                $numero_prenotazioni++;
            ?>
                <div class="book-container shadow-sm">
                    <div class="row no-gutters">
                        <div class="col-md-9 left-panel p-3 d-flex">
                            <img src="../img/books/<?php echo e($row['Copertina']); ?>" class="book-cover" alt="Copertina"
                                 style="width:140px; height:200px; object-fit:cover; border-radius:8px; margin-right:20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h4 class="h4 font-weight-bold mb-1"><?php echo e($row['Nome']); ?></h4>
                                    <?php if ($stato === 'Prenotato'): ?>
                                        <button class="delete-button" data-id="<?php echo $row['idPrenotazione']; ?>" title="Annulla">
                                            <svg class="icon-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <p class="info-meta mb-2" style="font-size: 1.1rem;"><?php echo e($row['Autore']); ?> | <?php echo e($row['CasaEditrice']); ?></p>
                                <p class="status-dot <?php echo $color_class; ?> mb-3">● <?php echo $stato; ?></p>
                                <div class="small text-muted mb-3">
                                    <span><strong>Inizio:</strong> <?php echo $inizio; ?></span><br>
                                    <span><strong>Scadenza:</strong> <?php echo $fine; ?></span><br>
                                    <span class="text-dark"><strong>ISBN:</strong> <?php echo e($row['ISBN']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 bg-light d-flex align-items-start p-3 border-left">
                            <p class="text-muted small mb-0">Prenotazione attiva nel sistema.</p>
                        </div>
                    </div>
                </div>
            <?php
            }

            if ($numero_prenotazioni === 0) {
                echo "<div class='text-center p-5'><h3 class='text-muted'>Nessuna prenotazione attiva al momento</h3></div>";
            }
            ?>
        </div>

        <div class="mt-5 mb-5 border-top pt-4">
            <div id="terminate-container" class="text-center">
                <button id="load-terminated" class="btn btn-outline-secondary px-5" data-id-utente="<?php echo e($email); ?>" style="border-radius: 25px;">
                    Mostra prenotazioni terminate
                </button>
            </div>
        </div>
    </div>

    <?php require_once('../nav/footer.php'); ?>
</body>

</html>
