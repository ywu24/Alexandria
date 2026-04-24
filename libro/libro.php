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
    <title>Alexandria's Library </title>
    <link rel="stylesheet" href="../css/libro.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/popup.css">
    <link rel="stylesheet" href="../css/messaggi.css">

    <!--script per importare parti di codice-->
    <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
</head>

<body>
    <div class="safe-area spaced-column">

        <div id="nav-placeholder">
            <?php
            require_once("../nav/nav.php"); ?>
        </div>

        <?php
        $maxCopie = isset($_POST['slider']) ? $_POST['slider'] : 2;
        $date = date('Y/m/d', time());
        $table = "Opera";

        if (!isset($_GET['id'])) {
            header("Location: ../lista/lista.php?errore=2");
            exit();
        }
        $book_id = $_GET['id'];

        try {
            $pdo = DatabaseConnection::getInstance()->getConnection();
        } catch (PDOException $e) {
            echo "Errore durante la connessione al database: " . $e->getMessage();
            exit;
        }

        //controllo se il libro è presente nel database
        $q = "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE id = :book_id and Opera.ISBN = copiaLibro.ISBN";
        $query = $pdo->prepare($q);
        $query->bindParam(':book_id', $book_id);
        $query->execute();
        $qty = $query->fetch();
        $query->closeCursor();

        if ($qty['qty'] == 0) {
            header("Location: ../lista/lista.php?errore=2");
            exit();
        }

        if (isset($_SESSION['email'])) {
            $email = $_SESSION['email'];
        } else {
            $email = null;
        }

        if (isset($email)) {
            $query = $conn->prepare('SELECT count(*) FROM Utente, Prenotazione WHERE Utente.email = Prenotazione.email 
                                AND Utente.email = :email AND((FinePrestito IS NULL AND FinePrenotazione >= CURDATE()) 
                                OR (FinePrestito IS NULL AND InizioPrestito IS NOT NULL))');
            $query->bindParam(':email', $email);
            $query->execute();
            $numeroPrenotazioni = $query->fetch()['count(*)'];
            $query->closeCursor();

            $giorniPrenotazione = 30;

            if (isset($_POST["prenota"])) {

                if ($_SESSION['utenza'] == 3) {
                    try {
                        $nPrenotazioni = $_POST['sliderino'];
                        for ($i = 0; $i < $nPrenotazioni; $i++) {
                            if ($query = $pdo->prepare('SELECT min(idCopia) AS id FROM copiaLibro, Opera 
                                                    WHERE Stato = 1 and id=:id and Opera.ISBN = copiaLibro.ISBN')) {
                                $query->bindParam(':id', $book_id);
                                $query->execute();
                                $copiaPrenotata = $query->fetch();
                                $query->closeCursor();
                            }
                            $id = $copiaPrenotata['id'];
                            $query = $pdo->prepare("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = :id");
                            $query->bindParam(':id', $id);
                            $query->execute();
                            $query->closeCursor();

                            $query = $pdo->prepare("INSERT INTO Prenotazione (`Email`, `idCopia`, `Inizio`, `Fine`) 
                                                    VALUES (:email, :id, NOW(), ADDDATE(NOW(), INTERVAL :giorni DAY))");
                            $query->bindParam(':email', $email);
                            $query->bindParam(':id', $id);
                            $query->bindParam(':giorni', $giorniPrenotazione);
                            $query->execute();
                            $query->closeCursor();
                            echo "<p class= 'successo'> Prenotazione effettuata con successo </p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class= 'errore'> Errore durante la prenotazione: " . $e->getMessage() . "</p>";
                        $id = $copiaPrenotata['id'];
                        $query = $pdo->prepare("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = :id");
                        $query->bindParam(':id', $id);
                        $query->execute();
                        $query->closeCursor();

                        $query = $pdo->prepare("INSERT INTO Prenotazione (`Email`, `idCopia`, `InizioPrenotazione`, `FinePrenotazione`) 
                                                VALUES (:email, :id, CURDATE(), ADDDATE(CURDATE(), INTERVAL :giorni DAY))");
                        $query->bindParam(':email', $email);
                        $query->bindParam(':id', $id);
                        $query->bindParam(':giorni', $giorniPrenotazione);
                        $query->execute();
                        $query->closeCursor();
                    }

                } else if ($_SESSION['utenza'] == 4) {
                    try {
                        if ($numeroPrenotazioni < 3) {
                            if ($query = $pdo->prepare('SELECT min(idCopia) AS id FROM copiaLibro, Opera 
                                                    WHERE Stato = 1 and id=:id and Opera.ISBN = copiaLibro.ISBN')) {
                                $query->bindParam(':id', $book_id);
                                $query->execute();
                                $copiaPrenotata = $query->fetch();
                                $query->closeCursor();
                            }
                            $id = $copiaPrenotata['id'];
                            $query = $pdo->prepare("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = :id");
                            $query->bindParam(':id', $id);
                            $query->execute();
                            $query->closeCursor();
                            
                            $query = $pdo->prepare("INSERT INTO Prenotazione (`Email`, `idCopia`, `Inizio`, `Fine`) 
                                                    VALUES (:email, :id, NOW(), ADDDATE(NOW(), INTERVAL :giorni DAY))");
                            $query->bindParam(':email', $email);
                            $query->bindParam(':id', $id);
                            $query->bindParam(':giorni', $giorniPrenotazione);
                            $query->execute();
                            $query->closeCursor();
                            echo "<p class= 'successo'> Prenotazione effettuata con successo </p>";
                        } else {
                            echo "<p class= 'errore'> Numero massimo di prenotazioni raggiunto </p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class= 'errore'> Errore durante la prenotazione: " . $e->getMessage() . "</p>";
                        $id = $copiaPrenotata['id'];
                        $query = $pdo->prepare("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = :id");
                        $query->bindParam(':id', $id);
                        $query->execute();
                        $query->closeCursor();

                        $query = $pdo->prepare("INSERT INTO Prenotazione (`Email`, `idCopia`, `InizioPrenotazione`, `FinePrenotazione`) 
                                                VALUES (:email, :id, CURDATE(), ADDDATE(CURDATE(), INTERVAL :giorni DAY))");
                        $query->bindParam(':email', $email);
                        $query->bindParam(':id', $id);
                        $query->bindParam(':giorni', $giorniPrenotazione);
                        $query->execute();
                        $query->closeCursor();
                    }
                }
            }
        }


        $q = "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE copiaLibro.Stato = 1 and id = :book_id and Opera.ISBN = copiaLibro.ISBN";
        $query = $conn->prepare($q);
        $query->bindParam(':book_id', $book_id);
        $query->execute();
        $qty = $query->fetch();
        $query->closeCursor();

        if ($qty['qty'] >= 1) {
            $disponibilita = "Disponibile";
            $color = "green";
        } else {
            $disponibilita = "Non disponibile";
            $color = "red";
        }


        try {
            $query = $pdo->prepare("SELECT Nome, Autore, Copertina, CasaEditrice, ISBN, Descrizione FROM Opera WHERE id = :id");
            $query->bindParam(':id', $book_id);
            $query->execute();
            $book = $query->fetch();
            $query->closeCursor();


            echo "<main>
               <div class='container'>
                   <div class='left-column'>
                       <img src=' ../img/books/" . $book['Copertina'] . "' alt='Copertina Libro' >
                   </div>
                   <div class='right-column'>
                       <div class='info'>
                           <div class='info-title'><h1 class='trunctitle'>" . $book['Nome'] . "</h1> <h6>ISBN: " . $book['ISBN'] . "</h6></div>
                           <div class='info-release'><h5>" . $book['Autore'] . "</h5> <span> | </span> <h5>" . $book['CasaEditrice'] . "</h5> <div class='status-container'> <h5>Stato:</h5>  <h5 class='status' style='color: $color'>" . $disponibilita . "</h5> </div></div>
                       </div>
                       <div class='desc'>
                           <p class='truncdesc'>" . $book['Descrizione'] . "</p>
                       </div>";
            if (isset($_SESSION['email']) && ($_SESSION['utenza'] == 3 || $_SESSION['utenza'] == 4) && $qty['qty'] >= 1) {
                if ($numeroPrenotazioni < 3 || $_SESSION['utenza'] == 3) {
                    echo "<div class='div-button'><button class='prenotazione open-button' name='prenota'>PRENOTA</button></div>";

                } else {
                    echo "<div class='div-button'><button class='prenotazioneDisabled'>PRENOTA</button></div>";
                    echo "<h6 class='nmax'> Numero massimo di prenotazioni raggiunto </h6>";
                }
            }
            echo
                "</div>
               </div>
           </main>";

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            exit;
        }
        ?>

        <!--inizio libro-->

        <dialog class="pop-up" id="modal">
            <?php
            if ($_SESSION['utenza'] == 3) {
                echo "<h4>Inserire quantità di copie da prenotare</h4>";
            } else if ($_SESSION['utenza'] == 4) {
                echo "<h4>Sei sicuro di voler confermare la prenotazione di:</h4>";
            }
            ?>

            <div class="prenotation-info">
                <?php
                $q = "SELECT count(idCopia) as copieMax FROM copiaLibro, Opera WHERE copiaLibro.Stato = 1 and id = :book_id and Opera.ISBN = copiaLibro.ISBN";
                $query = $pdo->prepare($q);
                $query->bindParam(':book_id', $book_id);
                $query->execute();
                $copieMax = $query->fetch();
                $query->closeCursor();

                echo "
                <span>" . $book['Nome'] . "</span>
                <span>" . $book['ISBN'] . "</span>
                <span>Durata prenotazione: " . $giorniPrenotazione . " giorni</span>
                <form class='form' action='libro.php?id=" . $book_id . "' method='post'>"; #form inviato dai bottoni "si e no" del popup
                if ($_SESSION['utenza'] == 3) {
                    echo "<input name='sliderino' type='range' value=1 min=1 max=" . $copieMax['copieMax'] . " id='slider'>
                <center><span id='sliderValue'>1</span></center>";
                }
                ?>
            </div>

            <button class="button close-button">no</button>
            <button class='button' type='submit' name='prenota'>si</button>
            </form>
        </dialog>

        <script>
            <?php
            if ($_SESSION['utenza'] == 3) {
                echo "const slider = document.getElementById('slider');
                    const sliderValue = document.getElementById('sliderValue');
        
                    slider.addEventListener('input', function () {
                        sliderValue.textContent = slider.value;
                    }); ";
            }
            ?>

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
    </div>
</body>

</html>