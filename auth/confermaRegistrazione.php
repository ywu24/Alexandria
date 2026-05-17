<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Email confirmation page for registration
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

// Check if temp session data exists
$hasTempData = isset(
    $_SESSION['temp_email'],
    $_SESSION['temp_password'],
    $_SESSION['passwordAgain'],
    $_SESSION['temp_nome'],
    $_SESSION['temp_cognome'],
    $_SESSION['codice']
);

// Clear expired sessions
if ($hasTempData && time() > $_SESSION['codice_scadenza']) {
    unset(
        $_SESSION['codice_scadenza'],
        $_SESSION['codice'],
        $_SESSION['temp_email'],
        $_SESSION['temp_nome'],
        $_SESSION['temp_cognome'],
        $_SESSION['temp_password'],
        $_SESSION['passwordAgain'],
        $_SESSION['tentativi'],
        $_SESSION['reinvii']
    );
    flash('error', 'Codice scaduto. Ricomincia la procedura.');
    redirect('registrazione.php');
}

if (!$hasTempData) {
    flash('error', 'Sessione non valida. Ricomincia la procedura.');
    redirect('registrazione.php');
}

// Handle code verification
if (isset($_POST['code']) && !empty($_POST['code'])) {
    if ($_SESSION['tentativi'] <= 0) {
        flash('error', 'Tentativi esauriti, riprova');
        redirect('registrazione.php');
    }

    $email = $_SESSION['temp_email'];
    $password = $_SESSION['temp_password'];
    $nome = $_SESSION['temp_nome'];
    $cognome = $_SESSION['temp_cognome'];
    $passwordAgain = $_SESSION['passwordAgain'];
    $codice = $_SESSION['codice'];

    if ($codice === $_POST['code']) {
        if (!validate_password($password)) {
            flash('error', 'La password non soddisfa i requisiti minimi di sicurezza');
            redirect('registrazione.php');
        }

        if ($password !== $passwordAgain) {
            flash('error', 'Le password non corrispondono');
            redirect('registrazione.php');
        }

        try {
            $authService->register($email, $password, $nome, $cognome, 4);

            // Auto-login after registration
            $user = $authService->authenticate($email, $password);
            if ($user) {
                $authService->login($user);
            }

            unset(
                $_SESSION['codice_scadenza'],
                $_SESSION['codice'],
                $_SESSION['temp_email'],
                $_SESSION['temp_nome'],
                $_SESSION['temp_cognome'],
                $_SESSION['temp_password'],
                $_SESSION['passwordAgain'],
                $_SESSION['tentativi'],
                $_SESSION['reinvii']
            );

            flash('success', 'Registrazione completata con successo! Login automatico eseguito');
            redirect('login.php');
        } catch (Exception $e) {
            flash('error', 'Errore durante la registrazione: ' . $e->getMessage());
            redirect('registrazione.php');
        }
    } else {
        $_SESSION['tentativi'] = $_SESSION['tentativi'] - 1;
        flash('error', 'I codici non corrispondono! Hai ancora ' . $_SESSION['tentativi'] . ' tentativi rimasti');
        redirect('confermaRegistrazione.php');
    }
}

// Handle resend email
if (isset($_POST['reinvia'])) {
    if ($_SESSION['reinvii'] <= 0) {
        flash('error', 'Reinvii mail esauriti');
        redirect('confermaRegistrazione.php');
    }

    $_SESSION['reinvii'] = $_SESSION['reinvii'] - 1;

    $sent = $notificationService->sendConfirmationEmail(
        $_SESSION['temp_email'],
        $_SESSION['temp_nome'] . ' ' . $_SESSION['temp_cognome'],
        $_SESSION['codice']
    );

    if ($sent) {
        flash('success', 'Email reinviata con successo, hai ancora ' . $_SESSION['reinvii'] . ' tentativi di riinvio mail rimasti');
    } else {
        flash('error', 'Tentativo di reinvio della mail fallito');
    }
    redirect('confermaRegistrazione.php');
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head('Conferma Registrazione',
        ['css/pages/auth.css', 'css/pages/footer.css'],
        ['js/theme.js', 'js/confermaRegistrazione.js'],
        '..'
    ); ?>
</head>

<body class="registration">
    <button id="theme-toggle" type="button" class="auth-theme-toggle" aria-label="Toggle theme">
        <svg class="icon icon-sun" style="display:none;"><use href="../img/icons.svg#sun"/></svg>
        <svg class="icon icon-moon" style="display:none;"><use href="../img/icons.svg#moon"/></svg>
    </button>
    <div class="messages">
        <?php render_messages(); ?>
    </div>

    <div class="container">
        <div class="left"></div>

        <div class="right">
            <div class="right-content">
                <form action="confermaRegistrazione.php" method="POST">
                    <h1>Verifica la tua email</h1>

                    <p style="margin-bottom: 20px; color: var(--color-text-muted);">
                        Abbiamo inviato un codice di conferma all'indirizzo:<br>
                        <strong><?php echo e($_SESSION['temp_email'] ?? 'tua email'); ?></strong>
                    </p>

                    <div>
                        <h3>CODICE DI CONFERMA</h3>
                        <input type="text" name="code" placeholder="Inserisci il codice a 8 caratteri" maxlength="8" required />
                        <h4>Inserisci il codice alfanumerico ricevuto via email per completare l'attivazione del tuo account.</h4>
                    </div>

                    <input type="submit" class="submit" name="submit_code" value="Verifica Account" />

                    <br />
                    <center>
                        <a href="registrazione.php" style="text-decoration: none; color: var(--color-text); font-size: 0.8em;">Torna alla registrazione</a>
                    </center>

                    <center>
                        <h4 class="privacy" style="margin-top: 30px;">Privacy &middot; Termini e Condizioni</h4>
                    </center>
                </form>
                <div class="reinvio"></div>
            </div>
        </div>
    </div>
    <?php require_once('../nav/footer.php'); ?>
</body>

</html>
