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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/design-system.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/pages/forms.css">
    <link rel="stylesheet" href="../css/pages/footer.css">
    <link rel="stylesheet" href="../css/utilities.css">
</head>

<body class="bg-light edit-profile">

    <div id="nav-placeholder">
        <?php require_once('../nav/nav.php'); ?>
    </div>

    <div>
        <?php render_messages(); ?>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 edit-section mb-4">
                <div class="card shadow-sm border-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="#profile-settings" class="text-dark">
                                <div class="section"><span>Your Profile</span></div>
                            </a></li>
                        <li class="list-group-item"><a href="#password-reset" class="text-dark">
                                <div class="section"><span>Change Password</span></div>
                            </a></li>
                    </ul>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-7 settings px-lg-4">

                <!-- Profile Card -->
                <div class="card mb-4 shadow-sm border-0 settings-section" id="profile-settings">
                    <div class="card-header bg-dark text-white font-weight-bold">Profile Information</div>
                    <div class="card-body text-center">

                        <div class="settings-profile-info mb-4">
                            <div class="edit-profile-parameter">
                                <!-- Immagine Profilo -->
                                <img src="../img/users/<?php echo e($userData['propic']); ?>" alt="Profile"
                                    class="rounded-circle mb-3 border">

                                <div class="data-profile-info">
                                    <?php if (isset($_SESSION['nome'])): ?>
                                        <h3 class="mb-1"><?php echo e($_SESSION['nome'] . ' ' . $_SESSION['cognome']); ?></h3>
                                        <p class="text-muted mb-2"><?php echo e($_SESSION['email']); ?></p>

                                        <?php if ($_SESSION['utenza'] != 2 && $_SESSION['utenza'] != 1): ?>
                                            <div class="mt-2 mb-4">
                                                <span class="badge badge-primary p-2" style="font-size: 1rem; border-radius: 20px;">
                                                    Punteggio: <?php echo $userData['punteggio']; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Parte inferiore: Form caricamento immagine -->
                        <div class="mt-4 px-lg-5">
                            <h6 class="text-muted mb-3">Change Profile Picture</h6>
                            <form action="./change_propic/change_propic.php" method="POST" enctype="multipart/form-data">
                                <div class="custom-file mb-3 text-left">
                                    <input type="file" name="image" class="custom-file-input" id="image">
                                    <label class="custom-file-label" for="image">Choose new image...</label>
                                </div>
                                <button type="submit" name="propicIns" class="btn btn-secondary btn-sm px-4">
                                    Update Image
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                <div id="messages"></div>

                <!-- Password Reset Card -->
                <div class="card mb-4 shadow-sm border-0 settings-section" id="password-reset">
                    <div class="card-header bg-dark text-white font-weight-bold">Security</div>
                    <div class="card-body">
                        <form action="./edit_profile.php" method="POST">
                            <div class="form-group mb-3">
                                <label class="section-title1">Current Password</label>
                                <input type="password" name="current_password" class="form-control password" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="section-title1">New Password</label>
                                <input type="password" name="new_password" class="form-control password" required>
                            </div>
                            <div class="form-group mb-4">
                                <label class="section-title1">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control password" required>
                            </div>
                            <input type="submit" name="change_password" value="Confirm Changes"
                                class="btn btn-primary btn-block btn-lg shadow-sm">
                        </form>
                    </div>
                </div>

                <!-- DANGER ZONE -->
                <div class="card shadow-sm danger-card" id="danger-zone">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-danger mb-1 font-weight-bold">Elimina account permanentemente</h5>
                            <p class="text-muted mb-0 small">L'eliminazione e irreversibile. Tutti i tuoi dati verranno cancellati. POSSIBILE SOLO SE NON SI HANNO PRESTITI O PRENOTAZIONI ATTIVI</p>
                        </div>
                        <?php if ($_SESSION['utenza'] == 2): ?>
                            <button type="submit" name="delete_account" class="btn btn-danger disabled">
                                Elimina Account
                            </button>
                        <?php else: ?>
                            <form action="../dashboard/dashboardUtenti/eliminaUtente.php" method="POST" onsubmit="return confirmDelete();">
                                <button type="submit" name="delete_account" class="btn btn-outline-danger">
                                    Elimina Account
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="edit_profile.js"></script>
    <script>
        document.querySelectorAll('.custom-file-input').forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.value.split("\\").pop();
                const label = this.nextElementSibling;
                if (label) {
                    label.classList.add('selected');
                    label.textContent = fileName;
                }
            });
        });
    </script>
    <?php require_once('../nav/footer.php'); ?>
</body>

</html>
