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

$statsBG = 'bg2';
$genreBG = 'bg1';
$isAdminLibrarian = ($utenza == 1 || $utenza == 2);
if ($isLoggedIn) {
    $user = $userService->getByEmail($email);
    if (!$isAdminLibrarian) {
        $bookingStats = $statsService->getUserBookingStats($email);
        $recentBookings = $bookingService->getRecentForUser($email, 2);
        $userBG = 'bg2';
        $statsBG = 'bg1';
        $genreBG = 'bg2';
    }
}

if ($isAdminLibrarian) {
    $accountClass = 'account-status-2';
} else {
    $accountClass = 'account-status';
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head('HomePage - Alexandria', ['css/pages/home.css', 'css/pages/footer.css'], [], '.'); ?>
</head>

<body>
    <script src="js/homepage.js"></script>

    <div id="nav-placeholder">
        <?php require_once("nav/nav.php"); ?>
    </div>

    <div id="messages">
        <?php render_messages(); ?>
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
                    <?php elseif ($isAdminLibrarian): ?>
                        <a href="prenotazione/prenotazioneAdmin.php" class="btn btn-outline-primary btn-lg">Gestisci Prestiti Utenti</a>
                    <?php else: ?>
                        <a href="prenotazione/prenotazione.php" class="btn btn-outline-primary btn-lg">I Miei Prestiti</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ============================================
             FEATURED BOOKS CAROUSEL
             ============================================ -->
        <?php if (!$isAdminLibrarian): ?>   
        <section class="featured-section bg1">
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
                                        <h3 class="text-break"><?php echo e($book['Nome']); ?></h3>
                                        <div class='info-release'>
                                            <span><?php echo e($book['Autore']); ?></span>
                                            <span><?php echo e($book['CasaEditrice']); ?></span>
                                            <span><?php echo e($book['AnnoPubblicazione']); ?></span>
                                            <span><?php echo e($book['ISBN']); ?></span>
                                            <span><?php echo e($book['Genere']); ?></span>
                                        </div>
                                        <p class='desc text-break'>
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
        <?php endif; ?>  

        <!-- ============================================
             USER ACCOUNT PANEL
             ============================================ -->
        <?php if ($isLoggedIn && ($utenza != 1 && $utenza != 2)): ?>
        <section class="account-section <?php echo $userBG; ?>">
            <div class="section-header">
                <h2>Il Tuo Account</h2>
                <p>Gestisci i tuoi prestiti e prenotazioni</p>
            </div>
            <div class='account-container'>
                <div class='recent-bookings'>
                    <div class='bookings-header'>
                        <h3>Ultimi Prestiti</h3>
                        <a href='prenotazione/prenotazione.php' class='view-all'>Vedi Tutti</a>
                    </div>
                    <?php if (count($recentBookings) > 0): ?>
                        <div class='bookings-list'>
                            <?php foreach ($recentBookings as $prenotazione):
                                $status = $bookingService->calculateStatus($prenotazione);
                            ?>
                                <div class='booking-card'>
                                    <div class='booking-cover'>
                                        <img src='img/books/<?php echo e($prenotazione['Copertina']); ?>' alt='Copertina del libro'>
                                    </div>
                                    <div class='booking-info'>
                                        <div class='booking-meta'>
                                            <h4><?php echo e($prenotazione['Nome']); ?></h4>
                                            <span class='booking-author'><?php echo e($prenotazione['Autore']); ?></span>
                                        </div>
                                        <div class='booking-status' style='color: <?php echo $status['color']; ?>'>
                                            <span class='status-indicator'></span>
                                            <span><?php echo $status['status']; ?></span>
                                        </div>
                                        <div class='booking-dates'>
                                            <div class='date-item'>
                                                <span class='date-label'>Inizio</span>
                                                <span class='date-value'><?php echo e($prenotazione['InizioPrenotazione']); ?></span>
                                            </div>
                                            <div class='date-item'>
                                                <span class='date-label'>Fine</span>
                                                <span class='date-value'><?php echo e($prenotazione['FinePrenotazione']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class='no-bookings'>
                            <div class='no-bookings-icon'>
                                <svg><use href="img/icons.svg#booking"/></svg>
                            </div>
                            <h4>Non hai ancora prenotato nessun libro</h4>
                            <p>Esplora la nostra collezione e prenota il tuo primo libro!</p>
                            <a href='lista/lista.php' class='btn btn-primary'>Sfoglia Libri</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class='account-stats'>
                    <div class='stats-header'>
                        <h3>Le tue statistiche</h3>
                    </div>
                    <div class='stats-grid'>
                        <div class='stat-item'>
                            <div class='stat-value'><?php echo $bookingStats['totali']; ?></div>
                            <div class='stat-label'>Totali</div>
                        </div>
                        <div class='stat-item'>
                            <div class='stat-value'><?php echo $bookingStats['prenotati']; ?></div>
                            <div class='stat-label'>Prenotazioni</div>
                        </div>
                        <div class='stat-item'>
                            <div class='stat-value'><?php echo $bookingStats['inCorso']; ?></div>
                            <div class='stat-label'>Prestiti in Corso</div>
                        </div>
                        <div class='stat-item'>
                            <div class='stat-value'><?php echo $bookingStats['riconsegnate']; ?></div>
                            <div class='stat-label'>Prestiti Conclusi</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============================================
             QUICK STATS SECTION
             ============================================ -->
        <section class="stats-section <?php echo $statsBG ?>">
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
        <?php if (!$isAdminLibrarian): ?>
        <section class="genres-section <?php echo $genreBG ?>">
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
        <?php endif; ?>

    </main>

    <?php require_once("nav/footer.php"); ?>
</body>

</html>
