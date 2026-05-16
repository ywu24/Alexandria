<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Report submission page with optional screenshot upload
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\ReportService;
use Alexandria\Services\NotificationService;

$reportService = new ReportService($pdo);
$notificationService = new NotificationService();

$root = '..';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_email = $_SESSION['email'] ?? '';
    $oggetto = $_POST['oggetto'] ?? '';
    $messaggio = $_POST['messaggio'] ?? '';
    $file = $_FILES['screenshot'] ?? null;

    try {
        $result = $reportService->create($user_email, $oggetto, $messaggio, $file);

        $notificationService->notifyReport(
            $user_email,
            $oggetto,
            $messaggio,
            $result['imgSegn']
        );

        flash('success', 'Segnalazione' . ($result['imgSegn'] ? ' e screenshot' : '') . ' inviata con successo');
        redirect('segnalazione.php');
    } catch (Exception $e) {
        flash('error', $e->getMessage());
        redirect('segnalazione.php');
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback Utente | Supporto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/design-system.css">
  <link rel="stylesheet" href="../css/components.css">
  <link rel="stylesheet" href="../css/layout.css">
  <link rel="stylesheet" href="../css/navigation.css">
  <link rel="stylesheet" href="../css/pages/forms.css">
  <link rel="stylesheet" href="../css/pages/footer.css">
  <link rel="stylesheet" href="../css/utilities.css">
  <link rel="icon" type="image/svg+xml" href="../img/feedbackFavicon.svg">
</head>

<body class="form-page">

  <div id="nav-placeholder">
    <?php require_once('../nav/nav.php'); ?>
  </div>

  <?php render_messages(); ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <div class="text-center mb-4">
          <h1 class="display-4">Feedback Utente</h1>
          <p class="text-muted">Inviaci i tuoi suggerimenti o segnala un problema</p>
        </div>

        <div class="card">
          <div class="card-header font-weight-bold">
            Modulo di Segnalazione
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
                <label for="file">Screenshot (facoltativo)</label>
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
          <a href="../index.php" class="text-secondary text-decoration-none">&larr; Torna alla Home</a>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/455452defb.js" crossorigin="anonymous"></script>
  <script src="segnalazione.js"></script>
  <?php require_once('../nav/footer.php'); ?>
</body>

</html>
