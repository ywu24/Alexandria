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

    if (!validate_email($email)) {
        flash('error', 'Email non valida');
        redirect('registrazione.php');
    }
    
    if ($password !== $passwordAgain) {
        flash('error', 'Le password non coincidono');
        redirect('registrazione.php');
    }
    
    if (!validate_password($password)) {
       flash('error', 'La password non rispetta i requisiti minimi di sicurezza');
       redirect('registrazione.php');
    }

    if ($authService->emailExists($email)) {
        flash('error', 'Utente già registrato');
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
      ['js/utils.js', 'js/registration.js'],
      '..'
  ); ?>
</head>

<body>
  <div id="nav-placeholder">
    <?php require_once('../nav/nav.php'); ?>
  </div>

  <main class="auth-main">
    <div class="auth-container">
      <div class="card auth-card">
        <div class="card-body">
          <form action="registrazione.php" method="POST" id="myform">
            <div class="text-center mb-lg">
              <h1>Crea un account</h1>
              <p class="text-muted">Unisciti alla nostra comunità per accedere a tutti i servizi</p>
            </div>

            <div id="messages">
              <?php render_messages(); ?>
            </div>

            <div class="form-group">
              <label class="form-label">NOME</label>
              <input type="text" name="nome" class="form-control" placeholder="Inserisci il tuo nome" required />
            </div>

            <div class="form-group">
              <label class="form-label">COGNOME</label>
              <input type="text" name="cognome" class="form-control" placeholder="Inserisci il tuo cognome" required />
            </div>

            <div class="form-group">
              <label class="form-label">EMAIL</label>
              <input type="email" name="email" class="form-control" placeholder="Inserisci il tuo indirizzo email" required />
            </div>

            <div class="form-group">
              <label class="form-label">PASSWORD</label>
              <input type="password" name="password" class="form-control" placeholder="Inserisci la tua password" required />
              <p class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                La password deve avere lunghezza compresa tra 8 e 50 caratteri e contenere almeno un carattere speciale, es. !#$.,:;()
              </p>
            </div>

            <div class="form-group">
              <label class="form-label">CONFERMA PASSWORD</label>
              <input type="password" name="passwordAgain" class="form-control" placeholder="Conferma la tua password" required />
            </div>

            <div class="text-center">
              <button type="submit" name="submit" class="btn btn-primary btn-lg w-100">Registrami</button>
            </div>

            <div class="auth-footer text-center">
              <p>Hai già un account? <a href="login.php">Accedi qui</a></p>
              <div class="privacy">
                <p class="text-muted">Privacy &middot; Termini e Condizioni</p>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php require_once('../nav/footer.php'); ?>
</body>

</html>
