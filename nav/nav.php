<?php
if (isset($_POST['logout'])) {
    setcookie("email", "", time() - 1, '/');
    setcookie("password", "", time() - 1, '/');
    session_start();
    session_destroy();
    header("Location: auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/nav.css">
</head>

<body style="background:#ffffff !important;">

<nav class="nav1">

    <a href="index.php">
        <img src="/img/logo.png" class="logo">
    </a>

    <?php
    require_once("utils/connect.php");
    require_once("auth/cookies.php");

    echo "
    <form method='post' action='index.php' class='search-form'>
        <input type='search' name='search' placeholder='Search...'>
        <button type='submit' name='search_btn'>
            <img src='img/search-icon.png' alt='Search' class='search-icon'>
        </button>
    </form>";
    ?>

<?php

if (isset($_SESSION['email'])) {

    $email = $_SESSION['email'];

    if ($q = $conn->prepare('SELECT * FROM Utente WHERE Email=?')) {
        $q->bind_param('s', $email);
        $q->execute();
        $result = $q->get_result();
        $utente = $result->fetch_assoc();
    }

    echo "
    <ul>
        <li class='li-icon'>
            <a href='lista.php'>
                <img src='img/library-icon.svg' class='icon svg'>
            </a>
        </li>

        <li>
            <img src='img/account.png' class='icon' onclick='toggleMenu()'>
        </li>

        <div class='sub-menu-wrap' id='subMenu'>
            <div class='sub-menu'>

                <div class='user-info'>
                    <img src='" . $utente["propic"] . "'>
                    <h3>" . $utente["Nome"] . " " . $utente["Cognome"] . "</h3>
                </div>

                <a href='lista.php' class='sub-menu-link'>
                    <img src='img/library-icon.svg'>
                    <p>Library</p>
                    <span>></span>
                </a>
    ";

    if ($utente["Utenza"] == 1 || $utente["Utenza"] == 2) {
        echo "
        <a href='index.php' class='sub-menu-link'>
            <img src='img/dashboard.png'>
            <p>Dashboard</p>
            <span>></span>
        </a>";
    }

    echo "
                <a href='/auth/login.php' class='sub-menu-link'>
                    <form method='POST'>
                        <button name='logout'>
                            <img src='img/logout.png'>
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
            <a href='lista.php'>
                <img src='img/library-icon.svg' class='icon'>
            </a>
        </li>

        <li>
            <img src='img/account.png' class='icon' onclick='toggleMenu()'>
        </li>

        <div class='sub-menu-wrap' id='subMenu'>
            <div class='sub-menu'>

                <a href='login.php' class='sub-menu-link'>
                    <img src='img/login.png'>
                    <p>Login</p>
                    <span>></span>
                </a>

                <a href='register.php' class='sub-menu-link'>
                    <img src='img/register.png'>
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