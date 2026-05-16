<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Homepage with featured books, stats, and user account panel
 */

require_once __DIR__ . '/src/bootstrap.php';

use Alexandria\Services\StatsService;
use Alexandria\Services\BookService;
use Alexandria\Services\BookingService;
use Alexandria\Services\UserService;

$statsService = new StatsService($pdo);
$bookService = new BookService($pdo);
$bookingService = new BookingService($pdo);
$userService = new UserService($pdo);

$root = '.';

// Fetch stats for Quick Stats section
$totalBooks = $statsService->getTotalBooks();
$activeUsers = $statsService->getActiveUsers();
$booksBorrowed = $statsService->getBooksBorrowed();
$genresAvailable = $statsService->getGenresAvailable();
$genres = $statsService->getGenresWithCounts();

// Fetch latest books for carousel
$latestBooks = $bookService->getLatest(3);

// User account data
$isLoggedIn = isset($_SESSION['email']);
$utenza = $_SESSION['utenza'] ?? null;
$email = $_SESSION['email'] ?? null;

$user = null;
$recentBookings = [];
$bookingStats = ['totali' => 0, 'inCorso' => 0, 'riconsegnate' => 0, 'prenotati' => 0];

if ($isLoggedIn) {
    $user = $userService->getByEmail($email);
    $isAdmin = ($utenza == 1 || $utenza == 2);
    $bookingStats = $statsService->getUserBookingStats($email, $isAdmin);

    if (!$isAdmin) {
        $recentBookings = $bookingService->getRecentForUser($email, 2);
    }
}

$accountClass = $isLoggedIn ? 'account-status' : 'account-status-2 blur';
$accountHref = $isLoggedIn ? "href='edit_profile/edit_profile.php'" : "href='auth/login.php'";

if ($isLoggedIn && ($utenza == 1 || $utenza == 2)) {
    $accountClass = 'account-status-2';
}

