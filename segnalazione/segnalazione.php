<?php
    session_start();
    $root = '..';
    require_once($root. "/utils/connect.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $user_email = $_SESSION['email'];
        $oggetto = addslashes($_POST['oggetto']);
        $messaggio = addslashes($_POST['messaggio']);

        if(!empty($_FILES['screenshot'])) { 

        //Recupera i dati del file inviato
        $file = $_FILES['screenshot'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];

        // Estrae l'estensione del file
        $file_ext = explode('.', $file_name);
        $file_ext = strtolower(end($file_ext));

        // Crea un nome univoco per il file
        $base = pathinfo($file_name, PATHINFO_FILENAME);
        $file_name_new = preg_replace('/\s+/', '', $base) . uniqid() . '.' . $file_ext;

        // Specifica la directory di destinazione per il file
        $file_destination = $root. '/img/segnalazioni/' . $file_name_new;

        
        // Controlla se ci sono errori durante il caricamento del file
        if ($file_error === 0) {
            // Verifica che il file sia di un formato supportato
            $allowed = array('jpg', 'jpeg', 'png');
            if (in_array($file_ext, $allowed)) {
                // Verifica che la dimensione del file non superi un limite specificato
                if ($file_size <= 5000000) {
                    // Carica il file
                    move_uploaded_file($file_tmp, $file_destination);
                    $imgSegn = $root. "/img/segnalazioni/" . $file_name_new; 
                    $sql = "INSERT INTO Segnalazione (userEmail, Oggetto, Messaggio, imgSegn) VALUES ('$user_email', '$oggetto', '$messaggio', '$imgSegn')";
                    mysqli_query($conn,$sql);
                    echo '<center><h1 style="color: green; font-weight:bold">Segnalazione e screenshot inviati con successo</h1></center>';
                    echo "<meta http-equiv='refresh' content='2;url=../index.php'>";
                } else {
                    echo '<center><h1 style="color: red; font-weight:bold">Il file è troppo grande dimensione massima 5MB</h1></center>';
                    echo "<meta http-equiv='refresh' content='2;url=segnalazione.php'>";
                }
            } else {
                    echo '<center><h1 style="color: red; font-weight:bold">Il file non è supportato caricare solo file di formato: jpg, jpeg o png</h1></center>';
                    echo "<meta http-equiv='refresh' content='2;url=segnalazione.php'>";
            }
        } else {
            $sql = "INSERT INTO Segnalazione (userEmail, Oggetto, Messaggio) VALUES ('$user_email', '$oggetto', '$messaggio')";
            mysqli_query($conn,$sql);
            echo '<center><h1 style="color: green; font-weight:bold">Segnalazione inviata con successo</h1></center>';
            echo "<meta http-equiv='refresh' content='2;url=../index.php'>";
        }

        }
    }
        

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $root; ?>/nav/nav.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="icon" type="image/x-icon" href="<?php echo $root; ?>/img/feedbackFavicon.png">
  <link rel="stylesheet" href="<?php echo $root; ?>/css/segnalazione.css">
  <title>Feedback Utente</title>
</head>
<body>

    <div id="nav-placeholder">
        <?php 
        require_once($root. "/nav/nav.php"); ?>
    </div>

  <div class="centered-form">
    <div class="form-container mt-5">
      <h2 class="text-center">Feedback Utente</h2>
      <form action="segnalazione.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="oggetto">Oggetto:</label>
          <input type="text" class="form-control" id="oggetto" name="oggetto" maxlength="50" required>
          <small class="text-muted" id="oggetto-counter">Caratteri rimanenti: 50</small>
        </div>
        <div class="form-group">
          <label for="messaggio">Messaggio:</label>
          <textarea class="form-control" id="messaggio" name="messaggio" rows="5" maxlength="250" required></textarea>
          <small class="text-muted" id="messaggio-counter">Caratteri rimanenti: 250</small>
        </div>
        <div class="form-group">
          <label for="file">Screenshot (facoltativo):</label>
          <div class="preview-image-container">
            <input type="file" class="form-control-file" id="file" name="screenshot" accept="image/*" onchange="previewImage(event)">
            <img id="image-preview" class="preview-image" src="#" alt="Anteprima immagine" style="display: none;">
            <i class="delete-icon fas fa-trash bg-danger text-white rounded-pill p-2" onclick="deleteImage()" title="Rimuovi Screenshot"></i>
          </div>
          <small class="text-muted">Formati supportati: jpg, jpeg, png (Massimo 5MB)</small>
        </div>
        <button type="submit" class="btn btn-primary">Invia segnalazione</button>
      </form>
    </div>
  </div>
  
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="https://kit.fontawesome.com/455452defb.js" crossorigin="anonymous"></script>  
  <script>
    // Conteggio dei caratteri rimanenti
    document.getElementById('oggetto').addEventListener('input', function() {
      var counter = document.getElementById('oggetto-counter');
      counter.innerText = 'Caratteri rimanenti: ' + (50 - this.value.length);
    });

    document.getElementById('messaggio').addEventListener('input', function() {
      var counter = document.getElementById('messaggio-counter');
      counter.innerText = 'Caratteri rimanenti: ' + (250 - this.value.length);
    });

    // Mostra un'anteprima dell'immagine selezionata dall'utente
    function previewImage(event) {
      var input = event.target; 
      var preview = document.getElementById('image-preview'); 

      if (input.files && input.files[0]) { // Verifica se sono stati selezionati file
        var reader = new FileReader();

        reader.onload = function(e) {
          preview.src = e.target.result; 
          preview.style.display = 'block';
        }

        reader.readAsDataURL(input.files[0]); // Legge il contenuto del file come URL dati
      } else {
        preview.src = '#'; 
        preview.style.display = 'none'; 
      }
    }

    // Rimuovi l'immagine selezionata dal form
    function deleteImage() {
      var preview = document.getElementById('image-preview'); 
      var input = document.getElementById('file');
      preview.src = '#';
      preview.style.display = 'none';
      input.value = '';
    }
  </script>
</body>
</html>
