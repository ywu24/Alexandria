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
    <?php render_head('Gestione Utente',
        ['css/pages/forms.css', 'css/pages/footer.css'],
        ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'],
        '../..'
    ); ?>
</head>

<body class="form-page">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0 text-white">Modifica Utente</h2>
                    </div>
                    <div class="card-body">
                        <div id="messages">
                            <?php render_messages(); ?>
                        </div>
                        <form action="salvaModifiche.php" method="post">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo e($nome); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="cognome" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo e($cognome); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" id="email" name="email" value="<?php echo e($email); ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ruolo" class="form-label">Ruolo</label>
                                    <select class="form-control" id="ruolo" name="ruolo">
                                        <option <?php if ($ruolo == 4) echo "selected"; ?> value="4">Standard</option>
                                        <option <?php if ($ruolo == 3) echo "selected"; ?> value="3">Premium</option>
                                        <option <?php if ($ruolo == 2) echo "selected"; ?> value="2">Bibliotecario</option>
                                        <option <?php if ($ruolo == 1) echo "selected"; ?> value="1">Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" value=""
                                        placeholder="Opzionale. Lasciare vuoto per non cambiare.">
                                </div>
                            </div>
                            <input type="hidden" name="id" value="<?php echo (int) $user['id']; ?>">
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="dashboardUtenti.php" class="btn btn-outline-secondary">Annulla</a>
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once("../../nav/footer.php"); ?>
</body>

</html>
