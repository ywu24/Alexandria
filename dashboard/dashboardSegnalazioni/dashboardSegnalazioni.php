<?php 
// LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = "../..";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Segnalazioni - Alexandria's Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/dashboard.css">
    <link rel="stylesheet" href="../../css/utilities.css">
    <link rel="shortcut icon" href="../../img/segnDashFavicon.png" type="image/x-icon">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light reports-dashboard">
    <div id="nav-placeholder">
        <?php require_once('../../nav/nav.php'); ?>
    </div>

    <div class="container py-5">
        <!-- Titolo Centrato Uniformato -->
        <div class="mb-5 text-center">
            <h1 class="display-4 font-weight-bold">Segnalazioni Utenti</h1>
            <p class="lead text-muted">Gestione e monitoraggio delle problematiche riscontrate dai lettori</p>
        </div>

        <!-- Messaggi di Feedback (Ripristinati come in precedenza) -->
        <div class="messages mb-4">
            <?php
            if(isset($_SESSION['errore'])){
                echo "<p class='errore'>ERRORE: ".$_SESSION['errore'] ."</p>";
                unset($_SESSION['errore']);
            }
            else if(isset($_SESSION['successo'])){
                echo "<p class='successo'>".$_SESSION['successo'] ."</p>";
                unset($_SESSION['successo']);
            }
            ?>
        </div>

        <div class="row">
            <?php
            try {
                $pdo = DatabaseConnection::getInstance()->getConnection();
            } catch (PDOException $e) {
                echo "<div class='col-12'><p class='errore'>Errore durante la connessione al database.</p></div>";
                exit;
            }

            try {
                $sql = "SELECT idSegnalazione, userEmail, Oggetto FROM Segnalazione ORDER BY idSegnalazione DESC";
                $query = $pdo->prepare($sql);
                $query->execute();

                if ($query->rowCount() > 0) {
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        echo "
                        <div class='col-md-6 mb-4'>
                            <div class='card h-100 border-0 shadow-sm'>
                                <div class='card-header bg-dark text-white font-weight-bold d-flex justify-content-between align-items-center'>
                                    <span>Segnalazione n° " . $row['idSegnalazione'] . "</span>
                                    <i class='fa fa-exclamation-circle text-warning'></i>
                                </div>
                                <div class='card-body'>
                                    <h6 class='card-subtitle mb-3 text-primary font-weight-bold'>" . $row['userEmail'] . "</h6>
                                    <p class='card-text text-secondary'><strong>Oggetto:</strong> " . $row['Oggetto'] . "</p>
                                </div>
                                <div class='card-footer bg-white border-0 pb-3'>
                                    <a href='dettaglio.php?id=" . $row['idSegnalazione'] . "' class='btn btn-outline-primary btn-block shadow-none'>Visualizza Dettagli</a>
                                </div>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<div class='col-12 text-center py-5'><h4 class='text-muted font-weight-light'>Nessuna segnalazione trovata.</h4></div>";
                }
            } catch (Exception $e) {
                echo "<div class='col-12'><p class='errore'>Errore durante il recupero delle segnalazioni.</p></div>";
            }
            ?>
        </div>
    </div>
</body>
</html>