<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint to mark all user notifications as read
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\NotificationService;

// Risposta sempre in formato JSON
header('Content-Type: application/json');

// 1. Controllo Autenticazione (Utilizzando la sessione gestita da bootstrap)
$email = $_SESSION['email'] ?? null;

if (!$email) {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(["success" => false, "error" => "Non autorizzato"]);
    exit;
}

// 2. Inizializzazione del Servizio
$notificationService = new NotificationService($pdo);

try {
    // 3. Esecuzione dell'operazione tramite il Servizio
    // Il metodo si occupa internamente di trovare l'utente e aggiornare le notifiche
    $notificationService->markAllAsRead($email);

    // 4. Risposta di Successo
    http_response_code(200); // 200 OK
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    // 5. Gestione di sicurezza dell'errore
    http_response_code(500); // 500 Internal Server Error
    error_log('[api/mark_notifications_read.php] Error: ' . $e->getMessage());
    
    // Non passiamo l'errore SQL nativo al client per sicurezza
    echo json_encode(["success" => false, "error" => "Impossibile aggiornare le notifiche. Errore interno."]);
    exit;
}