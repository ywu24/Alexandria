<?php
session_start();
$root = "../"; 
require_once("../utils/connect.php");
require_once("../auth/cookies.php");

if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['email'];

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();

    // 1. Recupero l'ID utente
    $q = $pdo->prepare('SELECT id FROM Utente WHERE Email = :email');
    $q->execute([':email' => $email]);
    $utente_id = $q->fetchColumn();

    // 2. Recuperiamo TUTTE le notifiche (senza LIMIT)
    $stmt = $pdo->prepare("SELECT * FROM notifiche WHERE utente_id = :id ORDER BY data_creazione DESC");
    $stmt->execute([':id' => $utente_id]);
    $notifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // 3. Segniamo tutte le notifiche come lette dato che l'utente è entrato nella pagina
    $update = $pdo->prepare("UPDATE notifiche SET letta = 1 WHERE utente_id = :id AND letta = 0");
    $update->execute([':id' => $utente_id]);

   
} catch (PDOException $e) {
    echo "Errore notifiche.php: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <title>Tutte le Notifiche - Alexandria Library</title>
    <link rel="stylesheet" href="../css/unified.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/notification.css">
</head>
<body>
    <?php require_once("../nav/nav.php"); ?>

    <div class="notifiche-page-container">
        <h1>Centro Notifiche</h1>
        <hr>

        <?php if (empty($notifiche)): ?>
            <p>Non hai ancora ricevuto nessuna notifica.</p>
        <?php else: ?>
            <?php foreach ($notifiche as $n): ?>
                <div class="notifica-card <?php echo $n['letta'] == 0 ? 'new' : ''; ?>">
                    <span class="notifica-title"><?php echo htmlspecialchars($n['titolo']); ?></span>
                    <p><?php echo htmlspecialchars($n['messaggio']); ?></p>
                    <div class="notifica-time">Ricevuta il: <?php echo date('d/m/Y H:i', strtotime($n['data_creazione'])); ?></div>
                    <?php if ($n['url_azione']): ?>
                        <a href="<?php echo $root . $n['url_azione']; ?>" class="btn-link">Vai al dettaglio ></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>