<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Edit user form page
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\UserService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin()) {
    redirect('dashboardUtenti.php');
}

if (!isset($_GET['id'])) {
    redirect('dashboardUtenti.php?errore=default');
}

$userService = new UserService($pdo);
$user = $userService->getById((int) $_GET['id']);

if (!$user) {
    redirect('dashboardUtenti.php?errore=3');
}

$nome = $user['Nome'];
$cognome = $user['Cognome'];
$email = $user['Email'];
$ruolo = $user['Utenza'];

$root = '../..';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <title>Gestione Utente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/forms.css">
    <link rel="stylesheet" href="../../css/pages/footer.css">
    <link rel="stylesheet" href="../../css/utilities.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="form-page">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>
    <div class="container">
        <h2>Gestione Utente</h2>
        <?php render_messages(); ?>
        <form action="salvaModifiche.php" method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo e($nome); ?>">
            </div>
            <div class="form-group">
                <label for="cognome">Cognome:</label>
                <input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo e($cognome); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo e($email); ?>">
            </div>
            <div class="form-group">
                <label for="ruolo">Ruolo:</label>
                <select class="form-control" id="ruolo" name="ruolo">
                    <option <?php if ($ruolo == 4) echo "selected"; ?> value="4">Standard</option>
                    <option <?php if ($ruolo == 3) echo "selected"; ?> value="3">Premium</option>
                    <option <?php if ($ruolo == 2) echo "selected"; ?> value="2">Bibliotecario</option>
                    <option <?php if ($ruolo == 1) echo "selected"; ?> value="1">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password (Opzionale):</label>
                <input type="password" class="form-control" id="password" name="password" value=""
                    placeholder="Opzionale. Lasciare vuoto se non si intende cambiare password.">
            </div>
            <input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>">
            <button type="submit" class="btn btn-primary">Salva Modifiche</button>
        </form>
    </div>
    <?php require_once("../../nav/footer.php"); ?>
</body>

</html>
