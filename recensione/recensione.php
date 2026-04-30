<?php
session_start();
$root = '..';
require_once("../utils/connect.php");

// Controllo ID Opera
if (!isset($_GET['id'])) {
    header("Location: ../lista/lista.php");
    exit();
}

$idOpera = (int)$_GET['id'];

// Istanza del database tramite PDO (come nel tuo esempio)
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    die("Errore durante la connessione al database: " . $e->getMessage());
}

if (isset($_POST['titolo']) && isset($_POST['messaggio']) && isset($_POST['voto'])) {
    // Controllo se l'utente è loggato
    if (!isset($_SESSION['email'])) {
        header("Location: ../login/login.php");
        exit();
    }    

    $user_email = $_SESSION['email'];
    $titolo = $_POST['titolo'];
    $messaggio = $_POST['messaggio'];
    $voto = (int)$_POST['voto'];

    try {
        // 1. Preparazione della query per la recensione
        $sql = "INSERT INTO recensione (userEmail, Titolo, Messaggio, Voto, idOpera) 
                VALUES (:email, :titolo, :messaggio, :voto, :idOpera)";
        
        $stmt = $pdo->prepare($sql);
        
        // Binding dei parametri
        $stmt->bindParam(':email', $user_email);
        $stmt->bindParam(':titolo', $titolo);
        $stmt->bindParam(':messaggio', $messaggio);
        $stmt->bindParam(':voto', $voto, PDO::PARAM_INT);
        $stmt->bindParam(':idOpera', $idOpera, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $sqlPunti = "UPDATE utente SET punteggio = punteggio + 5 WHERE Email = :email";
            $stmtPunti = $pdo->prepare($sqlPunti);
            $stmtPunti->bindParam(':email', $user_email);
            $stmtPunti->execute();
            $stmtPunti->closeCursor();

            $_SESSION['success_msg'] = "Recensione inviata con successo!";
            $_SESSION['punti_guadagnati'] = true; 

        } else {
            $_SESSION['error_msg'] = "Errore durante l'invio della recensione. Riprova.";
        }
        
        $stmt->closeCursor();

    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Errore database: " . $e->getMessage();
    }

    header("Location: recensione.php?id=$idOpera");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="../nav/nav.css">
  <link rel="stylesheet" href="../css/segnalazione.css">
  <link rel="stylesheet" href="../css/messaggi.css">
  <link rel="stylesheet" href="../css/recensioni.css">
  <script src="https://kit.fontawesome.com/455452defb.js" crossorigin="anonymous"></script>
  <link rel="icon" type="image/x-icon" href="../img/feedbackFavicon.png">
  <title>Lascia una recensione</title>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="./recensione.js" defer></script>
</head>

<body>

  <div id="nav-placeholder">
    <?php require_once("../nav/nav.php"); ?>
  </div>
  
  <div id="messages" class="container mt-3 text-center">
    <?php
    if (isset($_SESSION['success_msg'])) {
      echo '<p class="successo">' . htmlspecialchars($_SESSION['success_msg']) . '</p>';
      unset($_SESSION['success_msg']);
    }

    if (isset($_SESSION['error_msg'])) {
      echo '<p class="errore">' . htmlspecialchars($_SESSION['error_msg']) . '</p>';
      unset($_SESSION['error_msg']);
    }

    // Controlliamo se dobbiamo attivare il trigger per i punti
    if (isset($_SESSION['punti_guadagnati'])) {
        echo '<script> const puntiGuadagnati = true; </script>';
        unset($_SESSION['punti_guadagnati']);
    } else {
        echo '<script> const puntiGuadagnati = false; </script>';
    }
    ?>
  </div>

  <div class="centered-form">
    <div class="form-container mt-4 mb-5 p-4 bg-white shadow rounded" style="max-width: 600px; margin: 0 auto;">
      <h2 class="text-center mb-4"><i class="fas fa-star text-warning"></i> La tua recensione</h2>
      
      <form action="recensione.php?id=<?php echo $idOpera; ?>" method="POST">
        
        <div class="form-group">
          <label><strong>Valutazione:</strong></label>
          <div class="rating-css">
            <input type="radio" id="star5" name="voto" value="5" required>
            <label for="star5" title="5 Stelle"><i class="fas fa-star"></i></label>
            
            <input type="radio" id="star4" name="voto" value="4">
            <label for="star4" title="4 Stelle"><i class="fas fa-star"></i></label>
            
            <input type="radio" id="star3" name="voto" value="3">
            <label for="star3" title="3 Stelle"><i class="fas fa-star"></i></label>
            
            <input type="radio" id="star2" name="voto" value="2">
            <label for="star2" title="2 Stelle"><i class="fas fa-star"></i></label>
            
            <input type="radio" id="star1" name="voto" value="1">
            <label for="star1" title="1 Stella"><i class="fas fa-star"></i></label>
          </div>
        </div>

        <div class="form-group">
          <label for="titolo"><strong>Titolo della recensione:</strong></label>
          <input type="text" class="form-control" id="titolo" name="titolo" maxlength="50" placeholder="Riassumi la tua esperienza" required>
          <small class="text-muted" id="titolo-counter">Caratteri rimanenti: 50</small>
        </div>
        
        <div class="form-group">
          <label for="messaggio"><strong>Scrivi la tua recensione:</strong></label>
          <textarea class="form-control" id="messaggio" name="messaggio" rows="5" maxlength="500" placeholder="Cosa ti è piaciuto o non ti è piaciuto?" required></textarea>
          <small class="text-muted" id="messaggio-counter">Caratteri rimanenti: 500</small>
        </div>
        
        <button type="submit" class="btn btn-warning btn-block text-white font-weight-bold">Pubblica recensione</button>
      </form>
    </div>
  </div>

</body>
</html>
