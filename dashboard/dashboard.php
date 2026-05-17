<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Main dashboard landing page with links to sub-dashboards
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;

$authService = new AuthService($pdo);

if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../index.php');
}

$root = '..';
?>
<!DOCTYPE html>
<html lang="it">

<head>
  <?php render_head('Dashboard Admin',
      ['css/pages/dashboard.css', 'css/pages/footer.css'],
      [],
      '..'
  ); ?>
  <link rel="icon" type="image/svg+xml" href="../img/dashboard.svg">
</head>

<body class="dashboard-landing">

  <div id="nav-placeholder">
    <?php require_once('../nav/nav.php'); ?>
  </div>
  <div class="container">
    <a href="dashboardUtenti/dashboardUtenti.php">
      <div class="card-container">
        <svg class="icon"><use href="../img/icons.svg#account"/></svg>
        <h4>Dashboard Utenti</h4>
      </div>
    </a>
    <a href="dashboardLibri/dashboardLibri.php">
      <div class="card-container">
        <svg class="icon"><use href="../img/icons.svg#list"/></svg>
        <h4>Dashboard Libri</h4>
      </div>
    </a>
    <a href="dashboardSegnalazioni/dashboardSegnalazioni.php">
      <div class="card-container">
        <svg class="icon"><use href="../img/icons.svg#warning"/></svg>
        <h4>Dashboard Segnalazioni</h4>
      </div>
    </a>
    <a href="../prenotazione/prenotazioneAdmin.php">
      <div class="card-container">
        <svg class="icon"><use href="../img/icons.svg#booking"/></svg>
        <h4>Dashboard Prenotazioni</h4>
      </div>
    </a>

  </div>
  <?php require_once('../nav/footer.php'); ?>
</body>
