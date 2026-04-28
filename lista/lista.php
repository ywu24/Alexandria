<?php
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

        //page controls
        // Show the pagination if the rows numbers is worth displaying 
        if ($lastPage != 1) {
            /*
                First we check if we are on page one. If yes then we don't need a link to 
                the previous page or the first page so we do nothing. If we aren't then we
                generate links to the first page, and to the previous pages.
            */
            $queryString = !empty($urlParams) ? http_build_query($urlParams) . "&" : "";
            $baseUrl = $pageTo . "?" . $queryString;

            if ($page > 1) {
                $previous = $page - 1;
                // Concatenate the link to the variable
                $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $previous . '">&laquo;</a>';
            }

            // Render clickable number links that should appear on the left of the target (current) page number
            for ($i = $page - 4; $i < $page; $i++) {
                if ($i > 0)
                    $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a>';
            }

            // Render the target (current) page number, but without it being a clickable link
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
$whereForPaginator = "";
$orderBy = "ORDER BY Nome ASC";
$params = [];
$urlParams = [];

if (isset($_POST["search_btn"]) || isset($_POST["search"])) {
    $search_text = '%' . $_POST["search"] . '%';
    $whereClause = "WHERE Nome LIKE ? OR Autore LIKE ? OR ISBN LIKE ? OR CasaEditrice LIKE ?";
    $params = [$search_text, $search_text, $search_text, $search_text];
    $urlParams = ['search' => $_POST["search"]];
} elseif (isset($_POST['genere_btn'])) {
    $genere = $_POST['genere_btn'];
    $whereClause = "WHERE Genere = ?";
    $params = [$genere];
    $urlParams = ['genere_btn' => $genere];
}

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
    <link rel="stylesheet" href="../css/lista.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/messaggi.css">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="all">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="safe-area spaced-column">

        <div id="nav-placeholder">
            <?php
            require_once("../nav/nav.php"); ?>
        </div>
        <?php
        if (isset($_GET["errore"])) {
            if ($_GET["errore"] == 1) {
                echo "<p class= 'errore'> Nessuna prenotazione trovata </p>";
            } else if ($_GET["errore"] == 2) {
                echo "<p class= 'errore'> Nessun libro con questo ID </p>";
            } else {
                echo "<p class= 'errore'> Errore generico </p>";
            }
        } ?>
        <div class="container" data-user-book-container>
            <h6 class="home-list">‎</h6>

            <div class="main-container">
                <!-- ricerca avanzata -->
                <div class="left-column">
                    <div class="sort-by" id="sort-by" style="cursor:pointer;">
                        <img src="../img/arrow.png" alt="" class="icon-small">
                        <h5>Filtra</h5>
                    </div>

                    <div class="left-container" id="left-container">
                        <a href="lista.php?sort=titolo" class="filter-item"
                            style="text-decoration: none; color: inherit;">
                            <img src="../img/title.png" alt="" class="icon1">
                            <h5 class="titolo">Titolo</h5>
                        </a>

                        <div class="filter-item" id="genere-trigger" style="cursor:pointer;">
                            <img src="../img/genere.png" alt="" class="icon1">
                            <h5 style="flex-grow: 1;">Genere</h5>
                            <img src="../img/down-arrow.png" class="arrow-icon" id="genere-arrow" alt="">
                        </div>

                        <div class="genere-subwrap" id="genere-subwrap" style="display:none;">
                            <form action="lista.php" method="post">
                                <?php
                                $generi = ["Romanzo Storico", "Giallo", "Biografia", "Avventura", "Azione", "Fantascienza", "Horror", "Umoristico", "Distopia"];
                                foreach ($generi as $g) {
                                    echo "
                                    <button type='submit' name='genere_btn' value='$g' class='genere-sublink'>
                                        $g
                                    </button>";
                                }
                                ?>
                            </form>
                        </div>

                        <a href="lista.php?sort=anno" class="filter-item"
                            style="text-decoration: none; color: inherit;">
                            <img src="../img/calendar.png" alt="" class="icon1">
                            <h5 style="margin:0">Anno</h5>
                        </a>
                    </div>
                </div>

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

                                $q = "SELECT AVG(Voto) as media, COUNT(*) as totale FROM Recensione WHERE idOpera = :id";
                                
                                if ($query = $pdo->prepare($q)) {
                                    $query->bindParam(':id', $id, PDO::PARAM_INT);
                                    $query->execute();
                                    $dati_media = $query->fetch(PDO::FETCH_ASSOC);

                                    $media = $dati_media['media'] ?? 0; // Se null (nessuna recensione), imposta a 0
                                    $media_arrotondata = round((float)$media);
                                    $totale_recensioni = (int)$dati_media['totale'];
                                    
                                    $query->closeCursor();

                                    echo "
                                <a href='../libro/libro.php?id=$id'>
                                    <div class='book-list hvr-float data-single-book'>
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

                    <div class="center" style="margin-top: 30px;">
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
</body>

</html>