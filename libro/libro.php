<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Book detail page with reviews, ratings, and booking
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\BookService;
use Alexandria\Services\ReviewService;
use Alexandria\Services\BookingService;
use Alexandria\Services\NotificationService;

$bookService = new BookService($pdo);
$reviewService = new ReviewService($pdo);
$bookingService = new BookingService($pdo);
$notificationService = new NotificationService();

$root = '..';

// Validate book ID
if (!isset($_GET['id'])) {
    redirect('../lista/lista.php?errore=2');
}
$book_id = (int) $_GET['id'];

if (!$bookService->exists($book_id)) {
    redirect('../lista/lista.php?errore=2');
}

// Fetch book data
$book = $bookService->getById($book_id);
$ratingStats = $bookService->getRatingStats($book_id);
$availability = $bookService->getAvailability($book_id);
$availableCopies = $bookService->getAvailableCopies($book_id);

// User session data
$email = $_SESSION['email'] ?? null;
$utenza = $_SESSION['utenza'] ?? null;
$isLoggedIn = $email !== null;
$isPremium = $isLoggedIn && $utenza == 3;
$isStandard = $isLoggedIn && $utenza == 4;
$canBook = $isLoggedIn && ($isPremium || $isStandard) && $availableCopies >= 1;

// Count active bookings for standard users
$numeroPrenotazioni = 0;
if ($isStandard) {
    $numeroPrenotazioni = $bookingService->countActiveBookings($email);
}

$giorniPrenotazione = 30;

// Handle POST booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prenota']) && $canBook) {
    $success = false;
    $error_msg = '';

    if ($isPremium) {
        $nPrenotazioni = (int) ($_POST['sliderino'] ?? 1);
        try {
            $result = $bookingService->createMultipleBookings($email, $book_id, $nPrenotazioni, $giorniPrenotazione);
            if ($result['success']) {
                $notificationService->notifyBooking(
                    $email,
                    'Premium',
                    $result['isbn'] ?? '',
                    date('d-m-Y'),
                    date('d-m-Y', strtotime('+' . $giorniPrenotazione . ' days')),
                    $result['count']
                );
                $success = true;
            } else {
                $error_msg = $result['message'];
            }
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }
    } elseif ($isStandard) {
        if ($numeroPrenotazioni < 3) {
            try {
                $result = $bookingService->createBooking($email, $book_id, $giorniPrenotazione);
                if ($result['success']) {
                    $notificationService->notifyBooking(
                        $email,
                        'Standard',
                        $result['isbn'] ?? '',
                        date('d-m-Y'),
                        date('d-m-Y', strtotime('+' . $giorniPrenotazione . ' days')),
                        null,
                        $result['copyId'] ?? null
                    );
                    $success = true;
                } else {
                    $error_msg = $result['message'];
                }
            } catch (Exception $e) {
                $error_msg = $e->getMessage();
            }
        } else {
            $error_msg = 'Numero massimo di prenotazioni raggiunto';
        }
    }

    if ($success) {
        flash('success', 'Prenotazione effettuata con successo');
        redirect('libro.php?id=' . $book_id);
    } else {
        flash('error', $error_msg);
        redirect('libro.php?id=' . $book_id);
    }
}

// Determine booking button state
$canShowButton = false;
if ($canBook) {
    if ($isPremium) {
        $canShowButton = true;
    } else {
        $canShowButton = $numeroPrenotazioni < 3;
    }
}

// Fetch initial reviews
$reviews = $reviewService->getForBook($book_id, 5, 0);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head("Alexandria's Library", ['css/pages/libro.css', 'css/pages/footer.css'], ['js/book.js'], '..'); ?>
</head>

