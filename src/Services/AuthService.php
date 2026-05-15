<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file Authentication service handling login, registration, email verification, and remember-me tokens
 */

namespace Alexandria\Services;

use PDO;
use RuntimeException;

class AuthService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Authenticate a user by email and password
     *
     * @param string $email User email
     * @param string $password Plain text password
     * @return array|null User data array if authenticated, null otherwise
     */
    public function authenticate(string $email, string $password): ?array
    {
        $query = $this->pdo->prepare('SELECT * FROM Utente WHERE Email = :email');
        $query->bindParam(':email', $email);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['Password'])) {
            return null;
        }

        return $user;
    }

    /**
     * Log a user in by populating session data and setting cookies
     *
     * @param array $user User data from database
     * @param string $domain Cookie domain
     * @return void
     */
    public function login(array $user, string $domain = 'alexandria.it'): void
    {
        $_SESSION['email'] = $user['Email'];
        $_SESSION['nome'] = $user['Nome'];
        $_SESSION['cognome'] = $user['Cognome'];
        $_SESSION['utenza'] = $user['Utenza'];

        setcookie('email', $user['Email'], time() + 60 * 60 * 24 * 90, '/', $domain);
        setcookie('password', $user['Password'], time() + 60 * 60 * 24 * 90, '/', $domain);
    }

    /**
     * Attempt auto-login from remember-me cookies
     *
     * @return array|null User data if successful, null otherwise
     */
    public function attemptCookieLogin(): ?array
    {
        if (isset($_SESSION['email'])) {
            return null;
        }

        if (!isset($_COOKIE['email']) || !isset($_COOKIE['password'])) {
            return null;
        }

        $query = $this->pdo->prepare('SELECT * FROM Utente WHERE Email = :email');
        $query->bindParam(':email', $_COOKIE['email']);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        if (!$user) {
            return null;
        }

        if ($_COOKIE['password'] !== $user['Password']) {
            return null;
        }

        $_SESSION['email'] = $user['Email'];
        $_SESSION['nome'] = $user['Nome'];
        $_SESSION['cognome'] = $user['Cognome'];
        $_SESSION['utenza'] = $user['Utenza'];

        return $user;
    }

    /**
     * Check if an email is already registered
     *
     * @param string $email Email to check
     * @return bool True if exists
     */
    public function emailExists(string $email): bool
    {
        $query = $this->pdo->prepare('SELECT Email FROM Utente WHERE Email = :email');
        $query->bindParam(':email', $email);
        $query->execute();
        $result = $query->fetch();
        $query->closeCursor();
        return $result !== false;
    }

    /**
     * Register a new user after email verification
     *
     * @param string $email User email
     * @param string $password Plain text password (will be hashed)
     * @param string $nome First name
     * @param string $cognome Last name
     * @param int $utenza User type (default 4 = standard)
     * @return bool True on success
     * @throws RuntimeException On database error
     */
    public function register(string $email, string $password, string $nome, string $cognome, int $utenza = 4): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $query = $this->pdo->prepare(
            'INSERT INTO Utente (Email, Nome, Cognome, Password, Utenza) VALUES (:email, :nome, :cognome, :password, :utenza)'
        );
        $query->bindParam(':email', $email);
        $query->bindParam(':nome', $nome);
        $query->bindParam(':cognome', $cognome);
        $query->bindParam(':password', $hash);
        $query->bindParam(':utenza', $utenza, PDO::PARAM_INT);

        return $query->execute();
    }

    /**
     * Generate a random alphanumeric confirmation code
     *
     * @param int $length Code length
     * @return string The generated code
     */
    public function generateConfirmationCode(int $length = 8): string
    {
        $caratteri = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $codice = '';
        $max = strlen($caratteri) - 1;

        for ($i = 0; $i < $length; $i++) {
            $codice .= $caratteri[random_int(0, $max)];
        }

        return $codice;
    }

    /**
     * Logout the current user, clearing session and cookies
     *
     * @return void
     */
    public function logout(): void
    {
        setcookie('email', '', time() - 1, '/');
        setcookie('password', '', time() - 1, '/');
        session_destroy();
    }

    /**
     * Check if the current user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['email']);
    }

    /**
     * Check if the current user has admin privileges
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return isset($_SESSION['utenza']) && ($_SESSION['utenza'] == 1);
    }

    /**
     * Check if the current user has librarian privileges
     *
     * @return bool
     */
    public function isLibrarian(): bool
    {
        return isset($_SESSION['utenza']) && ($_SESSION['utenza'] == 2);
    }

    /**
     * Check if the current user is a premium user
     *
     * @return bool
     */
    public function isPremium(): bool
    {
        return isset($_SESSION['utenza']) && $_SESSION['utenza'] == 3;
    }

    /**
     * Check if the current user is a standard user
     *
     * @return bool
     */
    public function isStandard(): bool
    {
        return isset($_SESSION['utenza']) && $_SESSION['utenza'] == 4;
    }

    /**
     * Get the current authenticated user's email
     *
     * @return string|null
     */
    public function getCurrentUserEmail(): ?string
    {
        return $_SESSION['email'] ?? null;
    }

    /**
     * Get the current authenticated user's type
     *
     * @return int|null
     */
    public function getCurrentUserType(): ?int
    {
        return isset($_SESSION['utenza']) ? (int) $_SESSION['utenza'] : null;
    }

    /**
     * Change user password after verifying current password
     *
     * @param string $email User email
     * @param string $currentPassword Current plain text password
     * @param string $newPassword New plain text password
     * @return bool True on success
     * @throws RuntimeException If current password is wrong or update fails
     */
    public function changePassword(string $email, string $currentPassword, string $newPassword): bool
    {
        $query = $this->pdo->prepare('SELECT Password FROM Utente WHERE Email = :email');
        $query->bindParam(':email', $email);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        if (!$user || !password_verify($currentPassword, $user['Password'])) {
            throw new RuntimeException('Password attuale errata');
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = $this->pdo->prepare('UPDATE Utente SET Password = :password WHERE Email = :email');
        $update->bindParam(':password', $hash);
        $update->bindParam(':email', $email);

        return $update->execute();
    }
}
