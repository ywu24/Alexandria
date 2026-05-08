<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio Segnalazione - Alexandria's Library</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/colors.css">
    <link rel="stylesheet" href="../../css/messaggi.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
   <div id="nav-placeholder">
    <?php 
    $root = "../..";
    session_start();
    require_once("../../utils/connect.php");
    require_once("../../auth/cookies.php");
    require_once('../../nav/nav.php');
    ?>
  </div>

<div class="container py-5">

<?php
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "<div class='alert alert-danger shadow-sm'>Errore di connessione: " . $e->getMessage() . "</div>";
    exit;
}

$idSegnalazione = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idSegnalazione <= 0) {
    echo "<div class='alert alert-warning shadow-sm'>ID segnalazione non valido.</div>";
    exit;
}

try {
    $sql = "SELECT userEmail, Oggetto, Messaggio, imgSegn FROM Segnalazione WHERE idSegnalazione = :id";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id', $idSegnalazione, PDO::PARAM_INT);
    $query->execute();

    if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // Prepariamo l'immagine se esiste
        $imgHtml = "";
        if (!empty($row['imgSegn'])) {
            $imgHtml = "
            <div class='p-3 bg-white border-top text-center'>
                <p class='text-muted small mb-2'>Allegato:</p>
                <img src='../../img/segnalazioni/" . htmlspecialchars($row['imgSegn']) . "' class='img-fluid rounded shadow-sm' alt='Immagine segnalazione' style='max-height: 400px;'>
            </div>";
        }

        echo "
        <!-- Header Uniformato -->
        <div class='text-center mb-5'>
            <h1 class='display-4 font-weight-bold'>Dettaglio Segnalazione</h1>
            <p class='lead text-muted'>Gestione pratica n° " . $idSegnalazione . "</p>
        </div>

        <div class='row justify-content-center'>
            <div class='col-lg-8'>
                <div class='card border-0 shadow-sm overflow-hidden'>
                    <!-- Header Nero coerente -->
                    <div class='card-header bg-dark text-white p-3 font-weight-bold d-flex justify-content-between align-items-center'>
                        <span>Mittente: " . htmlspecialchars($row['userEmail']) . "</span>
                        <i class='fas fa-info-circle'></i>
                    </div>
                    
                    <div class='card-body p-4'>
                        <h4 class='text-primary font-weight-bold mb-3'>" . htmlspecialchars($row['Oggetto']) . "</h4>
                        <div class='p-3 bg-light rounded text-secondary' style='min-height: 100px;'>
                            " . nl2br(htmlspecialchars($row['Messaggio'])) . "
                        </div>
                        
                        <div class='row mt-4'>
                            <div class='col-md-6 mb-2'>
                                <a href='dashboardSegnalazioni.php' class='btn btn-outline-secondary btn-block shadow-none'>
                                    <i class='fas fa-arrow-left mr-2'></i> Torna Indietro
                                </a>
                            </div>
                            <div class='col-md-6 mb-2'>
                                <a href='eliminaSegnalazione.php?id=" . $idSegnalazione . "' 
                                   class='btn btn-danger btn-block shadow-none' 
                                   onclick='return confirm(\"Sei sicuro di voler eliminare questa segnalazione?\")'>
                                   <i class='fas fa-trash-alt mr-2'></i> Elimina Pratica
                                </a>
                            </div>
                        </div>
                    </div>
                    " . $imgHtml . "
                </div>
            </div>
        </div>";

    } else {
        echo "<div class='alert alert-info text-center shadow-sm'>Segnalazione non trovata.</div>";
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger shadow-sm'>Errore durante il recupero: " . $e->getMessage() . "</div>";
}
?>

</div> 
</body>
</html>