<?php
session_start();
header('Content-Type: application/json');

$root = "..";
require_once("../utils/connect.php");
require_once("../auth/cookies.php");

// Se non è loggato, esco
if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "error" => "Non autorizzato"]);
    exit;
}

// Connessione al DB
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo json_encode(["error" => "Errore di connessione al db"]);
    exit;
}

try {
    // Recupero l'ID dell'utente dalla mail
    $q = $pdo->prepare('SELECT id FROM Utente WHERE Email = :email');
    $q->execute([':email' => $_SESSION['email']]);
    $utente = $q->fetch();

    if ($utente) {
        $utente_id = $utente['id'];

        // Eseguo l'UPDATE per segnare come lette le notifiche dell'utente
        $stmt = $pdo->prepare("UPDATE notifiche SET letta = 1 WHERE utente_id = :id AND letta = 0");
        $stmt->execute([':id' => $utente_id]);
        
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Utente non trovato"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Errore: " . $e->getMessage()]);
}
?>