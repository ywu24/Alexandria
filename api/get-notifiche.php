<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint for fetching recent notifications and unread count
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\NotificationService;

// Assicuriamoci di restituire sempre JSON
header('Content-Type: application/json');

// 1. Controllo Autenticazione
$email = $_SESSION['email'] ?? null;

if (!$email) {
    // Codice 401: Unauthorized
    http_response_code(401);
    echo json_encode(["error" => "Non autorizzato", "non_lette" => 0, "notifiche" => []]);
    exit;
}

// 2. Inizializzazione del Servizio
$notificationService = new NotificationService($pdo);

try {
    // 3. Recupero Dati tramite il Servizio
    $non_lette = $notificationService->getUnreadCount($email);
    $lista_notifiche = $notificationService->getLatestNotifications($email, 5);

    // 4. Risposta di Successo
    http_response_code(200);
    echo json_encode([
        "unread_count" => $non_lette,
        "notifiche" => $lista_notifiche
    ]);

} catch (Exception $e) {
    // 5. Gestione Errori Centralizzata
    http_response_code(500);
    error_log('[api/get_notifiche.php] Error: ' . $e->getMessage());
    
    // Non esponiamo l'errore SQL/PHP al frontend per motivi di sicurezza
    echo json_encode(["error" => "Si è verificato un errore del server durante il recupero delle notifiche."]);
    exit;
}