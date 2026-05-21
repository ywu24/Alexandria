<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint for fetching recent notifications and unread count
 */

/**
 * API Endpoint: Recupero Notifiche Utente
 * 
 * Questo script funge da endpoint AJAX per recuperare le notifiche di un utente.
 * Flusso di funzionamento:
 * 1. Imposta l'header della risposta su JSON.
 * 2. Verifica che l'utente sia autenticato tramite la variabile di sessione 'email'.
 *    Se non lo è, restituisce un errore 401 (Unauthorized).
 * 3. Utilizza NotificationService per interrogare il database e recuperare
 *    il conteggio delle notifiche non lette e le ultime 5 notifiche.
 * 4. Restituisce i dati formattati in JSON con status 200 (OK).
 * 5. In caso di eccezioni (es. database offline), logga l'errore sul server
 *    e restituisce un messaggio di errore generico (500 Internal Server Error)
 *    per non esporre dati sensibili.
 */

/*
 * NotificationService si occupa dell’accesso a database e inserisce, legge, segna come lette le notifiche
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