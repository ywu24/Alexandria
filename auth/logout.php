<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Logout handler - clears session and cookies
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\AuthService;

$authService = new AuthService($pdo);
$authService->logout();
redirect('../index.php');