<body class="libro-detail">
    <div class="safe-area spaced-column">

        <div id="nav-placeholder">
            <?php require_once('../nav/nav.php'); ?>
        </div>

        <div id="messages">
            <?php render_messages(); ?>
        </div>

        <main>
            <div class="container">
                <div class="card shadow-sm">
                    <div class="card-body p-lg-5">
                        <div class="row g-4">
                            <div class="col-md-4 col-lg-3">
                                <div class="left-column">
                                    <img src="../img/books/<?php echo e($book['Copertina']); ?>" alt="Copertina Libro"
                                        class="img-fluid rounded shadow">
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-9">
                                <div class="right-column">
                                    <div class="info mb-4">
                                        <div class="info-title mb-3">
                                            <h1 class="trunctitle fw-bold"><?php echo e($book['Nome']); ?></h1>
                                            <h6 class="text-muted">ISBN: <?php echo e($book['ISBN']); ?></h6>
                                        </div>
                                        <div class="info-release d-flex align-items-center gap-2 flex-wrap">
                                            <h5 class="mb-0"><?php echo e($book['Autore']); ?></h5>
                                            <span class="text-muted">|</span>
                                            <h5 class="mb-0"><?php echo e($book['CasaEditrice']); ?></h5>
                                            <div class="status-container ms-auto d-flex align-items-center gap-2">
                                                <span class="text-muted small fw-bold">Stato:</span>
                                                <span
                                                    class="badge <?php echo $availability['disponibilita'] === 'Disponibile' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $availability['disponibilita']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rating-display mb-4 py-3 border-top border-bottom">
                                        <?php if ($ratingStats['totale'] > 0): ?>
                                            <span class="rating-stars">
                                                <?php echo render_stars($ratingStats['media']); ?>
                                            </span>
                                            <span class="media-voto fw-bold ms-2"><?php echo $ratingStats['media']; ?> /
                                                5</span>
                                            <small class="text-muted ms-1">(<?php echo $ratingStats['totale']; ?>
                                                recensioni)</small>
                                        <?php else: ?>
                                            <span class="text-muted">Ancora nessuna recensione</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="desc mb-4">
                                        <p class="truncdesc text-break text-secondary">
                                            <?php echo e($book['Descrizione']); ?>
                                        </p>
                                    </div>
                                    <?php if ($canShowButton): ?>
                                        <div class="div-button">
                                            <button class="btn btn-primary btn-lg w-100 open-button fw-bold"
                                                name="prenota">PRENOTA</button>
                                        </div>
                                    <?php elseif ($canBook && !$canShowButton): ?>
                                        <div class="div-button">
                                            <button class="btn btn-secondary btn-lg w-100" disabled>PRENOTA</button>
                                        </div>
                                        <h6 class="nmax text-center text-danger mt-2 small">Numero massimo di prenotazioni
                                            raggiunto</h6>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-5">

            <section class="container-fluid mb-5 fluid">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h2 class="fw-bold">Recensioni degli utenti</h2>
                    </div>
                    <div class="col-lg-12">
                        <?php if (count($reviews) > 0): ?>
                            <div class="review-feed" id="reviews-container" data-book-id="<?php echo $book_id; ?>">
                                <?php foreach ($reviews as $row): ?>
                                    <?php render_review_card($row, '../'); ?>
                                <?php endforeach; ?>
                                <div id="scroll-trigger" class="text-center py-3">
                                    <div class="spinner-border text-primary d-none" role="status">
                                        <span class="visually-hidden">Caricamento in corso...</span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-reviews-container text-center py-5 border rounded bg-light">
                                <h5 class="text-muted">Non ci sono ancora recensioni.</h5>
                                <p>Sii il primo a condividere la tua opinione!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>

        <?php if ($canShowButton): ?>
            <dialog class="pop-up" id="modal">
                <div class="card-body text-center">
                    <h4 class="mb-4 fw-bold">
                        <?php if ($isPremium): ?>
                            Inserire quantità di copie da prenotare
                        <?php elseif ($isStandard): ?>
                            Confermare la prenotazione di:
                        <?php endif; ?>
                    </h4>

                    <div class="prenotation-info mb-4">
                        <div class="mb-2 fw-bold text-primary"><?php echo e($book['Nome']); ?></div>
                        <div class="text-muted small mb-2"><?php echo e($book['ISBN']); ?></div>
                        <div class="badge bg-light text-dark border">Durata prenotazione:
                            <?php echo $giorniPrenotazione; ?> giorni
                        </div>

                        <form class="mt-4" action="libro.php?id=<?php echo $book_id; ?>" method="post">
                            <?php if ($isPremium): ?>
                                <div class="px-4 mb-3">
                                    <input name="sliderino" type="range" class="form-range" value="1" min="1"
                                        max="<?php echo $availableCopies; ?>" id="slider">
                                    <div class="mt-2">
                                        <span id="sliderValue" class="h4 fw-bold text-primary">1</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <button class="btn btn-outline-secondary px-4 close-button" type="button">No</button>
                                <button class="btn btn-primary px-4" type="submit" name="prenota">Sì</button>
                            </div>
                        </form>
                    </div>
                </div>
            </dialog>
        <?php endif; ?>

    </div>
    <?php require_once('../nav/footer.php'); ?>
</body>

</html>