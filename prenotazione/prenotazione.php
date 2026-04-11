<?php
session_start();
require_once("../utils/connect.php");
$root = '..';
require_once("../auth/cookies.php");
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
    <link rel="stylesheet" href="../css/popup.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/colors.css">

    <!--script per importare parti di codice-->
    <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
</head>

<body>

    <div id="nav-placeholder">
        <?php 
        require_once("../nav/nav.php"); ?>    
    </div>

    <center>
        <h1>Prenotazione Libro</h1>
    </center>
    <div class="container">
    <?php

        if (isset($_SESSION['email'])) {
        }else{
            header("Location: ../index.php");
        }
        $table = "Prenotazione";
        $table1 = "copiaLibro";
        $table2 = "Opera";
        $email = $_SESSION['email'];

        if (isset($_POST["delete"]) and isset($_GET["id"])) {
            $idprenotazione = $_GET["id"];
            $conn->query("UPDATE `Prenotazione` SET `Stato` = '5' WHERE `Prenotazione`.`idPrenotazione` = $idprenotazione AND Prenotazione.idPrenotazione <> 4");
            $conn->query("UPDATE copiaLibro, Prenotazione SET copiaLibro.Stato = '1' WHERE copiaLibro.idCopia = Prenotazione.idCopia AND Prenotazione.idPrenotazione = $idprenotazione");
        }
        if (isset($_POST["termina"]) and isset($_GET["id"])) {
            $idprenotazione = $_GET["id"];
            $conn->query("UPDATE `Prenotazione` SET `Stato` = '3' WHERE `Prenotazione`.`idPrenotazione` = $idprenotazione AND Prenotazione.idPrenotazione <> 4");
        }
        
        foreach ($conn->query("SELECT idPrenotazione, Copertina, $table.idCopia, Inizio, Fine, Autore, Nome, CasaEditrice, $table.Stato FROM $table, $table1, $table2 WHERE $table1.idCopia = $table.idCopia AND $table2.ISBN = $table1.ISBN and $table.Email = '$email' and $table.Stato <> 5 ORDER BY $table.idPrenotazione DESC") as $row) {

            if ($row['Stato'] == 0) {
                $stato = "Prenotato";
                $color = "#ff7600";
            }else if($row['Stato'] == 1){
                $stato = "In Prestito";
                $color = "green";
            }else if($row['Stato'] == 2){
                $stato = "In ritardo";
                $color = "red";
            }else if($row['Stato'] == 3){
                $stato = "Riconsegnare";
                $color = "#ff7600";
            }else if($row['Stato'] == 4){
                $stato = "Riconsegnato";
                $color = "#686868";
            }

            echo
            "<div class='book-container'>
            <div class='book-link'>
                <img src='" . $root . $row['Copertina']."' alt=''  class='book-cover' width='160px'>
                <div class='book-section'>
                    <h3>" . $row['Nome']. "</h3>
                    <div class='info-release'><h5>" . $row['Autore'] . "</h5> <span>|</span> <h5>" . $row['CasaEditrice'] . "</h5> <span>|</span> <h5>Stato:</h5> <h5 class='status' style='color:$color'>" . $stato . "</h5></div>
                    <form action=''><span>inizio prenotazione:</span><span>" . $row['Inizio'] . "</span></form>
                    <form action=''><span>fine prenotazione:</span><span>" . $row['Fine'] . "</span></form>
                    ";
                if($row['Stato'] == 0){
                    echo "<form action='prenotazione.php?id=".$row['idPrenotazione']."' method='post'><button class='delete-button' name='delete'><img src='../img/trash-bin.png' alt='' class='icon1' style='position: relative' width='24px'></button></form><br>";
                }
                if($row['Stato'] == 1){
                    echo "
                    <form class='form' action='prenotazione.php?id=".$row['idPrenotazione']."' method='post'><button name='termina' width='24px'>Termina Prenotazione</button>
                    ";
            }
            echo "
                </div>
            </div>
            </div>";
            
        }

        ?>

</body>