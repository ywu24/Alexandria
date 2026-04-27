<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio Segnalazioni</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
   <div id="nav-placeholder">
    <?php $root = "../..";
    require_once('../../nav/nav.php');
    require_once("../../utils/connect.php");
    ?>
  </div>
<div class="container mt-5">

<?php
// 1. Connessione tramite Singleton
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Errore di connessione: " . $e->getMessage() . "</div>";
    exit;
}

// 2. Controllo e sanificazione ID
$idSegnalazione = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idSegnalazione <= 0) {
    echo "<div class='alert alert-warning'>ID segnalazione non valido.</div>";
    exit;
}

// 3. Esecuzione query con Prepared Statement
try {
    $sql = "SELECT userEmail, Oggetto, Messaggio, imgSegn FROM Segnalazione WHERE idSegnalazione = :id";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id', $idSegnalazione, PDO::PARAM_INT);
    $query->execute();

    if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // Prepariamo l'immagine se esiste
        $imgHtml = "";
        if (!empty($row['imgSegn'])) {
            $imgHtml = "<img src='../" . htmlspecialchars($row['imgSegn']) . "' class='card-img-bottom' alt='Immagine segnalazione'>";
        }

        // Layout unificato (evitiamo duplicati di codice)
        echo "
        <div class='text-center mb-4'>
            <h2>Dettagli Segnalazione n° " . $idSegnalazione . "</h2>
            <h5>Utente: " . htmlspecialchars($row['userEmail']) . "</h5>
            <hr>
        </div>

        <div class='row justify-content-center'>
            <div class='col-md-6'>
                <div class='card shadow-sm'>
                    <div class='card-body'>
                        <h5 class='card-title text-primary'>" . htmlspecialchars($row['Oggetto']) . "</h5>
                        <p class='card-text'>" . nl2br(htmlspecialchars($row['Messaggio'])) . "</p>
                        <a href='eliminaSegnalazione.php?id=" . $idSegnalazione . "' 
                           class='btn btn-danger btn-block mt-3' 
                           onclick='return confirm(\"Sei sicuro di voler eliminare questa segnalazione?\")'>
                           <i class='fa fa-trash'></i> Elimina Segnalazione
                        </a>
                    </div>
                    " . $imgHtml . "
                </div>
            </div>
        </div>";

    } else {
        echo "<div class='alert alert-info text-center'>Segnalazione non trovata.</div>";
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Errore durante il recupero: " . $e->getMessage() . "</div>";
}
?>

</div> </body>
</html>