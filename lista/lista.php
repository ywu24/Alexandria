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
    <?php render_head("Alexandria's Library", ['css/pages/library.css', 'css/pages/footer.css', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'], ['js/lista.js'], '..'); ?>
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
                            render_book_card($row, $availability, $rating, '../');
                        }
                    } else {
                        echo "<h4>Nessun libro trovato.</h4>";
                    }
                    ?>

                    <?php render_pagination($pagination); ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once("../nav/footer.php"); ?>
</body>

</html>
