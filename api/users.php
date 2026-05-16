<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\UserService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$userService = new UserService($pdo);

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'Nome';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$utenzaFilter = isset($_GET['utenza']) && $_GET['utenza'] !== '' ? (int) $_GET['utenza'] : null;

$ruoli = [1 => "Admin", 2 => "Bibliotecario", 3 => "Premium", 4 => "Standard"];

try {
    $users = $userService->getUsers(
        $search !== '' ? $search : null,
        $sort,
        $limit,
        $offset,
        $utenzaFilter
    );

    $response = array_map(function ($row) use ($ruoli) {
        return [
            'id'        => (int) $row['id'],
            'name'      => $row['Nome'],
            'surname'   => $row['Cognome'],
            'email'     => $row['Email'],
            'role'      => $ruoli[$row['Utenza']] ?? 'N/A',
            'role_id'   => (int) $row['Utenza'],
            'score'     => (int) $row['punteggio'],
        ];
    }, $users);

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
