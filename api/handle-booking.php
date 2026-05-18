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

header('Content-Type: application/json');

$authService = new AuthService($pdo);

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
        }

        sendBookingResponse(true, 'Prenotazione eliminata con successo');
    } catch (Exception $e) {
        sendBookingError('Errore durante l\'eliminazione: ' . $e->getMessage(), 500);
    }
} else {
    sendBookingError('ID = ' . $id);
}
