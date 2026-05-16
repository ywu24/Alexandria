<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file User service handling CRUD, profile updates, password changes, and account deletion
 */

namespace Alexandria\Services;

use PDO;
use PDOException;
use RuntimeException;

class UserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get a user by email
     *
     * @param string $email User email
     * @return array|null User data or null
     */
    public function getByEmail(string $email): ?array
    {
        $query = $this->pdo->prepare('SELECT * FROM Utente WHERE Email = :email');
        $query->bindParam(':email', $email);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $user ?: null;
    }

    /**
     * Get user profile data (propic, punteggio)
     *
     * @param string $email User email
     * @return array ['propic' => string, 'punteggio' => int]
     */
    public function getProfileData(string $email): array
    {
        $query = $this->pdo->prepare('SELECT propic, punteggio FROM Utente WHERE Email = :email');
        $query->bindParam(':email', $email);
        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        return [
            'propic' => $data['propic'] ?? 'userDashFavicon.svg',
            'punteggio' => (int) ($data['punteggio'] ?? 0),
        ];
    }

    /**
     * Get users with optional search and sorting
     *
     * @param string|null $search Search text
     * @param string $sortType Sort column
     * @param int $limit Records per page
     * @param int $offset Offset
     * @param int|null $utenzaFilter Filter by user type
     * @return array User records
     */
    public function getUsers(
        ?string $search = null,
        string $sortType = 'id',
        int $limit = 10,
        int $offset = 0,
        ?int $utenzaFilter = null
    ): array {
        $params = [];
        $where = '';

        if ($search !== null && $search !== '') {
            $where = " WHERE (Nome LIKE :search OR Cognome LIKE :search OR Email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($utenzaFilter !== null) {
            $where .= ($where === '' ? ' WHERE' : ' AND') . " Utenza = :utenza";
            $params[':utenza'] = $utenzaFilter;
        }

        $allowedSort = ['id', 'Nome', 'Cognome', 'Email', 'Utenza', 'punteggio'];
        if (!in_array($sortType, $allowedSort, true)) {
            $sortType = 'id';
        }

        $sql = "SELECT * FROM Utente $where ORDER BY $sortType LIMIT :limit OFFSET :offset";
        $query = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $query->bindValue($k, $v);
        }
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count users matching criteria
     *
     * @param string|null $search Search text
     * @param int|null $utenzaFilter Filter by user type
     * @return int
     */
    public function countUsers(?string $search = null, ?int $utenzaFilter = null): int
    {
        $params = [];
        $where = '';

        if ($search !== null && $search !== '') {
            $where = " WHERE (Nome LIKE :search OR Cognome LIKE :search OR Email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($utenzaFilter !== null) {
            $where .= ($where === '' ? ' WHERE' : ' AND') . " Utenza = :utenza";
            $params[':utenza'] = $utenzaFilter;
        }

        $query = $this->pdo->prepare("SELECT COUNT(*) as tot FROM Utente $where");
        $query->execute($params);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['tot'] ?? 0);
    }

    /**
     * Insert a new user (admin function)
     *
     * @param array $data User data (email, nome, cognome, password, utenza)
     * @return bool True on success
     * @throws RuntimeException On duplicate or error
     */
    public function insert(array $data): bool
    {
        if ($this->getByEmail($data['email'])) {
            throw new RuntimeException('Utente gia registrato');
        }

        $hash = password_hash($data['password'], PASSWORD_BCRYPT);

        $query = $this->pdo->prepare(
            'INSERT INTO Utente (Email, Nome, Cognome, Password, Utenza) VALUES (:email, :nome, :cognome, :password, :utenza)'
        );
        return $query->execute([
            ':email' => $data['email'],
            ':nome' => $data['nome'],
            ':cognome' => $data['cognome'],
            ':password' => $hash,
            ':utenza' => $data['utenza'],
        ]);
    }

    /**
     * Update a user's basic info
     *
     * @param string $email User email (identifier)
     * @param array $data Fields to update
     * @return bool
     */
    public function update(string $email, array $data): bool
    {
        $allowedFields = ['Nome', 'Cognome', 'Email', 'Utenza', 'punteggio', 'propic'];
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields, true)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[':oldEmail'] = $email;
        $sql = "UPDATE Utente SET " . implode(', ', $fields) . " WHERE Email = :oldEmail";
        $query = $this->pdo->prepare($sql);
        return $query->execute($params);
    }

    /**
     * Delete a user account
     *
     * @param string $email User email
     * @return bool True on success
     * @throws RuntimeException If user has active bookings
     */
    public function delete(string $email): bool
    {
        // Check for active bookings
        $check = $this->pdo->prepare(
            "SELECT COUNT(*) as cnt FROM Prenotazione WHERE Email = :email AND FinePrestito IS NULL"
        );
        $check->execute([':email' => $email]);
        $result = $check->fetch(PDO::FETCH_ASSOC);

        if (($result['cnt'] ?? 0) > 0) {
            throw new RuntimeException('Impossibile eliminare: l\'utente ha prenotazioni o prestiti attivi');
        }

        $query = $this->pdo->prepare("DELETE FROM Utente WHERE Email = :email");
        return $query->execute([':email' => $email]);
    }

    /**
     * Get a user by ID
     *
     * @param int $id User ID
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $query = $this->pdo->prepare('SELECT * FROM Utente WHERE id = :id');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $user ?: null;
    }

    /**
     * Update a user by ID (admin function, includes optional password)
     *
     * @param int $id User ID
     * @param array $data Fields to update
     * @return bool
     */
    public function updateById(int $id, array $data): bool
    {
        $allowedFields = ['Nome', 'Cognome', 'Email', 'Utenza'];
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields, true)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (!empty($data['Password'])) {
            $fields[] = "Password = :password";
            $params[':password'] = password_hash($data['Password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[':id'] = $id;
        $sql = "UPDATE Utente SET " . implode(', ', $fields) . " WHERE id = :id";
        $query = $this->pdo->prepare($sql);
        return $query->execute($params);
    }

    /**
     * Admin delete a user (can delete even with active bookings, restores copy status)
     *
     * @param string $emailToDelete Email of user to delete
     * @param string $adminEmail Admin email (cannot delete self)
     * @return bool
     */
    public function adminDelete(string $emailToDelete, string $adminEmail): bool
    {
        $this->pdo->beginTransaction();

        try {
            // Restore copies for active bookings
            $updateLibri = $this->pdo->prepare("
                UPDATE copiaLibro
                SET Stato = 1
                WHERE idCopia IN (
                    SELECT idCopia FROM Prenotazione
                    WHERE Email = :email AND FinePrestito IS NULL
                )
            ");
            $updateLibri->execute([':email' => $emailToDelete]);

            $query = $this->pdo->prepare("DELETE FROM Utente WHERE Email = :email AND Email != :admin");
            $query->bindParam(':email', $emailToDelete);
            $query->bindParam(':admin', $adminEmail);
            $query->execute();
            $n = $query->rowCount();

            $this->pdo->commit();
            return $n > 0;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore durante l\'eliminazione: ' . $e->getMessage());
        }
    }

    /**
     * Update user profile picture
     *
     * @param string $email User email
     * @param string $filename New propic filename
     * @return bool
     */
    public function updatePropic(string $email, string $filename): bool
    {
        $query = $this->pdo->prepare('UPDATE Utente SET propic = :propic WHERE Email = :email');
        $query->bindParam(':propic', $filename);
        $query->bindParam(':email', $email);
        return $query->execute();
    }

    /**
     * Add points to a user's score
     *
     * @param string $email User email
     * @param int $points Points to add
     * @return bool
     */
    public function addPoints(string $email, int $points): bool
    {
        $query = $this->pdo->prepare('UPDATE Utente SET punteggio = punteggio + :points WHERE Email = :email');
        $query->bindParam(':points', $points, PDO::PARAM_INT);
        $query->bindParam(':email', $email);
        return $query->execute();
    }
}