if (!$isLoggedIn) {
    $user = ['Nome' => '', 'Cognome' => '', 'propic' => 'userDashFavicon.svg'];
    $email = 'eg@example.com';
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/pages/home.css">
    <link rel="stylesheet" href="css/pages/footer.css">
    <link rel="stylesheet" href="css/utilities.css">
    <title>HomePage - Alexandria </title>
</head>

<body>
    <script src="homepage.js"></script>

    <div id="nav-placeholder">
        <?php $root = '.'; require_once("nav/nav.php"); ?>
    </div>

    <div id="messages">
        <?php render_legacy_messages(); ?>
    </div>

    <main class="home-main">

        <!-- ============================================
             HERO SECTION
             ============================================ -->
        <section class="hero-section">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1>Benvenutə in Alexandria</h1>
                <p class="hero-subtitle">Il mito rinasce nella più grande collezione al mondo</p>
                <p class="hero-description">Scopri nuovi libri, organizza e gestisci i tuoi prestiti e le tue prenotazioni con facilità. La soluzione moderna per bibliofili e bibliotecari.</p>
                <div class="hero-cta">
                    <a href="lista/lista.php" class="btn btn-primary btn-lg">Sfoglia Libri</a>
                    <?php if (!$isLoggedIn): ?>
                        <a href="auth/login.php" class="btn btn-outline-primary btn-lg">Inizia</a>
                    <?php else: ?>
                        <a href="prenotazione/prenotazione.php" class="btn btn-outline-primary btn-lg">Le Mie Prenotazioni</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ============================================
             FEATURED BOOKS CAROUSEL
             ============================================ -->
        <section class="featured-section">
            <div class="section-header">
                <h2>Libri in Evidenza</h2>
                <p>Scopri le ultime novità della nostra collezione</p>
            </div>

            <div class="book-slider">
                <section class="section slider">
                    <div class="section__entry section__entry--center"></div>
                    <input type="radio" name="slider" id="slide-1" class="slider__radio">
                    <input type="radio" name="slider" id="slide-2" class="slider__radio" checked>
                    <input type="radio" name="slider" id="slide-3" class="slider__radio">
                    <div class="slider__holder">

                        <?php foreach ($latestBooks as $index => $book): ?>
                        <label for="slide-<?php echo $index + 1; ?>" class="slider__item slider__item--<?php echo $index + 1; ?> card">
                            <div class='slider__item-content'>
                                <div class='left-column'>
                                    <a href='libro/libro.php?id=<?php echo $book['id']; ?>'>
                                        <img src='img/books/<?php echo e($book['Copertina']); ?>' alt='' class='cover'>
                                    </a>
                                </div>
                                <a href='libro/libro.php?id=<?php echo $book['id']; ?>'>
                                    <div class='right-column'>
                                        <h3><?php echo e($book['Nome']); ?></h3>
                                        <div class='info-release'>
                                            <span><?php echo e($book['Autore']); ?></span>
                                            <span><?php echo e($book['CasaEditrice']); ?></span>
                                            <span><?php echo e($book['AnnoPubblicazione']); ?></span>
                                            <span><?php echo e($book['ISBN']); ?></span>
                                            <span><?php echo e($book['Genere']); ?></span>
                                        </div>
                                        <p class='desc'>
                                            <?php echo e($book['Descrizione']); ?>
                                        </p>
                                    </div>
                                </a>
                            </div>
                        </label>
                        <?php endforeach; ?>

                    </div>
                    <div class="slider-dots">
                        <label for="slide-1" class="dot dot--1" aria-label="Vai allo slide 1"></label>
                        <label for="slide-2" class="dot dot--2" aria-label="Vai allo slide 2"></label>
                        <label for="slide-3" class="dot dot--3" aria-label="Vai allo slide 3"></label>
                    </div>
                </section>
            </div>
        </section>

        <!-- ============================================
             QUICK STATS SECTION
             ============================================ -->
        <section class="stats-section">
            <div class="section-header">
                <h2>La Biblioteca In Cifre</h2>
                <p>I numeri chiave della nostra collezione</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <svg class="stat-icon"><use href="img/icons.svg#library"/></svg>
                    <div class="stat-number" data-target="<?php echo $totalBooks; ?>">0</div>
                    <div class="stat-label">Libri Totali</div>
                </div>
                <div class="stat-card">
                    <svg class="stat-icon"><use href="img/icons.svg#account"/></svg>
                    <div class="stat-number" data-target="<?php echo $activeUsers; ?>">0</div>
                    <div class="stat-label">Utenti Attivi</div>
                </div>
                <div class="stat-card">
                    <svg class="stat-icon"><use href="img/icons.svg#booking"/></svg>
                    <div class="stat-number" data-target="<?php echo $booksBorrowed; ?>">0</div>
                    <div class="stat-label">Prestiti Effettuati</div>
                </div>
                <div class="stat-card">
                    <svg class="stat-icon"><use href="img/icons.svg#genre"/></svg>
                    <div class="stat-number" data-target="<?php echo $genresAvailable; ?>">0</div>
                    <div class="stat-label">Generi Disponibili</div>
                </div>
            </div>
        </section>

        <!-- ============================================
             CATEGORIES / GENRES GRID
             ============================================ -->
        <section class="genres-section">
            <div class="section-header">
                <h2>Esplora per Genere</h2>
                <p>Scegli la tua prossima lettura</p>
            </div>
            <div class="genres-grid">
                <?php foreach ($genres as $genre): ?>
                    <a href="lista/lista.php?genere=<?php echo urlencode($genre['Genere']); ?>" class="genre-card">
                        <svg class="genre-icon"><use href="img/icons.svg#genre"/></svg>
                        <h4 class="genre-name"><?php echo e($genre['Genere']); ?></h4>
                        <span class="genre-count"><?php echo $genre['count']; ?> libri</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============================================
             USER ACCOUNT PANEL
             ============================================ -->
        <section class="account-section">
            <?php if ($isLoggedIn && $utenza != 1 && $utenza != 2): ?>
                <div class='info-account'>
                    <a href='prenotazione/prenotazione.php'>
                        <div class='info-prenotazioni'>
                            <h2 class='ultime-prenotazioni'>Ultimi Prestiti</h2>
                            <?php if (count($recentBookings) > 0): ?>
                                <?php foreach ($recentBookings as $prenotazione):
                                    $status = $bookingService->calculateStatus($prenotazione);
                                ?>
                                    <div class='book-prenotation'>
                                        <img src='img/books/<?php echo e($prenotazione['Copertina']); ?>' alt='' class='cover'>
                                        <div class='book-right-column'>
                                            <h4><?php echo e($prenotazione['Nome']); ?></h4>
                                            <span><?php echo e($prenotazione['Autore']); ?></span>
                                            <span style='font-weight: bold; margin-top: 9px; color: <?php echo $status['color']; ?>'><?php echo $status['status']; ?></span>
                                            <div class='inizio-fine'>
                                                <span>Inizio Prenotazione</span>
                                                <span><?php echo e($prenotazione['InizioPrenotazione']); ?></span>
                                            </div>
                                            <div class='inizio-fine'>
                                                <span>Fine Prenotazione</span>
                                                <span><?php echo e($prenotazione['FinePrenotazione']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class='book-prenotation'>
                                    <div>
                                        <h4>Non hai prenotato nessun libro</h4>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php elseif ($isLoggedIn && ($utenza == 1 || $utenza == 2)): ?>
                <div class='info-account'>
                    <a href='prenotazione/prenotazione.php'>
                        <div class='info-prenotazioni'>
                            <h2 class='ultime-prenotazioni'>Ultimi Prestiti</h2>
                            <div class='book-prenotation'>
                                <div>
                                    <h4>Non hai prenotato nessun libro</h4>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php else: ?>
                <div class='info-account'>
                    <a href='prenotazione/prenotazione.php'>
                        <div class='info-prenotazioni'>
                            <h2 class='ultime-prenotazioni'>Ultimi Prestiti</h2>
                            <div class='book-prenotation'>
                                <div>
                                    <h4>Non hai prenotato nessun libro</h4>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>

            <a <?php echo $accountHref; ?>>
                <div class='<?php echo $accountClass; ?>'>
                    <div class='account-status-acc'>
                        <span>Bentornato</span>
                        <h2><?php echo e($user['Nome'] ?? '') . ' ' . e($user['Cognome'] ?? ''); ?></h2>
                        <img src='./img/users/<?php echo e($user['propic'] ?? 'userDashFavicon.svg'); ?>' alt=''>
                        <span><?php echo e($email); ?></span>
                        <span>---------- Prenotazioni ----------</span>
                    </div>
                </div>
            </a>
            <div class='account-status-prenotazioni'>
                <div class='numero-prenotazioni'>
                    <h3>Totali</h3>
                    <span><?php echo $bookingStats['totali']; ?></span>
                </div>
                <div class='numero-prenotazioni'>
                    <h3>Prestiti In corso</h3>
                    <span><?php echo $bookingStats['inCorso']; ?></span>
                </div>
                <div class='numero-prenotazioni'>
                    <h3>Prestiti Riconsegnati</h3>
                    <span><?php echo $bookingStats['riconsegnate']; ?></span>
                </div>
                <div class='numero-prenotazioni'>
                    <h3>Prenotazioni</h3>
                    <span><?php echo $bookingStats['prenotati']; ?></span>
                </div>
            </div>
        </section>

    </main>

    <?php require_once("nav/footer.php"); ?>
</body>

</html>
