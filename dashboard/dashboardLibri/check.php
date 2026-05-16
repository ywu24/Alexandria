<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Checks if a book exists by ISBN and redirects to add form
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\BookService;

if (isset($_POST['check'])) {
    $isbn = $_POST['isbn-check'] ?? '';
    $bookService = new BookService($pdo);
    $exists = $bookService->getByIsbn($isbn) !== null;

    $_SESSION['libroEsiste'] = $exists;
    redirect('aggiungiLibro.php');
}
