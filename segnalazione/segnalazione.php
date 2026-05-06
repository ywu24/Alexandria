<?php
session_start();
$root = '..';
require_once("../utils/connect.php");
require_once("../utils/mailer.php");

$msg = "";

if (isset($_SESSION['success_msg'])) {
  $msg = '<div class="messages fade show" >
            <p class= "successo">
            <strong>Successo!:</strong> ' . $_SESSION['success_msg'] . '
          </p>
        </div>';
  unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
  $msg = '<div class="messages fade show" >
            <p class= "errore">
            <strong>Errore:</strong> ' . $_SESSION['error_msg'] . '
          </p>
        </div>';
  unset($_SESSION['error_msg']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $user_email = $_SESSION['email'];
  $oggetto = addslashes($_POST['oggetto']);
  $messaggio = addslashes($_POST['messaggio']);

  if (isset($_FILES['screenshot'])) {

    try {
      $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
      echo "Errore durante la connessione al database: " . $e->getMessage();
      exit;
    }

    $file = $_FILES['screenshot'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));

    $base = pathinfo($file_name, PATHINFO_FILENAME);
    $file_name_new = preg_replace('/\s+/', '', $base) . uniqid() . '.' . $file_ext;

    $file_destination = '../img/segnalazioni/' . $file_name_new;

    if ($file_error === 0) {
      $allowed = array('jpg', 'jpeg', 'png');
      if (in_array($file_ext, $allowed)) {
        if ($file_size <= 5000000) {
          move_uploaded_file($file_tmp, $file_destination);
          $imgSegn = $file_name_new;

          try {
            $query = $pdo->prepare("INSERT INTO Segnalazione (userEmail, Oggetto, Messaggio, imgSegn) VALUES (:email, :oggetto, :messaggio, :imgSegn)");
            $query->bindParam(':email', $user_email);
            $query->bindParam(':oggetto', $oggetto);
            $query->bindParam(':messaggio', $messaggio);
            $query->bindParam(':imgSegn', $imgSegn);
            $query->execute();
            $query->closeCursor();

            // mandare email della segnalazione al bibliotecario:
            $email_biblio = getenv('EMAIL_BIBLIO');
            if ($email_biblio) {
              if (
                sendEmail(
                  $email_biblio,
                  'Bibliotecario',
                  'Nuova segnalazione da ' . $user_email,
                  '<h2>È stata effettuata una nuova segnalazione!</h2>
                                    <p>Informazioni sulla segnalazione:</p>
                                    <ul>
                                        <li>Email Utente: ' . $user_email . '</li>
                                        <li>Oggetto: ' . $oggetto . '</li>
                                        <li>Messaggio: <p>' . $messaggio . '</p></li>
                                        <li>Immagine: ' . $imgSegn . '</li>
                                    </ul>'
                )
              ) {
                $_SESSION['success_msg'] = "Segnalazione e screenshot inviati con successo";
                header("Location: segnalazione.php");
                exit();
              } else {
                $_SESSION["error_msg"] = "Errore nell'invio dell'email al bibliotecario";
                header("Location: segnalazione.php");
                exit();
              }
            } else {
              $_SESSION['success_msg'] = "Segnalazione e screenshot inviati con successo";
              header("Location: segnalazione.php");
              exit();
            }

          } catch (PDOException $e) {
            throw new Exception("Errore durante l'invio della segnalazione: " . $e->getMessage());
          }
        } else {
          $_SESSION['error_msg'] = "Il file è troppo grande (max 5MB)";
          header("Location: segnalazione.php");
          exit();
        }
      } else {
        $_SESSION['error_msg'] = "Formato non supportato (solo jpg, jpeg, png)";
        header("Location: segnalazione.php");
        exit();
      }
    } else {
      try {
        $query = $pdo->prepare("INSERT INTO Segnalazione (userEmail, Oggetto, Messaggio) VALUES (:email, :oggetto, :messaggio)");
        $query->bindParam(':email', $user_email);
        $query->bindParam(':oggetto', $oggetto);
        $query->bindParam(':messaggio', $messaggio);
        $query->execute();
        $query->closeCursor();

        // mandare email della segnalazione al bibliotecario:
        $email_biblio = getenv('EMAIL_BIBLIO');
        if ($email_biblio) {
          if (
            sendEmail(
              $email_biblio,
              'Bibliotecario',
              'Nuova segnalazione da ' . $user_email,
              '<h2>È stata effettuata una nuova segnalazione!</h2>
                                    <p>Informazioni sulla segnalazione:</p>
                                    <ul>
                                        <li>Email Utente: ' . $user_email . '</li>
                                        <li>Oggetto: ' . $oggetto . '</li>
                                        <li>Messaggio: <p>' . $messaggio . '</p></li>
                                        <li>Immagine: Nessuna</li>
                                    </ul>'
            )
          ) {
            $_SESSION['success_msg'] = "Segnalazione inviato con successo";
            header("Location: segnalazione.php");
            exit();
          } else {
            $_SESSION["error_msg"] = "Errore nell'invio dell'email al bibliotecario";
            header("Location: segnalazione.php");
            exit();
          }
        } else {
          $_SESSION['success_msg'] = "Segnalazione inviato con successo";
          header("Location: segnalazione.php");
          exit();
        }

      } catch (PDOException $e) {
        throw new Exception("Errore durante l'invio della segnalazione: " . $e->getMessage());
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback Utente | Supporto</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/unified.css">
  <link rel="stylesheet" href="../css/nav.css">
  <link rel="stylesheet" href="../css/segnalazione.css">
  <link rel="stylesheet" href="../css/messaggi.css">
  <link rel="icon" type="image/x-icon" href="../img/feedbackFavicon.png">
</head>

<body>

  <div id="nav-placeholder">
    <?php require_once("../nav/nav.php"); ?>
  </div>
  <?php echo $msg; ?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <div class="text-center mb-4">
          <h1 class="display-4">💬 Feedback Utente</h1>
          <p class="text-muted">Inviaci i tuoi suggerimenti o segnala un problema</p>
        </div>



        <div class="card">
          <div class="card-header font-weight-bold">
            📩 Modulo di Segnalazione
          </div>
          <div class="card-body">
            <form action="segnalazione.php" method="POST" enctype="multipart/form-data">

              <div class="form-group">
                <label for="oggetto">Oggetto</label>
                <input type="text" class="form-control" id="oggetto" name="oggetto" maxlength="50"
                  placeholder="Di cosa si tratta?" required>
                <div class="text-right">
                  <small class="text-muted" id="oggetto-counter">Caratteri rimanenti: 50</small>
                </div>
              </div>

              <div class="form-group">
                <label for="messaggio">Messaggio</label>
                <textarea class="form-control" id="messaggio" name="messaggio" rows="5" maxlength="250"
                  placeholder="Descrivi qui la tua segnalazione..." required></textarea>
                <div class="text-right">
                  <small class="text-muted" id="messaggio-counter">Caratteri rimanenti: 250</small>
                </div>
              </div>

              <div class="form-group mb-4">
                <label for="file">🖼️ Screenshot (facoltativo)</label>
                <div class="custom-file mb-2">
                  <input type="file" class="custom-file-input" id="file" name="screenshot" accept="image/*"
                    onchange="previewImage(event)">
                  <label class="custom-file-label" for="file">Scegli file...</label>
                </div>
                <small class="text-muted d-block mb-3">Formati: jpg, jpeg, png (Max 5MB)</small>

                <div class="position-relative text-center">
                  <i id="trash-btn" class="delete-icon fas fa-trash bg-danger text-white rounded-pill p-2"
                    onclick="deleteImage()" title="Rimuovi Screenshot"></i>
                  <img id="image-preview" class="preview-image" src="#" alt="Anteprima" style="display: none;">
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-block btn-lg">
                Invia Segnalazione
              </button>

            </form>
          </div>
        </div>

        <div class="text-center mt-4">
          <a href="../index.php" class="text-secondary text-decoration-none">← Torna alla Home</a>
        </div>

      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="https://kit.fontawesome.com/455452defb.js" crossorigin="anonymous"></script>
  <script src="segnalazione.js"></script>
  <script>

  </script>
</body>

</html>