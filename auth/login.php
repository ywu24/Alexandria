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
      ['js/login.js'],
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
          <form action="login.php" id="myform" method="POST">
            <div class="text-center mb-lg">
              <h1>Accedi</h1>
              <p class="text-muted">Bentornato! Inserisci i tuoi dati per accedere</p>
            </div>

            <div class="form-group">
              <label class="form-label">EMAIL</label>
              <input type="email" name="email" class="form-control" placeholder="Inserisci il tuo indirizzo email" required />
            </div>

            <div class="form-group">
              <label class="form-label">PASSWORD</label>
              <input type="password" name="password" class="form-control" placeholder="Inserisci la tua password" required />
              <?php if ($error == 1): ?>
                <div class="alert alert-danger mt-sm">
                  <?php echo e($err_message); ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="text-center">
              <button type="submit" name="submit" class="btn btn-primary btn-lg w-100">Accedi</button>
            </div>

            <div class="auth-footer text-center">
              <p>Non hai un account? <a href="registrazione.php">Crea un account</a></p>
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
