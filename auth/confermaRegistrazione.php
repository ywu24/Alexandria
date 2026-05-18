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
    $_SESSION['codice_scadenza'],
    $_SESSION['codice'],
    $_SESSION['temp_email'],
    $_SESSION['temp_nome'],
    $_SESSION['temp_cognome'],
    $_SESSION['temp_password'],
    $_SESSION['passwordAgain'],
    $_SESSION['reinviiRimasti'],
    $_SESSION['tentativiRimasti']
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
        $_SESSION['reinviiRimasti'],
        $_SESSION['tentativiRimasti'],
        $_SESSION['ultimo_invio']
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
    if ($_SESSION['tentativiRimasti'] <= 0) {
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
        if ($password !== $passwordAgain) {
            flash('error', 'Le password non corrispondono');
            redirect('registrazione.php');
        }

        if (!validate_password($password)) {
            flash('error', 'La password non soddisfa i requisiti minimi di sicurezza');
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
                $_SESSION['reinviiRimasti'],
                $_SESSION['tentativiRimasti'],  
                $_SESSION['ultimo_invio']
            );

            flash('success', 'Registrazione completata con successo! Login automatico eseguito');
            redirect('login.php');
        } catch (Exception $e) {
            flash('error', 'Errore durante la registrazione: ' . $e->getMessage());
            redirect('registrazione.php');
        }
    } else {
        $_SESSION['tentativiRimasti'] = $_SESSION['tentativiRimasti'] - 1;
        flash('error', 'I codici non corrispondono! Hai ancora ' . $_SESSION['tentativiRimasti'] . ' tentativi rimasti');
        redirect('confermaRegistrazione.php');
    }
}

// Handle resend email
if (isset($_POST['reinvia'])) {
    $ajax = isset($_POST['ajax']);
    $remainingResends = $_SESSION['reinviiRimasti'];
    $response = ['success' => false, 'message' => '', 'remaining' => $remainingResends];

    if ($remainingResends <= 0) {
        $response['message'] = 'Reinvii mail esauriti';
        if ($ajax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        flash('error', $response['message']);
        redirect('confermaRegistrazione.php');
    }

    

    if (isset($_SESSION['ultimo_invio']) && (time() - $_SESSION['ultimo_invio']) < 15) {
        $wait = 15 - (time() - $_SESSION['ultimo_invio']);
        $response['message'] = "Attendi {$wait} secondi prima di reinviare";
        if ($ajax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        flash('error', $response['message']);
        redirect('confermaRegistrazione.php');
    }


    $sent = $notificationService->sendConfirmationEmail(
        $_SESSION['temp_email'],
        $_SESSION['temp_nome'] . ' ' . $_SESSION['temp_cognome'],
        $_SESSION['codice']
    );

    if ($sent) {
        $_SESSION['reinviiRimasti'] = $_SESSION['reinviiRimasti'] - 1;
        $_SESSION['ultimo_invio'] = time();
        $remainingResends = $_SESSION['reinviiRimasti'];
        $response['success'] = true;
        $response['message'] = 'Email reinviata con successo';
        $response['remaining'] = $remainingResends;
    } else {
        $response['message'] = 'Tentativo di reinvio della mail fallito';
    }

    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    if ($sent) {
        flash('success', $response['message']);
    } else {
        flash('error', $response['message']);
    }
    redirect('confermaRegistrazione.php');
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head('Conferma Registrazione',
        ['css/pages/auth.css', 'css/pages/forms.css', 'css/pages/footer.css'],
        ['js/confirmRegistration.js'],
        '..'
    ); ?>
</head>

    <body class="form-page">
    <div id="nav-placeholder">
        <?php require_once('../nav/nav.php'); ?>
    </div>
    <div id="messages">
        <?php render_messages(); ?>
    </div>

    <main class="auth-main">
        <div class="auth-container">
            <div class="card auth-card">
                <div class="card-body">
                    <?php
                    $resendRemaining = max(0, $_SESSION['reinviiRimasti']);
                    ?>
                    <form action="confermaRegistrazione.php" method="POST">
                        <div class="text-center mb-lg">
                            <h1>Verifica la tua email</h1>
                            <p class="text-muted">Inserisci il codice ricevuto via email per completare la registrazione</p>
                        </div>

                        <div id="messages">
                            <?php render_messages(); ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">CODICE DI CONFERMA</label>
                            <input type="text" name="code" class="form-control" placeholder="Inserisci il codice a 8 caratteri" minlength="8" maxlength="8" required />
                            <p class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                Abbiamo inviato un codice di conferma all'indirizzo:<br>
                                <strong><?php echo e($_SESSION['temp_email'] ?? 'tua email'); ?></strong>
                            </p>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_code" class="btn btn-primary btn-lg w-100">Verifica Account</button>
                        </div>
                        <div class="text-center mt-4">
                            <a href="registrazione.php" class="text-secondary text-decoration-none">&larr; Torna alla Registrazione</a>
                        </div>
                        <div class="auth-footer text-center">
                            <p>Non hai ricevuto il codice?
                                <button type="button" id="reinvia-link" class="btn btn-link p-0"
                                    data-remaining-sends="<?php echo $resendRemaining; ?>"
                                    data-cooldown-until="<?php echo isset($_SESSION['ultimo_invio']) ? ($_SESSION['ultimo_invio'] + 15) : 0; ?>">
                                    Reinvia codice
                                </button>
                            </p>
                            <div id="countdown-timer" class="text-muted" style="display: none; font-size: 0.85rem; margin-top: 5px;"></div>
                            <div id="reinvia-status" class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                Reinvii rimasti: <?php echo $resendRemaining; ?>/3
                            </div>
                            <div class="privacy">
                                <p class="text-muted">Privacy &middot; Termini e Condizioni</p>
                            </div>
                        </div>
                    </form>

                    <form action="confermaRegistrazione.php" method="POST" id="reinvia-form" style="display: none;">
                        <input type="hidden" name="reinvia" value="1" />
                    </form>
                </div>
            </div>
        </div>
    </main>
        <?php require_once('../nav/footer.php'); ?>
</body>

</html>
