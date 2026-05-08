<?php
// LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once("../utils/connect.php");
$root = '..';
require_once("../auth/cookies.php");
require_once("../utils/mailer.php");


try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

//AJAX

if (isset($_GET['ajax_reviews']) && $_GET['ajax_reviews'] == '1') {
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $book_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $limit = 5; // Numero di recensioni da caricare ad ogni scroll

    try {
        $query_ajax = "SELECT r.*, u.propic 
               FROM recensione r 
               JOIN utente u ON r.userEmail = u.email 
               WHERE r.idOpera = :book_id 
               ORDER BY r.id DESC 
               LIMIT :limit OFFSET :offset";
        $stmt_ajax = $pdo->prepare($query_ajax);
        $stmt_ajax->bindParam(':book_id', $book_id, PDO::PARAM_INT);
        $stmt_ajax->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt_ajax->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt_ajax->execute();

        $recensioni_ajax = $stmt_ajax->fetchAll(PDO::FETCH_ASSOC);

        foreach ($recensioni_ajax as $row) {
            $voto = (int) $row['Voto'];
            ?>
            <div class="review-card mb-4">
                <div class="review-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php if (!empty($row['propic']) && file_exists("../img/users/" . $row['propic'])): ?>
                                <img src="../img/users/<?php echo $row['propic']; ?>" alt="Avatar"
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($row['userEmail'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h5 class="m-0 fw-bold"><?php echo htmlspecialchars($row['Titolo']); ?></h5>
                            <small class="text-muted"><?php echo htmlspecialchars($row['userEmail']); ?></small>
                        </div>
                    </div>
                    <div class="review-rating">
                        <?php echo str_repeat("★", $voto) . str_repeat("☆", 5 - $voto); ?>
                    </div>
                </div>
                <div class="review-body">
                    <p><?php echo nl2br(htmlspecialchars($row['Messaggio'])); ?></p>
                </div>
            </div>
            <?php
        }
    } catch (PDOException $e) {
        echo "";
    }
    // FONDAMENTALE: Interrompe l'esecuzione della pagina qui se è una chiamata AJAX
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
    <title>Alexandria's Library </title>
    <link rel="stylesheet" href="../css/unified.css">
    <link rel="stylesheet" href="../css/libro.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/popup.css">
    <link rel="stylesheet" href="../css/messaggi.css">

    <!--script per importare parti di codice-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./libro.js" defer></script>
</head>

<body>
    <div class="safe-area spaced-column">

        <div id="nav-placeholder">
            <?php
            require_once("../nav/nav.php"); ?>
        </div>

        <?php
        if (isset($_GET['success'])) {
            echo "<p class='successo'>Prenotazione effettuata con successo</p>";
        }
        if (isset($_GET['errore'])) {
            echo "<p class='errore'>" . htmlspecialchars($_GET['errore']) . "</p>";
        }

        $maxCopie = isset($_POST['slider']) ? $_POST['slider'] : 2;
        $date = date('Y/m/d', time());
        $table = "Opera";

        if (!isset($_GET['id'])) {
            header("Location: ../lista/lista.php?errore=2");
            exit();
        }
        $book_id = $_GET['id'];

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

        // Recupera il voto medio dei libri
        $q = "SELECT AVG(Voto) as media, COUNT(*) as totale FROM recensione WHERE idOpera = :id";
        $query = $pdo->prepare($q);
        $query->bindParam(':id', $book_id, PDO::PARAM_INT);
        $query->execute();
        $dati_media = $query->fetch(PDO::FETCH_ASSOC);
        $media_val = $dati_media['media'] ?? 0;
        $media = round((float) $media_val, 1);
        $totale_recensioni = (int) ($dati_media['totale'] ?? 0);
        $query->closeCursor();

        if (isset($_SESSION['email'])) {
            $email = $_SESSION['email'];
        } else {
            $email = null;
        }

        if (isset($email)) {
            $query = $pdo->prepare('SELECT count(*) FROM Utente, Prenotazione WHERE Utente.email = Prenotazione.email 
                                AND Utente.email = :email AND((FinePrestito IS NULL AND FinePrenotazione >= CURDATE()) 
                                OR (FinePrestito IS NULL AND InizioPrestito IS NOT NULL))');
            $query->bindParam(':email', $email);
            $query->execute();
            $numeroPrenotazioni = $query->fetch()['count(*)'];
            $query->closeCursor();

            $giorniPrenotazione = 30;

            if (isset($_POST["prenota"])) {
                $success = false;
                $error_msg = "";

                if ($_SESSION['utenza'] == 3) {
                    try {
                        $nPrenotazioni = $_POST['sliderino'];
                        for ($i = 0; $i < $nPrenotazioni; $i++) {
                            //MIN(Opera.ISBN) è usato solo per poter prendere l'ISBN del libro, tanto è uguale per tutte le copie
                            $query = $pdo->prepare('SELECT min(idCopia) AS id, MIN(Opera.ISBN) as ISBN FROM copiaLibro, Opera 
                                                    WHERE Stato = 1 and id=:id and Opera.ISBN = copiaLibro.ISBN');

                            $query->bindParam(':id', $book_id);
                            $query->execute();
                            $copiaPrenotata = $query->fetch();
                            $query->closeCursor();

                            $id = $copiaPrenotata['id'];
                            $ISBN = $copiaPrenotata['ISBN']; //viene sovrascritto con lo stesso valore ad ogni ciclo, si potrebbe migliorare 
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

                            // mandare email della prenotazione al bibliotecario:
                            $email_biblio = getenv('EMAIL_BIBLIO');
                            if ($email_biblio) {
                                if (
                                    sendEmail(
                                        $email_biblio,
                                        'Bibliotecario',
                                        'Nuova prenotazione',
                                        '<h2>È stata effettuata una nuova prenotazione!</h2>
                                    <p>Informazioni sulla prenotazione:</p>
                                    <ul>
                                        <li>Email Utente: ' . $email . '</li>
                                        <li>Tipo Utente: Premium</li>
                                        <li>ISBN: ' . $ISBN . '</li>
                                        <li>Quantità: ' . $nPrenotazioni . '</li>
                                        <li>Data inizio: ' . date('d-m-Y') . '</li>
                                        <li>Data fine: ' . date('d-m-Y', strtotime('+' . $giorniPrenotazione . ' days')) . '</li>
                                    </ul>'
                                    )
                                ) {
                                    echo "<p class='successo'>Email mandato al bibliotecario</p>"; // per debug
                                } else {
                                    echo "<p class='errore'>Errore nell'invio dell'email</p>"; //per debug
                                }
                            }
                            $success = true;
                            #echo "<p class= 'successo'> Prenotazione effettuata con successo </p>";
                        }
                    } catch (Exception $e) {
                        $error_msg = $e->getMessage();
                        #echo "<p class= 'errore'> Errore durante la prenotazione: " . $e->getMessage() . "</p>";
                    }

                } else if ($_SESSION['utenza'] == 4) {
                    try {
                        if ($numeroPrenotazioni < 3) {
                            //MIN(Opera.ISBN) è usato solo per poter prendere l'ISBN del libro, tanto è uguale per tutte le copie
                            $query = $pdo->prepare('SELECT min(idCopia) AS id, MIN(Opera.ISBN) as ISBN FROM copiaLibro, Opera  
                                                    WHERE Stato = 1 and id=:id and Opera.ISBN = copiaLibro.ISBN');
                            $query->bindParam(':id', $book_id);
                            $query->execute();
                            $copiaPrenotata = $query->fetch();
                            $query->closeCursor();

                            $id = $copiaPrenotata['id'];
                            $ISBN = $copiaPrenotata['ISBN'];
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

                            // mandare email della prenotazione al bibliotecario:
                            $email_biblio = getenv('EMAIL_BIBLIO');
                            if ($email_biblio) {
                                if (
                                    sendEmail(
                                        $email_biblio,
                                        'Bibliotecario',
                                        'Nuova prenotazione',
                                        '<h2>È stata effettuata una nuova prenotazione!</h2>
                                        <p>Informazioni sulla prenotazione:</p>
                                        <ul>
                                            <li>Email Utente: ' . $email . '</li>
                                            <li>Tipo Utente: Standard</li>
                                            <li>ISBN: ' . $ISBN . '</li>
                                            <li>ID Copia: ' . $id . '</li>
                                            <li>Data inizio: ' . date('d-m-Y') . '</li>
                                            <li>Data fine: ' . date('d-m-Y', strtotime('+' . $giorniPrenotazione . ' days')) . '</li>
                                        </ul>'
                                    )
                                ) {
                                    echo "<p class='successo'>Email mandato al bibliotecario</p>"; // per debug
                                } else {
                                    echo "<p class='errore'>Errore nell'invio dell'email</p>"; //per debug
                                }
                            }
                            $success = true;
                            #echo "<p class= 'successo'> Prenotazione effettuata con successo </p>";
                        } else {
                            $error_msg = "Numero massimo di prenotazioni raggiunto";
                            #echo "<p class= 'errore'> Numero massimo di prenotazioni raggiunto </p>";
                        }
                    } catch (Exception $e) {
                        $error_msg = $e->getMessage();
                        #echo "<p class= 'errore'> Errore durante la prenotazione: " . $e->getMessage() . "</p>";
                    }
                }
                if ($success) {
                    header("Location: libro.php?id=" . $book_id . "&success=1");
                } else {
                    header("Location: libro.php?id=" . $book_id . "&errore=" . urlencode($error_msg));
                }
                exit();
            }
        }


        $q = "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE copiaLibro.Stato = 1 and id = :book_id and Opera.ISBN = copiaLibro.ISBN";
        $query = $pdo->prepare($q);
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


            $stelle_piene = str_repeat("★", round($media));
            $stelle_vuote = str_repeat("☆", 5 - round($media));

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
                        <div class='rating-display'>
                            " . ($totale_recensioni > 0 ?
                "<span class='rating-stars'>$stelle_piene<span class='stars-empty'>$stelle_vuote</span></span> 
                                <span class='media-voto'>$media / 5</span> 
                                <small class='text-muted'>($totale_recensioni recensioni)</small>"
                : "<span class='text-muted'>Ancora nessuna recensione</span>") . "
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
               </div>";
            ?>

            <hr class="my-5">
            <section class="container-fluid mb-5 fluid">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h2 class="fw-bold">Recensioni degli utenti</h2>
                    </div>

                    <div class="col-lg-12">
                        <?php
                        try {
                            $limit = 5; // Limite iniziale
                            $query_commenti = "SELECT r.*, u.propic 
                                            FROM recensione r 
                                            JOIN Utente u ON r.userEmail = u.email
                                            WHERE r.idOpera = :book_id 
                                            ORDER BY r.id DESC 
                                            LIMIT :limit";
                            $stmt_commenti = $pdo->prepare($query_commenti);
                            $stmt_commenti->bindParam(':book_id', $book_id, PDO::PARAM_INT);
                            $stmt_commenti->bindValue(':limit', $limit, PDO::PARAM_INT);
                            $stmt_commenti->execute();

                            $recensioni = $stmt_commenti->fetchAll(PDO::FETCH_ASSOC);

                            if (count($recensioni) > 0) {
                                // Aggiunto data-book-id e id container per JS
                                echo '<div class="review-feed" id="reviews-container" data-book-id="' . $book_id . '">';

                                foreach ($recensioni as $row) {
                                    $voto = (int) $row['Voto'];
                                    ?>
                                    <div class="review-card mb-4">
                                        <div class="review-header">
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php if (!empty($row['propic']) && file_exists("../img/users/" . $row['propic'])): ?>
                                                        <img src="../img/users/<?php echo $row['propic']; ?>" alt="Avatar"
                                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                    <?php else: ?>
                                                        <?php echo strtoupper(substr($row['userEmail'], 0, 1)); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h5 class="m-0 fw-bold"><?php echo htmlspecialchars($row['Titolo']); ?></h5>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['userEmail']); ?></small>
                                                </div>
                                            </div>
                                            <div class="review-rating">
                                                <?php echo str_repeat("★", $voto) . str_repeat("☆", 5 - $voto); ?>
                                            </div>
                                        </div>
                                        <div class="review-body">
                                            <p><?php echo nl2br(htmlspecialchars($row['Messaggio'])); ?></p>
                                        </div>
                                    </div>
                                    <?php
                                }
                                // Aggiunto trigger per lo scroll
                                echo '<div id="scroll-trigger" class="text-center py-3">
                                        <div class="spinner-border text-primary d-none" role="status">
                                            <span class="visually-hidden">Caricamento in corso...</span>
                                        </div>
                                    </div>';

                                echo '</div>';

                            } else {
                                echo "
                                <div class='no-reviews-container text-center py-5 border rounded bg-light'>
                                    <h5 class='text-muted'>Non ci sono ancora recensioni.</h5>
                                    <p>Sii il primo a condividere la tua opinione!</p>
                                </div>";
                            }

                            $stmt_commenti->closeCursor();

                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>Errore nel caricamento delle recensioni: " . $e->getMessage() . "</div>";
                        }
                        ?>
                    </div>
                </div>
            </section>

            </main>

            <?php
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


                <button class="button close-button">no</button>
                <button class='button' type='submit' name='prenota'>si</button>
                </form>
            </div>
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