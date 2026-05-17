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
            redirect('dashboardUtenti.php?rimosso=1');
        } else {
            redirect('dashboardUtenti.php?errore=2');
        }
    } catch (RuntimeException $e) {
        redirect('dashboardUtenti.php?errore=1');
    }
}

redirect('dashboardUtenti.php');
