<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Helpers
 * @file Input validation helper functions
 */

/**
 * Validate an email address
 *
 * @param string $email The email to validate
 * @return bool True if valid
 */
function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate a password against project requirements
 * - Length between 8 and 50 characters
 * - Must contain at least one special character
 *
 * @param string $password The password to validate
 * @return bool True if valid
 */
function validate_password(string $password): bool
{
    if (strlen($password) < 8 || strlen($password) > 50) {
        return false;
    }
    return preg_match('/[!#$.,:;()@%^\-&_+=\[\]|\\\/?~`]/', $password) === 1;
}

/**
 * Validate that required fields are present and not empty
 *
 * @param array $fields Array of field names to check
 * @param array $source Source array (e.g., $_POST)
 * @return array Array of missing field names
 */
function validate_required(array $fields, array $source): array
{
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($source[$field]) || trim($source[$field]) === '') {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Validate a file upload
 *
 * @param array $file The $_FILES entry
 * @param array $allowedExtensions Allowed file extensions
 * @param int $maxSize Maximum file size in bytes
 * @return array ['valid' => bool, 'error' => string|null, 'ext' => string|null]
 */
function validate_upload(array $file, array $allowedExtensions = ['jpg', 'jpeg', 'png'], int $maxSize = 5000000): array
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => true, 'error' => null, 'ext' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Il file supera la dimensione massima consentita dal server',
            UPLOAD_ERR_FORM_SIZE => 'Il file supera la dimensione massima consentita dal form',
            UPLOAD_ERR_PARTIAL => 'Il file e stato caricato solo parzialmente',
            UPLOAD_ERR_NO_TMP_DIR => 'Cartella temporanea mancante',
            UPLOAD_ERR_CANT_WRITE => 'Errore durante la scrittura del file',
            UPLOAD_ERR_EXTENSION => 'Un estensione PHP ha interrotto il caricamento',
        ];
        return ['valid' => false, 'error' => $messages[$file['error']] ?? 'Errore sconosciuto durante il caricamento', 'ext' => null];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        return ['valid' => false, 'error' => 'Formato non supportato (solo ' . implode(', ', $allowedExtensions) . ')', 'ext' => null];
    }

    // Validate MIME type via finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];
    if (isset($allowedMimes[$ext]) && !in_array($mime, $allowedMimes[$ext], true)) {
        return ['valid' => false, 'error' => 'Tipo MIME non valido per l\'estensione fornita', 'ext' => null];
    }

    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'Il file e troppo grande (max ' . ($maxSize / 1000000) . 'MB)', 'ext' => null];
    }

    return ['valid' => true, 'error' => null, 'ext' => $ext];
}

/**
 * Validate that a value is a positive integer
 *
 * @param mixed $value The value to check
 * @return int|false The integer value, or false if invalid
 */
function validate_positive_int(mixed $value): int|false
{
    $int = filter_var($value, FILTER_VALIDATE_INT);
    if ($int === false || $int < 1) {
        return false;
    }
    return $int;
}

/**
 * Validate a date string format
 *
 * @param string $date The date string
 * @param string $format Expected format (default: Y-m-d)
 * @return bool True if valid
 */
function validate_date(string $date, string $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d !== false && $d->format($format) === $date;
}

/**
 * Sanitize a string by stripping HTML tags and trimming
 *
 * @param string|null $str Input string
 * @return string Sanitized string
 */
function sanitize_string(?string $str): string
{
    return trim(strip_tags($str ?? ''));
}

/**
 * Validate ISBN format (basic 10 or 13 digit check)
 *
 * @param string $isbn The ISBN to validate
 * @return bool True if valid format
 */
function validate_isbn(string $isbn): bool
{
    $clean = preg_replace('/[^0-9X]/i', '', $isbn);
    return strlen($clean) === 10 || strlen($clean) === 13;
}
