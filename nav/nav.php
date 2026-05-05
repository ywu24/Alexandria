<?php
require_once( $root . "/utils/connect.php");
require_once($root . "/auth/cookies.php");

if (isset($_POST['logout'])) {
    setcookie("email", "", time() - 1, '/');
    setcookie("password", "", time() - 1, '/');
    session_start();
    session_destroy();
    header("Location: " . $root . "/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $root; ?>/css/nav.css">
</head>

<body style="background:#ffffff !important;">

<nav class="nav1">

    <a href="<?php echo $root; ?>/index.php">
        <img src="<?php echo $root; ?>/img/logo.png" class="logo">
    </a>

    <?php  

    echo "
    <form method='get' action='" . $root . "/lista/lista.php' class='search-form'>
        <input type='search' name='search' placeholder='Search...'>
        <button type='submit' name='search_btn'>
        </button>
    </form>";
    ?>

<?php

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    try {
	    $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
    }

    try {
        $q = $pdo->prepare('SELECT * FROM Utente WHERE Email=:email');
        $q->bindParam(':email', $email);
        $q->execute();
        $utente = $q->fetch();
        $q->closeCursor();
    } catch (PDOException $e) {
        error_log('[nav.php] Error executing query: ' . $e->getMessage());
        echo '<h2 style="color: red;">Service unavailable, please try again later</h2>';
        exit;
    }

    echo "
    <ul>
        <li class='li-icon'>
            <a href='" . $root . "/lista/lista.php'>
                <img src='" . $root . "/img/library-icon.svg' class='icon svg'>
            </a>
        </li>";
        if(isset($_SESSION['utenza'])){
            if($_SESSION['utenza']==1 ||$_SESSION['utenza']==2 ){
                echo "
                    <li class ='li-icon'>
                        <a href='" . $root . "/dashboard/dashboard.php'>
                        <img src='" . $root . "/img/dashboard.svg' class='icon svg'>
                    </li>

                ";
            }
        }
        echo "
        <li class ='li-icon acc'>
            <a href='#'></a>
            <img src='" . $root . "/img/account.png' class='icon' onclick='toggleMenu()'>
        </li>

        <div class='sub-menu-wrap' id='subMenu'>
            <div class='sub-menu'>

                <div class='user-info'>
                    <img src='" . $root ."/img/users/". $utente["propic"] . "'>
                    <h3>" . $utente["Nome"] . " " . $utente["Cognome"] . "</h3>
                    <h4> Punti: ".$utente['punteggio']."</h4>
                </div>

                <a href='" . $root . "/prenotazione/prenotazione.php' class='sub-menu-link'>
                    <img src='" . $root . "/img/booking.png' alt=''>
                    <p>Prenotazioni</p>
                    <span>></span>
                </a>

                <a href='" . $root . "/lista/lista.php' class='sub-menu-link'>
                    <img src='" . $root . "/img/library-icon.svg'>
                    <p>Lista Libri</p>
                    <span>></span>
                </a>

                <a href='" . $root . "/edit_profile/edit_profile.php' class='sub-menu-link'>
                    <img src='" . $root . "/img/edit-profile.png' alt=''>
                    <p>Edit Profile</p>
                    <span>></span>
                </a>
    ";

    if ($utente["Utenza"] == 1 || $utente["Utenza"] == 2) {
        echo "
        <a href='" . $root . "/dashboard/dashboard.php' class='sub-menu-link'>
            <img src='" . $root . "/img/dashboard.png'>
            <p>Dashboard</p>
            <span>></span>
        </a>";
    }
    
    if ($utente["Utenza"] == 3 || $utente["Utenza"] == 4) {
                echo "<a href='" . $root . "/segnalazione/segnalazione.php' class='sub-menu-link'>
                <img src='" . $root . "/img/feedbackFavicon.png' alt=''>
                <p>Segnalazione</p>
                <span>></span>
            </a>";
            }
    echo "
                <a href='#' class='sub-menu-link'>
                    <form method='POST'>
                        <button name='logout' action='" . $root . "/nav/nav.php'>
                            <img src='" . $root . "/img/logout.png'>
                            <p class='logoutform'>Logout</p>
                            <span>></span>
                        </button>
                    </form>
                </a>

            </div>
        </div>
    </ul>
    </nav>";

} else {

    echo "
    <ul>

        <li class='li-icon'>
            <a href='" . $root . "/lista/lista.php'>
                <img src='" . $root . "/img/library-icon.svg' class='icon'>
            </a>
        </li>

        <li>
            <img src='" . $root . "/img/account.png' class='icon' onclick='toggleMenu()'>
        </li>

        <div class='sub-menu-wrap' id='subMenu'>
            <div class='sub-menu'>

                <a href='" . $root . "/auth/login.php' class='sub-menu-link'>
                    <img src='" . $root . "/img/login.png'>
                    <p>Login</p>
                    <span>></span>
                </a>

                <a href='" . $root . "/auth/registrazione.php' class='sub-menu-link'>
                    <img src='" . $root . "/img/register.png'>
                    <p>Register</p>
                    <span>></span>
                </a>

            </div>
        </div>
    </ul>
    </nav>";
}

?>

<script>
let subMenu = document.getElementById("subMenu");

function toggleMenu() {
    subMenu.classList.toggle("open-menu");
}
</script>


</body>
</html>