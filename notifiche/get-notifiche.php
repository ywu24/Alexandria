<?php
session_start();
header('Content-Type: application/json');

// Include i file necessari
$root = "..";
require_once("../utils/connect.php");
require_once("../auth/cookies.php");

// Controllo se l'utente è loggato tramite email
if (!isset($_SESSION['email'])) {
    echo json_encode(["error" => "Non autorizzato", "non_lette" => 0, "notifiche" => []]);
    exit;
}

// 1. Connessione al database con il tuo metodo
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo json_encode(["error" => "Errore di connessione al db"]);
    exit;
}

try {
    // 2. Recupero l'ID dell'utente usando la sua email
    $q = $pdo->prepare('SELECT id FROM Utente WHERE Email = :email');
    $q->execute([':email' => $_SESSION['email']]);
    $utente = $q->fetch();
    
    if (!$utente) {
        echo json_encode(["error" => "Utente non trovato"]);
        exit;
    }
    
    $utente_id = $utente['id'];

    // 3. Conta le notifiche NON lette per il badge
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifiche WHERE utente_id = :id AND letta = 0");
    $stmtCount->execute([':id' => $utente_id]);
    $non_lette = $stmtCount->fetchColumn();

    // 4. Prendi le ultime 5 notifiche da mostrare nel menu a tendina
    $stmtNotifiche = $pdo->prepare("SELECT * FROM notifiche WHERE utente_id = :id ORDER BY data_creazione DESC LIMIT 5");
    $stmtNotifiche->execute([':id' => $utente_id]);
    $lista_notifiche = $stmtNotifiche->fetchAll(PDO::FETCH_ASSOC);

    // Restituisci il risultato al frontend
    echo json_encode([
        "non_lette" => $non_lette,
        "notifiche" => $lista_notifiche
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Errore durante l'esecuzione della query: " . $e->getMessage()]);
}
?>