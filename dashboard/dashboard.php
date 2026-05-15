<?php
session_start(); //non togliere

//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


?>
<!DOCTYPE html>
<html lang="en">

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
  <link rel="stylesheet" href="../css/utilities.css">
  <link rel="shortcut icon" href="../img/dashboard.png" type="image/x-icon">
  <!--script per importare parti di codice-->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="dashboard-landing">

  <div id="nav-placeholder">
    <?php $root = "..";
    require_once('../nav/nav.php'); ?>
  </div>
  <div class="container">
    <a href="dashboardUtenti/dashboardUtenti.php">
      <div class="card-container">
        <img src="../img/account.png" alt="">
        <h4>Dashboard Utenti</h4>
      </div>
    </a>
    <a href="dashboardLibri/dashboardLibri.php">
      <div class="card-container">
        <img src="../img/list.png" alt="">
        <h4>Dashboard Libri</h4>
      </div>
    </a>
    <a href="dashboardSegnalazioni/dashboardSegnalazioni.php">
      <div class="card-container">
        <img src="../img/warning-icon.png" alt="">
        <h4>Dashboard Segnalazioni</h4>
      </div>
    </a>

  </div>
</body>