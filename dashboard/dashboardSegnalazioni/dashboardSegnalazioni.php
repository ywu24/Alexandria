<?php //LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = "../..";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Segnalazioni</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="../../css/nav.css">
  <link rel="stylesheet" href="../../css/messaggi.css">
  <link rel="stylesheet" href="../../css/dashboardSegnalazioni.css">
  <link rel="shortcut icon" href="../../img/segnDashFavicon.png" type="image/x-icon">
  <script src="https://code.jquery.com/jquery-1.12.2.js"></script>
</head>

<body>
  <div id="nav-placeholder">
    <?php 
    require_once('../../nav/nav.php');
    
    ?>
  </div>
  <div class="messages">
    <?php
    if(isset($_SESSION['errore'])){
      echo "<p class='errore'>ERRORE: ".$_SESSION['errore'] ."</p>";
    }
    else if(isset($_SESSION['successo'])){
      echo "<p class='successo'>".$_SESSION['successo'] ."</p>";
    }
    ?>
  </div>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <h2>Segnalazioni Utenti</h2>
        <hr>
      </div>
    </div>


    <div class="row">

      <?php
      
      
      try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
      } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
      }

      
      try {
        $sql = "SELECT idSegnalazione, userEmail, Oggetto FROM Segnalazione";
        $query = $pdo->prepare($sql);
        $query->execute();

        // Verifichiamo se ci sono risultati
        if ($query->rowCount() > 0) {
          while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            echo "
            <div class='col-md-6'>
                <div class='card mb-3'>
                    <div class='card-body'>
                        <h5 class='card-title'>Segnalazione n°" . $row['idSegnalazione'] . "</h5>
                        <h6 class='card-subtitle mb-2 text-muted'>" . $row['userEmail'] . "</h6>
                        <p class='card-text'>" . $row['Oggetto'] . "</p>
                        <a href='dettaglio.php?id=" . $row['idSegnalazione'] . "' class='card-link'>Dettagli</a>
                    </div>
                </div>
            </div>";
          }
        } else {
          echo "<p class='text-center'>Nessuna segnalazione trovata.</p>";
        }
      } catch (Exception $e) {
        echo "Errore durante il recupero delle segnalazioni: " . $e->getMessage();
        exit;
      }
      ?>
    </div>

    <!-- 

  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Segnalazione n°" . $row['idSegnalazione'] . "</h5>
          <h6 class="card-subtitle mb-2 text-muted">" . $row['userEmail'] . "</h6>
          <p class="card-text">" . $row['Oggetto'] . "</p>
          <a href="#" class="card-link">Dettagli</a>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Segnalazione 456</h5>
          <h6 class="card-subtitle mb-2 text-muted">Oggetto della segnalazione</h6>
          <p class="card-text">Breve descrizione della segnalazione.</p>
          <a href="dettagli.html" class="card-link">Dettagli</a>
        </div>
      </div>
    </div>
  </div> -->
</div>

</body>

</html>