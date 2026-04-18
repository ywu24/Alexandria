<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, user-scalable=no,
    initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <title>Medusa's Library </title>
    <link rel="stylesheet" href="../../css/libro.css">
    <link rel="stylesheet" href="../../nav/nav.css">
    <link rel="stylesheet" href="../../css/colors.css">
    <link rel="stylesheet" href="dettagli-prenotazione.css">

    <!--script per importare parti di codice-->
    <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
</head>

<body>
    <div class="safe-area spaced-column">

        <div id="nav-placeholder"><?php
			$root= "../..";
            require_once("../../auth/cookies.php");
            require_once("../../utils/connect.php");
			require_once("../../nav/nav.php");
		?></div>
	<div id = "messages">

    </div>


        <?php
        
        $table = "Opera";

        $id = $_GET['id'];

        if ($q2 = $conn->prepare('SELECT Opera.id FROM Opera, Prenotazione, copiaLibro WHERE Prenotazione.idPrenotazione=? AND Prenotazione.idCopia = copiaLibro.idCopia AND Opera.ISBN = copiaLibro.ISBN')) {
            $q2->bind_param('i', $id);
            $q2->execute();
            $result2 = $q2->get_result();
            $opera = $result2->fetch_assoc();
            $book_id = $opera['id'];
        }


        switch (true) {
            case isset($_POST['conferma']):
                $id = $_GET['id'];
                $conn->query("UPDATE `Prenotazione` SET `Stato` = '1' WHERE `Prenotazione`.`idPrenotazione` = $id");
                break;
            case isset($_POST['termina']):
                $id = $_GET['id'];
                $conn->query("UPDATE `Prenotazione` SET `Stato` = '4' WHERE `Prenotazione`.`idPrenotazione` = $id");
                $conn->query("UPDATE copiaLibro, Prenotazione SET copiaLibro.Stato = '1' WHERE copiaLibro.idCopia = Prenotazione.idCopia AND Prenotazione.idPrenotazione = $id");
                break;

            default:

                break;
        }
        if ($q = $conn->prepare('SELECT Stato FROM Prenotazione WHERE idPrenotazione=?')) {
            $q->bind_param('i', $id);
            $q->execute();
            $result = $q->get_result();
            $prenotazione = $result->fetch_assoc();
        }
        if ($prenotazione['Stato'] == 0) {
            $stato = "In Prenotazione";
        }else if($prenotazione['Stato'] == 1){
            $stato = "Prenotato";
        }else if($prenotazione['Stato'] == 2){
            $stato = "In ritardo";
        }else if($prenotazione['Stato'] == 3){
            $stato = "In consegna";
        }else if($prenotazione['Stato'] == 4){
            $stato = "Terminata";
        }else if($prenotazione['Stato'] == 5){
            $stato = "Eliminato";
        }

        

        if (isset($_GET['id'])) {

            try {
                foreach ($conn->query("SELECT Nome, Autore, Copertina, CasaEditrice, ISBN, Descrizione FROM $table WHERE id = $book_id") as $row);{

                    if($prenotazione['Stato'] == 0 || $prenotazione['Stato'] == 1 || $prenotazione['Stato'] == 3 || $prenotazione['Stato'] == 4 || $prenotazione['Stato'] == 5){
                        echo "<main>
                        <div class='container'>
                            <div class='left-column'>
                                <img src='../../img/books/" . $row['Copertina'] . "' alt='Copertina Libro' >
                            </div>
                            <div class='right-column'>
                                <div class='info'>
                                    <div class='info-title'><h1 class='trunctitle'>" . $row['Nome'] . "</h1> <h6>" . $row['ISBN'] . "</h6></div>
                                    <div class='info-release'><h5>" . $row['Autore'] . "</h5> | <h5>" . $row['CasaEditrice'] . "</h5> | <h5>Stato:</h5> <h5  style='color: green !important' class='status'>" . $stato . "</h5></div>
                                </div>
                                <div class='desc'>
                                    <p class='truncdesc'>" . $row['Descrizione'] . "</p>
                                </div>";
                             if ($prenotazione['Stato'] == 0) {
                                 echo "
                                 <form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='conferma'>Conferma Prenotazione</button></form>
                                 <form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Elimina Prenotazione</button></form>
                                 ";
                             } else if ($prenotazione['Stato'] == 1) {
                                 echo "<form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Conferma Consegna</button></form>";
                             }else if ($prenotazione['Stato'] == 2){
                                 echo "<form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Conferma Consegna</button></form>";
                             }else if($prenotazione['Stato'] == 3){
                                 echo "<form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Conferma Consegna</button></form>";
                             }
                             echo
                                 "</div>
                            </div>
                        </main>";
                    }else if($prenotazione['Stato'] == 2){
                        echo "<main>
                        <div class='container'>
                            <div class='left-column'>
                                <img src='../../img/books/" . $row['Copertina'] . "' alt='Copertina Libro' >
                            </div>
                            <div class='right-column'>
                                <div class='info'>
                                    <div class='info-title'><h1 class='trunctitle'>" . $row['Nome'] . "</h1> <h6>" . $row['ISBN'] . "</h6></div>
                                    <div class='info-release'><h5>" . $row['Autore'] . "</h5> | <h5>" . $row['CasaEditrice'] . "</h5> | <h5>Stato:</h5> <h5 style='color: red !important' class='status'>" . $stato . "</h5></div>
                                </div>
                                <div class='desc'>
                                    <p class='truncdesc'>" . $row['Descrizione'] . "</p>
                                </div>";
                             if ($prenotazione['Stato'] == 0) {
                                 echo "
                                 <div class='prenotation-status'>
                                 <form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='conferma'>Conferma Prenotazione</button></form>
                                 <form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Elimina Prenotazione</button></form>
                                 </div>
                                 ";
                             } else if ($prenotazione['Stato'] == 1) {
                                 echo "<form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Conferma Consegna</button></form>";
                             }else if ($prenotazione['Stato'] == 2){
                                 echo "<form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Conferma Consegna</button></form>";
                             }else if($prenotazione['Stato'] == 3){
                                 echo "<form action='dettaglioPrenotazione.php?id=" . $id . "' method='post'><button class='prenotazione' name='termina'>Conferma Consegna</button></form>";
                             }
                             echo
                                 "</div>
                            </div>
                        </main>";
                    }
                    
                } 


            } catch (PDOException $e) {
                print "Error!: " . $e->getMessage() . "<br/>";
                die();
            }


        }









        ?>

        <!--inizio libro-->

        <dialog class="pop-up" id="modal">
            <h4>Sei sicuro di voler confermare la prenotazione di:</h4>
            <div class="prenotation-info">
                <span>titololibro</span>
                <span>ISBN</span>
                <span>Durata prenotazione</span>
            </div>
            <form class="form" method="dialog">

                <button class="button close-button">no</button>
                <button class="button" type="submit" name='prenota'>si</button>
            </form>

        </dialog>
        </main>




        <script>
            const modal = document.querySelector("#modal");
            const openModal = document.querySelector(".open-button");
            const closeModal = document.querySelector(".close-button");

            openModal.addEventListener("click", () => {
                modal.showModal();
            });

            closeModal.addEventListener("click", () => {
                modal.close();
            });
        </script>