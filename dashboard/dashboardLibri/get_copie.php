<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file AJAX endpoint for book copies data
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\BookService;

header('Content-Type: application/json');

if (!isset($_POST['isbn'])) {
    echo json_encode([]);
    exit;
}

$bookService = new BookService($pdo);
$copie = $bookService->getCopies($_POST['isbn']);

echo json_encode($copie);
