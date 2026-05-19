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
        ['js/utils.js', 'js/modalUtils.js', 'js/updateCopies.js'],
        '../..'
    ); ?>
    <link rel="icon" type="image/svg+xml" href="../../img/bookDashFavicon.svg">
</head>

<body class="dashboard-table">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>

    <div class="container-fluid py-5">
        <div id="messages">
        <?php render_messages(); ?>
        </div>

        <div class="mb-5 text-center">
            <h1 class="text-center mb-5 font-weight-extra-bold">Dashboard Libri</h1>
            <p class="lead text-muted">Gestione del catalogo libri, disponibilità e monitoraggio delle copie</p>
        </div>

        <form id="filtriForm" class="form-inline mx-auto mb-4" onsubmit="return false;">
            <input id="searchInput" class="form-control mr-sm-2 searchbar" type="search" name="search" placeholder="Cerca Libro" aria-label="Cerca">
            <input type="hidden" name="sort_type" id="sort_type" value="id">
            <button id="searchBtn" class="btn btn-outline-info" type="button">Cerca</button>
        </form>

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
                        <th scope="col" class="col-nascondi col-copie">Copie <button class="sort_btn" data-sort="copie">&#9650;</button></th>
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

    <div class="d-flex justify-content-center w-100 my-4">
        <button id="loadMoreBtn" class="btn btn-outline-primary shadow-sm" style="display:none;">
            Carica Altro...
        </button>
    </div>
    <?php require_once('../../nav/footer.php'); ?>
</body>

</html>
