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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <title>Alexandria's Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/pages/libro.css">
    <link rel="stylesheet" href="../css/pages/footer.css">
    <link rel="stylesheet" href="../css/utilities.css">
    <script src="./libro.js" defer></script>
</head>

<body class="libro-detail">
    <div class="safe-area spaced-column">

        <div id="nav-placeholder">
            <?php require_once('../nav/nav.php'); ?>
        </div>

        <?php render_messages(); ?>

        <main>
            <div class="container">
                <div class="left-column">
                    <img src="../img/books/<?php echo e($book['Copertina']); ?>" alt="Copertina Libro">
                </div>
                <div class="right-column">
                    <div class="info">
                        <div class="info-title">
                            <h1 class="trunctitle"><?php echo e($book['Nome']); ?></h1>
                            <h6>ISBN: <?php echo e($book['ISBN']); ?></h6>
                        </div>
                        <div class="info-release">
                            <h5><?php echo e($book['Autore']); ?></h5>
                            <span> | </span>
                            <h5><?php echo e($book['CasaEditrice']); ?></h5>
                            <div class="status-container">
                                <h5>Stato:</h5>
                                <h5 class="status" style="color: <?php echo $availability['color']; ?>">
                                    <?php echo $availability['disponibilita']; ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="rating-display">
                        <?php if ($ratingStats['totale'] > 0): ?>
                            <span class="rating-stars">
                                <?php echo render_stars($ratingStats['media']); ?>
                            </span>
                            <span class="media-voto"><?php echo $ratingStats['media']; ?> / 5</span>
                            <small class="text-muted">(<?php echo $ratingStats['totale']; ?> recensioni)</small>
                        <?php else: ?>
                            <span class="text-muted">Ancora nessuna recensione</span>
                        <?php endif; ?>
                    </div>
                    <div class="desc">
                        <p class="truncdesc"><?php echo e($book['Descrizione']); ?></p>
                    </div>
                    <?php if ($canShowButton): ?>
                        <div class="div-button">
                            <button class="prenotazione open-button" name="prenota">PRENOTA</button>
                        </div>
                    <?php elseif ($canBook && !$canShowButton): ?>
                        <div class="div-button">
                            <button class="prenotazioneDisabled">PRENOTA</button>
                        </div>
                        <h6 class="nmax">Numero massimo di prenotazioni raggiunto</h6>
                    <?php endif; ?>
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
                <?php if ($isPremium): ?>
                    <h4>Inserire quantità di copie da prenotare</h4>
                <?php elseif ($isStandard): ?>
                    <h4>Sei sicuro di voler confermare la prenotazione di:</h4>
                <?php endif; ?>

                <div class="prenotation-info">
                    <span><?php echo e($book['Nome']); ?></span>
                    <span><?php echo e($book['ISBN']); ?></span>
                    <span>Durata prenotazione: <?php echo $giorniPrenotazione; ?> giorni</span>
                    <form class="form" action="libro.php?id=<?php echo $book_id; ?>" method="post">
                        <?php if ($isPremium): ?>
                            <input name="sliderino" type="range" value="1" min="1" max="<?php echo $availableCopies; ?>" id="slider">
                            <center><span id="sliderValue">1</span></center>
                        <?php endif; ?>
                        <button class="button close-button" type="button">no</button>
                        <button class="button" type="submit" name="prenota">si</button>
                    </form>
                </div>
            </dialog>
        <?php endif; ?>

    </div>
    <?php require_once('../nav/footer.php'); ?>
</body>

</html>
