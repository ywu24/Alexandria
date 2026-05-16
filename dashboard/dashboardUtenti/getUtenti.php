<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file AJAX endpoint for user table data
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\UserService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    exit(json_encode([]));
}

$userService = new UserService($pdo);

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$sort = $_POST['sort_type'] ?? 'id';
$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 10;
$offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;

$utenzaFilter = null;
if (isset($_POST['utenza']) && $_POST['utenza'] !== '') {
    $utenzaFilter = (int) $_POST['utenza'];
}

try {
    $utenti = $userService->getUsers(
        $search !== '' ? $search : null,
        $sort,
        $limit,
        $offset,
        $utenzaFilter
    );
} catch (PDOException $e) {
    exit(json_encode([["html" => "<td>Errore SQL: " . $e->getMessage() . "</td>"]]));
}

$ruoli = [1 => "Admin", 2 => "Bibliotecario", 3 => "Premium", 4 => "Standard"];
$response = [];

foreach ($utenti as $row) {
    $desc = $ruoli[$row['Utenza']] ?? "N/A";

    if ($authService->isAdmin()) {
        $html = "
            <th scope='row' class='col-nascondi row-header'>{$row['id']}</th>
            <td>" . e($row['Nome']) . "</td>
            <td>" . e($row['Cognome']) . "</td>
            <td class='col-nascondi'>" . e($row['Email']) . "</td>
            <td class='col-nascondi'>$desc</td>
            <td class='col-nascondi'>{$row['punteggio']}</td>
            <td class='col-nascondi'>
                <div class='btn_actions'>
                    <a class='btn btn-primary btn-sm' href='dettaglioUtente.php?id={$row['id']}'>Prenotazioni</a>
                    <a class='btn btn-primary btn-sm' href='modificaUtente.php?id={$row['id']}'>Modifica</a>
                    <a class='btn btn-danger btn-sm' href='eliminaUtente.php?id={$row['Email']}'>Elimina</a>
                </div>
            </td>";
    } else {
        $html = "
            <td>" . e($row['Nome']) . "</td>
            <td>" . e($row['Cognome']) . "</td>
            <td class='col-nascondi'>" . e($row['Email']) . "</td>
            <td class='col-nascondi'>{$row['punteggio']}</td>
            <td class='col-nascondi'>
                <div class='btn_actions text-center'>
                    <a class='btn btn-primary btn-sm' href='dettaglioUtente.php?id={$row['id']}'>Prenotazioni</a>
                </div>
            </td>";
    }

    $response[] = [
        'html'      => $html,
        'id'        => $row['id'],
        'email'     => $row['Email'],
        'punteggio' => $row['punteggio'],
        'ruolo'     => $desc
    ];
}

echo json_encode($response);
