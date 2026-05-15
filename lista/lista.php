<?php
// LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = '..';
require_once("../utils/connect.php");

require_once("../auth/cookies.php");
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

$table = "Opera";
$maxPerPage = 10;
$paginationCtrls = '';

function paginator($where, $additionalParams = [], $urlParams = [])
{
    global $pdo, $table, $maxPerPage, $paginationCtrls;
    $pageTo = "lista.php";

    $countQuery = "SELECT count(*) as tot FROM $table $where";
    try {
        $query = $pdo->prepare($countQuery);
        $query->execute($additionalParams);
        $result = $query->fetch();
        $query->closeCursor();
        $rowCount = $result['tot'];
    } catch (PDOException $e) {
        throw new Exception("Error executing count query: " . $e->getMessage());
    }

    if ($rowCount > 0) {
        $p = isset($_GET['page']) ? $_GET['page'] : 1;
        $page = (int) preg_replace('#[^0-9]#', '', $p);
        $lastPage = ceil($rowCount / $maxPerPage);

        if ($page < 1)
            $page = 1;
        elseif ($page > $lastPage)
            $page = $lastPage;

        $limit = 'LIMIT ' . $maxPerPage . ' OFFSET ' . ($page - 1) * $maxPerPage;

        if ($lastPage != 1) {
            $queryString = !empty($urlParams) ? http_build_query($urlParams) . "&" : "";
            $baseUrl = $pageTo . "?" . $queryString;

            if ($page > 1) {
                $previous = $page - 1;
                $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $previous . '">&laquo;</a>';
            }

            for ($i = $page - 4; $i < $page; $i++) {
                if ($i > 0)
                    $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a>';
            }

            $paginationCtrls .= '<a class="active">' . $page . '</a>';

            for ($i = $page + 1; $i <= $lastPage; $i++) {
                $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a>';
                if ($i >= $page + 4)
                    break;
            }

            if ($page != $lastPage) {
                $next = $page + 1;
                $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $next . '">&raquo;</a>';
            }
        }
        return $limit;
    }
    return "LIMIT $maxPerPage";
}

$whereClause = "";
$params = [];
$urlParams = [];

if (isset($_GET["search_btn"]) || isset($_GET["search"])) {
    $search_text = '%' . $_GET["search"] . '%';
    $whereClause = "WHERE Nome LIKE ? OR Autore LIKE ? OR ISBN LIKE ? OR CasaEditrice LIKE ?";
    $params = [$search_text, $search_text, $search_text, $search_text];
    $urlParams = ['search' => $_GET["search"]];
} elseif (isset($_GET['genere_btn'])) {
    $genere = $_GET['genere_btn'];
    $whereClause = "WHERE Genere = ?";
    $params = [$genere];
    $urlParams = ['genere_btn' => $genere];
}

$orderBy = "ORDER BY Nome ASC";
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'titolo') {
        $orderBy = "ORDER BY Nome ASC";
    } elseif ($_GET['sort'] == 'anno') {
        $orderBy = "ORDER BY AnnoPubblicazione DESC";
    }
    $urlParams['sort'] = $_GET['sort'];
}

