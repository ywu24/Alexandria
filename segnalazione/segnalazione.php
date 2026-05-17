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
  <?php render_head('Feedback Utente | Supporto',
      ['css/pages/forms.css', 'css/pages/footer.css'],
      ['js/report.js', 'https://kit.fontawesome.com/455452defb.js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'],
      '..'
  ); ?>
  <link rel="icon" type="image/svg+xml" href="../img/feedbackFavicon.svg">
</head>

<body class="form-page">

  <div id="nav-placeholder">
    <?php require_once('../nav/nav.php'); ?>
  </div>

  <?php render_messages(); ?>

  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <div class="text-center mb-5">
          <h1 class="display-5 fw-bold">Feedback Utente</h1>
          <p class="text-muted">Inviaci i tuoi suggerimenti o segnala un problema per aiutarci a migliorare</p>
        </div>

        <div class="card shadow-sm">
          <div class="card-header text-center py-3 text-white">
            <h5 class="mb-0">Modulo di Segnalazione</h5>
          </div>
          <div class="card-body p-4">
            <form action="segnalazione.php" method="POST" enctype="multipart/form-data">

              <div class="mb-4">
                <label for="oggetto" class="form-label">Oggetto</label>
                <input type="text" class="form-control" id="oggetto" name="oggetto" maxlength="50"
                  placeholder="Di cosa si tratta?" required>
                <div class="text-end mt-1">
                  <small class="text-muted" id="oggetto-counter">Caratteri rimanenti: 50</small>
                </div>
              </div>

              <div class="mb-4">
                <label for="messaggio" class="form-label">Messaggio</label>
                <textarea class="form-control" id="messaggio" name="messaggio" rows="5" maxlength="250"
                  placeholder="Descrivi qui la tua segnalazione..." required></textarea>
                <div class="text-end mt-1">
                  <small class="text-muted" id="messaggio-counter">Caratteri rimanenti: 250</small>
                </div>
              </div>

              <div class="mb-4">
                <label for="file" class="form-label">Screenshot (facoltativo)</label>
                <input type="file" class="form-control" id="file" name="screenshot" accept="image/*"
                  onchange="previewImage(event)">
                <div class="form-text mb-3">Formati supportati: jpg, jpeg, png (Max 5MB)</div>

                <div class="preview-image-container text-center mx-auto">
                  <i id="trash-btn" class="delete-icon fas fa-trash bg-danger text-white rounded-pill p-2"
                    onclick="deleteImage()" title="Rimuovi Screenshot"></i>
                  <img id="image-preview" class="preview-image d-none" src="#" alt="Anteprima">
                </div>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                  Invia Segnalazione
                </button>
              </div>

            </form>
          </div>
        </div>

        <div class="text-center mt-4">
          <a href="../index.php" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-1"></i> Torna alla Home
          </a>
        </div>

      </div>
    </div>
  </main>

  <?php require_once('../nav/footer.php'); ?>
</body>

</html>
