<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Review submission page for books
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\ReviewService;

$reviewService = new ReviewService($pdo);

$root = '..';

// Validate book ID
if (!isset($_GET['id'])) {
    redirect('../lista/lista.php');
}
$idOpera = (int) $_GET['id'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titolo'], $_POST['messaggio'], $_POST['voto'])) {
    if (!isset($_SESSION['email'])) {
        redirect('../auth/login.php');
    }

    $user_email = $_SESSION['email'];
    $titolo = $_POST['titolo'];
    $messaggio = $_POST['messaggio'];
    $voto = (int) $_POST['voto'];

    try {
        $reviewService->create($user_email, $idOpera, $titolo, $messaggio, $voto);
        flash('success', 'Recensione inviata con successo!');
        $_SESSION['punti_guadagnati'] = true;
    } catch (Exception $e) {
        flash('error', $e->getMessage());
    }

    redirect('recensione.php?id=' . $idOpera);
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
  <?php render_head('Lascia una recensione',
      ['css/pages/recensioni.css', 'css/pages/footer.css'],
      ['js/recensione.js', 'https://kit.fontawesome.com/455452defb.js'],
      '..'
  ); ?>
  <link rel="icon" type="image/svg+xml" href="../img/feedbackFavicon.svg">
</head>

<body class="form-page">

  <div id="nav-placeholder">
    <?php require_once('../nav/nav.php'); ?>
  </div>

  <div id="messages" class="container mt-3 text-center" data-punti-guadagnati="<?php echo isset($_SESSION['punti_guadagnati']) ? '1' : '0'; unset($_SESSION['punti_guadagnati']); ?>">
    <?php render_messages(); ?>
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
          <textarea class="form-control" id="messaggio" name="messaggio" rows="5" maxlength="500" placeholder="Cosa ti e piaciuto o non ti e piaciuto?" required></textarea>
          <small class="text-muted" id="messaggio-counter">Caratteri rimanenti: 500</small>
        </div>

        <button type="submit" class="btn btn-warning btn-block text-white font-weight-bold">Pubblica recensione</button>
      </form>
    </div>
  </div>
  <?php require_once('../nav/footer.php'); ?>
</body>

</html>
