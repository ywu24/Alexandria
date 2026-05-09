<?php
// Funzione da richiamare quando ad esempio un libro scade o è disponibile
function creaNotifica($pdo, $utente_id, $titolo, $messaggio, $url_azione = null) {
    $sql = "INSERT INTO notifiche (utente_id, titolo, messaggio, url_azione) 
            VALUES (:utente_id, :titolo, :messaggio, :url_azione)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':utente_id' => $utente_id,
        ':titolo' => $titolo,
        ':messaggio' => $messaggio,
        ':url_azione' => $url_azione
    ]);
}
?>