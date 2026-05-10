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
    die("Errore connessione: " . $e->getMessage());
}

/**
 * Funzione helper per calcolare lo stato in PHP 
 * (deve essere identica alla logica in getPrenotazioni.php e nel JS)
 */
function calcolaStatoPHP($row) {
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
    <title>Alexandria - Gestione Prenotazioni</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/unified.css">
    <link rel="stylesheet" href="../css/dettaglioUtenti.css">
</head>
<body>
    <div id="nav-placeholder"><?php require_once("../nav/nav.php"); ?></div>

    <!-- ... (stesso inizio PHP di prima per sessione e connessione) ... -->

<div class="container mt-5">
    <h1 class="text-center mb-5 font-weight-extra-bold">Prenotazioni 📅</h1>

    <!-- BARRA FILTRI -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <form id="filtriForm" class="form-inline justify-content-center p-3 bg-white shadow-sm filter-bar">
                <label class="mr-2 small text-muted">Stato:</label>
                <select name="filtro_stato" class="form-control form-control-sm border-0 font-weight-bold filter-select">
                    <option value="tutti">Tutti gli stati</option>
                    <option value="Prenotato">Prenotati</option>
                    <option value="In Prestito">In Prestito</option>
                    <option value="In Ritardo">In Ritardo</option>
                    <option value="Terminato">Terminati</option>
                </select>
                
                <div class="filter-divider mx-3"></div>
                
                <label class="mr-2 small text-muted">Dal:</label>
                <input type="date" name="data_inizio" class="form-control form-control-sm border-0">
                
                <label class="mx-2 small text-muted">Al:</label>
                <input type="date" name="data_fine" class="form-control form-control-sm border-0">
                
                <button type="reset" class="btn btn-link btn-sm text-secondary ml-3">Reset</button>
            </form>
        </div>
    </div>

    <!-- CONTENITORE PRENOTAZIONI -->
    <div id="bookings-container">
        <?php
        // Query iniziale per i primi 10
        $sql = "SELECT idPrenotazione, InizioPrenotazione, FinePrenotazione, InizioPrestito, 
                       FinePrestito, FineAttesa, Autore, Nome, Opera.ISBN, email, Copertina
                FROM Prenotazione 
                JOIN copiaLibro ON copiaLibro.idCopia = Prenotazione.idCopia 
                JOIN Opera ON Opera.ISBN = copiaLibro.ISBN 
                ORDER BY idPrenotazione DESC LIMIT 10";
        
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch()):
            $info = calcolaStatoPHP($row); // Usa la funzione helper definita prima
            $id = $row['idPrenotazione'];
            $inizio = $row["InizioPrestito"] ?? $row["InizioPrenotazione"];
            $fine = $row["FinePrestito"] ?? ($row["InizioPrestito"] ? $row["FineAttesa"] : $row["FinePrenotazione"]);
        ?>
           <div class="book-container shadow-sm" id="container-prenotazione-<?= $id ?>">
                <div class="row no-gutters">
                    <div class="col-md-6 left-panel">
                        <div class="media media-book">
                            <img src="../img/books/<?= $row['Copertina'] ?>" class="mr-4 shadow-sm book-cover">
                            <div class="media-body">
                                <h3 class="h5 font-weight-bold book-title"><?= htmlspecialchars($row['Nome']) ?></h3>
                                <p class="text-muted mb-1"><?= htmlspecialchars($row['Autore']) ?></p>
                                <p class="small mb-2 status-badge <?= $info['color'] ?>">● <?= strtoupper($info['stato']) ?></p>
                                <div class="small text-muted">
                                    <span>Dal: <?= date("d/m/Y", strtotime($inizio)) ?></span><br>
                                    <span>Al: <?= date("d/m/Y", strtotime($fine)) ?></span><br>
                                    <span>User: <?= htmlspecialchars($row['email']) ?></span>
                                </div>
                                <button class="btn btn-dark btn-sm mt-3" onclick="apriDettaglioPrenotazione(<?= $id ?>)">Gestisci</button>
                            </div>
                        </div>
                    </div>
                    <!-- Nota: ID e display gestiti da JS -->
                    <div class="col-md-6 right-panel" id="dettaglio-content-<?= $id ?>"></div>
                    <div class="col-md-6 right-panel text-center text-muted" id="placeholder-<?= $id ?>">
                        <small>Seleziona "Gestisci" per azioni</small>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="text-center my-5">
        <button id="caricaAltro" class="btn btn-outline-primary">Carica Altro...</button>
    </div>
</div>

    <script src="dettaglioPrenotazione.js"></script>
    <script src="prenotazioneAdmin.js"></script>
</body>
</html>