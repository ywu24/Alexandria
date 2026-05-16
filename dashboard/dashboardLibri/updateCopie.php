<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file AJAX endpoint for adjusting book copy count
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\BookService;

header('Content-Type: text/plain');

if (!isset($_POST['copie']) || !isset($_POST['isbn'])) {
    echo 'ERRORE: parametri mancanti';
    exit;
}

$isbn = $_POST['isbn'];
$nuoveCopie = (int) $_POST['copie'];

$bookService = new BookService($pdo);

try {
    $result = $bookService->adjustCopyCount($isbn, $nuoveCopie);
    echo $result['message'];
} catch (RuntimeException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo 'ERRORE: ' . $e->getMessage();
}
