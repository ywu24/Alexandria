<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Book catalog page with search, filters, and pagination
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\BookService;

$bookService = new BookService($pdo);

$root = '..';

// Build filters
$search = null;
$genre = null;
$urlParams = [];
$orderBy = "ORDER BY Nome ASC";

if (isset($_GET["search_btn"]) || isset($_GET["search"])) {
    $search = $_GET["search"];
    $urlParams = ['search' => $search];
} elseif (isset($_GET['genere_btn'])) {
    $genre = $_GET['genere_btn'];
    $urlParams = ['genere_btn' => $genre];
}

if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'titolo') {
        $orderBy = "ORDER BY Nome ASC";
    } elseif ($_GET['sort'] == 'anno') {
        $orderBy = "ORDER BY AnnoPubblicazione DESC";
    }
    $urlParams['sort'] = $_GET['sort'];
}

// Pagination
$countQuery = "SELECT count(*) as tot FROM Opera WHERE 1=1";
$countParams = [];

if ($search !== null) {
    $countQuery = "SELECT count(*) as tot FROM Opera WHERE Nome LIKE ? OR Autore LIKE ? OR ISBN LIKE ? OR CasaEditrice LIKE ?";
    $searchText = '%' . $search . '%';
    $countParams = [$searchText, $searchText, $searchText, $searchText];
} elseif ($genre !== null) {
    $countQuery = "SELECT count(*) as tot FROM Opera WHERE Genere = ?";
    $countParams = [$genre];
}

$pagination = paginate($pdo, $countQuery, $countParams, 10, 'lista.php', $urlParams);
$limit = $pagination['limit'];

// Fetch books
$books = $bookService->search($search, $genre, $orderBy, $limit);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alexandria's Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/pages/library.css">
    <link rel="stylesheet" href="../css/pages/footer.css">
    <link rel="stylesheet" href="../css/utilities.css">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="all">
</head>

