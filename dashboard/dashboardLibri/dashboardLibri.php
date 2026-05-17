<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Book management dashboard - lists books with search and pagination
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
    <?php render_head('Dashboard Libri',
        ['css/pages/dashboard.css', 'css/pages/footer.css'],
        ['js/utils.js', 'js/aggiornaCopie.js'],
        '../..'
    ); ?>
    <link rel="icon" type="image/svg+xml" href="../../img/bookDashFavicon.svg">
</head>

<body class="dashboard-table">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>

    <div id="messages"></div>

    <div class="container-fluid">
        <h1 class="text-center" style="font-size:4rem !important;">&#128218;</h1>

        <div class="d-flex justify-content-between mb-4">
            <!-- Cerca gestito da JS -->
            <div class="form-inline mx-auto">
                <input class="form-control mr-sm-2 searchbar" type="search" id="searchInput"
                    placeholder="Cerca libro...">
                <button class="btn btn-outline-info" id="searchBtn" type="button">Cerca</button>
            </div>
        </div>

        <?php if ($authService->isAdmin()): ?>
            <a href="aggiungiLibro.php" class="btn btn_addlibro btn-success">Aggiungi Libro</a>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">ISBN <button class="sort_btn" data-sort="ISBN">&#9650;</button></th>
                        <th scope="col">Titolo <button class="sort_btn" data-sort="Nome">&#9650;</button></th>
                        <th scope="col" class="col-nascondi">Autore <button class="sort_btn"
                                data-sort="Autore">&#9650;</button></th>
                        <th scope="col" class="col-nascondi">Genere <button class="sort_btn"
                                data-sort="Genere">&#9650;</button></th>
                        <th scope="col" class="col-nascondi">Anno <button class="sort_btn"
                                data-sort="AnnoPubblicazione">&#9650;</button></th>
                        <th scope="col" class="col-nascondi">Casa Editrice <button class="sort_btn"
                                data-sort="Genere">&#9650;</button></th>
                        <th scope="col" class="col-nascondi">Copie <button class="sort_btn" data-sort="copie">&#9650;</button>
                        </th>
                        <th scope="col" class="col-nascondi">Azioni </th>
                        <th scope="col" class="mobile-only">Info</th>
                    </tr>
                </thead>
                <tbody id="libriTableBody">
                    <!-- I dati verranno inseriti qui tramite JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center my-4">
        <button id="loadMoreBtn" class="btn btn-outline-primary shadow-sm" style="display:none;">
            Carica Altro...
        </button>
    </div>
    <?php require_once('../../nav/footer.php'); ?>
</body>

</html>
