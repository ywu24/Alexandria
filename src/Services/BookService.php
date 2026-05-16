<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file Book service handling CRUD, copy management, availability checks, search, and pagination
 */

namespace Alexandria\Services;

use PDO;
use PDOException;
use RuntimeException;

class BookService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get a single book by ID
     *
     * @param int $id Book ID
     * @return array|null Book data or null if not found
     */
    public function getById(int $id): ?array
    {
        $query = $this->pdo->prepare(
            "SELECT Nome, Autore, Copertina, CasaEditrice, ISBN, Descrizione, Genere, AnnoPubblicazione, id FROM Opera WHERE id = :id"
        );
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $book = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $book ?: null;
    }

    /**
     * Get book by ISBN
     *
     * @param string $isbn
     * @return array|null
     */
    public function getByIsbn(string $isbn): ?array
    {
        $query = $this->pdo->prepare("SELECT * FROM Opera WHERE ISBN = :isbn");
        $query->bindParam(':isbn', $isbn);
        $query->execute();
        $book = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $book ?: null;
    }

    /**
     * Check if a book exists by ID
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        $query = $this->pdo->prepare(
            "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE id = :book_id AND Opera.ISBN = copiaLibro.ISBN"
        );
        $query->bindParam(':book_id', $id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return ($result['qty'] ?? 0) > 0;
    }

    /**
     * Get total copy count for a book
     *
     * @param int $id Book ID
     * @return int
     */
    public function getTotalCopies(int $id): int
    {
        $query = $this->pdo->prepare(
            "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE id = :book_id AND Opera.ISBN = copiaLibro.ISBN"
        );
        $query->bindParam(':book_id', $id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return (int) ($result['qty'] ?? 0);
    }

    /**
     * Get available copy count for a book
     *
     * @param int $id Book ID
     * @return int
     */
    public function getAvailableCopies(int $id): int
    {
        $query = $this->pdo->prepare(
            "SELECT count(idCopia) as qty FROM copiaLibro, Opera WHERE copiaLibro.Stato = 1 AND id = :book_id AND Opera.ISBN = copiaLibro.ISBN"
        );
        $query->bindParam(':book_id', $id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return (int) ($result['qty'] ?? 0);
    }

    /**
     * Get availability info for display
     *
     * @param int $id Book ID
     * @return array ['disponibilita' => string, 'color' => string]
     */
    public function getAvailability(int $id): array
    {
        $qty = $this->getAvailableCopies($id);
        if ($qty >= 1) {
            return ['disponibilita' => 'Disponibile', 'color' => 'var(--color-success)'];
        }
        return ['disponibilita' => 'Non disponibile', 'color' => 'var(--color-danger)'];
    }

    /**
     * Get the next available copy ID for a book
     *
     * @param int $id Book ID
     * @return array|null ['id' => int, 'ISBN' => string] or null if none available
     */
    public function getNextAvailableCopy(int $id): ?array
    {
        $query = $this->pdo->prepare(
            "SELECT MIN(copiaLibro.idCopia) AS id, MIN(Opera.ISBN) as ISBN 
             FROM copiaLibro, Opera 
             WHERE Stato = 1 AND id = :id AND Opera.ISBN = copiaLibro.ISBN"
        );
        $query->bindParam(':id', $id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        if (!$result || empty($result['id'])) {
            return null;
        }
        return $result;
    }

    /**
     * Get rating stats for a book
     *
     * @param int $id Book ID
     * @return array ['media' => float, 'totale' => int]
     */
    public function getRatingStats(int $id): array
    {
        $query = $this->pdo->prepare("SELECT AVG(Voto) as media, COUNT(*) as totale FROM recensione WHERE idOpera = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $dati = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        return [
            'media' => round((float) ($dati['media'] ?? 0), 1),
            'totale' => (int) ($dati['totale'] ?? 0),
        ];
    }

    /**
     * Search books with optional filters
     *
     * @param string|null $search Search text
     * @param string|null $genre Genre filter
     * @param string $orderBy Order clause
     * @param string $limit LIMIT clause
     * @return array Book records
     */
    public function search(?string $search = null, ?string $genre = null, string $orderBy = 'ORDER BY Nome ASC', string $limit = 'LIMIT 10'): array
    {
        $whereClause = '';
        $params = [];

        if ($search !== null) {
            $searchText = '%' . $search . '%';
            $whereClause = "WHERE Nome LIKE ? OR Autore LIKE ? OR ISBN LIKE ? OR CasaEditrice LIKE ?";
            $params = [$searchText, $searchText, $searchText, $searchText];
        } elseif ($genre !== null) {
            $whereClause = "WHERE Genere = ?";
            $params = [$genre];
        }

        $query = $this->pdo->prepare("SELECT * FROM Opera $whereClause $orderBy $limit");
        $query->execute($params);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return $result;
    }

    /**
     * Get count for search/filter
     *
     * @param string|null $search Search text
     * @param string|null $genre Genre filter
     * @return int
     */
    public function searchCount(?string $search = null, ?string $genre = null): int
    {
        $whereClause = '';
        $params = [];

        if ($search !== null) {
            $searchText = '%' . $search . '%';
            $whereClause = "WHERE Nome LIKE ? OR Autore LIKE ? OR ISBN LIKE ? OR CasaEditrice LIKE ?";
            $params = [$searchText, $searchText, $searchText, $searchText];
        } elseif ($genre !== null) {
            $whereClause = "WHERE Genere = ?";
            $params = [$genre];
        }

        $query = $this->pdo->prepare("SELECT count(*) as tot FROM Opera $whereClause");
        $query->execute($params);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return (int) ($result['tot'] ?? 0);
    }

    /**
     * Get latest books for carousel/slider
     *
     * @param int $limit Number of books
     * @return array
     */
    public function getLatest(int $limit = 3): array
    {
        $books = [];
        $lastId = null;

        for ($i = 0; $i < $limit; $i++) {
            if ($i === 0) {
                $q = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione 
                      FROM Opera WHERE id = (SELECT MAX(id) FROM Opera)";
                $query = $this->pdo->prepare($q);
            } else {
                $q = "SELECT id, Nome, Autore, Genere, Copertina, CasaEditrice, ISBN, AnnoPubblicazione, Descrizione 
                      FROM Opera WHERE id = (SELECT MAX(id) FROM Opera WHERE id < :idLibro)";
                $query = $this->pdo->prepare($q);
                $query->bindParam(':idLibro', $lastId, PDO::PARAM_INT);
            }

            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            $query->closeCursor();

            if (!$row) {
                $row = [
                    'id' => 1,
                    'Nome' => '',
                    'Autore' => '',
                    'Genere' => '',
                    'Copertina' => 'default.jpg',
                    'CasaEditrice' => '',
                    'ISBN' => '',
                    'AnnoPubblicazione' => '',
                    'Descrizione' => ''
                ];
            }

            $lastId = $row['id'];
            $books[] = $row;
        }

        return $books;
    }

    /**
     * Insert a new book with copies
     *
     * @param array $data Book data (isbn, nome, autore, genere, descrizione, copertina, casa_editrice, anno_pubblicazione)
     * @param int $copies Number of copies to create
     * @return bool True on success
     * @throws RuntimeException On duplicate ISBN or DB error
     */
    public function insert(array $data, int $copies = 1): bool
    {
        $check = $this->pdo->prepare("SELECT ISBN FROM Opera WHERE ISBN = :isbn");
        $check->execute([':isbn' => $data['isbn']]);

        if ($check->rowCount() > 0) {
            throw new RuntimeException('Errore: il DataBase ha gia i dati sul libro. Impossibile inserire Opera.');
        }

        $this->pdo->beginTransaction();

        try {
            $q1 = "INSERT INTO Opera (`ISBN`, `Nome`, `Autore`, `Genere`, `Descrizione`, `Copertina`, `CasaEditrice`, `AnnoPubblicazione`)
                   VALUES (:isbn, :nome, :autore, :genere, :descr, :copertina, :casa, :anno)";
            $query1 = $this->pdo->prepare($q1);
            $query1->execute([
                ':isbn' => $data['isbn'],
                ':nome' => $data['nome'],
                ':autore' => $data['autore'],
                ':genere' => $data['genere'],
                ':descr' => $data['descrizione'],
                ':copertina' => $data['copertina'],
                ':casa' => $data['casa_editrice'],
                ':anno' => $data['anno_pubblicazione'],
            ]);

            $q2 = "INSERT INTO copiaLibro (`ISBN`, `Stato`) VALUES (:isbn, '1')";
            $query2 = $this->pdo->prepare($q2);
            for ($i = 0; $i < $copies; $i++) {
                $query2->execute([':isbn' => $data['isbn']]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore DB: ' . $e->getMessage());
        }
    }

    /**
     * Add copies to an existing book
     *
     * @param string $isbn Book ISBN
     * @param int $copies Number of copies to add
     * @return bool True on success
     * @throws RuntimeException If book does not exist
     */
    public function addCopies(string $isbn, int $copies): bool
    {
        $check = $this->pdo->prepare("SELECT ISBN FROM Opera WHERE ISBN = :isbn");
        $check->execute([':isbn' => $isbn]);

        if ($check->rowCount() === 0) {
            throw new RuntimeException('Errore: il DataBase non ha i dati sul libro.');
        }

        $q = "INSERT INTO copiaLibro (`ISBN`, `Stato`) VALUES (:isbn, '1')";
        $query = $this->pdo->prepare($q);
        for ($i = 0; $i < $copies; $i++) {
            $query->execute([':isbn' => $isbn]);
        }

        return true;
    }

    /**
     * Update a copy's status
     *
     * @param int $copyId Copy ID
     * @param int $status 0 = unavailable, 1 = available
     * @return bool
     */
    public function updateCopyStatus(int $copyId, int $status): bool
    {
        $query = $this->pdo->prepare("UPDATE copiaLibro SET Stato = :stato WHERE idCopia = :id");
        $query->bindParam(':stato', $status, PDO::PARAM_INT);
        $query->bindParam(':id', $copyId, PDO::PARAM_INT);
        return $query->execute();
    }

    /**
     * Delete a book and its copies
     *
     * @param string $isbn Book ISBN
     * @return bool True on success
     */
    public function delete(string $isbn): bool
    {
        $this->pdo->beginTransaction();
        try {
            // Delete copies first
            $q1 = $this->pdo->prepare("DELETE FROM copiaLibro WHERE ISBN = :isbn");
            $q1->execute([':isbn' => $isbn]);

            // Then delete the book
            $q2 = $this->pdo->prepare("DELETE FROM Opera WHERE ISBN = :isbn");
            $q2->execute([':isbn' => $isbn]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore durante l\'eliminazione: ' . $e->getMessage());
        }
    }

    /**
     * Get all copies for a book
     *
     * @param string $isbn Book ISBN
     * @return array
     */
    public function getCopies(string $isbn): array
    {
        $query = $this->pdo->prepare("SELECT * FROM copiaLibro WHERE ISBN = :isbn");
        $query->execute([':isbn' => $isbn]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get books with copy counts for dashboard listing
     *
     * @param string|null $search Search text
     * @param string $sort Sort column
     * @param int $limit Records per page
     * @param int $offset Offset
     * @return array Book records with 'copie' count
     */
    public function getBooksWithCopies(?string $search = null, string $sort = 'Nome', int $limit = 10, int $offset = 0): array
    {
        $allowedSort = [
            'ISBN' => 'Opera.ISBN',
            'Nome' => 'Nome',
            'Autore' => 'Autore',
            'Genere' => 'Genere',
            'AnnoPubblicazione' => 'AnnoPubblicazione',
            'CasaEditrice' => 'CasaEditrice',
            'copie' => 'copie',
        ];
        $sortColumn = $allowedSort[$sort] ?? 'Nome';

        $sql = "SELECT Opera.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, COUNT(idCopia) as copie
                FROM Opera
                LEFT JOIN copiaLibro ON Opera.ISBN = copiaLibro.ISBN
                WHERE 1=1";

        $params = [];
        if ($search !== null && $search !== '') {
            $sql .= " AND (Nome LIKE :s1 OR Autore LIKE :s2 OR Genere LIKE :s3 OR AnnoPubblicazione LIKE :s4 OR CasaEditrice LIKE :s5 OR Opera.ISBN LIKE :s6)";
            $searchTerm = '%' . $search . '%';
            $params = [':s1' => $searchTerm, ':s2' => $searchTerm, ':s3' => $searchTerm, ':s4' => $searchTerm, ':s5' => $searchTerm, ':s6' => $searchTerm];
        }

        $sql .= " GROUP BY Opera.ISBN";
        $sql .= ($sort === 'copie') ? " ORDER BY copie DESC" : " ORDER BY $sortColumn ASC";
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update book data by old ISBN
     *
     * @param string $oldIsbn Original ISBN (identifier)
     * @param array $data Fields to update (isbn, nome, autore, genere, descrizione, casaed, annopub)
     * @return bool
     */
    public function update(string $oldIsbn, array $data): bool
    {
        $sql = "UPDATE Opera
                SET ISBN = :isbn_new,
                    Nome = :nome,
                    Autore = :autore,
                    Genere = :genere,
                    Descrizione = :descr,
                    CasaEditrice = :casaed,
                    AnnoPubblicazione = :annopub
                WHERE ISBN = :isbn_old";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':isbn_new' => $data['isbn'],
            ':nome' => $data['nome'],
            ':autore' => $data['autore'],
            ':genere' => $data['genere'],
            ':descr' => $data['descrizione'],
            ':casaed' => $data['casaed'],
            ':annopub' => $data['annopub'],
            ':isbn_old' => $oldIsbn,
        ]);
    }

    /**
     * Delete a single copy by ID (only if status is available)
     *
     * @param int $copyId Copy ID
     * @return bool True if deleted
     */
    public function deleteCopy(int $copyId): bool
    {
        $query = $this->pdo->prepare("DELETE FROM copiaLibro WHERE idCopia = :id AND Stato = 1");
        $query->bindParam(':id', $copyId, PDO::PARAM_INT);
        $query->execute();
        return $query->rowCount() > 0;
    }

    /**
     * Adjust copy count for a book to reach a target number
     *
     * @param string $isbn Book ISBN
     * @param int $targetCount Desired total copy count
     * @return array ['action' => string, 'count' => int, 'message' => string]
     */
    public function adjustCopyCount(string $isbn, int $targetCount): array
    {
        if ($targetCount < 0) {
            throw new RuntimeException('ERRORE: il numero inserito deve essere positivo o al piu uguale zero!');
        }

        $query = $this->pdo->prepare("SELECT COUNT(idCopia) as copie FROM copiaLibro WHERE ISBN = :isbn GROUP BY ISBN");
        $query->execute([':isbn' => $isbn]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $currentCount = (int) ($result['copie'] ?? 0);

        $diff = $targetCount - $currentCount;

        if ($diff > 0) {
            $insert = $this->pdo->prepare("INSERT INTO copiaLibro (ISBN, stato) VALUES (:isbn, 1)");
            for ($i = 0; $i < $diff; $i++) {
                $insert->execute([':isbn' => $isbn]);
            }
            return ['action' => 'added', 'count' => $diff, 'message' => "okInseriti $diff libri con successo!"];
        } elseif ($diff < 0) {
            $toDelete = abs($diff);
            $delete = $this->pdo->prepare("DELETE FROM copiaLibro WHERE ISBN = :isbn AND stato = 1 LIMIT $toDelete");
            $delete->execute([':isbn' => $isbn]);
            $deleted = $delete->rowCount();
            if ($deleted != $toDelete) {
                return ['action' => 'removed', 'count' => $deleted, 'message' => "WARNING: eliminati solo $deleted su $toDelete richiesti: non puoi eliminare libri in prestito!"];
            }
            return ['action' => 'removed', 'count' => $deleted, 'message' => "okEliminati $deleted libri con successo!"];
        }

        return ['action' => 'none', 'count' => 0, 'message' => 'ignora'];
    }
}
