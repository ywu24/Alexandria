<?php
session_start();
$root = '..';
require_once("../utils/connect.php");
require_once("../auth/cookies.php");
$email = $_SESSION['email'];
//$message = "no";

if ($q = $conn->prepare('SELECT propic FROM Utente WHERE Email=?')) {
    $q->bind_param('s', $email);
    $q->execute();
    $result = $q->get_result();
    $propics = $result->fetch_assoc();
}

if (isset($_POST['change_password'])) {
    if ($stmt = $conn->prepare('SELECT * FROM Utente WHERE Email = ?')) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (password_verify($_POST['current_password'], $user['Password'])) {

            $password = $_POST['new_password'];
            $password_again = $_POST['confirm_password'];

            if (strlen($password) >= 8 && strpbrk($password, "!#$.,:;()")) {

                if (strlen($password) <= 50) {

                    if ($password === $password_again) {

                        if ($stmt1 = $conn->prepare('UPDATE Utente SET Password = ? WHERE Email = ?')) {

                            $password = password_hash($password, PASSWORD_BCRYPT);
                            $stmt1->bind_param('ss', $password, $email);
                            $stmt1->execute();

                            $_SESSION['success_msg'] = "Password cambiata con successo";
                            header("Location: edit_profile.php");
                            exit;

                        } else {
                            $_SESSION['error_msg'] = "Errore durante l'aggiornamento";
                        }

                    } else {
                        $_SESSION['error_msg'] = "Le password non corrispondono";
                    }

                } else {
                    $_SESSION['error_msg'] = "Password troppo lunga";
                }

            } else {
                $_SESSION['error_msg'] = "Password non sicura";
            }

        } else {
            $_SESSION['error_msg'] = "Password attuale errata";
        }

    }
}
                       
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/edit_profile.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/messaggi.css">
</head>

<body>

    <div class="safe-area spaced-column">
            <div id="nav-placeholder">
                <?php 
                require_once("../nav/nav.php"); ?>
            </div>
    </div>
    <div id = "messages">
    <?php
        if (isset($_SESSION['success_msg'])) {
            echo '<p class="successo">' . $_SESSION['success_msg'] . '</p>';
            unset($_SESSION['success_msg']);
        }

        if (isset($_SESSION['error_msg'])) {
            echo '<p class="errore">' . $_SESSION['error_msg'] . '</p>';
            unset($_SESSION['error_msg']);
        }
    ?>
    </div>

     <div class="safe-area">
        <div class="container">
            <div class="edit-section">
                <ul>
                    <li><a href="#profile-settings">
                            <div class="section"><span>Your Profile</span></div>
                        </a></li>
                    <li><a href="#password-reset">
                            <div class="section"><span>Change Password</span></div>
                        </a></li>
                </ul>
            </div>
            <div class="settings">
                <div class="settings-section" id="profile-settings">
                    <div class="settings-profile-info">
                        <div class="data-profile-info">
                            <?php
                            if (isset($_SESSION['nome'])) {
                                echo "
                                <span>" . $_SESSION['nome'] . " " . $_SESSION['cognome'] . "</span>
                                <p> " . $_SESSION['email'] . "</p>
                                ";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <div class="settings-profile-info">
                        <?php

                        echo "
                        <div class='edit-profile-parameter'>
                            <img src='../img/users/" . $propics["propic"] . "' alt=''>
                            <span class='edit-action'>Cambia Immagine</span>
                            <form action='./change_propic/change_propic.php' method='POST' enctype='multipart/form-data'>
                                <input type='file' name='image'>
                                <button type='submit' name='propicIns'>Cambia Immagine</button>
                            </form>
                        </div>";
                        ?>
                    

                    </div>
                </div>                

                <div class="settings-section" id="password-reset">
                    <div class="settings-profile-info">
                        <div class="password-reset">
                            <span class="section-title" style="padding-bottom: 10px;">Change Password</span>
                            <form action="./edit_profile.php" method="POST">
                                <div class="password-block">
                                    <span class="section-title1">Current Password</span>
                                    <input type="password" name="current_password" class="password">
                                </div>
                                <div class="password-block">
                                    <span class="section-title1">New Password</span>
                                    <input type="password" name="new_password" class="password">
                                </div>
                                <div class="password-block">
                                    <span class="section-title1">Confirm Password</span>
                                    <input type="password" name="confirm_password" class="password">
                                </div>
                                <input type="submit" name="change_password" value="Confirm">
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</body>

</html>
