<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file Review service handling create, fetch with pagination, and rating averages
 */

namespace Alexandria\Services;

use PDO;
use PDOException;
use RuntimeException;

class ReviewService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get reviews for a book with pagination
     *
     * @param int $bookId Book ID
     * @param int $limit Number of reviews
     * @param int $offset Offset
     * @return array Review records with user propic
     */
    public function getForBook(int $bookId, int $limit = 5, int $offset = 0): array
    {
         $query = $this->pdo->prepare(
             "SELECT r.*, u.propic 
              FROM recensione r 
              JOIN Utente u ON r.userEmail = u.email 
              WHERE r.idOpera = :book_id 
              ORDER BY r.id DESC 
              LIMIT $limit OFFSET $offset"
         );
         $query->bindParam(':book_id', $bookId, PDO::PARAM_INT);
         $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count reviews for a book
     *
     * @param int $bookId Book ID
     * @return int
     */
    public function countForBook(int $bookId): int
    {
        $query = $this->pdo->prepare("SELECT COUNT(*) FROM recensione WHERE idOpera = :id");
        $query->bindParam(':id', $bookId, PDO::PARAM_INT);
        $query->execute();
        return (int) $query->fetchColumn();
    }

    /**
     * Check if a user has already reviewed a book
     *
     * @param string $email User email
     * @param int $bookId Book ID
     * @return bool
     */
    public function hasUserReviewed(string $email, int $bookId): bool
    {
        $query = $this->pdo->prepare(
            "SELECT 1 FROM recensione WHERE userEmail = :email AND idOpera = :idOpera LIMIT 1"
        );
        $query->bindParam(':email', $email);
        $query->bindParam(':idOpera', $bookId, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch() !== false;
    }

    /**
     * Check if a user has borrowed a specific book (required for review eligibility)
     *
     * @param string $email User email
     * @param int $bookId Book ID
     * @return bool
     */
    public function hasUserBorrowedBook(string $email, int $bookId): bool
    {
        $query = $this->pdo->prepare(
            "SELECT 1 
             FROM Prenotazione 
             JOIN copiaLibro ON Prenotazione.idCopia = copiaLibro.idCopia 
             JOIN Opera ON copiaLibro.ISBN = Opera.ISBN 
             WHERE Prenotazione.Email = :email 
             AND Opera.id = :idOpera 
             AND FinePrestito IS NOT NULL"
        );
        $query->bindParam(':email', $email);
        $query->bindParam(':idOpera', $bookId, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch() !== false;
    }

    /**
     * Create a new review
     *
     * @param string $email User email
     * @param int $bookId Book ID
     * @param string $titolo Review title
     * @param string $messaggio Review message
     * @param int $voto Rating (1-5)
     * @return bool True on success
     * @throws RuntimeException If user not eligible or DB error
     */
    public function create(string $email, int $bookId, string $titolo, string $messaggio, int $voto): bool
    {
        if ($this->hasUserReviewed($email, $bookId)) {
            throw new RuntimeException('Hai gia recensito questo libro!');
        }

        if (!$this->hasUserBorrowedBook($email, $bookId)) {
            throw new RuntimeException('Non puoi recensire libri che non hai mai preso in prestito!');
        }

        $this->pdo->beginTransaction();

        try {
            $sql = "INSERT INTO recensione (userEmail, Titolo, Messaggio, Voto, idOpera) 
                    VALUES (:email, :titolo, :messaggio, :voto, :idOpera)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':titolo', $titolo);
            $stmt->bindParam(':messaggio', $messaggio);
            $stmt->bindParam(':voto', $voto, PDO::PARAM_INT);
            $stmt->bindParam(':idOpera', $bookId, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();

            // Add points to user
            $sqlPunti = "UPDATE Utente SET punteggio = punteggio + 5 WHERE Email = :email";
            $stmtPunti = $this->pdo->prepare($sqlPunti);
            $stmtPunti->bindParam(':email', $email);
            $stmtPunti->execute();
            $stmtPunti->closeCursor();

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore database: ' . $e->getMessage());
        }
    }

    /**
     * Get the average rating and count for a book
     *
     * @param int $bookId Book ID
     * @return array ['media' => float, 'totale' => int]
     */
    public function getAverageRating(int $bookId): array
    {
        $query = $this->pdo->prepare("SELECT AVG(Voto) as media, COUNT(*) as totale FROM recensione WHERE idOpera = :id");
        $query->bindParam(':id', $bookId, PDO::PARAM_INT);
        $query->execute();
        $dati = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        return [
            'media' => round((float) ($dati['media'] ?? 0), 1),
            'totale' => (int) ($dati['totale'] ?? 0),
        ];
    }
}
