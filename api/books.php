<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$bookService = new BookService($pdo);

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'Nome';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

try {
    $books = $bookService->getBooksWithCopies(
        $search !== '' ? $search : null,
        $sort,
        $limit,
        $offset
    );

    $response = array_map(function ($row) {
        return [
            'isbn'  => $row['ISBN'],
            'title' => $row['Nome'],
            'author' => $row['Autore'],
            'genre' => $row['Genere'],
            'year'  => $row['AnnoPubblicazione'],
            'publisher' => $row['CasaEditrice'],
            'copies' => (int) $row['copie'],
        ];
    }, $books);

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
