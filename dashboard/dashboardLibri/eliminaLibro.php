<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file AJAX endpoint for deleting a book
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

if (!isset($_POST['isbn'])) {
    echo 'Errore: ISBN mancante';
    exit;
}

$bookService = new BookService($pdo);
$isbn = $_POST['isbn'];

try {
    $bookService->delete($isbn);
    echo "okLibro con isbn $isbn eliminato con successo!";
} catch (RuntimeException $e) {
    echo "Errore nell'eliminazione del libro con isbn $isbn, " . $e->getMessage();
}
