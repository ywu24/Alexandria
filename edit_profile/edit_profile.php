<?php
session_start();
$root = '..';
require_once("../utils/connect.php");
require_once("../auth/cookies.php");
$email = $_SESSION['email'];
$message = "no";

if ($q = $conn->prepare('SELECT propic FROM Utente WHERE Email=?')) {
    $q->bind_param('s', $email);
    $q->execute();
    $result = $q->get_result();
    $propics = $result->fetch_assoc();
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
</head>

<body>

    <div class="safe-area spaced-column">
            <div id="nav-placeholder">
                <?php 
                require_once( "../nav/nav.php"); ?>
            </div>
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

                    <!--
                    <li><a href="#email-noti" class="blur">
                            <div class="section"><span>Email Notification</span></div>
                        </a></li>
                    <li><a href="#push-noti" class="blur">
                            <div class="section"><span>Push Notification</span></div>
                        </a></li>
                    <li><a href="#privacy-settings" class="blur">
                            <div class="section"><span>Privacy</span></div>
                        </a></li>
                    -->
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
                            <img src='" . $root ."/img/users/" . $propics["propic"] . "' alt=''>
                            <span class='edit-action'>Cambia Immagine</span>
                            <form action='./change_propic/change_propic.php' method='POST' enctype='multipart/form-data'>
                                <input type='file' name='image'>
                                <button type='submit' name='propicIns'>Cambia Immagine</button>
                            </form>
                        </div>";
                        ?>
                        <?php
                        if (isset($_SESSION['error_msg'])) {
                            echo "<p style='color:red'>" . $_SESSION['error_msg'] . "</p>";
                            unset($_SESSION['error_msg']);
                        }
                        ?>


                    <!--
                        <div class="edit-phone-parameter">
                            <span>Phone Number</span>
                            <p class="phone-number">+39 111 111 1111</p>
                            <a href="" class="edit-action">edit</a>
                        </div>
                        <div class="personal-parameter">
                            <div class="edit-personal-parameter">
                                <span>Gender</span>
                                <p>male/female</p>
                            </div>

                            <div class="edit-personal-parameter">
                                <span>Birth Date</span>
                                <p>mm/dd/yy</p>
                            </div>
                        </div>

                        -->

                    </div>
                </div>
                <?php
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
                                        if ($stmt1 = $conn->prepare('UPDATE Utente SET Password = ? where Email = ?')) {
                                            $password = password_hash($password, PASSWORD_BCRYPT);
                                            $stmt1->bind_param('ss', $password, $email);
                                            $stmt1->execute();
                                            $message = '<h2 style="color: #2ac32d;">Password cambiata!</h2>';
                                        } else {
                                            $message = '<h2 style="color: red;">Errore, operazione fallita</h2>';
                                        }
                                    } else {
                                        $message = '<h2 style="color: red;">Le password non corrispondono, si prega di riprovare</h2>';
                                    }
                                } else {
                                    $message = '<h2 style="color: red;">La password supera il limite di lunghezza</h2>';
                                }
                            } else {
                                $message = '<h2 style="color: red;">La password non soddisfa i requisiti minimi di sicurezza</h2>';
                            }
                        } else {
                            $message = '<h2 style="color: red;">Incorrect password</h2>';
                        }

                        $stmt->close();
                    } else {
                        $message = '<h2 style="color: red;">Errore, operazione fallita</h2>';
                    }
                }
                ?>

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
                                <?php
                                if ($message != "no") {
                                    echo $message;
                                }
                                ?>
                            </form>
                        </div>
                    </div>
                </div>

            <!--
                <div class="settings-section blur" id="email-noti">
                    <div class="settings-profile-info">
                        <div class="radio-section">
                            <span class="section-title">FeedBack Emails</span>
                            <div class="radio-option">
                                <label><input type="radio" name="Feedback" value="On">On</label>
                                <label><input type="radio" name="Feedback" value="Off">Off</label>
                            </div>
                        </div>
                    </div>
                    <p class="section-explain">Consenti a Biblioteca Alexandria di inviarti email per aggiornarti sulle
                        azioni relative al tuo account?</p>
                </div>

                <div class="settings-section blur">
                    <div class="settings-profile-info">
                        <div class="radio-section">
                            <span class="section-title">Reminder Emails</span>
                            <div class="radio-option">
                                <label><input type="radio" name="Reminder" value="On">On</label>
                                <label><input type="radio" name="Reminder" value="Off">Off</label>
                            </div>
                        </div>
                    </div>
                    <p class="section-explain">Consenti a "Biblioteca Alexandria di inviarti email riguardanti promemoria
                        relativi al tuo account?</p>
                </div>

                <div class="settings-section blur">
                    <div class="settings-profile-info">
                        <div class="radio-section">
                            <span class="section-title">News Emails</span>
                            <div class="radio-option">
                                <label for=""><input type="radio" name="News" value="On">On</label>
                                <label for=""><input type="radio" name="News" value="Off">Off</label>
                            </div>
                        </div>
                    </div>
                    <p class="section-explain">Consenti a Biblioteca Alexandria di inviarti email riguardanti nuove
                        aggiunte all interno della libereria della tua scuola?</p>
                </div>

                <div class="settings-section blur" id="push-noti">
                    <div class="settings-profile-info">
                        <div class="radio-section">
                            <span class="section-title">FeedBack Notification</span>
                            <div class="radio-option">
                                <label for=""><input type="radio" name="Feedback-noti" value="On">On</label>
                                <label for=""><input type="radio" name="Feedback-noti" value="Off">Off</label>
                            </div>
                        </div>
                    </div>
                    <p class="section-explain">Consenti a Biblioteca Alexandria di inviarti notifiche per aggiornarti
                        sulle azioni relative al tuo account?</p>
                </div>

                <div class="settings-section blur">
                    <div class="settings-profile-info">
                        <div class="radio-section">
                            <span class="section-title">Reminder Notification</span>
                            <div class="radio-option">
                                <label for=""><input type="radio" name="Reminder-noti" value="On">On</label>
                                <label for=""><input type="radio" name="Reminder-noti" value="Off">Off</label>
                            </div>
                        </div>
                    </div>
                    <p class="section-explain">Consenti a Biblioteca Alexandria di inviarti notifiche riguardanti
                        promemoria relativi al tuo account?</p>
                </div>

                <div class="settings-section blur">
                    <div class="settings-profile-info">
                        <div class="radio-section">
                            <span class="section-title">News Notification</span>
                            <div class="radio-option">
                                <label for=""><input type="radio" name="News-noti" value="On">On</label>
                                <label for=""><input type="radio" name="News-noti" value="Off">Off</label>
                            </div>
                        </div>
                    </div>
                    <p class="section-explain">Consenti a Biblioteca Alexandria di inviarti notifiche riguardanti nuove
                        aggiunte all interno della libereria della tua scuola?</p>
                </div>

                <div class="settings-section blur" id="privacy-settings">
                    <div class="settings-profile-info">
                        <div class="radio-section">
                            <span class="section-title">Private Account</span>
                            <div class="radio-option">
                                <label for=""><input type="radio" name="Privacy" value="On">On</label>
                                <label for=""><input type="radio" name="Privacy" value="Off">Off</label>
                            </div>
                        </div>
                    </div>
                    <p class="section-explain">Consenti a Biblioteca Alexandria di vedere le tue informazioni personali
                        durante la revisione delle segnalazioni</p>
                </div>
            -->

            </div>
        </div>
    </div>

    
</body>

</html>
