<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Helpers
 * @file Common utility functions used across the application
 */

/**
 * Escape HTML entities to prevent XSS
 *
 * @param string|null $str The string to escape
 * @return string The escaped string
 */
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL and terminate execution
 *
 * @param string $url The URL to redirect to
 * @return never
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Set a flash message in the session
 *
 * @param string $key The message key (e.g., 'success', 'error')
 * @param string $message The message text
 * @return void
 */
function flash(string $key, string $message): void
{
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][$key] = $message;
}

/**
 * Get and clear a flash message
 *
 * @param string $key The message key
 * @return string|null The message text, or null if not set
 */
function flash_get(string $key): ?string
{
    if (!isset($_SESSION['flash_messages'][$key])) {
        return null;
    }
    $message = $_SESSION['flash_messages'][$key];
    unset($_SESSION['flash_messages'][$key]);
    return $message;
}

/**
 * Check if a flash message exists
 *
 * @param string $key The message key
 * @return bool True if the flash message exists
 */
function flash_has(string $key): bool
{
    return isset($_SESSION['flash_messages'][$key]);
}

/**
 * Get old form input value from session
 *
 * @param string $key The form field name
 * @param mixed $default Default value if not found
 * @return mixed The old value or default
 */
function old(string $key, mixed $default = ''): mixed
{
    if (isset($_SESSION['old'][$key])) {
        $value = $_SESSION['old'][$key];
        unset($_SESSION['old'][$key]);
        return $value;
    }
    return $default;
}

/**
 * Store current POST data in session for repopulation
 *
 * @return void
 */
function store_old(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['old'] = $_POST;
    }
}

/**
 * Render star rating as HTML string
 *
 * @param int|float $rating The rating value (0-5)
 * @return string HTML star string
 */
function render_stars(int|float $rating): string
{
    $full = (int) round((float) $rating);
    $empty = 5 - $full;
    return str_repeat('&#9733;', $full) . str_repeat('&#9734;', $empty);
}

/**
 * Format a date from database format to Italian display format
 *
 * @param string|null $date The date string
 * @return string Formatted date (d/m/Y) or empty string
 */
function format_date(?string $date): string
{
    if (empty($date)) {
        return '';
    }
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($d === false) {
        $d = new DateTime($date);
    }
    return $d->format('d/m/Y');
}

/**
 * Get the application root URL
 *
 * @return string The application root URL
 */
function app_url(): string
{
    return rtrim(getenv('APP_URL') ?: '', '/');
}

/**
 * Sanitize a file name for safe storage
 *
 * @param string $fileName Original file name
 * @return string Sanitized file name
 */
function sanitize_filename(string $fileName): string
{
    $base = pathinfo($fileName, PATHINFO_FILENAME);
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $base = preg_replace('/[^a-zA-Z0-9_-]/', '', $base);
    return $base . uniqid() . '.' . strtolower($ext);
}
