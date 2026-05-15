 <?php
    //LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // Utilizzo:
    session_start();
    $root = '..';
    require_once("../utils/connect.php");
    require_once("cookies.php");
    require_once("../utils/mailer.php");
    // check to see if there is a user already logged in, if so redirect them 
    if (isset($_SESSION['email'])) {
        header("Location: ../index.php");
        exit;
    } // redirect the user to the home page
    try {
        $dbConnection = DatabaseConnection::getInstance();
        $pdo = $dbConnection->getConnection();
    } catch (Exception $e) {
        error_log('[registrazione.php] DB connection failed: ' . $e->getMessage());
        $_SESSION['error_msg'] = 'Service unavailable please try again later';
        header("Location: registrazione.php");
        exit();
    } //il controllo delle sessioni lo faccio qui per comunicare tempestivamente all'utente se il tempo è finito, ma lo ripeto anche dopo
    if (isset($_SESSION['temp_email']) && isset($_SESSION['temp_password']) && isset($_SESSION['passwordAgain']) && isset($_SESSION['temp_nome']) && isset($_SESSION['temp_cognome']) && isset($_SESSION['codice'])) {
        if (time() > $_SESSION['codice_scadenza']) {
            unset($_SESSION['codice_scadenza'], $_SESSION['codice'], $_SESSION['temp_email'], $_SESSION['temp_nome'], $_SESSION['temp_cognome'], $_SESSION['temp_password'], $_SESSION['passwordAgain'], $_SESSION['tentativi'], $_SESSION['reinvii']);
            $_SESSION['error_msg'] = "Codice scaduto. Ricomincia la procedura.";
            header("Location: registrazione.php");
            exit();
        }
    }



    if (isset($_SESSION['temp_email']) && isset($_SESSION['temp_password']) && isset($_SESSION['passwordAgain']) && isset($_SESSION['temp_nome']) && isset($_SESSION['temp_cognome']) && isset($_SESSION['codice'])) {
        if (isset($_POST['code']) && !empty($_POST['code'])) {
            if (time() > $_SESSION['codice_scadenza']) {
                unset($_SESSION['codice_scadenza'], $_SESSION['codice'], $_SESSION['temp_email'], $_SESSION['temp_nome'], $_SESSION['temp_cognome'], $_SESSION['temp_password'], $_SESSION['passwordAgain'], $_SESSION['tentativi'], $_SESSION['reinvii']);
                $_SESSION['error_msg'] = "Codice scaduto. Ricomincia la procedura.";
                header("Location: registrazione.php");
                exit();
            }
            if ($_SESSION['tentativi'] <= 0) {
                $_SESSION['error_msg'] = "Tentativi esauriti, riprova";
                header("Location: registrazione.php");
                exit();
            }
            if (time() > $_SESSION['codice_scadenza']) {
            }
            $email = $_SESSION['temp_email'];
            $password = $_SESSION['temp_password'];
            $nome = $_SESSION['temp_nome'];
            $cognome = $_SESSION['temp_cognome'];
            $passwordAgain = $_SESSION['passwordAgain'];
            $codice = $_SESSION['codice'];
            if ($codice === $_POST['code']) {
                if (strlen($password) >= 8 && preg_match('{[!#$.,:;()@%^\-&_+=\[\]|\\/<>?~`]}', $password)) {
                    if (strlen($password) <= 50) {
                        if ($password === $passwordAgain) {
                            if ($query1 = $pdo->prepare('INSERT INTO Utente (Email,Nome,Cognome,Password,Utenza) VALUES(:email,:nome,:cognome,:password,:utenza)')) {
                                $password = password_hash($password, PASSWORD_BCRYPT);
                                $utenza = 4;
                                $query1->bindParam(':email', $email);
                                $query1->bindParam(':nome', $nome);
                                $query1->bindParam(':cognome', $cognome);
                                $query1->bindParam(':password', $password);
                                $query1->bindParam(':utenza', $utenza);
                                $query1->execute();

                                # <a style="color: #2ac32d;" href="login.php">Accedere</a></h2>';
                                $_SESSION['nome'] = $_SESSION['temp_nome'];
                                $_SESSION['password'] = $_SESSION['temp_password'];
                                $_SESSION['email'] = $_SESSION['temp_email'];
                                $_SESSION['cognome'] = $_SESSION['temp_cognome'];
                                unset($_SESSION['codice_scadenza'], $_SESSION['codice'], $_SESSION['temp_email'], $_SESSION['temp_nome'], $_SESSION['temp_cognome'], $_SESSION['temp_password'], $_SESSION['passwordAgain'], $_SESSION['tentativi'], $_SESSION['reinvii']);
                                $_SESSION['utenza'] = 4;
                                $query1->closeCursor();

                                $_SESSION['success_msg'] = "Registrazione completata con successo! Login automatico eseguito";
                                header("Location: login.php");
                                exit();
                            } else {
                                $_SESSION['error_msg'] = "Errore, operazione fallita";
                                $query1->closeCursor();
                                header("Location: registrazione.php");
                                exit();
                            }
                        } else {
                            $_SESSION['error_msg'] = "Le password non corrispondono";
                            header("Location: registrazione.php");
                            exit();
                        }
                    } else {
                        $_SESSION['error_msg'] = "La password supera il limite di lunghezza";
                        header("Location: registrazione.php");
                        exit();
                    }
                } else {
                    $_SESSION['error_msg'] = "La password non soddisfa i requisiti minimi di sicurezza";
                    header("Location: registrazione.php");
                    exit();
                }
            } else {
                $_SESSION['error_msg'] = "I codici non corrispondono! Hai ancora " . $_SESSION['tentativi'] . " tentativi rimasti";
                $_SESSION['tentativi'] = $_SESSION['tentativi'] - 1;
                header("Location: confermaRegistrazione.php");
                exit();
            }
        } else if (isset($_POST['reinvia'])) {
            if ($_SESSION['reinvii'] <= 0) {
                $_SESSION['error_msg'] = "reinvii mail esauriti";
                header("Location: confermaRegistrazione.php");
                exit();
            }
            $_SESSION['reinvii'] = $_SESSION['reinvii'] - 1;

            if (
                sendEmail(
                    $_SESSION['temp_email'],
                    $_SESSION['temp_nome'] . ' ' . $_SESSION['temp_cognome'],
                    'Conferma Registrazione',
                    '<h2>Conferma la registrazione!</h2>
                        <p>Ciao, ' . $_SESSION['temp_nome'] . ' ' . $_SESSION['temp_cognome'] . '</br> conferma la tua mail inserendo questo codice sul sito:</p>
                        <ul>
                            <li>La tua email: ' . $_SESSION['temp_email'] . '</li>
                            <li>Il codice di conferma: ' . $_SESSION['codice'] . '</li>
                            
                        </ul>'
                )
            ) {
                $_SESSION['ok_msg'] = "email reinviata con successo hai ancora ". $_SESSION['reinvii'] . " tentativi di riinvio mail rimasti";
                header("Location: confermaRegistrazione.php");
                exit();
            } else {
                $_SESSION['error_msg'] = "tentativo di reinvio della mail fallito :(";
                header("Location: confermaRegistrazione.php");
                exit();
            }
        }
    }

    ?>
 <!DOCTYPE html>
 <html lang="it">

 <head>
     <meta charset="UTF-8" />
     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
     <title>Conferma Registrazione</title>
     <link rel="stylesheet" href="../css/unified.css">
     <link rel="stylesheet" href="../css/registrazione.css" />
     <link rel="stylesheet" href="../css/messaggi.css" />

     <link rel="preconnect" href="https://fonts.googleapis.com" />
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
     <link
         href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
         rel="stylesheet" />
     <script src="confermaRegistrazione.js"></script>
 </head>

 <body>
     <div class="messages">
         <?php
            if (isset($_SESSION['error_msg'])) {
                echo "<p class='errore' >" . $_SESSION['error_msg'] . "</p>";
                unset($_SESSION['error_msg']);
            } elseif (isset($_SESSION['ok_msg'])) {
                echo "<p class='successo' >" . $_SESSION['ok_msg'] . "</p>";
                unset($_SESSION['ok_msg']);
            }
            ?>
     </div>

     <div class="container">
         <div class="left"></div>

         <div class="right">
             <div class="right-content">

                 <form action="confermaRegistrazione.php" method="POST">
                     <h1>Verifica la tua email</h1>

                     <p style="margin-bottom: 20px; color: #555;">
                         Abbiamo inviato un codice di conferma all'indirizzo:<br>
                         <strong><?php echo isset($_SESSION['temp_email']) ? htmlspecialchars($_SESSION['temp_email']) : 'tua email'; ?></strong>
                     </p>

                     <div>
                         <h3>CODICE DI CONFERMA</h3>
                         <input type="text" name="code" placeholder="Inserisci il codice a 8 caratteri" maxlength="8" required />
                         <h4>Inserisci il codice alfanumerico ricevuto via email per completare l'attivazione del tuo account.</h4>
                     </div>

                     <input type="submit" class="submit" name="submit_code" value="Verifica Account" />

                     <br />
                     <center>
                         <a href="registrazione.php" style="text-decoration: none; color: #333; font-size: 0.8em;">Torna alla registrazione</a>
                     </center>

                     <center>
                         <h4 class="privacy" style="margin-top: 30px;">Privacy · Termini e Condizioni</h4>
                     </center>
                 </form>
                 <div class="reinvio"></div>

             </div>
         </div>
     </div>
 </body>

 </html>