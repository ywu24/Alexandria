<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function generaCodice($lunghezza = 8)
{
  // Definiamo i caratteri permessi (abbiamo tolto 0, O, 1, I per evitare confusioni)
  $caratteri = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
  $codice = '';
  $max = strlen($caratteri) - 1;

  for ($i = 0; $i < $lunghezza; $i++) {
    // random_int è sicuro a livello crittografico
    $codice .= $caratteri[random_int(0, $max)];
  }

  return $codice;
}

// Utilizzo:
session_start();
$root = '..';
require_once("../utils/connect.php");
require_once("cookies.php");
require_once("../utils/mailer.php");
// check to see if there is a user already logged in, if so redirect them 
if (isset($_SESSION['email'])) {
  header("Location: ../index.php");
  exit; // redirect the user to the home page
}

if (isset($_POST['submit'])) {
  if (
    !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['nome']) || !isset($_POST['cognome']) || !isset($_POST['passwordAgain']) ||
    empty($_POST['email']) || empty($_POST['password']) || empty($_POST['nome']) || empty($_POST['cognome']) || empty($_POST['passwordAgain'])
  ) {
    $_SESSION['error_msg'] = "Non possono esserci campi vuoti ";
    header("Location: registrazione.php");
    exit();
  }
  $email = $_POST['email'];
  $password = $_POST['password'];
  $nome = $_POST['nome'];
  $cognome = $_POST['cognome'];
  $passwordAgain = $_POST['passwordAgain'];
  $codice = "";
  if ($password != $passwordAgain) {
    $_SESSION['error_msg'] = 'Le due password non corrispondono!';
    header("Location: registrazione.php");
    exit();
  }
  try {
    $dbConnection = DatabaseConnection::getInstance();
    $pdo = $dbConnection->getConnection();
  } catch (Exception $e) {
    error_log('[registrazione.php] DB connection failed: ' . $e->getMessage());
    $_SESSION['error_msg'] = 'Service unavailable please try again later';
    header("Location: registrazione.php");
    exit();
  }

  if ($query = $pdo->prepare('SELECT Email FROM Utente WHERE Email = :email')) {
    $query->bindParam(':email', $email);
    $query->execute();
    $result = $query->fetch();
    $query->closeCursor();
    if ($result) {
      $_SESSION['error_msg'] = "Utente già registrato";
      header("Location: registrazione.php");
      exit();
    } else {
      $codice = generaCodice();
      if (
        sendEmail(
          $email,
          $_POST['nome'] . ' ' . $_POST['cognome'],
          'Conferma Registrazione',
          '<h2>Conferma la registrazione!</h2>
                        <p>Ciao, ' . $_POST['nome'] . ' ' . $_POST['cognome'] . '</br> conferma la tua mail inserendo questo codice sul sito:</p>
                        <ul>
                            <li>La tua email: ' . $email . '</li>
                            <li>Il codice di conferma: ' . $codice . '</li>
                            
                        </ul>'
        )
      ) {
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_password'] = $password;
        $_SESSION['temp_nome'] = $nome;
        $_SESSION['temp_cognome'] = $cognome;
        $_SESSION['passwordAgain'] = $passwordAgain;
        $_SESSION['codice'] = $codice;
        $_SESSION['codice_scadenza'] = time() + 600; // Valido per 10 minuti (600 secondi)
        $_SESSION['tentativi'] = 3;
        $_SESSION['reinvii'] = 1;

        #echo "<p class='successo'>Email mandato all'utente/p>"; // per debug
        #sleep(2);
        header("Location: confermaRegistrazione.php");
        exit();
      } else {
        $_SESSION['error_msg'] = "Errore nell'invio dell'email";
        header("Location: registrazione.php");
        exit();
      }
    }

  } else {
    $_SESSION['error_msg'] = "Errore, operazione fallita";
    header("Location: registrazione.php");
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrazione</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/design-system.css">
  <link rel="stylesheet" href="../css/components.css">
  <link rel="stylesheet" href="../css/layout.css">
  <link rel="stylesheet" href="../css/pages/auth.css">
  <link rel="stylesheet" href="../css/pages/footer.css">
  <link rel="stylesheet" href="../css/utilities.css">
  <script src="../js/theme.js"></script>
  <script src="registrazione.js"></script>
</head>

<body class="registration">
  <div id="messages">
    <?php
    if (isset($_SESSION['success_msg'])) {
      echo "<p class='successo'>" . $_SESSION['success_msg'] . "</p>";
      unset($_SESSION['success_msg']);
    } else if (isset($_SESSION['error_msg'])) {
      echo "<p class='errore'>" . $_SESSION['error_msg'] . "</p>";
      unset($_SESSION['error_msg']);
    }
    ?>
  </div>
  <div class="container">
    <div class="left"></div>

    <div class="right">
      <div class="right-content">

        <!-- L'attributo action è stato modificato in confermaRegistrazione.php -->
        <form action="registrazione.php" method="POST" id="myform">
          <h1>Crea un account</h1>
          <div>
            <h3>NOME</h3>
            <input type="text" name="nome" placeholder="Inserisci il tuo nome" required />
          </div>
          <div>
            <h3>COGNOME</h3>
            <input type="text" name="cognome" placeholder="Inserisci il tuo cognome" required />
          </div>
          <div>
            <h3>EMAIL</h3>
            <input type="email" name="email" placeholder="Inserisci il tuo indirizzo email" required />
          </div>
          <div>
            <h3>PASSWORD</h3>
            <input type="password" name="password" placeholder="Inserisci la tua password" required />
            <h4>La password deve avere lunghezza compresa tra 8 e 50 caratteri e contenere almeno un carattere speciale,
              es.
              !#$.,:;()</h4>
          </div>
          <div>
            <h3>CONFERMA PASSWORD</h3>
            <input type="password" name="passwordAgain" placeholder="Conferma la tua password" required />
          </div>
          <input type="submit" class="submit" name="submit" value="Registrami" />

          <a href="login.php" class="login-link">Hai già un account? Accedi qui</a>

          <div style="text-align: center;">
            <h4 class="privacy">Privacy · Termini e Condizioni</h4>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php require_once("../nav/footer.php"); ?>
</body>

</html>