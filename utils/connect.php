<?php
// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
} else {
    exit('Error: .env file not found');
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER');
$password = getenv('DB_PASS');
$database = getenv('DB_NAME');

$conn = mysqli_connect($host, $user, $password, $database);

if (mysqli_connect_error()) {
    exit('Error' . mysqli_connect_error());
}
?>