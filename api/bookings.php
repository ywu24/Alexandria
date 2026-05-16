<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookingService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$bookingService = new BookingService($pdo);

$filtroStato = $_GET['status'] ?? 'tutti';
$dataInizio = $_GET['from'] ?? null;
$dataFine = $_GET['to'] ?? null;
$sortType = $_GET['sort'] ?? 'idPrenotazione DESC';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

try {
    $bookings = $bookingService->getAll($filtroStato, $dataInizio, $dataFine, $sortType, $limit, $offset);

    $response = array_map(function ($row) {
        $color = 'text-muted';
        if ($row['stato_calcolato'] === 'Prenotato') $color = 'text-success';
        if ($row['stato_calcolato'] === 'In Prestito') $color = 'text-warning';
        if ($row['stato_calcolato'] === 'In Ritardo') $color = 'text-danger';

        $inizio = $row['InizioPrestito'] ?? $row['InizioPrenotazione'];
        $fine = $row['FinePrestito'] ?? ($row['FineAttesa'] ?? $row['FinePrenotazione']);

        return [
            'id'         => (int) $row['idPrenotazione'],
            'isbn'       => $row['ISBN'],
            'title'      => $row['Nome'],
            'author'     => $row['Autore'],
            'email'      => $row['email'],
            'cover'      => $row['Copertina'],
            'status'     => $row['stato_calcolato'],
            'color'      => $color,
            'start'      => date('d/m/Y', strtotime($inizio)),
            'end'        => date('d/m/Y', strtotime($fine)),
        ];
    }, $bookings);

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
