<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 session_start();
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");


$giorniPrenotazione = 30;
if(!isset($_GET['id'])){
            header("../../lista.php");
            die();
        }
         ?>

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
    <link rel="stylesheet" href="../../css/messaggi.css">
    <link rel="stylesheet" href="../../css/nav.css">
    <link rel="stylesheet" href="../../css/colors.css">
    <script src="dettaglioPrenotazione.js" defer></script>
    <link rel="stylesheet" href="../../css/dettagli-prenotazione.css">

    <!--script per importare parti di codice-->
    <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
</head>

<body>
    <div class="safe-area spaced-column">

        <div id="nav-placeholder"><?php
			$root= "../..";
           
			require_once("../../nav/nav.php");
		?></div>
	<div id = "messages">
   
    </div>
    <div id ="bookings">

        <?php
        
        $table = "Opera";
        
        $id = $_GET['id'];
    try{
        if ($q2 = $conn->prepare('SELECT Opera.id, Prenotazione.Email as Email FROM Opera, Prenotazione, copiaLibro WHERE Prenotazione.idPrenotazione=? AND Prenotazione.idCopia = copiaLibro.idCopia AND Opera.ISBN = copiaLibro.ISBN')) {
            $q2->bind_param('i', $id);
            $q2->execute();
            $result2 = $q2->get_result();
            $opera = $result2->fetch_assoc();
            $book_id = $opera['id'];
            $email = $opera['Email'];
        }
        echo "<h3> Prenotazione di " . $email . "</h3>";

       
        if ($q = $conn->prepare('SELECT InizioPrenotazione, InizioPrestito, FinePrenotazione, FinePrestito, FineAttesa FROM Prenotazione WHERE idPrenotazione=?')) {
            $q->bind_param('i', $id);
            $q->execute();
            $result = $q->get_result();
            $prenotazione = $result->fetch_assoc();
        }
        if ($prenotazione['InizioPrestito'] == NULL) {
            if(strtotime($prenotazione['FinePrenotazione'])< time()){
                ##ELIMINARE DAL DB
            }
            $stato = "Prenotato";
        }else {
            if($prenotazione['FinePrestito']==NULL){
                 $stato = "In Prestito";
                 if(time() > strtotime($prenotazione['FineAttesa'])){
                    $stato = "In Prestito (in ritardo)";
                }
            }
            else{
                $stato = "Terminata";
                if(strtotime($prenotazione['FinePrestito'])> strtotime($prenotazione['FineAttesa'])){
                    $stato = "Terminata in ritardo";
                }
            }
        }
    } catch(Exception $e){
        header("Location: dashboardUtenti.php");
        exit();
    }
        

        if (isset($_GET['id'])) {

            try {
                foreach ($conn->query("SELECT Nome, Autore, Copertina, CasaEditrice, ISBN, Descrizione FROM $table WHERE id = $book_id") as $row){

                    if($stato != "Terminata"){
                        echo "<main>
                        <div class='container' >
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
                             if ($stato =="Prenotato") {
                                if($_SESSION['utenza']== 1 ||$_SESSION['utenza']== 2){
                                    echo "
                                    <button data-id =" . $id . "  class='prenotazione conferma' name='conferma'>Conferma Prenotazione</button>";
                                }
                                    echo"
                                    <button data-id =" . $id . "  class='prenotazione elimina' name='elimina'>Elimina Prenotazione</button>
                                    ";
                             } else {
                                if($_SESSION['utenza']== 1 ||$_SESSION['utenza']== 2){
                                    echo "<button data-id =" . $id . " class='prenotazione termina' name='termina'>Conferma Consegna</button>";
                                }
                             }
                             echo
                                 "</div>
                            </div>
                        </main>";
                    }else { #se Prenotazione terminata
                        echo "<main>
                        <div class='container'>
                            <div class='left-column'>
                                <img src='../../" . $row['Copertina'] . "' alt='Copertina Libro' >
                            </div>
                            <div class='right-column'>
                                <div class='info'>
                                    <div class='info-title'><h1 class='trunctitle'>" . $row['Nome'] . "</h1> <h6>" . $row['ISBN'] . "</h6></div>
                                    <div class='info-release'><h5>" . $row['Autore'] . "</h5> | <h5>" . $row['CasaEditrice'] . "</h5> | <h5>Stato:</h5> <h5 style='color: red !important' class='status'>" . $stato . "</h5></div>
                                </div>
                                <div class='desc'>
                                    <p class='truncdesc'>" . $row['Descrizione'] . "</p>
                                </div></div> </div>
                            </div>
                        </main>";
                    }
                    
                } 


            } catch (PDOException $e) {
                print "Error!: " . $e->getMessage() . "<br/>";
                header("Location: dashboardUtenti.php");
                exit();
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