<body class="lista-page">
    <div id="nav-placeholder">
        <?php require_once("../nav/nav.php"); ?>
    </div>

    <div class="container py-5">
        <!-- Titolo Centrato -->
        <div class="mb-5 text-center">
            <h1 class="display-4 font-weight-bold">Il Nostro Catalogo</h1>
            <p class="lead text-muted">Esplora la collezione della biblioteca di Alessandria</p>
        </div>

        <?php
        if (isset($_GET["errore"])) {
            echo '<div class="alert alert-danger shadow-sm mb-4">';
            if ($_GET["errore"] == 1) {
                echo "Nessuna prenotazione trovata";
            } else if ($_GET["errore"] == 2) {
                echo "Nessun libro con questo ID";
            } else {
                echo "Errore generico";
            }
            echo '</div>';
        } ?>

        <div class="row">
            <!-- Sidebar Filtri -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden sticky-top" style="top: 20px; z-index: 100;">
                    <!-- Header Filtri -->
                    <div class="card-header font-weight-bold d-flex align-items-center" id="sort-by"
                        style="cursor:pointer;">
                        <svg class="icon" style="width:18px;height:18px;margin-right:8px;">
                            <use href="../img/icons.svg#relevance" />
                        </svg>
                        <span class="flex-grow-1">Filtra</span>
                        <svg class="icon" style="width:15px;height:15px;">
                            <use href="../img/icons.svg#arrow-down" />
                        </svg>
                    </div>

                    <div class="list-group list-group-flush" id="left-container">
                        <a href="lista.php?sort=titolo"
                            class="list-group-item list-group-item-action d-flex align-items-center border-0">
                            <svg class="icon" style="width:20px;height:20px;margin-right:12px;">
                                <use href="../img/icons.svg#list" />
                            </svg>
                            <h5 class="m-0 font-weight-normal" style="font-size: 1rem;">Titolo</h5>
                        </a>

                        <div class="list-group-item border-0" id="genere-trigger" style="cursor:pointer;">
                            <div class="d-flex align-items-center">
                                <svg class="icon" style="width:20px;height:20px;margin-right:12px;">
                                    <use href="../img/icons.svg#genre" />
                                </svg>
                                <h5 class="m-0 font-weight-normal flex-grow-1" style="font-size: 1rem;">Genere</h5>
                                <svg class="icon" style="width:12px;height:12px;" id="genere-arrow">
                                    <use href="../img/icons.svg#arrow-down" />
                                </svg>
                            </div>
                        </div>

                        <div class="px-3 py-2" id="genere-subwrap"
                            style="display:none;background-color:var(--color-bg-alt);">
                            <form action="lista.php" method="get">
                                <?php
                                $generi = ["Romanzo Storico", "Giallo", "Biografia", "Avventura", "Azione", "Fantascienza", "Horror", "Umoristico", "Distopia"];
                                foreach ($generi as $g) {
                                    echo "<button type='submit' name='genere_btn' value='$g' class='btn btn-sm btn-block btn-outline-secondary text-left mb-1 border-0 shadow-none'>$g</button>";
                                }
                                ?>
                            </form>
                        </div>

                        <a href="lista.php?sort=anno"
                            class="list-group-item list-group-item-action d-flex align-items-center border-0">
                            <svg class="icon" style="width:20px;height:20px;margin-right:12px;">
                                <use href="../img/icons.svg#calendar" />
                            </svg>
                            <h5 class="m-0 font-weight-normal" style="font-size: 1rem;">Anno</h5>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Colonna Destra -->
            <div class="col-lg-9">
                <div class="right-column">
                    <?php
                    if (count($books) > 0) {
                        foreach ($books as $row) {
                            $id = (int) $row['id'];
                            $availability = $bookService->getAvailability($id);
                            $rating = $bookService->getRatingStats($id);
                            $media_arrotondata = (int) round($rating['media']);
                    ?>
                            <a href='../libro/libro.php?id=<?php echo $id; ?>'>
                                <div class='book-list hvr-float data-single-book shadow-sm mb-3'>
                                    <img src='../img/books/<?php echo e($row['Copertina']); ?>' width='113' height='171' class='book-img' style='object-fit: cover;'>
                                    <div class='container-book'>
                                        <span class='book-link trunctitle' style='font-weight: bold; font-size: 1.2em; display: block; margin-bottom: 5px;'><?php echo e($row['Nome']); ?></span>
                                        <div class='rating-stars' style='margin-bottom: 5px; font-size: 0.9rem;'>
                                            <span style='color: var(--color-accent);'><?php echo render_stars($media_arrotondata); ?></span>
                                            <small style='font-size: 0.75rem; color: var(--color-text-muted);'> (<?php echo $rating['totale']; ?>)</small>
                                        </div>
                                        <p class='book-authors'><?php echo e($row['Autore']) . ' | ' . e($row['CasaEditrice']) . ' | ' . e($row['ISBN']) . ' | ' . e($row['Genere']); ?></p>
                                        <p class='desc'><?php echo e(strlen($row['Descrizione']) > 150 ? substr($row['Descrizione'], 0, 150) . "..." : $row['Descrizione']); ?></p>
                                        <span style='color: <?php echo $availability['color']; ?>; font-weight: bold;' class='disponibilita'><?php echo $availability['disponibilita']; ?></span>
                                    </div>
                                </div>
                            </a>
                    <?php
                        }
                    } else {
                        echo "<h4>Nessun libro trovato.</h4>";
                    }
                    ?>

                    <div class="center" style="margin-top: 30px; display: flex; justify-content: center;">
                        <div class="pagination">
                            <?php echo $pagination['controls']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sortBy = document.getElementById('sort-by');
            var leftContainer = document.getElementById('left-container');
            if (sortBy && leftContainer) {
                sortBy.addEventListener('click', function () {
                    leftContainer.style.display = leftContainer.style.display === 'none' ? 'block' : 'none';
                });
            }

            var genereTrigger = document.getElementById('genere-trigger');
            var genereSubwrap = document.getElementById('genere-subwrap');
            if (genereTrigger && genereSubwrap) {
                genereTrigger.addEventListener('click', function () {
                    if (genereSubwrap.style.display === 'none') {
                        genereSubwrap.style.display = 'block';
                    } else {
                        genereSubwrap.style.display = 'none';
                    }
                });
            }
        });
    </script>
    <?php require_once("../nav/footer.php"); ?>
</body>

</html>
