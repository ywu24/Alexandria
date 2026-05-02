<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = "../..";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");


$giorniPrenotazione = 30;
if (!isset($_GET['id'])) {
    header("../../lista.php");
    exit;
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

        <div id="nav-placeholder"><?php require_once("../../nav/nav.php"); ?></div>
        <div id="messages">

        </div>
        <div id="bookings">

            <?php

            $table = "Opera";

            $id = $_GET['id'];

            try {
                $pdo = DatabaseConnection::getInstance()->getConnection();
            } catch (PDOException $e) {
                echo "Errore durante la connessione al database: " . $e->getMessage();
                exit;
            }

            try {
                // 1. Recupero dati Opera ed Email
                if (
                    $q1 = $pdo->prepare('SELECT Opera.id, Prenotazione.Email as Email FROM Opera, Prenotazione, copiaLibro 
                             WHERE Prenotazione.idPrenotazione=:id 
                             AND Prenotazione.idCopia = copiaLibro.idCopia 
                             AND Opera.ISBN = copiaLibro.ISBN')
                ) {
                    $q1->bindParam(':id', $id);
                    $q1->execute();
                    $opera = $q1->fetch();
                    $q1->closeCursor();

                    $book_id = $opera['id'];
                    $email = $opera['Email'];
                }
                echo "<h3> Prenotazione di " . $email . "</h3>";

                // 2. Recupero date e calcolo stato (Logica allineata al secondo file)
                if (
                    $q2 = $pdo->prepare('SELECT InizioPrenotazione, InizioPrestito, FinePrenotazione, FinePrestito, FineAttesa 
                             FROM Prenotazione WHERE idPrenotazione=:id')
                ) {
                    $q2->bindParam(':id', $id);
                    $q2->execute();
                    $prenotazione = $q2->fetch();
                    $q2->closeCursor();
                }

                $inizio = "";
                $fine = "";
                $stato = "";
                $color = "black";

                if ($prenotazione['InizioPrestito'] == NULL) {
                    // Caso: Ancora solo prenotato
                    $stato = "Prenotato";
                    $color = "green";
                    $inizio = $prenotazione["InizioPrenotazione"];
                    $fine = $prenotazione["FinePrenotazione"];
                } else {
                    if ($prenotazione['FinePrestito'] == NULL) {
                        // Caso: In prestito attivo
                        if (time() > strtotime($prenotazione['FineAttesa'])) {
                            $stato = "In Ritardo";
                            $color = "red";
                        } else {
                            $stato = "In Prestito";
                            $color = "orange";
                        }
                        $inizio = $prenotazione["InizioPrestito"];
                        $fine = $prenotazione["FineAttesa"]; // La data entro cui riconsegnare
                    } else {
                        // Caso: Concluso
                        $stato = "Terminata";
                        $color = "grey";
                        $inizio = $prenotazione["InizioPrestito"];
                        $fine = $prenotazione["FinePrestito"];
                    }
                }
            } catch (Exception $e) {
                header("Location: dashboardUtenti.php");
                exit();
            }

            if (isset($_GET['id'])) {
                try {
                    $query = $pdo->prepare("SELECT Nome, Autore, Copertina, CasaEditrice, ISBN, Descrizione FROM $table WHERE id = :id");
                    $query->bindParam(':id', $book_id);
                    $query->execute();
                    $row = $query->fetch();

                    if ($row) {
                        // Inizio dell'output HTML
                        // CRITICO: l'ID 'bookings' deve essere presente per il JS
                        echo "<div id='bookings'>";

                        if ($stato != "Terminata") {
                            echo "<main>
                <div class='container'>
                    <div class='left-column'>
                        <img src='../../img/books/" . $row['Copertina'] . "' alt='Copertina Libro'>
                    </div>
                    <div class='right-column'>
                        <div class='info'>
                            <div class='info-title'>
                                <h1 class='trunctitle'>" . $row['Nome'] . "</h1> 
                                <h6>ISBN: " . $row['ISBN'] . "</h6>
                            </div>
                            <div class='info-release'>
                                <h5>" . $row['Autore'] . "</h5> | 
                                <h5>" . $row['CasaEditrice'] . "</h5> | 
                                <div class='status-container'>
                                    <h5>Stato:</h5> 
                                    <h5 class='status' style='color: $color !important'>$stato</h5>
                                </div>
                            </div>
                        </div>

                        <div class='desc'>
                            <!-- Inserimento date di inizio e fine -->
                            <div class='book-dates'>
                                <p><strong>Inizio:</strong> " . $inizio . "</p>
                                <p><strong>Fine:</strong> " . $fine . "</p>
                            </div>
                            <div class='truncdesc'>
                                <p>" . $row['Descrizione'] . "</p>
                            </div>
                        </div>

                        <div class='div-button'>";

                            if ($stato == "Prenotato") {
                                if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {
                                    //classe 'prenotazione' richiesta dal JS
                                    echo "<button data-id='$id' class='prenotazione conferma' name='conferma'>Conferma Prenotazione</button>";
                                }
                                echo "<button data-id='$id' class='prenotazione elimina' name='elimina'>Elimina Prenotazione</button>";
                            } else {
                                if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {
                                    echo "<button data-id='$id' class='prenotazione termina' name='termina'>Conferma Consegna</button>";
                                }
                            }

                            echo "      </div> 
                    </div> 
                </div>
                </main>";
                        } else {
                            // Caso Prenotazione terminata
                            echo "<main>
                <div class='container'>
                    <div class='left-column'>
                        <img src='../../img/books/" . $row['Copertina'] . "' alt='Copertina Libro' >
                    </div>
                    <div class='right-column'>
                        <div class='info'>
                            <div class='info-title'>
                                <h1 class='trunctitle'>" . $row['Nome'] . "</h1> 
                                <h6>ISBN: " . $row['ISBN'] . "</h6>
                            </div>
                            <div class='info-release'>
                                <h5>" . $row['Autore'] . "</h5> | 
                                <h5>" . $row['CasaEditrice'] . "</h5> | 
                                <div class='status-container'>
                                    <h5>Stato:</h5> 
                                    <h5 style='color: red !important' class='status'>$stato</h5>
                                </div>
                            </div>
                        </div>
                        <div class='desc'>
                            <div class='book-dates'>
                                <p><strong>Inizio:</strong> " . $inizio . "</p>
                                <p><strong>Fine:</strong> " . $fine . "</p>
                            </div>
                            <div class='truncdesc'>
                                <p>" . $row['Descrizione'] . "</p>
                            </div>
                        </div>
                    </div> 
                </div>
                </main>";
                        }

                        echo "</div>"; // Fine div#bookings
                    }
                    $query->closeCursor();
                } catch (PDOException $e) {
                    echo "Errore: " . $e->getMessage();
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