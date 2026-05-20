<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint for booking actions (confirm, complete, admin cancel)
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../utils/mailer.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookingService;
use Alexandria\Services\NotificationService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);

$notificationService = new NotificationService($pdo);

if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

function sendBookingResponse(bool $success, string $message, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function sendBookingError(string $message, int $httpCode = 400): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if (!isset($_POST['id'])) {
    sendBookingError('Errore: prenotazione non definita');
}

$bookingService = new BookingService($pdo);
$id = (int) $_POST['id'];

if (isset($_POST['conferma'])) {
    try {
        if ($bookingService->confirm($id)) {
            $booking = $bookingService->getById($id);
            
            // INIZIALIZZAZIONE DATI PER NOTIFICHE 
            // 1. PRENDI L'EMAIL DAI DATI DELLA PRENOTAZIONE
            $emailUtente = $booking['Email'];
            // 2. RECUPERA L'ID DELL'UTENTE CHE HA PRENOTATO
            $stmtUid = $pdo->prepare("SELECT id FROM Utente WHERE Email = ?");
            $stmtUid->execute([$emailUtente]);
            $current_user_id = $stmtUid->fetchColumn();

            // Recuperiamo tutti gli ID dei bibliotecari (Utenza = 2)
            $stmtBiblio = $pdo->prepare("SELECT id FROM Utente WHERE Utenza = 2");
            $stmtBiblio->execute();
            $librarian_ids = $stmtBiblio->fetchAll(PDO::FETCH_COLUMN);

            if ($booking) {
                sendEmail(
                    $booking['Email'],
                    $booking['Email'],
                    'Libro Ritirato',
                    '<h2>Hai ritirato il libro ' . $booking['Nome'] . '</h2>
                    <p>Informazioni sul prestito:</p>
                    <ul>
                        <li>ISBN: ' . $booking['ISBN'] . '</li>
                        <li>ID Copia: ' . $booking['idCopia'] . '</li>
                        <li>ID Prenotazione: ' . $id . '</li>
                        <li>Data inizio prestito: ' . $booking['InizioPrestito'] . '</li>
                        <li>Data fine prestito: ' . $booking['FineAttesa'] . '</li>
                    </ul>'
                );

            // 2. Notifica In-App per l'utente loggato 
            if ($current_user_id) {
                $notificationService->creaNotifica(
                    $current_user_id,
                    'Libro Ritirato',
                    "Hai ritirato il libro: " . $booking['Nome'] . " Per maggiori informazioni vedi i dettagli",
                    'prenotazione/prenotazione.php'
                );
            }

            // 3. Notifica In-App per i bibliotecari
            foreach ($librarian_ids as $biblio_id) {
                $notificationService->creaNotifica(
                    (int)$biblio_id,
                    'L\'utente ha ritirato il libro',
                    "L'utente " . $current_user_id .  " ha ritirato il libro: " . $booking['Nome'],
                    'prenotazione/prenotazione.php'
                );
                }
            }

            sendBookingResponse(true, 'Prestito confermato con successo');
        } else {
            sendBookingResponse(false, 'Errore: prenotazione scaduta o non trovata');
        }
    } catch (Exception $e) {
        sendBookingError('Errore: ' . $e->getMessage(), 500);
    }
} elseif (isset($_POST['termina'])) {
    try {
        $result = $bookingService->complete($id);

        if ($result['success'] && !$result['late']) {
            $booking = $bookingService->getById($id);
            
            // INIZIALIZZAZIONE DATI PER NOTIFICHE 
            // 1. PRENDI L'EMAIL DAI DATI DELLA PRENOTAZIONE
            $emailUtente = $booking['Email'];
            // 2. RECUPERA L'ID DELL'UTENTE CHE HA PRENOTATO
            $stmtUid = $pdo->prepare("SELECT id FROM Utente WHERE Email = ?");
            $stmtUid->execute([$emailUtente]);
            $current_user_id = $stmtUid->fetchColumn();

            // Recuperiamo tutti gli ID dei bibliotecari (Utenza = 2)
            $stmtBiblio = $pdo->prepare("SELECT id FROM Utente WHERE Utenza = 2");
            $stmtBiblio->execute();
            $librarian_ids = $stmtBiblio->fetchAll(PDO::FETCH_COLUMN);

            if ($booking) {
                sendEmail(
                    $booking['Email'],
                    $booking['Email'],
                    'Libro Restituito',
                    '<h2>Hai restituito il libro ' . $booking['Nome'] . '</h2>
                    <p>Informazioni sul prestito:</p>
                    <ul>
                        <li>ISBN: ' . $booking['ISBN'] . '</li>
                        <li>ID Copia: ' . $booking['idCopia'] . '</li>
                        <li>ID Prenotazione: ' . $id . '</li>
                        <li>Data inizio prestito: ' . $booking['InizioPrestito'] . '</li>
                        <li>Data fine prestito: ' . $booking['FineAttesa'] . '</li>
                        <li>Data restituzione: ' . $booking['FinePrestito'] . '</li>
                    </ul>'
                );

            // 2. Notifica In-App per l'utente loggato 
            if ($current_user_id) {
                $notificationService->creaNotifica(
                    $current_user_id,
                    'Libro Restituito',
                    "Hai restituito il libro: " . $booking['Nome'] . " Per maggiori informazioni vedi i dettagli",
                    'prenotazione/prenotazione.php'
                );
            }

            // 3. Notifica In-App per i bibliotecari
            foreach ($librarian_ids as $biblio_id) {
                $notificationService->creaNotifica(
                    (int)$biblio_id,
                    'L\'utente ha restituito il libro',
                    "L'utente " . $current_user_id .  " ha restituito il libro: " . $booking['Nome'],
                    'prenotazione/prenotazione.php'
                );
                }
            }
        }

        sendBookingResponse($result['success'], $result['message'], ['late' => $result['late'] ?? false]);
    } catch (Exception $e) {
        sendBookingError('Errore: ' . $e->getMessage(), 500);
    }
} elseif (isset($_POST['elimina'])) {
    try {
        $result = $bookingService->adminCancel($id);

        if (!$result['success']) {
            sendBookingResponse(false, 'Errore: prenotazione non trovata');
        }

        if ($result['bookingData']) {
            $emailBiblio = getenv('EMAIL_BIBLIO') ?: '';
            sendEmail(
                $result['bookingData']['Email'] ?? '',
                $result['bookingData']['Email'] ?? '',
                'Prenotazione Annullata',
                '<h2>Abbiamo annullato la tua prenotazione del libro ' . $result['bookingData']['Titolo'] . '</h2>
                <p>Informazioni sulla prenotazione:</p>
                <ul>
                    <li>ISBN: ' . $result['bookingData']['ISBN'] . '</li>
                    <li>ID Copia: ' . $result['bookingData']['idCopia'] . '</li>
                    <li>ID Prenotazione: ' . $id . '</li>
                    <li>Data inizio prenotazione: ' . $result['bookingData']['InizioPrenotazione'] . '</li>
                    <li>Data fine prenotazione: ' . $result['bookingData']['FinePrenotazione'] . '</li>
                </ul>
                <br>
                <p>Per maggiori informazioni, si prega di contattare il bibliotecario (' . $emailBiblio . ').</p>'
            );

        // 2. Notifica In-App per l'utente loggato 
        $userId = $result['userId'] ?? null;
        if ($userId) {
            $notificationService->creaNotifica(
                $userId,
                'Prenotazione Eliminata',
                "La tua prenotazione del libro: " . $result['bookingData']['Titolo'] . " è stata eliminata. Per maggiori informazioni contatta il bibliotecario." . $emailBiblio,
                'prenotazione/prenotazione.php'
            );
        }

        }

        sendBookingResponse(true, 'Prenotazione eliminata con successo');
    } catch (Exception $e) {
        sendBookingError('Errore durante l\'eliminazione: ' . $e->getMessage(), 500);
    }
} else {
    sendBookingError('ID = ' . $id);
}
