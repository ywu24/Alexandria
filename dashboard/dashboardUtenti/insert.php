<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Handles user insertion by admin
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\UserService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin()) {
    redirect('dashboardUtenti.php');
}

if (empty($_POST['nome']) || empty($_POST['cognome']) || empty($_POST['email']) || empty($_POST['password'])) {
    redirect('aggiungiUtente.php?error=1');
}

$userService = new UserService($pdo);

$nome = $_POST['nome'];
$cognome = $_POST['cognome'];
$email = $_POST['email'];
$ruolo = !empty($_POST['ruolo']) ? $_POST['ruolo'] : '4';
$password = $_POST['password'];

try {
    $userService->insert([
        'email' => $email,
        'nome' => $nome,
        'cognome' => $cognome,
        'password' => $password,
        'utenza' => $ruolo,
    ]);
    redirect('dashboardUtenti.php?aggiunto=1');
} catch (RuntimeException $e) {
    redirect('aggiungiUtente.php?error=2');
} catch (PDOException $e) {
    redirect('aggiungiUtente.php?error=2');
}
