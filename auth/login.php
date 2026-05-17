<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Login page with email/password authentication
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;

$authService = new AuthService($pdo);

$root = '..';

if ($authService->isAuthenticated()) {
    redirect('../index.php');
}

$domain = 'alexandria.it';
$error = -1;
$err_message = '';
$message = '<a href="registrazione.php">Crea un account</a>';

if ($error === -1 && isset($_POST['submit'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = $authService->authenticate($email, $password);

    if ($user) {
        $authService->login($user, $domain);
        if ($user['Utenza'] == 1 || $user['Utenza'] == 2) {
            redirect('../dashboard/dashboard.php');
        } else {
            redirect('../index.php');
        }
    } else {
        
        $_SESSION['login_error'] = ['type' => 1, 'msg' => 'Incorrect user or password'];
        redirect('login.php');
    }
}

if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error']['type'];
    $err_message = $_SESSION['login_error']['msg'];
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
  <?php render_head('Accedi',
      ['css/pages/auth.css', 'css/pages/footer.css'],
      ['js/theme.js', 'auth/login.js'],
      '..'
  ); ?>
</head>

<body>
  <button id="theme-toggle" type="button" class="auth-theme-toggle" aria-label="Toggle theme">
    <svg class="icon icon-sun" style="display:none;"><use href="../img/icons.svg#sun"/></svg>
    <svg class="icon icon-moon" style="display:none;"><use href="../img/icons.svg#moon"/></svg>
  </button>
  <div class="container">
    <div class="left"></div>

    <div class="right">
      <div class="right-content">
        <form action="login.php" id="myform" method="POST">
          <h1>Accedi</h1>
          <div class="inputs">
            <div class="field">
              <h3>EMAIL</h3>
              <input type="text" name="email" placeholder="Inserisci il tuo indirizzo email" required />
            </div>
            <div class="field">
              <h3>PASSWORD</h3>
              <input type="password" name="password" placeholder="Inserisci la tua password" required />
              <?php if ($error == 1): ?>
                <h2 style="color: var(--color-danger);"><?php echo e($err_message); ?></h2>
              <?php endif; ?>
            </div>
          </div>
          <input type="submit" name="submit" class="submit" value="Accedi" />
          <br />
          <?php echo $message; ?>
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
