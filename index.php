<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

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
    <link rel="stylesheet" href="css/unified.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/hover.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/messaggi.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>HomePage - Alexandria </title>
</head>

<body>
    <script src="homepage.js"></script>
    <?php
    $root = '.';
    require_once("nav/nav.php"); ?>
    
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
                            try {
                                $pdo = DatabaseConnection::getInstance()->getConnection();
                            } catch (PDOException $e) {
                                echo "Errore durante la connessione al database: " . $e->getMessage();
                                exit;
                            }
                            $table = "Opera";
                            $q = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione 
                                        FROM $table WHERE id = (SELECT MAX(id) FROM Opera)";
                            if ($query = $pdo->prepare($q)) {
                                $query->execute();
                                $row = $query->fetch();
                                $query->closeCursor();

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
                                    <img src='img/books/" . $row['Copertina'] . "' alt='' class='cover'>
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
                            } else {
                                throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
                            }
                            ?>
                    </div>

                    </label> <!-- Slider__item -->
                    <label for="slide-2" class="slider__item slider__item--2 card ">
                        <?php
                        $q = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione 
                                    FROM $table WHERE id = (SELECT MAX(id) FROM Opera WHERE id < :idLibro)";
                        if ($query = $pdo->prepare($q)) {
                            $query->bindParam(':idLibro', $idLibro);
                            $query->execute();
                            $row = $query->fetch();
                            $query->closeCursor();

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
                                <img src='img/books/" . $row['Copertina'] . "' alt='' class='cover'>
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
                        } else {
                            throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
                        }
                        ?>
            </div>

            </label> <!-- Slider__item -->

            <label for="slide-3" class="slider__item slider__item--3 card">

                <?php
                $q = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione 
                        FROM $table WHERE id = (SELECT MAX(id) FROM Opera WHERE id < :idLibro)";
                if ($query = $pdo->prepare($q)) {
                    $query->bindParam(':idLibro', $idLibro);
                    $query->execute();
                    $row = $query->fetch();
                    $query->closeCursor();

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
                                <img src='img/books/" . $row['Copertina'] . "' alt='' class='cover'>
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
                } else {
                    throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
                }
                ?>
        </div>

        </label> <!-- Slider__item -->

    </div> <!-- Slider Holder -->
    </section> <!-- Section Slider -->
    </div>

    <?php
    //ultimi prestiti
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
        $q = "SELECT Nome, Autore, Copertina, InizioPrenotazione, FinePrenotazione, InizioPrestito, FinePrestito, FineAttesa, idPrenotazione, Inizioprestito, FineAttesa 
                    FROM Prenotazione, Opera, copiaLibro 
                    WHERE copiaLibro.idCopia = Prenotazione.idCopia 
                    AND copiaLibro.ISBN = Opera.ISBN 
                    AND idPrenotazione = (SELECT MAX(idPrenotazione) FROM Prenotazione  
                                            WHERE InizioPrestito IS NOT NULL AND Email = :email)";
        if (!($query = $pdo->prepare($q))) {
            throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
        }
        $query->bindParam(':email', $email);
        $query->execute();

        $result = $query->fetchAll();
    
        $count = count($result);
        $query->closeCursor();
        if ($count != 0) {
            $prenotazione = $result[0];
            $idPrenotazione = $prenotazione['idPrenotazione'];

            if (($prenotazione['InizioPrestito'] == NULL) && (strtotime($prenotazione['FinePrenotazione']) > time())) {
                $stato = "Prenotato";
                $color = "#ff7600";
            } else if (strtotime($prenotazione['FinePrenotazione']) <= time()) {
                //ELIMINA DAL DB
            } else if ($prenotazione['InizioPrestito'] != NULL && $prenotazione['FinePrestito'] == NULL && strtotime($prenotazione['FineAttesa']) >= time()) {
                $stato = "In Prestito";
                $color = "green";
            } else if ($prenotazione['InizioPrestito'] != NULL && $prenotazione['FinePrestito'] == NULL && strtotime($prenotazione['FineAttesa']) < time()) {
                $stato = "In ritardo";
                $color = "red";
                /*
                } else if ($prenotazione['Stato'] == 3) {
                    $stato = "Riconsegnare";
                    $color = "#ff7600";
                    NON HO SAPUTO DECIFRARE CHE SIGNIFICA... non l'ho tradotto nel nuovo sistema.
                */
            } else if ($prenotazione['InizioPrestito'] != NULL && $prenotazione['FinePrestito'] != NULL) {
                $stato = "Riconsegnato";
                $color = "#686868";
            }

            echo "
                    <div class='info-account'>
                    <a href='prenotazione/prenotazione.php'>
                        <div class='info-prenotazioni'>
                        <h2 class='ultime-prenotazioni'>Ultimi Prestiti</h2>
                            <div class='book-prenotation'>
                                <img src='img/books/" . $prenotazione['Copertina'] . "' alt='' class='cover'>

                                <div class='book-right-column'>
                                    <h4>" . $prenotazione['Nome'] . "</h4>
                                    <span>" . $prenotazione['Autore'] . "</span>
                                    <span style='font-weight: bold; margin-top: 9px; color: $color'>" . $stato . "</span>
                                    <div class='inizio-fine'>
                                        <span>Inizio Prenotazione</span>
                                        <span>" . $prenotazione['InizioPrenotazione'] . "</span>
                                    </div>
                                    
                                    <div class='inizio-fine'>
                                        <span>Fine Prenotazione</span>
                                        <span>" . $prenotazione['FinePrenotazione'] . "</span>
                                    </div>
                                </div>

                            </div>";

            //2
    
            $q = "SELECT Nome, Autore, Copertina,  idPrenotazione, InizioPrenotazione, FinePrenotazione, InizioPrestito, FinePrestito, FineAttesa
                                FROM Prenotazione, Opera, copiaLibro 
                                WHERE copiaLibro.idCopia = Prenotazione.idCopia 
                                AND copiaLibro.ISBN = Opera.ISBN 
                                AND idPrenotazione = (SELECT MAX(idPrenotazione) FROM Prenotazione 
                                WHERE FinePrestito!=NULL AND idPrenotazione <:idPrenotazione AND Email = :email)";
            if (!($query = $pdo->prepare($q))) {
                throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
            }
            $query->bindParam(":idPrenotazione", $idPrenotazione);
            $query->bindParam(":email", $email);
            $query->execute();
            $result = $query->fetchAll();
            $query->closeCursor();
            $count = count($result);

            if ($count != 0) {
                $prenotazione = $result[0];
                $idPrenotazione = $prenotazione['idPrenotazione'];
                if (($prenotazione['InizioPrestito'] == NULL) && (strtotime($prenotazione['FinePrenotazione']) > time())) {
                    $stato = "Prenotato";
                    $color = "#ff7600";
                } else if (strtotime($prenotazione['FinePrenotazione']) <= time()) {
                    //ELIMINA DAL DB
                } else if ($prenotazione['InizioPrestito'] != NULL && $prenotazione['FinePrestito'] == NULL && strtotime($prenotazione['FineAttesa']) >= time()) {
                    $stato = "In Prestito";
                    $color = "green";
                } else if ($prenotazione['InizioPrestito'] != NULL && $prenotazione['FinePrestito'] == NULL && strtotime($prenotazione['FineAttesa']) < time()) {
                    $stato = "In ritardo";
                    $color = "red";
                    /*
                } else if ($prenotazione['Stato'] == 3) {
                    $stato = "Riconsegnare";
                    $color = "#ff7600";
                    NON HO SAPUTO DECIFRARE CHE SIGNIFICA... non l'ho tradotto nel nuovo sistema.
                */
                } else if ($prenotazione['InizioPrestito'] != NULL && $prenotazione['FinePrestito'] != NULL) {
                    $stato = "Riconsegnato";
                    $color = "#686868";
                }

                echo "
                                <div class='book-prenotation'>
                                    <img src='img/books/" . $prenotazione['Copertina'] . "' alt='' class='cover'>
                                    <div class='book-right-column'>
                                        <h4>" . $prenotazione['Nome'] . "</h4>
                                        <span>" . $prenotazione['Autore'] . "</span>
                                        <span style='font-weight: bold; margin-top: 9px; color: $color'>" . $stato . "</span>
                                        <div class='inizio-fine'>
                                            <span>Inizio Prenotazione</span>
                                            <span>" . $prenotazione['InizioPrenotazione'] . "</span>
                                        </div>

                                        <div class='inizio-fine'>
                                            <span>Fine Prenotazione</span>
                                            <span>" . $prenotazione['FinePrenotazione'] . "</span>
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
                            <h2 class='ultime-prenotazioni'>Ultimi Prestiti</h2>
                                <div class='book-prenotation'>
                                    <div>
                                        <h4>Non hai prenotato nessun libro</h4>
                                    </div>
                                </div>
                            </div>";
        }
    }

    if (isset($_SESSION['email'])) {
        $q = "SELECT Nome, Cognome, propic FROM Utente WHERE Email = :email";
        $query = $pdo->prepare($q);
        $query->bindParam(':email', $email);
        $query->execute();
        $utente = $query->fetch();
        $query->closeCursor();

        if ($utenza != 1 && $utenza != 2) {
            $totali = "SELECT count(idPrenotazione) as totali FROM Prenotazione 
                        WHERE Email = :email AND FinePrestito IS NULL";

            $inCorso = "SELECT count(idPrenotazione) as incorso FROM Prenotazione 
                        WHERE Email = :email AND FinePrestito IS NULL";

            $prenotazioni = "SELECT count(idPrenotazione) as prenotati FROM Prenotazione 
                        WHERE Email = :email AND InizioPrestito IS NULL";

            $riconsegnate = "SELECT count(idPrenotazione) as riconsegnate FROM Prenotazione 
                        WHERE Email = :email AND FinePrestito IS NOT NULL";
        } else {
            $totali = "SELECT count(idPrenotazione) as totali FROM Prenotazione 
                        WHERE Email = :email";

            $inCorso = "SELECT count(idPrenotazione) as incorso FROM Prenotazione 
                        WHERE Email = :email AND FinePrestito IS NULL";

            $prenotazioni = "SELECT count(idPrenotazione) as prenotati FROM Prenotazione 
                        WHERE Email = :email AND InizioPrestito IS NULL";

            $riconsegnate = "SELECT count(idPrenotazione) as riconsegnate FROM Prenotazione 
                        WHERE Email = :email AND FinePrestito IS NOT NULL";
        }

        try {
            $query = $pdo->prepare($totali);
            $query->bindParam(':email', $email);
            $query->execute();
            $pTotali = $query->fetch();
            $query->closeCursor();

            $query = $pdo->prepare($inCorso);
            $query->bindParam(':email', $email);
            $query->execute();
            $p_inCorso = $query->fetch();
            $query->closeCursor();

            $query = $pdo->prepare($riconsegnate);
            $query->bindParam(':email', $email);
            $query->execute();
            $p_riconsegnate = $query->fetch();
            $query->closeCursor();

            $query = $pdo->prepare($prenotazioni);
            $query->bindParam(':email', $email);
            $query->execute();
            $p_prenotati = $query->fetch();
            $query->closeCursor();

        } catch (PDOException $e) {
            throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
        }

    } else {
        $utente = ['Nome' => '', 'Cognome' => '', 'propic' => 'userDashFavicon.png'];
        $email = 'eg@example.com';
        $pTotali = ['totali' => 0];
        $p_inCorso = ['incorso' => 0];
        $p_riconsegnate = ['riconsegnate' => 0];
        $p_prenotati = ['prenotati' => 0];
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
                <h3> Prestiti In corso</h3>
                <span>" . $p_inCorso['incorso'] . "</span>
            </div>

            <div class='numero-prenotazioni'>
                <h3> Prestiti Riconsegnati</h3>
                <span>" . $p_riconsegnate['riconsegnate'] . "</span>
            </div>
            <div class='numero-prenotazioni'>
                <h3>Prenotazioni </h3>
                <span>" . $p_prenotati['prenotati'] . "</span>
            </div>
        </div>
    </div>";
    ?>
    </div>
    </div>
</body>

</html>