<?php
if (!isset($_SESSION['email'])) {

    if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
        
        require_once("../utils/connect.php");
        if ($stmt = $conn->prepare('SELECT * FROM Utente WHERE Email = ?')) {
            $stmt->bind_param('s', $_COOKIE['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            if (mysqli_num_rows($result) == 1) {
                $user = $result->fetch_assoc();

                if (!($_COOKIE['password'] === $user['Password'])) {
                    header('Location: login.php');
                } else {
                    $_SESSION['email'] = $user['Email'];
                    $_SESSION['nome'] = $user['Nome'];
                    $_SESSION['cognome'] = $user['Cognome'];
                    $_SESSION['utenza'] = $user['Utenza'];
                }

            } else {
                header('Location: login.php');
            }
        }
    }
}
?>