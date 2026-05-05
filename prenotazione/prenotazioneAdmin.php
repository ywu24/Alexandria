<?php
session_start();
require_once("../utils/connect.php");
$root = '..';
require_once("../auth/cookies.php");

if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
    header("Location: ../index.php");
    exit();
}

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

// Inizializzazione variabili per evitare errori notice nel template
$data_inizio = '';
$data_fine = '';
$orderBy = "idPrenotazione DESC";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alexandria's Library - Gestione Prenotazioni</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/styleDashboard.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/messaggi.css">
    <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
    <script src="prenotazioneAdmin.js"></script>
    <style>
        .clickable-row { cursor: pointer; }
        .clickable-row:hover { background-color: rgba(0, 0, 0, .075); }
        .sort_btn { background: none; border: none; color: white; cursor: pointer; }
    </style>
</head>

<body>
    <div id="nav-placeholder"><?php require_once("../nav/nav.php"); ?></div>
    <div id="messages"></div>

    <div class="container-fluid mt-4">
        <h1 class="text-center" style="font-size:4rem !important;">Prenotazioni 📅</h1>

        <!-- BARRA FILTRI (Gestita ora via JS) -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-10">
                <form id="filtriForm" class="form-inline justify-content-center bg-light p-3 shadow-sm" style="border-radius: 8px;">
                    <div class="form-group mx-2">
                        <label class="mr-2">Stato:</label>
                        <select name="filtro_stato" class="form-control form-control-sm">
                            <option value="tutti">Tutti (Storico completo)</option>
                            <option value="In corso">In corso (Attivi)</option>
                            <option value="Prenotato">Solo Prenotati</option>
                            <option value="In Prestito">Solo in Prestito</option>
                            <option value="In Ritardo">Solo in Ritardo</option>
                            <option value="Terminato">Solo Terminati</option>
                        </select>
                    </div>
                    <div class="form-group mx-2">
                        <label class="mr-2">Dal:</label>
                        <input type="date" name="data_inizio" class="form-control form-control-sm">
                    </div>
                    <div class="form-group mx-2">
                        <label class="mr-2">Al:</label>
                        <input type="date" name="data_fine" class="form-control form-control-sm">
                    </div>
                    <button type="reset" class="btn btn-outline-secondary btn-sm ml-1">Reset</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">ISBN <button type="button" class="sort_btn" data-sort="ISBN">&ensp;&#x25B2;</button></th>
                        <th scope="col">Titolo <button type="button" class="sort_btn" data-sort="Nome">&ensp;&#x25B2;</button></th>
                        <th scope="col">Utente</th>
                        <th scope="col">Stato</th>
                        <th scope="col">Periodo <button type="button" class="sort_btn" data-sort="InizioPrenotazione">&ensp;&#x25B2;</button></th>
                        <th scope="col">Azione</th>
                    </tr>
                </thead>
                <tbody id="bookings">
                    <?php
                    try {
                        // Query pulita: ritorna le prime 10 righe senza filtri preventivi
                        $sql = "SELECT idPrenotazione, Opera.id as idOpera, InizioPrenotazione, FinePrenotazione, InizioPrestito, 
                                       FinePrestito, FineAttesa, Autore, Nome, Opera.ISBN as ISBN, email 
                                FROM Prenotazione 
                                JOIN copiaLibro ON copiaLibro.idCopia = Prenotazione.idCopia 
                                JOIN Opera ON Opera.ISBN = copiaLibro.ISBN 
                                ORDER BY idPrenotazione DESC LIMIT 10";

                        $stmt = $pdo->query($sql);
                        $results = $stmt->fetchAll();

                        foreach ($results as $row) {
                            $stato_corrente = "";
                            $color = "";
                            $inizio = "";
                            $fine = "";

                            // Logica di calcolo stato (necessaria per la visualizzazione iniziale)
                            if ($row["InizioPrestito"] == NULL) {
                                if (strtotime($row['FinePrenotazione']) < time()) {
                                    $stato_corrente = "Terminato"; $color = "text-secondary";
                                } else {
                                    $stato_corrente = "Prenotato"; $color = "text-success";
                                }
                                $inizio = $row["InizioPrenotazione"]; $fine = $row["FinePrenotazione"];
                            } else {
                                if ($row['FinePrestito'] == NULL) {
                                    if (time() > strtotime($row['FineAttesa'])) {
                                        $stato_corrente = "In Ritardo"; $color = "text-danger";
                                    } else {
                                        $stato_corrente = "In Prestito"; $color = "text-warning";
                                    }
                                    $inizio = $row["InizioPrestito"]; $fine = $row["FineAttesa"];
                                } else {
                                    $stato_corrente = "Terminato"; $color = "text-secondary";
                                    $inizio = $row["InizioPrestito"]; $fine = $row["FinePrestito"];
                                }
                            }

                            $inizio_fmt = date("d/m/Y", strtotime($inizio));
                            $fine_fmt = date("d/m/Y", strtotime($fine));
                            $id = $row['idPrenotazione'];
                            $dettaglioUrl = "../dashboard/dashboardUtenti/dettaglioPrenotazione.php?id=$id";

                            echo "
                            <tr class=\"clickable-row\" onclick=\"window.location='{$dettaglioUrl}';\">
                                <th scope=\"row\">" . htmlspecialchars($row['ISBN']) . "</th>
                                <td><strong>" . htmlspecialchars($row['Nome']) . "</strong><br><small>" . htmlspecialchars($row['Autore']) . "</small></td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td><span class=\"font-weight-bold {$color}\">{$stato_corrente}</span></td>
                                <td><small>Dal: {$inizio_fmt}<br>Al: {$fine_fmt}</small></td>
                                <td><a href=\"{$dettaglioUrl}\" class=\"btn btn-primary btn-sm\">Gestisci</a></td>
                            </tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='6'>Errore caricamento: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

          <div class="d-flex justify-content-center w-100 my-5">
    <button id="caricaAltro" class="btn btn-outline-primary shadow-sm" style="display:none; min-width: 200px;">
        Carica Altro...
    </button>
</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>