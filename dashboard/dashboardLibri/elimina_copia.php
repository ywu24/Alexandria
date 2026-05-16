<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file AJAX endpoint for deleting a single book copy
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

if (!isset($_POST['id'])) {
    echo 'Errore: ID mancante';
    exit;
}

$bookService = new BookService($pdo);
$id = (int) $_POST['id'];

if ($bookService->deleteCopy($id)) {
    echo "okLibro con id $id eliminato con successo!";
} else {
    echo "Errore nell'eliminazione del libro con id $id";
}
