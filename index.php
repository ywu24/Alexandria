<?php
session_start();
$root = '.';
require_once("auth/cookies.php");
require_once("utils/connect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/hover.css">
    <link rel="stylesheet" href="css/nav.css">
    <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>HomePage - Alexandria </title>
</head>

<body>
    <script src="homepage.js"></script>
    <?php
    $root = '.';
    require_once("nav/nav.php"); ?>

    <div style="padding:20px;">

        <div class="main-container">
            <div class="book-slider">
                <h2 class="titolo-ultime-aggiunte">Ultime aggiunte</h2>
                <section class="section slider">
                    <div class="section__entry section__entry--center">

                    </div>
                    <input type="radio" name="slider" id="slide-1" class="slider__radio">
                    <input type="radio" name="slider" id="slide-2" class="slider__radio" checked>
                    <input type="radio" name="slider" id="slide-3" class="slider__radio">
                    <div class="slider__holder">

                        <label for="slide-1" class="slider__item slider__item--1 card">
                            <?php

                            $table = "Opera";
                            $query = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione FROM $table WHERE id = (SELECT MAX(id) FROM Opera)";
                            $result = mysqli_query($conn, $query);
                            $row = mysqli_fetch_assoc($result);

                            $row = ($row == null ? [
                                'id' => 1,
                                'Nome' => '',
                                'Autore' => '',
                                'Genere' => '',
                                'Copertina' => 'default.jpg',
                                'CasaEditrice' => '',
                                'ISBN' => '',
                                'AnnoPubblicazione' => '',
                                'Descrizione' => ''
                            ] : $row);
                            //errore non gestito se non esiste un libro con id 1 e l'utente clicca sull'href
                            
                            $idLibro = $row['id'];

                            echo "   
                                <div class='slider__item-content'>
                                <div class='left-column'>
                                <a href='libro/libro.php?id=" . $row['id'] . "'>
                                    <img src='./img/books/" . $row['Copertina'] . "' alt='' class='cover'>
                                </a>
                                </div>

                                <a href='libro/libro.php?id=" . $row['id'] . "'>
                                <div class='right-column'>

                                    <h3>" . $row['Nome'] . "</h3>
                                    <div class='info-release'>
                                        <span>" . $row['Autore'] . "</span>
                                        <span>" . $row['CasaEditrice'] . "</span>
                                        <span>" . $row['AnnoPubblicazione'] . "</span>
                                        <span>" . $row['ISBN'] . "</span>
                                        <span>" . $row['Genere'] . "</span>
                                    </div>

                                    <p class='desc'>
                                    " . $row['Descrizione'] . "
                                    </p>

                                </div>
                                </a>
                            ";
                            ?>
                    </div>

                    </label> <!-- Slider__item -->
                    <label for="slide-2" class="slider__item slider__item--2 card ">
                        <?php
                        $query = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione FROM $table WHERE id = (SELECT MAX(id) FROM Opera WHERE id < $idLibro)";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);

                        $row = ($row == null ? [
                            'id' => 1,
                            'Nome' => '',
                            'Autore' => '',
                            'Genere' => '',
                            'Copertina' => 'default.jpg',
                            'CasaEditrice' => '',
                            'ISBN' => '',
                            'AnnoPubblicazione' => '',
                            'Descrizione' => ''
                        ] : $row);
                        //errore non gestito se non esiste un libro con id 1 e l'utente clicca sull'href
                        
                        $idLibro = $row['id'];

                        echo "
                            <div class='slider__item-content'>
                            <div class='left-column'>
                            <a href='libro/libro.php?id=" . $row['id'] . "'>
                                <img src='./img/books/" . $row['Copertina'] . "' alt='' class='cover'>
                            </a>
                            </div>
                            <a href='libro/libro.php?id=" . $row['id'] . "'>

                            <div class='right-column'>
                                <h3>" . $row['Nome'] . "</h3>
                                <div class='info-release'>
                                    <span>" . $row['Autore'] . "</span>
                                    <span>" . $row['CasaEditrice'] . "</span>
                                    <span>" . $row['AnnoPubblicazione'] . "</span>
                                    <span>" . $row['ISBN'] . "</span>
                                    <span>" . $row['Genere'] . "</span>
                                </div>

                                <p class='desc'>
                                " . $row['Descrizione'] . "
                                </p>
                            </div>
                            </a>
                            ";
                        ?>
            </div>

            </label> <!-- Slider__item -->

            <label for="slide-3" class="slider__item slider__item--3 card">

                <?php
                $query = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione FROM $table WHERE id = (SELECT MAX(id) FROM Opera WHERE id < $idLibro)";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_assoc($result);

                $row = ($row == null ? [
                    'id' => 1,
                    'Nome' => '',
                    'Autore' => '',
                    'Genere' => '',
                    'Copertina' => 'default.jpg',
                    'CasaEditrice' => '',
                    'ISBN' => '',
                    'AnnoPubblicazione' => '',
                    'Descrizione' => ''
                ] : $row);
                //errore non gestito se non esiste un libro con id 1 e l'utente clicca sull'href
                
                $idLibro = $row['id'];

                echo "
                            <div class='slider__item-content'>
                            <div class='left-column'>
                            <a href='libro/libro.php?id=" . $row['id'] . "'>
                                <img src='./img/books/" . $row['Copertina'] . "' alt='' class='cover'>
                                </a>
                            </div>
                            <a href='libro/libro.php?id=" . $row['id'] . "'>

                            <div class='right-column'>
                                <h3>" . $row['Nome'] . "</h3>
                                <div class='info-release'>
                                    <span>" . $row['Autore'] . "</span>
                                    <span>" . $row['CasaEditrice'] . "</span>
                                    <span>" . $row['AnnoPubblicazione'] . "</span>
                                    <span>" . $row['ISBN'] . "</span>
                                    <span>" . $row['Genere'] . "</span>
                            </div>

                            <p class='desc'>
                            " . $row['Descrizione'] . "
                            </p>

                            </div>
                            </a>
                            ";
                ?>
        </div>

        </label> <!-- Slider__item -->

    </div> <!-- Slider Holder -->
    </section> <!-- Section Slider -->
    </div>

    <?php
    //ultime prenotazioni
    if (isset($_SESSION['utenza'])) {
        $utenza = $_SESSION['utenza'];
        $email = $_SESSION['email'];

        $class = "account-status-2";
        $href = "href='edit_profile/edit_profile.php'";
    } else {
        $class = "account-status-2 blur";
        $href = "href='auth/login.php'";
    }


    if (isset($_SESSION['utenza']) && $utenza != 1 && $utenza != 2) {
        $class = "account-status";

        //1
        $query2 = "SELECT Nome, Autore, Copertina, Prenotazione.Stato as Stato, idPrenotazione, Inizio, Fine 

                    FROM Prenotazione, Opera, copiaLibro 

                    WHERE copiaLibro.idCopia = Prenotazione.idCopia 

                    AND copiaLibro.ISBN = Opera.ISBN 

                    AND idPrenotazione = (SELECT MAX(idPrenotazione) FROM Prenotazione  WHERE Stato <> 5 AND Email = '$email')";


        $result2 = mysqli_query($conn, $query2);
        $count = $result2->num_rows;

        if ($count != 0) {
            $prenotazione = mysqli_fetch_assoc($result2);
            $idPrenotazione = $prenotazione['idPrenotazione'];

            if ($prenotazione['Stato'] == 0) {
                $stato = "Prenotato";
                $color = "#ff7600";

            } else if ($prenotazione['Stato'] == 1) {
                $stato = "In Prestito";
                $color = "green";

            } else if ($prenotazione['Stato'] == 2) {
                $stato = "In ritardo";
                $color = "red";

            } else if ($prenotazione['Stato'] == 3) {
                $stato = "Riconsegnare";
                $color = "#ff7600";

            } else if ($prenotazione['Stato'] == 4) {
                $stato = "Riconsegnato";
                $color = "#686868";

            }

            echo "
                <div class='info-account'>
                <a href='prenotazione/prenotazione.php'>
                    <div class='info-prenotazioni'>
                    <h2 class='ultime-prenotazioni'>Ultime Prenotazioni</h2>
                        <div class='book-prenotation'>
                            <img src='./img/books/" . $prenotazione['Copertina'] . "' alt='' class='cover'>

                            <div class='book-right-column'>
                                <h4>" . $prenotazione['Nome'] . "</h4>
                                <span>" . $prenotazione['Autore'] . "</span>
                                <span style='font-weight: bold; margin-top: 9px; color: $color'>" . $stato . "</span>
                                <div class='inizio-fine'>
                                    <span>Inizio Prenotazione</span>
                                    <span>" . $prenotazione['Inizio'] . "</span>
                                </div>
                                
                                <div class='inizio-fine'>
                                    <span>Fine Prenotazione</span>
                                    <span>" . $prenotazione['Fine'] . "</span>
                                </div>
                            </div>

                        </div>";

            //2
    
            $query2 = "SELECT Nome, Autore, Copertina, Prenotazione.Stato as Stato, idPrenotazione, Inizio, Fine 
                            FROM Prenotazione, Opera, copiaLibro 
                            WHERE copiaLibro.idCopia = Prenotazione.idCopia 
                            AND copiaLibro.ISBN = Opera.ISBN 
                            AND idPrenotazione = (SELECT MAX(idPrenotazione) FROM Prenotazione WHERE Stato <> 5 AND idPrenotazione < $idPrenotazione AND Email = '$email')";

            $result2 = mysqli_query($conn, $query2);
            $count = $result2->num_rows;

            if ($count != 0) {
                $prenotazione = mysqli_fetch_assoc($result2);
                $idPrenotazione = $prenotazione['idPrenotazione'];
                if ($prenotazione['Stato'] == 0) {
                    $stato = "Prenotato";
                    $color = "#ff7600";
                } else if ($prenotazione['Stato'] == 1) {
                    $stato = "In Prestito";
                    $color = "green";
                } else if ($prenotazione['Stato'] == 2) {
                    $stato = "In ritardo";
                    $color = "red";
                } else if ($prenotazione['Stato'] == 3) {
                    $stato = "Riconsegnare";
                    $color = "#ff7600";
                } else if ($prenotazione['Stato'] == 4) {
                    $stato = "Riconsegnato";
                    $color = "#686868";
                }

                echo "
                            <div class='book-prenotation'>
                                <img src='./img/books/" . $prenotazione['Copertina'] . "' alt='' class='cover'>
                                <div class='book-right-column'>
                                    <h4>" . $prenotazione['Nome'] . "</h4>
                                    <span>" . $prenotazione['Autore'] . "</span>
                                    <span style='font-weight: bold; margin-top: 9px; color: $color'>" . $stato . "</span>
                                    <div class='inizio-fine'>
                                        <span>Inizio Prenotazione</span>
                                        <span>" . $prenotazione['Inizio'] . "</span>
                                    </div>

                                    <div class='inizio-fine'>
                                        <span>Fine Prenotazione</span>
                                        <span>" . $prenotazione['Fine'] . "</span>
                                    </div>                  
                                </div>
                            </div>
                        </div>";
            } else {
                echo "</div>";
            }

        } else {

            echo "
                <div class='info-account'>
                    <a href='prenotazione/prenotazione.php'>
                        <div class='info-prenotazioni'>
                            <h2 class='ultime-prenotazioni'>Ultime Prenotazioni</h2>
                                <div class='book-prenotation'>
                                    <div>
                                        <h4>Non hai prenotato nessun libro</h4>
                                    </div>
                                </div>
                            </div>";
        }

    }

    if (isset($_SESSION['email'])) {
        $querys = "SELECT Nome, Cognome, propic FROM Utente WHERE Email = '$email'";
        $results = mysqli_query($conn, $querys);
        $utente = mysqli_fetch_assoc($results);

        if ($utenza != 1 && $utenza != 2) {
            $totali = "SELECT count(idPrenotazione) as totali FROM Prenotazione WHERE Email = '$email' AND Stato <> 5";
            $inCorso = "SELECT count(idPrenotazione) as incorso FROM Prenotazione WHERE Email = '$email' AND Stato <> 4 AND Stato <> 5 AND Stato <> 0";
            $riconsegnate = "SELECT count(idPrenotazione) as riconsegnate FROM Prenotazione WHERE Email = '$email' AND Stato = 4";

        } else {
            $totali = "SELECT count(idPrenotazione) as totali FROM Prenotazione";
            $inCorso = "SELECT count(idPrenotazione) as incorso FROM Prenotazione WHERE Stato <> 4 AND Stato <> 5 AND Stato <> 0";
            $riconsegnate = "SELECT count(idPrenotazione) as riconsegnate FROM Prenotazione WHERE Stato = 4";
        }

        $resultTotali = mysqli_query($conn, $totali);
        $pTotali = mysqli_fetch_assoc($resultTotali);

        $result_inCorso = mysqli_query($conn, $inCorso);
        $p_inCorso = mysqli_fetch_assoc($result_inCorso);

        $result_riconsegnate = mysqli_query($conn, $riconsegnate);
        $p_riconsegnate = mysqli_fetch_assoc($result_riconsegnate);
    } else {
        $utente = ['Nome' => '', 'Cognome' => '', 'propic' => 'userDashFavicon.png'];
        $email = 'eg@example.com';
        $pTotali = ['totali' => 0];
        $p_inCorso = ['incorso' => 0];
        $p_riconsegnate = ['riconsegnate' => 0];
    }


    echo "

        <a " . $href . ">
            <div class='" . $class . "'>
            <div class='account-status-acc'>
            <span>Bentornato</span>
            <h2>" . $utente['Nome'] . " " . $utente['Cognome'] . "</h2>
            <img src='./img/users/" . $utente['propic'] . "' alt=''>
            <span>" . $email . "</span>
            <span>---------- Prenotazioni ----------</span>
        </div>

        </a>
        <div class='account-status-prenotazioni'>
            <div class='numero-prenotazioni'>
                <h3>Totali</h3>
                <span>" . $pTotali['totali'] . "</span>
            </div>

            <div class='numero-prenotazioni'>
                <h3>In corso</h3>
                <span>" . $p_inCorso['incorso'] . "</span>
            </div>

            <div class='numero-prenotazioni'>
                <h3>Riconsegnate</h3>
                <span>" . $p_riconsegnate['riconsegnate'] . "</span>
            </div>
        </div>
    </div>";
    ?>
    </div>
    </div>
</body>

</html>