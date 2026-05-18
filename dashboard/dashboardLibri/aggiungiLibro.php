<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Add book form page
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

$msg = '';
if (isset($_SESSION['libroEsiste'])) {
    if ($_SESSION['libroEsiste']) {
        $msg = '<div id="messages">
                    <p class="successo"> Trovato! Il libro esiste già nel DataBase.</p>
                </div>';
    } else {
        $msg = '<div id="messages" role="alert">
                    <p class="errore"> Il libro non è presente. Compila tutti i campi.</p>
                </div>';
    }
    unset($_SESSION['libroEsiste']);
}

$root = '../..';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head(
        'Aggiungi Libro | Dashboard',
        ['css/pages/forms.css', 'css/pages/footer.css'],
        ['https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', 'js/report.js'],
        '../..'
    ); ?>
</head>

<body class="form-page">

    <div id="nav-placeholder">
        <?php require_once("../../nav/nav.php"); ?>
    </div>
    <?php echo $msg; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="text-center mb-4">
                    <h1 class="display-4">Aggiungi Libro</h1>
                    <p class="text-muted">Inserisci una nuova opera o aggiungi copie al catalogo</p>
                </div>

                <div class="card mb-4">
                    <div class="card-header font-weight-bold">
                        Verifica Esistenza
                    </div>
                    <div class="card-body">
                        <form action="check.php" method="post" class="form-inline justify-content-center">
                            <div class="input-group w-100">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">ISBN</span>
                                </div>
                                <input type="text" name="isbn-check" class="form-control"
                                    placeholder="Inserisci ISBN per controllare..." required>
                                <div class="input-group-append">
                                    <button type="submit" name="check" class="btn btn-secondary">Controlla</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header font-weight-bold">
                        Dettagli Opera
                    </div>
                    <div class="card-body">
                        <form action="insertLibro.php" method="post" enctype="multipart/form-data">

                            <div class="form-group">
                                <label for="isbn">ISBN</label>
                                <input type="text" name="isbn" id="isbn" class="form-control"
                                    placeholder="es. 9788804668237" required>
                            </div>

                            <div class="form-group">
                                <label for="titolo">Titolo</label>
                                <input type="text" name="titolo" id="titolo" class="form-control"
                                    placeholder="Il nome dell'opera" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="autore">Autore</label>
                                    <input type="text" class="form-control" id="autore" name="autore"
                                        placeholder="Nome e Cognome" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="genere">Genere</label>
                                    <select class="form-control" name="genere" id="genere" required>
                                        <option value="" disabled selected>Scegli...</option>
                                        <option value="Romanzo Storico">Romanzo Storico</option>
                                        <option value="Giallo">Giallo</option>
                                        <option value="Biografia">Biografia</option>
                                        <option value="Avventura">Avventura</option>
                                        <option value="Azione">Azione</option>
                                        <option value="Fantascienza">Fantascienza</option>
                                        <option value="Horror">Horror</option>
                                        <option value="Umoristico">Umoristico</option>
                                        <option value="Distopia">Distopia</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="desc">Descrizione / Trama</label>
                                <textarea rows="4" class="form-control" name="desc"
                                    placeholder="Breve riassunto del libro..." required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="casaed">Casa Editrice</label>
                                    <input type="text" class="form-control" id="casaed" name="casaed" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="annopub">Anno</label>
                                    <input type="text" class="form-control" id="annopub" name="annopub" maxlength="4"
                                        placeholder="AAAA" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="qty">Copie</label>
                                    <input type="number" class="form-control" id="qty" name="qty" value="1" min="1"
                                        max="50" required>
                                </div>
                            </div>


                            <div class="form-group mb-4">
                                <label for="image" class="form-label">Immagine di Copertina</label>
                                <div class="custom-file">
                                    <input type="file" class="form-control" id="file" name="image" id="image" accept="image/*"
                                    onchange="previewImage(event)">
                                </div>
        
                                <div class="preview-image-container text-center mx-auto">
                                    <i id="trash-btn"
                                        class="delete-icon fas fa-trash bg-danger text-white rounded-pill p-2"
                                        onclick="deleteImage()" title="Rimuovi Immagine"></i>
                                    <img id="image-preview" class="preview-image d-none" src="#" alt="Anteprima">
                                </div>
                            </div>
                            <div class="btn-container">
                                <button type="submit" name="btnOpera" class="btn btn-primary btn-block btn-lg">
                                    Salva Opera nel Database
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="dashboardLibri.php" class="text-secondary text-decoration-none">&larr; Torna alla
                        Dashboard</a>
                </div>

            </div>
        </div>
    </div>

    <?php require_once("../../nav/footer.php"); ?>
</body>

</html>