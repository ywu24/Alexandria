<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Registration page with email confirmation
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\NotificationService;

$authService = new AuthService($pdo);
$notificationService = new NotificationService();

$root = '..';

if ($authService->isAuthenticated()) {
    redirect('../index.php');
}

if (isset($_POST['submit'])) {
    $required = ['email', 'password', 'nome', 'cognome', 'passwordAgain'];
    $missing = validate_required($required, $_POST);

    if (!empty($missing)) {
        flash('error', 'Non possono esserci campi vuoti');
        redirect('registrazione.php');
    }

    $email = $_POST['email'];
    $password = $_POST['password'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $passwordAgain = $_POST['passwordAgain'];

    if ($password !== $passwordAgain) {
        flash('error', 'Le due password non corrispondono!');
        redirect('registrazione.php');
    }

    if ($authService->emailExists($email)) {
        flash('error', 'Utente gia registrato');
        redirect('registrazione.php');
    }

    $codice = $authService->generateConfirmationCode();

    $sent = $notificationService->sendConfirmationEmail(
        $email,
        $nome . ' ' . $cognome,
        $codice
    );

    if ($sent) {
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_password'] = $password;
        $_SESSION['temp_nome'] = $nome;
        $_SESSION['temp_cognome'] = $cognome;
        $_SESSION['passwordAgain'] = $passwordAgain;
        $_SESSION['codice'] = $codice;
        $_SESSION['codice_scadenza'] = time() + 600;
        $_SESSION['tentativi'] = 3;
        $_SESSION['reinvii'] = 1;
        redirect('confermaRegistrazione.php');
    } else {
        flash('error', 'Errore nell\'invio dell\'email');
        redirect('registrazione.php');
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
  <?php render_head('Registrazione',
      ['css/pages/auth.css', 'css/pages/footer.css'],
      ['js/theme.js', 'js/utils.js', 'js/registration.js'],
      '..'
  ); ?>
</head>

<body class="registration">
  <button id="theme-toggle" type="button" class="auth-theme-toggle" aria-label="Toggle theme">
    <svg class="icon icon-sun" style="display:none;"><use href="../img/icons.svg#sun"/></svg>
    <svg class="icon icon-moon" style="display:none;"><use href="../img/icons.svg#moon"/></svg>
  </button>
  <div id="messages">
    <?php render_messages(); ?>
  </div>
  <div class="container">
    <div class="left"></div>

    <div class="right">
      <div class="right-content">
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
            <h4>La password deve avere lunghezza compresa tra 8 e 50 caratteri e contenere almeno un carattere speciale, es. !#$.,:;()</h4>
          </div>
          <div>
            <h3>CONFERMA PASSWORD</h3>
            <input type="password" name="passwordAgain" placeholder="Conferma la tua password" required />
          </div>
          <input type="submit" class="submit" name="submit" value="Registrami" />

          <a href="login.php" class="login-link">Hai già un account? Accedi qui</a>

          <div style="text-align: center;">
            <h4 class="privacy">Privacy &middot; Termini e Condizioni</h4>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php require_once('../nav/footer.php'); ?>
</body>

</html>
