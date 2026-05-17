<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Navigation component with user menu, search, theme toggle, and logout
 */

use Alexandria\Services\AuthService;
use Alexandria\Services\UserService;

if (!isset($root)) $root = '';

if (isset($_POST['logout'])) {
    $authService = new AuthService($pdo);
    $authService->logout();
    redirect($root . '/index.php');
}
?>

<nav class="nav1">
    <a href="<?php echo $root; ?>/index.php">
        <img src="<?php echo $root; ?>/img/logo.png" class="logo">
    </a>

    <form method="get" action="<?php echo $root; ?>/lista/lista.php" class="search-form">
        <input type="search" name="search" placeholder="Search...">
        <button type="submit" name="search_btn">
            <svg width="20" height="20" aria-hidden="true">
                <use href="<?php echo $root; ?>/img/icons.svg#search"></use>
            </svg>
        </button>
    </form>

<?php
$isLoggedIn = isset($_SESSION['email']);
$utenza = $_SESSION['utenza'] ?? null;

if ($isLoggedIn) {
    $email = $_SESSION['email'];
    $userService = new UserService($pdo);
    $utente = $userService->getByEmail($email);
?>

    <ul>
        <li class="li-icon">
            <a href="<?php echo $root; ?>/lista/lista.php">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#library"/></svg>
            </a>
        </li>
        <?php if ($utenza == 1 || $utenza == 2): ?>
        <li class="li-icon">
            <a href="<?php echo $root; ?>/dashboard/dashboard.php">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#dashboard"/></svg>
            </a>
        </li>
        <?php endif; ?>
        <li class="li-icon acc">
            <a href="#"></a>
            <svg class="icon" onclick="toggleMenu()"><use href="<?php echo $root; ?>/img/icons.svg#account"/></svg>
        </li>
    </ul>

    <div class="sub-menu-wrap" id="subMenu">
        <div class="sub-menu">
            <div class="user-info">
                <img src="<?php echo $root; ?>/img/users/<?php echo $utente['propic'] ?? 'userDashFavicon.svg'; ?>">
                <h3 style="font-size: 1.2rem;"><?php echo ($utente['Nome'] ?? '') . ' ' . ($utente['Cognome'] ?? ''); ?></h3>
                <h4>Punti: <?php echo $utente['punteggio'] ?? 0; ?></h4>
            </div>

            <a href="<?php echo $root; ?>/prenotazione/prenotazione.php" class="sub-menu-link">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#booking"/></svg>
                <p>Prenotazioni</p>
                <span>&gt;</span>
            </a>

            <a href="<?php echo $root; ?>/lista/lista.php" class="sub-menu-link">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#library"/></svg>
                <p>Lista Libri</p>
                <span>&gt;</span>
            </a>

            <a href="<?php echo $root; ?>/edit_profile/edit_profile.php" class="sub-menu-link">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#edit-profile"/></svg>
                <p>Edit Profile</p>
                <span>&gt;</span>
            </a>

            <?php if ($utenza == 1 || $utenza == 2): ?>
            <a href="<?php echo $root; ?>/dashboard/dashboard.php" class="sub-menu-link">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#dashboard"/></svg>
                <p>Dashboard</p>
                <span>&gt;</span>
            </a>
            <?php endif; ?>

            <?php if ($utenza == 3 || $utenza == 4): ?>
            <a href="<?php echo $root; ?>/segnalazione/segnalazione.php" class="sub-menu-link">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#feedback"/></svg>
                <p>Segnalazione</p>
                <span>&gt;</span>
            </a>
            <?php endif; ?>

            <a href="#" class="sub-menu-link">
                <form method="POST">
                    <button name="logout" action="<?php echo $root; ?>/nav/nav.php">
                        <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#logout"/></svg>
                        <p class="logoutform">Logout</p>
                        <span>&gt;</span>
                    </button>
                </form>
            </a>

            <div class="sub-menu-divider"></div>

            <button id="theme-toggle" type="button" class="sub-menu-link theme-toggle-row" aria-label="Toggle theme">
                <svg class="icon icon-sun"><use href="<?php echo $root; ?>/img/icons.svg#sun"/></svg>
                <svg class="icon icon-moon" style="display:none;"><use href="<?php echo $root; ?>/img/icons.svg#moon"/></svg>
                <p>Cambia Tema</p>
            </button>
        </div>
    </div>
</nav>

<?php } else { ?>

    <ul>
        <li class="li-icon">
            <a href="<?php echo $root; ?>/lista/lista.php">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#library"/></svg>
            </a>
        </li>
        <li>
            <svg class="icon" onclick="toggleMenu()"><use href="<?php echo $root; ?>/img/icons.svg#account"/></svg>
        </li>
    </ul>

    <div class="sub-menu-wrap" id="subMenu">
        <div class="sub-menu">
            <a href="<?php echo $root; ?>/auth/login.php" class="sub-menu-link">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#login"/></svg>
                <p>Login</p>
                <span>&gt;</span>
            </a>

            <a href="<?php echo $root; ?>/auth/registrazione.php" class="sub-menu-link">
                <svg class="icon"><use href="<?php echo $root; ?>/img/icons.svg#register"/></svg>
                <p>Register</p>
                <span>&gt;</span>
            </a>

            <div class="sub-menu-divider"></div>

            <button id="theme-toggle" type="button" class="sub-menu-link theme-toggle-row" aria-label="Toggle theme">
                <svg class="icon icon-sun"><use href="<?php echo $root; ?>/img/icons.svg#sun"/></svg>
                <svg class="icon icon-moon" style="display:none;"><use href="<?php echo $root; ?>/img/icons.svg#moon"/></svg>
                <p>Cambia Tema</p>
            </button>
        </div>
    </div>
</nav>

<?php } ?>

<script src="<?php echo $root; ?>/js/theme.js"></script>
<script src="<?php echo $root; ?>/js/navigation.js"></script>