$limit = paginator($whereClause, $params, $urlParams);
$finalQuery = "SELECT * FROM $table $whereClause $orderBy $limit";
try {
    $query = $pdo->prepare($finalQuery);
    $query->execute($params);
    $result = $query->fetchAll();
    $query->closeCursor();
} catch (PDOException $e) {
    throw new Exception("Error executing main query: " . $e->getMessage());
}
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light lista-page">
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
                <div class="card border-0 shadow-sm overflow-hidden sticky-top"  style="top: 20px; z-index: 100;">
                    <!-- Header Nero con iconcina blu -->
                    <div class="card-header bg-dark text-white font-weight-bold d-flex align-items-center" id="sort-by" style="cursor:pointer;">
                        <i class="fa fa-filter mr-2" style="color: #007bff;"></i> 
                        <span class="flex-grow-1">Filtra</span>
                        <img src="../img/arrow.png" alt="" width="15" style="filter: invert(1);">
                    </div>

                    <div class="list-group list-group-flush" id="left-container">
                        <a href="lista.php?sort=titolo" class="list-group-item list-group-item-action d-flex align-items-center border-0 text-dark">
                            <img src="../img/title.png" alt="" class="mr-3" width="20">
                            <h5 class="m-0 font-weight-normal" style="font-size: 1rem;">Titolo</h5>
                        </a>

                        <div class="list-group-item border-0" id="genere-trigger" style="cursor:pointer;">
                            <div class="d-flex align-items-center">
                                <img src="../img/genere.png" alt="" class="mr-3" width="20">
                                <h5 class="m-0 font-weight-normal flex-grow-1" style="font-size: 1rem;">Genere</h5>
                                <img src="../img/down-arrow.png" width="12" id="genere-arrow">
                            </div>
                        </div>

                        <div class="bg-light px-3 py-2" id="genere-subwrap" style="display:none;">
                            <form action="lista.php" method="get">
                                <?php
                                $generi = ["Romanzo Storico", "Giallo", "Biografia", "Avventura", "Azione", "Fantascienza", "Horror", "Umoristico", "Distopia"];
                                foreach ($generi as $g) {
                                    echo "<button type='submit' name='genere_btn' value='$g' class='btn btn-sm btn-block btn-outline-secondary text-left mb-1 border-0 shadow-none'>$g</button>";
                                }
                                ?>
                            </form>
                        </div>

                        <a href="lista.php?sort=anno" class="list-group-item list-group-item-action d-flex align-items-center border-0 text-dark">
                            <img src="../img/calendar.png" alt="" class="mr-3" width="20">
                            <h5 class="m-0 font-weight-normal" style="font-size: 1rem;">Anno</h5>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Colonna Destra: INTEGRALMENTE come prima -->
            <div class="col-lg-9">
                <div class="right-column">
                    <?php
                    if (count($result) > 0) {
                        foreach ($result as $row) {
                            $id = $row['id'];

                            // Controllo disponibilità in tempo reale
                            $q = "SELECT count(idCopia) as qty FROM copiaLibro WHERE Stato = 1 AND ISBN = :isbn";

                            if ($query = $pdo->prepare($q)) {
                                $query->bindParam(':isbn', $row['ISBN']);
                                $query->execute();
                                $qtyRow = $query->fetch()['qty'];
                                $query->closeCursor();

                                if ($qtyRow >= 1) {
                                    $disponibilita = "Disponibile";
                                    $color = "green";
                                } else {
                                    $disponibilita = "Non disponibile";
                                    $color = "red";
                                }

                                $q = "SELECT AVG(Voto) as media, COUNT(*) as totale FROM recensione WHERE idOpera = :id";
                                
                                if ($query = $pdo->prepare($q)) {
                                    $query->bindParam(':id', $id, PDO::PARAM_INT);
                                    $query->execute();
                                    $dati_media = $query->fetch(PDO::FETCH_ASSOC);

                                    $media = $dati_media['media'] ?? 0;
                                    $media_arrotondata = round((float)$media);
                                    $totale_recensioni = (int)$dati_media['totale'];
                                    
                                    $query->closeCursor();

                                    echo "
                                <a href='../libro/libro.php?id=$id'>
                                    <div class='book-list hvr-float data-single-book shadow-sm mb-3'>
                                        <img src='" . "../img/books/" . $row['Copertina'] . "' width='113' height='171' class='book-img' style='object-fit: cover;'>
                                        <div class='container-book'>
                                            <span class='book-link trunctitle' style='font-weight: bold; font-size: 1.2em; display: block; margin-bottom: 5px;'>" . $row['Nome'] . "</span>

                                            <div class='rating-stars' style='margin-bottom: 5px; font-size: 0.9rem;'>
                                                <span style='color: #ffc107;'>" . str_repeat("★", $media_arrotondata) . str_repeat("☆", 5 - $media_arrotondata) . "</span>
                                                <small class='text-muted' style='font-size: 0.75rem;'> (" . $totale_recensioni . ")</small>
                                            </div>

                                            <p class='book-authors'>" . $row['Autore'] . " | " . $row['CasaEditrice'] . " | " . $row['ISBN'] . " | " . $row['Genere'] . "</p>
                                            <p class='desc'>" . (strlen($row['Descrizione']) > 150 ? substr($row['Descrizione'], 0, 150) . "..." : $row['Descrizione']) . "</p>
                                            <span style='color: $color; font-weight: bold;' class='disponibilita'>$disponibilita</span>
                                        </div>
                                    </div>
                                </a>";
                                }
                            }
                        }
                    } else {
                        echo "<h4>Nessun libro trovato.</h4>";
                    }
                    ?>

                    <div class="center" style="margin-top: 30px; display: flex; justify-content: center;">
                        <div class="pagination">
                            <?php echo $paginationCtrls; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Toggle sidebar filtri
            $("#sort-by").click(function () {
                $("#left-container").toggle();
            });
            // Toggle sottomenu generi
            $("#genere-trigger").click(function () {
                $("#genere-subwrap").slideToggle();
            });
        }); 
    </script>
    <?php require_once("../nav/footer.php"); ?>
</body>

</html>