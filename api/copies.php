<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\BookService;

header('Content-Type: application/json');

$isbn = $_GET['isbn'] ?? null;
if (!$isbn) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing isbn parameter']);
    exit;
}

try {
    $bookService = new BookService($pdo);
    $copies = $bookService->getCopies($isbn);

    $response = array_map(function ($copia) {
        return [
            'id'     => (int) $copia['idCopia'],
            'status' => $copia['Stato'] == 1 ? 'available' : 'borrowed',
        ];
    }, $copies);

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
