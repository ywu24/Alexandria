<?php
session_start();
$root = "../..";
require_once("../../utils/connect.php");
require_once("../../auth/cookies.php");

if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
    header("Location: ../../index.php");
    exit;
}

// Visualizzazione messaggi GET (mantenuti per redirect da altre pagine)
$feedback = "";
if (isset($_GET['aggiunto'])) $feedback = '<p class="successo">Utente aggiunto con successo!</p>';
if (isset($_GET['rimosso'])) $feedback = '<p class="successo">Utente rimosso con successo!</p>';
if (isset($_GET['aggiornato'])) $feedback = '<p class="successo">Utente aggiornato con successo!</p>';
if (isset($_GET['errore'])) {
    $err = $_GET['errore'];
    $feedback = '<p class="errore">Operazione non riuscita (Codice: '.htmlspecialchars($err).')</p>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Utenti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/dashboard.css">
    <link rel="stylesheet" href="../../css/pages/footer.css">
    <link rel="stylesheet" href="../../css/utilities.css">
    <link rel="shortcut icon" href="../../img/userDash.png" type="image/x-icon">
</head>
<body class="dashboard-table">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>

    <div class="container-fluid">
        <?php echo $feedback; ?>
        <h1 class="text-center" style="font-size:4rem !important;">👤</h1>
        
        <!-- Form gestito interamente da getUtenti.php e JS -->
        <form id="filtriForm" class="form-inline mx-auto" style="width: 300px;" onsubmit="return false;">
            <input id="searchInput" class="form-control mr-sm-2 searchbar" type="search" name="search" placeholder="Ricerca un utente" aria-label="Cerca">
            <input type="hidden" name="sort_type" id="sort_type" value="id">
            <button id="searchBtn" class="btn btn-outline-info my-2 my-sm-0" type="button">Cerca</button>
        </form>

        <?php if ($_SESSION['utenza'] == 1): ?>
            <a href="aggiungiUtente.php" class="btn btn_adduser btn-success">Aggiungi utente</a>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <?php if ($_SESSION['utenza'] == 1): ?>
                            <th scope="col" class='col-nascondi'>#<button class="sort_btn" data-sort="id">&ensp; &#x25B2;</button></th>
                            <th scope="col">Nome<button class="sort_btn" data-sort="Nome">&ensp; &#x25B2;</button></th>
                            <th scope="col">Cognome<button class="sort_btn" data-sort="Cognome">&ensp; &#x25B2;</button></th>
                            <th scope="col" class='col-nascondi'>Email<button class="sort_btn" data-sort="Email">&ensp; &#x25B2;</button></th>
                            <th scope="col" class='col-nascondi'>Ruolo<button class="sort_btn" data-sort="Utenza">&ensp; &#x25B2;</button></th>
                            <th scope="col" class='col-nascondi'>Punteggio<button class="sort_btn" data-sort="punteggio">&ensp; &#x25B2;</button></th>
                            <th scope="col" class='col-nascondi'>Azioni</th>
                            <th scope="col" class="mobile-only mobile-toggle-col">Info</th>
                        <?php else: ?>
                            <th scope="col">Nome<button class="sort_btn" data-sort="Nome">&ensp; &#x25B2;</button></th>
                            <th scope="col">Cognome<button class="sort_btn" data-sort="Cognome">&ensp; &#x25B2;</button></th>
                            <th scope="col" class='col-nascondi'>Email<button class="sort_btn" data-sort="Email">&ensp; &#x25B2;</button></th>
                            <th scope="col" class='col-nascondi'>Punteggio<button class="sort_btn" data-sort="punteggio">&ensp; &#x25B2;</button></th>
                            <th scope="col" class="col-nascondi text-center">Azioni</th>
                            <th scope="col" class="mobile-only mobile-toggle-col">Info</th>
                        <?php endif; ?>
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
    <script>const USER_TYPE = <?php echo $_SESSION['utenza']; ?>;</script>
    <script src="dashboardUtenti.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once("../../nav/footer.php"); ?>
</body>
</html>