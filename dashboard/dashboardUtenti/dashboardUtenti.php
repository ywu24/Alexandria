<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file User management dashboard - lists users with search and pagination
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;

$authService = new AuthService($pdo);

if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

$root = '../..';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <?php render_head('Dashboard Utenti',
        ['css/pages/dashboard.css', 'css/pages/footer.css'],
        ['js/userDashboard.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'],
        '../..'
    ); ?>
    <link rel="icon" type="image/svg+xml" href="../../img/userDashFavicon.svg">
</head>
<body class="dashboard-table">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>

    <div class="container-fluid py-5">
        <div class="messages mb-4">
            <?php render_messages(); ?>
        </div>
        <div class="mb-5 text-center">
            <h1 class="text-center mb-5 font-weight-extra-bold">Dashboard Utenti</h1>
        </div>

        <!-- Form gestito interamente da dashboardUtenti.js e api/users.php -->
        <form id="filtriForm" class="form-inline mx-auto" style="width: 300px;" onsubmit="return false;">
            <input id="searchInput" class="form-control mr-sm-2 searchbar" type="search" name="search" placeholder="Cerca Utente" aria-label="Cerca">
            <input type="hidden" name="sort_type" id="sort_type" value="id">
            <button id="searchBtn" class="btn btn-outline-info my-2 my-sm-0" type="button">Cerca</button>
        </form>

        <?php if ($authService->isAdmin()): ?>
            <a href="aggiungiUtente.php" class="btn btn_adduser btn-success">Aggiungi utente</a>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col" class='col-nascondi'>#<button class="sort_btn" data-sort="id">&ensp; &#x25B2;</button></th>
                        <th scope="col">Nome<button class="sort_btn" data-sort="Nome">&ensp; &#x25B2;</button></th>
                        <th scope="col">Cognome<button class="sort_btn" data-sort="Cognome">&ensp; &#x25B2;</button></th>
                        <th scope="col" class='col-nascondi'>Email<button class="sort_btn" data-sort="Email">&ensp; &#x25B2;</button></th>
                        <th scope="col" class='col-nascondi'>Ruolo<button class="sort_btn" data-sort="Utenza">&ensp; &#x25B2;</button></th>
                        <th scope="col" class='col-nascondi'>Punteggio<button class="sort_btn" data-sort="punteggio">&ensp; &#x25B2;</button></th>
                        <th scope="col" class='col-nascondi'>Azioni</th>
                        <th scope="col" class="mobile-only mobile-toggle-col">Info</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!-- Contenuto dinamico -->
                </tbody>
            </table>
        </div>

       <div class="d-flex justify-content-center w-100 my-5">
    <button id="caricaAltro" class="btn btn-outline-primary shadow-sm" style="display:none; min-width: 200px;">
        Carica Altro...
    </button>
</div>
    </div>
    <script>const USER_TYPE = <?php echo (int) ($authService->getCurrentUserType() ?? 0); ?>;</script>
    <?php require_once('../../nav/footer.php'); ?>
</body>
</html>
