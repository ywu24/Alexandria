<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage API
 * @file AJAX endpoint for loading reviews with infinite scroll
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\ReviewService;

$reviewService = new ReviewService($pdo);

$bookId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = 5;

if ($bookId <= 0) {
    http_response_code(400);
    exit;
}

try {
    $reviews = $reviewService->getForBook($bookId, $limit, $offset);

    if (empty($reviews)) {
        exit;
    }

    foreach ($reviews as $row) {
        render_review_card($row, '../');
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('[api/reviews.php] Error: ' . $e->getMessage());
    exit;
}
