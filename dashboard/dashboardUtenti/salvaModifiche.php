<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Handles saving user edits by admin
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\UserService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin()) {
    redirect('dashboardUtenti.php');
}

$id = (int) ($_POST['id'] ?? 0);
$nome = $_POST['nome'] ?? '';
$cognome = $_POST['cognome'] ?? '';
$email = $_POST['email'] ?? '';
$ruolo = $_POST['ruolo'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($id) || empty($nome) || empty($cognome) || empty($email) || empty($ruolo)) {
    redirect("modificaUtente.php?error=1&id=$id");
}

$userService = new UserService($pdo);

try {
    $data = [
        'Nome' => $nome,
        'Cognome' => $cognome,
        'Email' => $email,
        'Utenza' => $ruolo,
    ];
    if (!empty($password)) {
        $data['Password'] = $password;
    }

    $userService->updateById($id, $data);
    redirect('dashboardUtenti.php?aggiornato=1');
} catch (PDOException $e) {
    redirect("modificaUtente.php?error=2&id=$id");
}
