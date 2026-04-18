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

        //controllo se il libro è presente nel database
        $disp = "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE id = $book_id and Opera.ISBN = copiaLibro.ISBN";
        $result_disp = mysqli_query($conn, $disp);
        $qty = mysqli_fetch_assoc($result_disp);

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
            $query = $conn->prepare('SELECT count(*) FROM Utente, Prenotazione WHERE Utente.email = Prenotazione.email AND Utente.email = ? AND Stato >= 0 AND Stato <= 3');
            $query->bind_param('s', $email);
            $query->execute();
            $r = $query->get_result();
            $numeroPrenotazioni = $r->fetch_assoc();
            $numeroPrenotazioni = $numeroPrenotazioni['count(*)'];

            $giorniPrenotazione = 30;

            if (isset($_POST["prenota"])) {

                if ($_SESSION['utenza'] == 3) {
                    $nPrenotazioni = $_POST['sliderino'];
                    for ($i = 0; $i < $nPrenotazioni; $i++) {
                        if ($q2 = $conn->prepare('SELECT min(idCopia) AS id FROM copiaLibro, Opera WHERE Stato = 1 and id=? and Opera.ISBN = copiaLibro.ISBN')) {
                            $q2->bind_param('i', $book_id);
                            $q2->execute();
                            $result = $q2->get_result();
                            $copiaPrenotata = $result->fetch_assoc();
                        }
                        $id = $copiaPrenotata['id'];
                        $conn->query("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = $id");
                        $conn->query("INSERT INTO Prenotazione (`Email`, `idCopia`, `Inizio`, `Fine`) VALUES ('$email', $id, NOW(), ADDDATE(NOW(), INTERVAL $giorniPrenotazione DAY))");
                    }
                } else if ($_SESSION['utenza'] == 4) {
                    if ($numeroPrenotazioni < 3) {
                        if ($q2 = $conn->prepare('SELECT min(idCopia) AS id FROM copiaLibro, Opera WHERE Stato = 1 and id=? and Opera.ISBN = copiaLibro.ISBN')) {
                            $q2->bind_param('i', $book_id);
                            $q2->execute();
                            $result = $q2->get_result();
                            $copiaPrenotata = $result->fetch_assoc();
                        }
                        $id = $copiaPrenotata['id'];
                        $conn->query("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = $id");
                        $conn->query("INSERT INTO Prenotazione (`Email`, `idCopia`, `Inizio`, `Fine`) VALUES ('$email', $id, NOW(), ADDDATE(NOW(), INTERVAL $giorniPrenotazione DAY))");
                    }
                }
            }
        }


        $disp = "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE copiaLibro.Stato = 1 and id = $book_id and Opera.ISBN = copiaLibro.ISBN";
        $result_disp = mysqli_query($conn, $disp);
        $qty = mysqli_fetch_assoc($result_disp);

        if ($qty['qty'] >= 1) {
            $disponibilita = "Disponibile";
            $color = "green";
        } else {
            $disponibilita = "Non disponibile";
            $color = "red";
        }


        try {
            $stmt = $conn->prepare("SELECT Nome, Autore, Copertina, CasaEditrice, ISBN, Descrizione FROM Opera WHERE id = ?");
            $stmt->bind_param('i', $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();

            echo "<main>
               <div class='container'>
                   <div class='left-column'>
                       <img src='../img/books/" . $book['Copertina'] . "' alt='Copertina Libro' >
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
            die();
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
                $disp = "SELECT count(idCopia) as copieMax FROM copiaLibro, Opera WHERE copiaLibro.Stato = 1 and id = $book_id and Opera.ISBN = copiaLibro.ISBN";
                $result_disp = mysqli_query($conn, $disp);
                $copieMax = mysqli_fetch_assoc($result_disp);

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