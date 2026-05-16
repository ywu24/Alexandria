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
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/design-system.css">
  <link rel="stylesheet" href="../css/components.css">
  <link rel="stylesheet" href="../css/layout.css">
  <link rel="stylesheet" href="../css/navigation.css">
  <link rel="stylesheet" href="../css/pages/dashboard.css">
  <link rel="stylesheet" href="../css/pages/footer.css">
  <link rel="stylesheet" href="../css/utilities.css">
  <link rel="icon" type="image/svg+xml" href="../img/dashboard.svg">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="dashboard-landing">

  <div id="nav-placeholder">
    <?php $root = '..'; require_once('../nav/nav.php'); ?>
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

  </div>
  <?php require_once('../nav/footer.php'); ?>
</body>
