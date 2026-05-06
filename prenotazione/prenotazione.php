<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../utils/connect.php");
$root = '..';
require_once("../auth/cookies.php");
if(isset($_SESSION['utenza'])){
    if($_SESSION['utenza']==1 || $_SESSION['utenza']==2){
        header("Location: prenotazioneAdmin.php");
        die(); 
    }
} else{
    header("Location: ../index.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alexandria's Library</title>
    
    <!-- Bootstrap per classi di utilità su titoli e bottoni -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">    
    <link rel="stylesheet" href="../css/unified.css">
    <link rel="stylesheet" href="../css/prenotazione.css">
    <link rel="stylesheet" href="../css/dettaglioUtenti.css">
    <link rel="stylesheet" href="../css/popup.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/messaggi.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="prenotazione.js"></script>
</head>

<body>

    <div id="nav-placeholder">
        <?php require_once("../nav/nav.php"); ?>
    </div>

    <!-- Messaggi originali -->
    <div id="messages">
        <?php
        if (isset($_SESSION['success_msg'])) {
            echo '<p class="successo">' . $_SESSION['success_msg'] . '</p>';
            unset($_SESSION['success_msg']);
        }
        if (isset($_SESSION['error_msg'])) {
            echo '<p class="errore">' . $_SESSION['error_msg'] . '</p>';
            unset($_SESSION['error_msg']);
        }
        ?>
    </div>

    <!-- SEZIONE TITOLO E SOTTOTITOLO AGGIORNATA -->
    <div class="container mt-5 mb-4">
        <div class="text-center">
            <h1 class="display-4 font-weight-bold">Prenotazione Libro</h1>
            <p class="lead text-muted">Visualizza e gestisci lo stato dei tuoi prestiti attivi</p>
        </div>
    </div>

    <div class="container">
        <?php
        if (!isset($_SESSION['email'])) {
            header("Location: ../index.php");
            exit;
        }

        $table = "Prenotazione";
        $table1 = "copiaLibro";
        $table2 = "Opera";
        $email = $_SESSION['email'];

        try {
            $pdo = DatabaseConnection::getInstance()->getConnection();
        } catch (PDOException $e) {
            echo "Errore durante la connessione al database: " . $e->getMessage();
            exit;
        }

        $numero_prenotazioni = 0;
        try {
            $query = $pdo->prepare("SELECT idPrenotazione, $table2.id as idOpera, Copertina, $table.idCopia, InizioPrenotazione, FinePrenotazione, InizioPrestito, 
                                FinePrestito, FineAttesa, Autore, Nome, CasaEditrice, $table2.ISBN as ISBN FROM $table, $table1, $table2 
                                WHERE $table1.idCopia = $table.idCopia AND $table2.ISBN = $table1.ISBN and $table.Email = :email 
                                ORDER BY $table.idPrenotazione DESC");
            $query->bindParam(':email', $email);
            $query->execute();
            
            foreach ($query->fetchAll() as $row) {
                $inizio = "";
                $fine = "";
                $stato = "";

                if ($row["InizioPrestito"] == NULL) {
                    if (strtotime($row['FinePrenotazione']) < time()) {
                        // Logica gestione scaduti...
                    } else {
                        $stato = "Prenotato";
                        $color = "green";
                        $inizio = $row["InizioPrenotazione"];
                        $fine = $row["FinePrenotazione"];
                    }
                } else {
                    if ($row['FinePrestito'] == NULL) {
                        if (time() > strtotime($row['FineAttesa'])) {
                            $stato = "In Ritardo";
                            $color = "red";
                        } else {
                            $stato = "In Prestito";
                            $color = "orange";
                        }
                        $inizio = $row["InizioPrestito"];
                        $fine = "Da riconsegnare entro = " . $row["FinePrenotazione"];
                    } else {
                        $stato = "Terminato";
                    }
                }

                if ($stato != "Terminato") {
                    $numero_prenotazioni++;
                    echo "
                <div class='book-container'>
                    <div class='book-link'>
                        <img src='../img/books/" . $row['Copertina'] . "' alt='' class='book-cover' width='160px'>
                        <div class='book-section'>
                            <div class='info-title'><h3 class='trunctitle'>" . $row['Nome'] . "</h3> <h6>ISBN: " . $row['ISBN'] . "</h6></div>
                            <div class='info-release'>
                                <h5>" . $row['Autore'] . "</h5>
                                <h5>" . $row['CasaEditrice'] . "</h5>
                                <h5>Stato:</h5> 
                                <h5 class='status' style='color: $color'>" . $stato . "</h5>
                            </div>
                            
                            <div class='book-dates'>
                                <span>Inizio: " . $inizio . "</span><br>
                                <span>Fine: " . $fine . "</span>
                            </div>";

                    if ($stato == "Prenotato") {
                        echo "
                        <button class='delete-button' data-id ='" . $row['idPrenotazione'] . "' name='delete'>
                            <img src='../img/trash-bin.png' alt='' class='icon1' style='position: relative' width='24px'>
                        </button>";
                    }

                    echo "</div></div></div>";
                }
            }
            $query->closeCursor();
        } catch (PDOException $e) {
            echo "Errore nella query.";
        }

        echo '<hr>
                <div id="terminate-container">
                        <button id="load-terminated" class="btn btn-secondary" data-id-utente="' . $_SESSION['email'] . '">
                            Mostra prenotazioni terminate
                        </button>
                </div>';

        if ($numero_prenotazioni == 0) {
            echo "<h2>Nessuna Prenotazione Attiva al momento</h2>";
        }
        echo "</div>"; 
        ?>
</body>
</html>