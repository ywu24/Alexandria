<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Handles user deletion (self-delete or admin delete)
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\UserService;

$authService = new AuthService($pdo);
$userService = new UserService($pdo);

// Self-delete from edit_profile page
if (!$authService->isAdmin() && isset($_POST['delete_account'])) {
    $email = $_SESSION['email'] ?? '';

    try {
        $userService->delete($email);
        $authService->logout();
        redirect('../../index.php');
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
        redirect('../../edit_profile/edit_profile.php');
    }
}

// Admin delete from dashboard
if ($authService->isAdmin() && isset($_GET['id'])) {
    $emailToDelete = $_GET['id'];
    $adminEmail = $_SESSION['email'] ?? '';

    try {
        if ($userService->adminDelete($emailToDelete, $adminEmail)) {
            flash('success', 'Utente eliminato con successo');
            redirect('dashboardUtenti.php');
        } else {
            flash('error', 'Errore durante l\'eliminazione dell\'utente');
            redirect('dashboardUtenti.php');
        }
    } catch (RuntimeException $e) {
        flash('error', 'Errore durante l\'eliminazione dell\'utente: ' . $e->getMessage());
        redirect('dashboardUtenti.php');
    }
}

redirect('dashboardUtenti.php');
