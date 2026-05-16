<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file AJAX endpoint for book table data
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookService;

header('Content-Type: application/json');

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    exit(json_encode([]));
}

$bookService = new BookService($pdo);

$search = $_POST['search'] ?? '';
$sort = $_POST['sort_type'] ?? 'Nome';
$offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
$limit = 10;

$libri = $bookService->getBooksWithCopies(
    $search !== '' ? $search : null,
    $sort,
    $limit,
    $offset
);

$response = [];
foreach ($libri as $row) {
    $isbn = $row['ISBN'];
    $btnElimina = ($row['copie'] == 0)
        ? "<button type='button' class='btn btn-danger btn-sm elimina'>Elimina</button>"
        : "";

    $html = "
    <tr data-isbn='$isbn'>
        <th scope='row' class='row-header'><button class='btn btn-sm btn-info btn-espandi' type='button' data-isbn='$isbn'>+</button> $isbn</th>
        <td>" . e($row['Nome']) . "</td>
        <td class='col-nascondi'>" . e($row['Autore']) . "</td>
        <td class='col-nascondi'>" . e($row['Genere']) . "</td>
        <td class='col-nascondi'>{$row['AnnoPubblicazione']}</td>
        <td class='col-nascondi'>" . e($row['CasaEditrice']) . "</td>
        <td class='col-nascondi'>
            <input type='number' value='{$row['copie']}' class='form-control form-control-sm d-inline-block w-auto'>
            <button class='btn btn-outline-info btn-sm save'>Salva</button>
        </td>
        <td class='col-nascondi'>
            <a class='btn btn-primary btn-sm btn_modifica' href='modificaLibro.php?id=$isbn'>Modifica</a>
            $btnElimina
        </td>
        <td class='mobile-only'>
            <button class='btn btn-sm btn-secondary btn-info-mobile' type='button'>Info</button>
        </td>
    </tr>
    <tr id='row-details-$isbn' class='bg-light d-none'>
        <td colspan='9'><div id='content-$isbn' class='p-3'>Caricamento in corso...</div></td>
    </tr>";

    $response[] = ['html' => $html];
}

echo json_encode($response);
