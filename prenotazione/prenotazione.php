<?php
session_start();
require_once("../utils/connect.php");
$root = '..';
require_once("../auth/cookies.php");
?>
<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, user-scalable=no,
    initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <title>Alexandria's Library</title>
    <link rel="stylesheet" href="../css/prenotazione.css">
    <link rel="stylesheet" href="../css/dettaglioUtenti.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="../css/popup.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/messaggi.css">

    <!--script per importare parti di codice-->
    <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
    <script src="prenotazione.js"></script>
    
</head>

<body>

    <div id="nav-placeholder">
        <?php
        require_once("../nav/nav.php"); ?>
    </div>
    <div id = "messages">
    </div>
        <h1>Prenotazione Libro</h1>
    
    <div class="container">
        <?php

        if (isset($_SESSION['email'])) {
        } else {
            header("Location: ../index.php");
        }
        $table = "Prenotazione";
        $table1 = "copiaLibro";
        $table2 = "Opera";
        $email = $_SESSION['email'];

        
        ################
        $numero_prenotazioni = 0;
        foreach ($conn->query("SELECT idPrenotazione, Copertina, $table.idCopia, InizioPrenotazione, FinePrenotazione, InizioPrestito, FinePrestito, FineAttesa, Autore, Nome, CasaEditrice FROM $table, $table1, $table2 WHERE $table1.idCopia = $table.idCopia AND $table2.ISBN = $table1.ISBN and $table.Email = '$email'  ORDER BY $table.idPrenotazione DESC") as $row) {
            
            $inizio="";
            $fine ="";
            $stato = "";
            
            
            if ($row["InizioPrestito"] == NULL) {
                if(strtotime($row['FinePrenotazione']) < time()){ echo "OOOO"; }
                else {
                    $stato = "Prenotato"; $color = "green";
                    $inizio = $row["InizioPrenotazione"]; $fine = $row["FinePrenotazione"];
                }
            } else {
                if($row['FinePrestito'] == NULL){
                    if(time() > strtotime($row['FineAttesa'])){
                        $stato="In Ritardo"; $color = "red";
                    } else {
                        $stato="In Prestito"; $color = "orange";
                    }
                    $inizio = $row["InizioPrestito"];
                    $fine = "Da riconsegnare entro = ". $row["FinePrenotazione"];
                } else { $stato = "Terminato"; }
            }

            
            if($stato != "Terminato"){
                $numero_prenotazioni++;
                echo "
                <div class='book-container'>
                    <div class='book-link'>
                        <img src='../img/books/" . $row['Copertina'] . "' alt='' class='book-cover' width='160px'>
                        <div class='book-section'>
                            <h3>" . $row['Nome'] . "</h3>
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

                            
                           

                if($stato == "Prenotato"){
                    echo "
                        <button class='delete-button' data-id ='" . $row['idPrenotazione'] . "' name='delete'>
                            <img src='../img/trash-bin.png' alt='' class='icon1' style='position: relative' width='24px'>
                        </button>";
                } 
            
                echo "
                        </div> </div> </div> ";
            }
        } // Fine foreach
        echo '<hr>
				<div id="terminate-container">
						<button id="load-terminated" class="btn btn-secondary" data-id-utente=' . $_SESSION['email'] . '>
							Mostra prenotazioni terminate
						</button>
				</div>';
		
        
        if($numero_prenotazioni==0){
            echo "<h2>nessuna Prenotazione Attiva al momento</h2>";
        }
        echo "</div>"; // Chiude il div 'container'
?>

</body>