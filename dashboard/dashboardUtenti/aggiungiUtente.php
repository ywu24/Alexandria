<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Add user form page
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin()) {
    redirect('../dashboard.php');
}

$root = '../..';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php render_head('Aggiungi utente',
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
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h1 class="display-4">Aggiungi Utente</h1>
                        <p class="text-muted">Crea un nuovo account utente</p>
                </div>
                <?php render_messages(); ?>
                <div class="card">
                    <div class="card-header font-weight-bold">
                        Dati Utente
                    </div>
                    <div class="card-body">
                        <form action="insert.php" method="post">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" name="nome" id="nome" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="cognome">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email / Nome utente</label>
                                <input type="text" name="email" id="email" class="form-control" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="ruolo">Ruolo</label>
                                    <select class="form-control" id="ruolo" name="ruolo">
                                        <option value="4">Standard</option>
                                        <option value="3">Premium</option>
                                        <option value="2">Bibliotecario</option>
                                        <option value="1">Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                            </div>

                            <div class="btn-container">
                                <button type="submit" name="submit" class="btn btn-primary btn-block btn-lg">Aggiungi Utente</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="dashboardUtenti.php" class="text-secondary text-decoration-none">&larr; Torna alla Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    <?php require_once("../../nav/footer.php"); ?>
</body>
</html>
