<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file User profile edit page with password change and picture upload
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\UserService;
use Alexandria\Services\AuthService;

$userService = new UserService($pdo);
$authService = new AuthService($pdo);

$root = '..';

if (!$authService->isAuthenticated()) {
    redirect('../auth/login.php');
}

$email = $_SESSION['email'];
$userData = $userService->getProfileData($email);

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        flash('error', 'Le password non corrispondono');
        redirect('edit_profile.php');
    }

    if (!validate_password($newPassword)) {
        flash('error', 'Password non sicura');
        redirect('edit_profile.php');
    }

    try {
        $authService->changePassword($email, $currentPassword, $newPassword);
        flash('success', 'Password cambiata con successo');
    } catch (Exception $e) {
        flash('error', $e->getMessage());
    }
    redirect('edit_profile.php');
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <?php render_head('Edit Profile', ['css/pages/forms.css', 'css/pages/footer.css'], ['js/utils.js'], '..'); ?>
</head>

<body class="bg-light edit-profile">

    <div id="nav-placeholder">
        <?php require_once('../nav/nav.php'); ?>
    </div>

    <div class="container py-5">
        <div id="messages">
            <?php render_messages(); ?>
        </div>
        <div class="row justify-content-center">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#profile-settings" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                                <svg class="icon me-3" style="width:20px; height:20px;"><use href="../img/icons.svg#account"/></svg>
                                <span>Your Profile</span>
                            </a>
                            <a href="#password-reset" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                                <svg class="icon me-3" style="width:20px; height:20px;"><use href="../img/icons.svg#edit-profile"/></svg>
                                <span>Change Password</span>
                            </a>
                            <a href="#danger-zone" class="list-group-item list-group-item-action d-flex align-items-center py-3 text-danger">
                                <svg class="icon me-3" style="width:20px; height:20px; fill:currentColor;"><use href="../img/icons.svg#warning"/></svg>
                                <span>Danger Zone</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-7">

                <!-- Profile Card -->
                <div class="card mb-4 shadow-sm border-0 settings-section" id="profile-settings">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body p-4 text-center">

                        <div class="profile-header mb-4">
                            <div class="position-relative d-inline-block mb-3">
                                <img src="../img/users/<?php echo e($userData['propic']); ?>" alt="Profile"
                                     class="rounded-circle border shadow-sm profile-img">
                            </div>

                            <div class="profile-details">
                                <?php if (isset($_SESSION['nome'])): ?>
                                    <h3 class="fw-bold mb-1"><?php echo e($_SESSION['nome'] . ' ' . $_SESSION['cognome']); ?></h3>
                                    <p class="text-muted mb-2"><?php echo e($_SESSION['email']); ?></p>

                                    <?php if ($_SESSION['utenza'] != 2 && $_SESSION['utenza'] != 1): ?>
                                        <div class="mb-3">
                                            <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.9rem;">
                                                Punteggio: <?php echo $userData['punteggio']; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="profile-upload-section bg-light rounded p-4 mt-4">
                            <h6 class="text-muted mb-3">Change Profile Picture</h6>
                            <form action="./change_propic/change_propic.php" method="POST" enctype="multipart/form-data" class="row g-3 justify-content-center">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="file" name="image" class="form-control" id="image">
                                        <button type="submit" name="propicIns" class="btn btn-secondary">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Reset Card -->
                <div class="card mb-4 shadow-sm border-0 settings-section" id="password-reset">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0">Security</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="./edit_profile.php" method="POST">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-muted">Current Password</label>
                                    <input type="password" name="current_password" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted">New Password</label>
                                    <input type="password" name="new_password" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-muted">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" name="change_password" class="btn btn-primary btn-lg w-100 shadow-sm">
                                        Update Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DANGER ZONE -->
                <div class="card shadow-sm border-0 danger-card" id="danger-zone">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-danger mb-1 fw-bold">Elimina account permanentemente</h5>
                            <p class="text-muted mb-0 small">L'eliminazione è irreversibile. Tutti i tuoi dati verranno cancellati.</p>
                            <p class="text-muted mb-0 small fw-bold">Possibile solo se non si hanno prestiti o prenotazioni attivi.</p>
                        </div>
                        <?php if ($_SESSION['utenza'] == 2 || $_SESSION['utenza'] == 1): ?>
                            <button type="button" class="btn btn-danger disabled">
                                Elimina Account
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                Elimina Account
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger fw-bold" id="deleteAccountModalLabel">Conferma Eliminazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <svg width="80" height="80" fill="currentColor" class="bi bi-exclamation-triangle text-danger" viewBox="0 0 16 16"><use href="../img/icons.svg#warning"/></svg>
                    </div>
                    <p class="fs-5">Sei assolutamente sicuro di voler eliminare il tuo account?</p>
                    <p class="text-muted">Questa azione è <strong>irreversibile</strong> e tutti i tuoi dati verranno cancellati definitivamente.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Annulla</button>
                    <form action="../dashboard/dashboardUtenti/eliminaUtente.php" method="POST">
                        <button type="submit" name="delete_account" class="btn btn-danger px-4">Sì, elimina definitivamente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once('../nav/footer.php'); ?>
</body>


</html>
