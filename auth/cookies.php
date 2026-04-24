<?php
require_once($root . "/utils/connect.php");
try {
    if (!isset($_SESSION['email'])) {
        if (isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
            try {
                $pdo = DatabaseConnection::getInstance()->getConnection();
            } catch (PDOException $e) {
                echo "Errore durante la connessione al database: " . $e->getMessage();
                exit;
            }

            $query = $pdo->prepare('SELECT * FROM Utente WHERE Email = :email');
            $query->bindParam(':email', $_COOKIE['email']);
            $query->execute();
            $result = $query->fetch();

            if ($result) {
                $user = $result;
                if (!($_COOKIE['password'] === $user['Password'])) {
                    throw new Exception('Invalid password stored in cookie');
                } else {
                    $_SESSION['email'] = $user['Email'];
                    $_SESSION['nome'] = $user['Nome'];
                    $_SESSION['cognome'] = $user['Cognome'];
                    $_SESSION['utenza'] = $user['Utenza'];
                }
            } else {
                throw new Exception('User not found for email in cookie');
            }
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: ' . $root . '/auth/login.php');
    exit;
}
