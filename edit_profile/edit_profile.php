<?php
session_start();
$root = '..';
require_once("../utils/connect.php");
require_once("../auth/cookies.php");
$email = $_SESSION['email'];

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

if ($query = $pdo->prepare('SELECT propic FROM Utente WHERE Email=:email')) {
    $query->bindParam(':email', $email);
    $query->execute();
    $propics = $query->fetch();
    $query->closeCursor();
} else {
    echo "Errore durante la preparazione della query: " . $pdo->errorInfo()[2];
    exit;
}

if (isset($_POST['change_password'])) {
    if ($query = $pdo->prepare('SELECT * FROM Utente WHERE Email = :email')) {
        $query->bindParam(':email', $email);
        $query->execute();
        $user = $query->fetch();
        $query->closeCursor();

        if (password_verify($_POST['current_password'], $user['Password'])) {
            $password = $_POST['new_password'];
            $password_again = $_POST['confirm_password'];
            if (strlen($password) >= 8 && preg_match('{[!#$.,:;()@%^\-&_+=\[\]|\\/<>?~`]}', $password)) {
                if (strlen($password) <= 50) {
                    if ($password === $password_again) {
                        if ($query = $pdo->prepare('UPDATE Utente SET Password = :password WHERE Email = :email')) {
                            $password = password_hash($password, PASSWORD_BCRYPT);
                            $query->bindParam(':password', $password);
                            $query->bindParam(':email', $email);
                            $query->execute();
                            $query->closeCursor();
                            $_SESSION['success_msg'] = "Password cambiata con successo";
                            header("Location: edit_profile.php");
                            exit;
                        } else {
                            $_SESSION['error_msg'] = "Errore durante l'aggiornamento";
                        }
                    } else {
                        $_SESSION['error_msg'] = "Le password non corrispondono";
                    }
                } else {
                    $_SESSION['error_msg'] = "Password troppo lunga";
                }
            } else {
                $_SESSION['error_msg'] = "Password non sicura";
            }
        } else {
            $_SESSION['error_msg'] = "Password attuale errata";
        }
    } else {
        $_SESSION['error_msg'] = "Errore durante la preparazione della query";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <!-- Tuoi CSS Originali -->
    <link rel="stylesheet" href="../css/edit_profile.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/messaggi.css">
</head>

<body class="bg-light">

    <div id="nav-placeholder">
        <?php require_once("../nav/nav.php"); ?>
    </div>
    <div class="messages">
        <?php
        if (isset($_SESSION['success_msg'])) {
            echo '<p class="successo">' . $_SESSION['success_msg'] . '</p>';
            unset($_SESSION['success_msg']);
        }
        if (isset($_SESSION['error_msg'])) {
            echo '<p class="errore">' . $_SESSION['error_msg'] . '</p>';
            unset($_SESSION['error_msg']);
        }
        ?></div>
    <div class="container py-5">



        <div class="row justify-content-center">
            <!-- Sidebar Navigation (Desktop Only via your CSS or d-none d-lg-block) -->
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
                    <div class="card-header bg-dark text-white font-weight-bold">👤 Profile Information</div>
                    <div class="card-body text-center">
                        <div class="settings-profile-info">
                            <div class="data-profile-info">
                                <?php if (isset($_SESSION['nome'])): ?>
                                    <h4 class="mb-1"><?php echo $_SESSION['nome'] . " " . $_SESSION['cognome']; ?></h4>
                                    <p class="text-muted"><?php echo $_SESSION['email']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Picture Card -->
                <div class="card mb-4 shadow-sm border-0 settings-section">
                    <div class="card-header bg-dark text-white font-weight-bold">🖼️ Profile Picture</div>
                    <div class="card-body text-center">
                        <div class='edit-profile-parameter'>
                            <img src='../img/users/<?php echo $propics["propic"]; ?>' alt='Profile' class="rounded-circle mb-3 border" style="width: 120px; height: 120px; object-fit: cover;">
                            <form action='./change_propic/change_propic.php' method='POST' enctype='multipart/form-data' class="mt-2">
                                <div class="custom-file mb-3 text-left">
                                    <input type='file' name='image' class="custom-file-input" id="image">
                                    <label class="custom-file-label" for="image">Choose image...</label>
                                </div>
                                <button type='submit' name='propicIns' class="btn btn-secondary btn-block">Update Image</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Reset Card -->
                <div class="card mb-4 shadow-sm border-0 settings-section" id="password-reset">
                    <div class="card-header bg-dark text-white font-weight-bold">🔑 Security</div>
                    <div class="card-body">
                        <form action="./edit_profile.php" method="POST">
                            <div class="form-group mb-3">
                                <label class="section-title1">Current Password</label>
                                <input type="password" name="current_password" class="form-control password">
                            </div>
                            <div class="form-group mb-3">
                                <label class="section-title1">New Password</label>
                                <input type="password" name="new_password" class="form-control password">
                            </div>
                            <div class="form-group mb-4">
                                <label class="section-title1">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control password">
                            </div>
                            <input type="submit" name="change_password" value="Confirm Changes" class="btn btn-primary btn-block btn-lg shadow-sm">
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts per Bootstrap e gestione input file -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script>
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    </script>
</body>

</html>