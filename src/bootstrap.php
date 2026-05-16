<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Core
 * @file Central bootstrap file - loads environment, starts secure session, initializes database, and defines global helpers
 */

// Prevent multiple inclusions
if (defined('ALEXANDRIA_BOOTSTRAPPED')) {
    return;
}
define('ALEXANDRIA_BOOTSTRAPPED', true);

// Base path constants
if (!defined('ALEXANDRIA_ROOT')) {
    define('ALEXANDRIA_ROOT', dirname(__DIR__));
}

// Composer autoload
require_once ALEXANDRIA_ROOT . '/vendor/autoload.php';

// Load environment variables
if (file_exists(ALEXANDRIA_ROOT . '/.env')) {
    $env = parse_ini_file(ALEXANDRIA_ROOT . '/.env');
    if ($env !== false) {
        foreach ($env as $key => $value) {
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
        }
    }
} else {
    throw new RuntimeException('Error: .env file not found');
}

// Error handling based on APP_DEBUG
$debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Secure session initialization
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Require the singleton database connection class
require_once ALEXANDRIA_ROOT . '/utils/connect.php';

// Initialize PDO connection
$pdo = null;
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (Exception $e) {
    error_log('[bootstrap.php] DB connection failed: ' . $e->getMessage());
    if ($debug) {
        die('Database connection failed: ' . $e->getMessage());
    }
    die('Service unavailable. Please try again later.');
}

// Helper functions
require_once __DIR__ . '/Helpers/functions.php';
require_once __DIR__ . '/Helpers/validation.php';
require_once __DIR__ . '/Helpers/pagination.php';

// Attempt cookie-based auto-login
$authService = new \Alexandria\Services\AuthService($pdo);
$authService->attemptCookieLogin();

// View functions
require_once __DIR__ . '/Views/components/messages.php';
require_once __DIR__ . '/Views/components/head.php';
require_once __DIR__ . '/Views/components/review-card.php';
require_once __DIR__ . '/Views/components/book-card.php';
require_once __DIR__ . '/Views/components/star-rating.php';
