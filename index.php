<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . "/auth/cookies.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/nav.css"> 
    <title>HomePage - Alexandria </title>
</head>

<body>

    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/nav/nav.php"); ?>

    <div style="padding:20px;">

    <?php
    if (isset($_SESSION['email'])) {
        echo '
            <h3>Logged in as '. $_SESSION['nome'] . ' ' . $_SESSION['cognome'] . '</h3>
            <h4>Email: '. $_SESSION['email'] . '</h4>
            <h4>Utenza: '. $_SESSION['utenza']
            ;

        if ($_SESSION['utenza'] === 1) echo ' (admin)</h4>';
        else if ($_SESSION['utenza'] === 2) echo ' (bibliotecario)</h4>';
        else if ($_SESSION['utenza'] === 3) echo ' (standard user)</h4>';

        echo '<br><a href="auth/logout.php">Logout</a>';
    }
    else {
        echo '
            <h3>Not logged in.</h3>
            <br><a href="auth/login.php">Login</a>
        ';
    }
    ?>
</body>
</html>