<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Edit book form page
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

$bookService = new BookService($pdo);

if (!isset($_GET['id'])) {
    redirect('dashboardLibri.php');
}

$book = $bookService->getByIsbn($_GET['id']);
if (!$book) {
    redirect('dashboardLibri.php?errore=1');
}

$titolo = $book['Nome'];
$autore = $book['Autore'];
$genere = $book['Genere'];
$desc = $book['Descrizione'];
$casaed = $book['CasaEditrice'];
$annopub = $book['AnnoPubblicazione'];
$isbn = $book['ISBN'];

$root = '../..';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head('Modifica libro',
        ['css/pages/forms.css', 'css/pages/footer.css'],
        ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'],
        '../..'
    ); ?>
</head>

<body class="form-page">
    <div id="nav-placeholder">
        <?php require_once("../../nav/nav.php"); ?>
    </div>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0 text-white">Modifica Libro</h2>
                    </div>
                    <div class="card-body">
                        <form action="salvaModificheLibro.php" method="post">
                            <div class="mb-3">
                                <label for="titolo" class="form-label">Titolo</label>
                                <input type="text" class="form-control" id="titolo" name="titolo" value="<?php echo e($titolo); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="autore" class="form-label">Autore</label>
                                <input type="text" class="form-control" id="autore" name="autore" value="<?php echo e($autore); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="desc" class="form-label">Descrizione</label>
                                <textarea rows="5" class="form-control" id="desc" name="desc"><?php echo e($desc); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="casaed" class="form-label">Casa Editrice</label>
                                    <input type="text" class="form-control" id="casaed" name="casaed" value="<?php echo e($casaed); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="annopub" class="form-label">Anno Pubblicazione</label>
                                    <input type="text" class="form-control" id="annopub" name="annopub" value="<?php echo e($annopub); ?>" maxlength="4">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="genere" class="form-label">Genere</label>
                                    <select class="form-control" name="genere" id="genere">
                                        <option <?php if ($genere == "Romanzo Storico") echo "selected"; ?> value="Romanzo Storico">Romanzo Storico</option>
                                        <option <?php if ($genere == "Giallo") echo "selected"; ?> value="Giallo">Giallo</option>
                                        <option <?php if ($genere == "Biografia") echo "selected"; ?> value="Biografia">Biografia</option>
                                        <option <?php if ($genere == "Avventura") echo "selected"; ?> value="Avventura">Avventura</option>
                                        <option <?php if ($genere == "Azione") echo "selected"; ?> value="Azione">Azione</option>
                                        <option <?php if ($genere == "Fantascienza") echo "selected"; ?> value="Fantascienza">Fantascienza</option>
                                        <option <?php if ($genere == "Horror") echo "selected"; ?> value="Horror">Horror</option>
                                        <option <?php if ($genere == "Umoristico") echo "selected"; ?> value="Umoristico">Umoristico</option>
                                        <option <?php if ($genere == "Distopia") echo "selected"; ?> value="Distopia">Distopia</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="isbn" class="form-label">ISBN</label>
                                    <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo e($isbn); ?>" required>
                                </div>
                            </div>
                            <input type="hidden" name="id" value="<?php echo e($isbn); ?>">
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="dashboardLibri.php" class="btn btn-outline-secondary">Annulla</a>
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
