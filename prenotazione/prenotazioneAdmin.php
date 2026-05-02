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

// --- LOGICA FILTRI E ORDINAMENTO ---
// Controllo del filtro stato
if (isset($_POST['filtro_stato'])) {
    $filtro_stato = $_POST['filtro_stato'];
} else {
    $filtro_stato = 'tutti';
}

// Controllo della data di inizio
if (!empty($_POST['data_inizio'])) {
    $data_inizio = $_POST['data_inizio'];
} else {
    $data_inizio = '';
}

// Controllo della data di fine
if (!empty($_POST['data_fine'])) {
    $data_fine = $_POST['data_fine'];
} else {
    $data_fine = '';
}

$orderBy = "idPrenotazione DESC";
if (isset($_POST['sort_isbn'])) $orderBy = "ISBN";
if (isset($_POST['sort_nome'])) $orderBy = "Nome";
if (isset($_POST['sort_data'])) $orderBy = "InizioPrenotazione";
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
    <script src="Admin.js"></script>
    <style>
        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover {
            background-color: rgba(0, 0, 0, .075);
        }

        .sort_btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div id="nav-placeholder"><?php require_once("../nav/nav.php"); ?></div>
    <div id="messages"></div>

    <div class="container-fluid mt-4">
        <h1 class="text-center" style="font-size:4rem !important;">Prenotazioni 📅</h1>

        <!-- BARRA FILTRI -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-10">
                <form method="POST" class="form-inline justify-content-center bg-light p-3 shadow-sm" style="border-radius: 8px;">
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
                        <input type="date" name="data_inizio" class="form-control form-control-sm" value="<?= htmlspecialchars($data_inizio) ?>">
                    </div>
                    <div class="form-group mx-2">
                        <label class="mr-2">Al:</label>
                        <input type="date" name="data_fine" class="form-control form-control-sm" value="<?= htmlspecialchars($data_fine) ?>">
                    </div>
                    <button type="submit" class="btn btn-info btn-sm ml-2">Applica Filtri</button>
                    <a href="prenotazione.php" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <form action="" method="post">
                            <th scope="col">ISBN <button class="sort_btn" name="sort_isbn">&ensp;&#x25B2;</button></th>
                            <th scope="col">Titolo <button class="sort_btn" name="sort_nome">&ensp;&#x25B2;</button></th>
                            <th scope="col">Utente</th>
                            <th scope="col">Stato</th>
                            <th scope="col">Periodo <button class="sort_btn" name="sort_data">&ensp;&#x25B2;</button></th>
                            <th scope="col">Azione</th>
                        </form>
                    </tr>
                </thead>
                <tbody id="bookings">
                    <?php
                    $table = "Prenotazione";
                    $table1 = "copiaLibro";
                    $table2 = "Opera";

                    try {
                        // Costruzione dinamica della query con parametri
                        $params = [];
                        $sql = "SELECT idPrenotazione, $table2.id as idOpera, InizioPrenotazione, FinePrenotazione, InizioPrestito, 
                                       FinePrestito, FineAttesa, Autore, Nome, $table2.ISBN as ISBN, email 
                                FROM $table 
                                JOIN $table1 ON $table1.idCopia = $table.idCopia 
                                JOIN $table2 ON $table2.ISBN = $table1.ISBN 
                                WHERE 1=1";

                        if ($data_inizio) {
                            $sql .= " AND InizioPrenotazione >= :data_inizio";
                            $params[':data_inizio'] = $data_inizio;
                        }
                        if ($data_fine) {
                            $sql .= " AND InizioPrenotazione <= :data_fine";
                            $params[':data_fine'] = $data_fine;
                        }

                        $sql .= " ORDER BY $orderBy";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $results = $stmt->fetchAll();

                        $numero_prenotazioni = 0;

                        foreach ($results as $row) {
                            $stato_corrente = "";
                            $color = "";
                            $inizio = "";
                            $fine = "";

                            // Calcolo Stato Temporale
                            if ($row["InizioPrestito"] == NULL) {
                                if (strtotime($row['FinePrenotazione']) < time()) {
                                    $stato_corrente = "Terminato";
                                    $color = "text-secondary";
                                } else {
                                    $stato_corrente = "Prenotato";
                                    $color = "text-success";
                                }
                                $inizio = $row["InizioPrenotazione"];
                                $fine = $row["FinePrenotazione"];
                            } else {
                                if ($row['FinePrestito'] == NULL) {
                                    if (time() > strtotime($row['FineAttesa'])) {
                                        $stato_corrente = "In Ritardo";
                                        $color = "text-danger";
                                    } else {
                                        $stato_corrente = "In Prestito";
                                        $color = "text-warning";
                                    }
                                    $inizio = $row["InizioPrestito"];
                                    $fine = $row["FineAttesa"];
                                } else {
                                    $stato_corrente = "Terminato";
                                    $color = "text-secondary";
                                    $inizio = $row["InizioPrestito"];
                                    $fine = $row["FinePrestito"];
                                }
                            }

                            // Filtro Stato applicato lato PHP (perché calcolato a runtime)
                            if ($filtro_stato != 'tutti') {
                                if ($filtro_stato == 'In corso') {
                                    if ($stato_corrente == "Terminato") continue;
                                } else {
                                    if ($stato_corrente != $filtro_stato) continue;
                                }
                            }

                            $inizio_formattato = date("d/m/Y", strtotime($inizio));
                            $fine_formattata = date("d/m/Y", strtotime($fine));

                            $numero_prenotazioni++;
                            $id = $row['idPrenotazione'];
                            $dettaglioUrl = "../dashboard/dashboardUtenti/dettaglioPrenotazione.php?id=$id";
                    ?>
                            <tr class="clickable-row" onclick="window.location='<?= $dettaglioUrl ?>';">
                                <th scope="row"><?= htmlspecialchars($row['ISBN']) ?></th>
                                <td><strong><?= htmlspecialchars($row['Nome']) ?></strong><br><small><?= htmlspecialchars($row['Autore']) ?></small></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><span class="font-weight-bold <?= $color ?>"><?= $stato_corrente ?></span></td>
                                <td><small>Dal: <?= $inizio_formattato ?><br>Al: <?= $fine_formattata ?></small></td>
                                <td>
                                    <a href="<?= $dettaglioUrl ?>" class="btn btn-primary btn-sm">Gestisci</a>
                                </td>
                            </tr>
                    <?php }
                    } catch (PDOException $e) {
                        echo "Errore: " . $e->getMessage();
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <?php if ($numero_prenotazioni == 0): ?>
            <h4 class="text-center mt-5 text-muted">Nessun record trovato per i filtri selezionati</h4>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>