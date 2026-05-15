<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2) {
    header("Location: ../../lista/lista.php");
    exit;
}

require_once("../../utils/connect.php");
$root = "../..";

function calcolaStatoPHP($row)
{
    $oggi = time();
    if ($row["InizioPrestito"] == NULL) {
        if (strtotime($row['FinePrenotazione']) < $oggi) return ['stato' => 'Terminato', 'color' => 'text-secondary'];
        return ['stato' => 'Prenotato', 'color' => 'text-success'];
    } else {
        if ($row['FinePrestito'] == NULL) {
            if ($oggi > strtotime($row['FineAttesa'])) return ['stato' => 'In Ritardo', 'color' => 'text-danger'];
            return ['stato' => 'In Prestito', 'color' => 'text-warning'];
        }
        return ['stato' => 'Terminato', 'color' => 'text-muted'];
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Alexandria - Gestione Utente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/user-detail.css">
    <link rel="stylesheet" href="../../css/utilities.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- AGGIUNTI I SCRIPT MANCANTI -->
    <script src="dettaglioUtente.js"></script>
    <script src="dettaglioPrenotazione.js"></script>
</head>

<body class="user-detail">
    <div id="nav-placeholder"><?php require_once("../../nav/nav.php"); ?></div>

    <div class="container mt-5">
        <?php
        $id_utente_get = $_GET['id'] ?? null;
        if (!$id_utente_get) {
            header("Location: dashboardUtenti.php");
            exit;
        }

        try {
            $pdo = DatabaseConnection::getInstance()->getConnection();

            $stmtU = $pdo->prepare("SELECT * FROM Utente WHERE id = :id");
            $stmtU->execute([':id' => $id_utente_get]);
            $u = $stmtU->fetch();

            if (!$u) {
                header("Location: dashboardUtenti.php?errore=3");
                exit;
            }
            $propic = !empty($u['propic']) ? $u['propic'] : "userDashFavicon.png";
        ?>

            <!-- HEADER UTENTE -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-10">
                    <div class="user-summary shadow-sm d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="../../img/users/<?= $propic ?>" class="user-avatar mr-3">
                            <div>
                                <h2 class="h5 font-weight-bold mb-0"><?= htmlspecialchars($u['Nome'] . " " . $u['Cognome']) ?></h2>
                                <p class="small text-muted mb-0"><?= htmlspecialchars($u['Email']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="small text-muted">Punteggio</span>
                            <div class="user-score"><?= $u['punteggio'] ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="messages">
                <?php if (isset($_GET['eliminato'])) echo "<p class='successo'>Prenotazione eliminata con successo</p>"; ?>
            </div>

            <div id="bookings-container">
                <?php
                $sql = "SELECT idPrenotazione, Copertina, Prenotazione.idCopia, InizioPrenotazione, 
                           FinePrenotazione, InizioPrestito, FinePrestito, FineAttesa, Autore, Nome, CasaEditrice 
                    FROM Prenotazione
                    JOIN copiaLibro ON copiaLibro.idCopia = Prenotazione.idCopia 
                    JOIN Opera ON Opera.ISBN = copiaLibro.ISBN 
                    WHERE Prenotazione.Email = :email AND FinePrestito IS NULL
                    ORDER BY idPrenotazione DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([':email' => $u['Email']]);

                while ($row = $stmt->fetch()):
                    $info = calcolaStatoPHP($row);
                    $idPreno = $row['idPrenotazione'];
                    $inizio = $row["InizioPrestito"] ?? $row["InizioPrenotazione"];
                    $fine = $row["FinePrestito"] ?? ($row["InizioPrestito"] ? $row["FineAttesa"] : $row["FinePrenotazione"]);
                ?>
                   <div class="book-container shadow-sm" id="container-prenotazione-<?= $idPreno ?>">
                        <div class="row no-gutters">
                            <div class="col-md-6 left-panel">
                                <div class="media media-book">
                                    <img src="../../img/books/<?= $row['Copertina'] ?>" class="mr-4 shadow-sm book-cover">
                                    <div class="media-body">
                                        <h3 class="h5 font-weight-bold book-title"><?= htmlspecialchars($row['Nome']) ?></h3>
                                        <p class="text-muted mb-1 small"><?= htmlspecialchars($row['Autore']) ?></p>
                                        
                                        <!-- CLASSE status-badge AGGIUNTA -->
                                        <p class="small mb-2 status-badge <?= $info['color'] ?>">
                                            ● <?= strtoupper($info['stato']) ?>
                                        </p>

                                        <div class="small text-muted">
                                            <span>Dal: <?= date("d/m/Y", strtotime($inizio)) ?></span><br>
                                            <span>Al: <?= date("d/m/Y", strtotime($fine)) ?></span>
                                        </div>
                                        <button class="btn btn-dark btn-sm mt-3" onclick="apriDettaglioPrenotazione(<?= $idPreno ?>)">Gestisci</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 right-panel" id="dettaglio-content-<?= $idPreno ?>"></div>

                            <div class="col-md-6 right-panel text-center text-muted" id="placeholder-<?= $idPreno ?>">
                                <small>Seleziona "Gestisci" per azioni</small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="text-center my-5">
                <hr>
                <div id="terminate-container">
                    <button id="load-terminated" class="btn btn-outline-primary btn-terminated"
                        data-id-utente="<?= $id_utente_get ?>">
                        Mostra prenotazioni terminate
                    </button>
                </div>
            </div>

        <?php
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Errore: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>