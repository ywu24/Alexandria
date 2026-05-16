<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file Booking service handling create/cancel/complete bookings, status calculation, and validation
 */

namespace Alexandria\Services;

use PDO;
use PDOException;
use RuntimeException;

class BookingService
{
    private PDO $pdo;
    private BookService $bookService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->bookService = new BookService($pdo);
    }

    /**
     * Calculate the status of a booking from its database row
     *
     * @param array $row Booking row with InizioPrenotazione, FinePrenotazione, InizioPrestito, FinePrestito, FineAttesa
     * @return array ['status' => string, 'color' => string, 'startDate' => string, 'endDate' => string]
     */
    public function calculateStatus(array $row): array
    {
        $inizio = '';
        $fine = '';
        $stato = '';
        $color = '';

        if (empty($row['InizioPrestito'])) {
            if (strtotime($row['FinePrenotazione']) < time()) {
                $stato = 'Scaduto';
                $color = 'text-muted';
            } else {
                $stato = 'Prenotato';
                $color = 'text-success';
            }
            $inizio = $row['InizioPrenotazione'];
            $fine = $row['FinePrenotazione'];
        } else {
            if (empty($row['FinePrestito'])) {
                if (time() > strtotime($row['FineAttesa'])) {
                    $stato = 'In Ritardo';
                    $color = 'text-danger';
                } else {
                    $stato = 'In Prestito';
                    $color = 'text-warning';
                }
                $inizio = $row['InizioPrestito'];
                $fine = $row['FineAttesa'];
            } else {
                $stato = 'Terminato';
                $color = 'text-muted';
                $inizio = $row['InizioPrestito'];
                $fine = $row['FinePrestito'];
            }
        }

        return [
            'status' => $stato,
            'color' => $color,
            'startDate' => $inizio,
            'endDate' => $fine,
        ];
    }

    /**
     * Get the textual status color for a given status string
     *
     * @param string $status Status text
     * @return string Bootstrap text color class
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'Prenotato' => 'text-success',
            'In Prestito' => 'text-warning',
            'In Ritardo' => 'text-danger',
            default => 'text-muted',
        };
    }

    /**
     * Count active bookings for a user
     *
     * @param string $email User email
     * @return int
     */
    public function countActiveBookings(string $email): int
    {
        $query = $this->pdo->prepare(
            'SELECT count(*) as cnt FROM Utente, Prenotazione 
             WHERE Utente.email = Prenotazione.email 
             AND Utente.email = :email 
             AND ((FinePrestito IS NULL AND FinePrenotazione >= CURDATE()) 
                  OR (FinePrestito IS NULL AND InizioPrestito IS NOT NULL))'
        );
        $query->bindParam(':email', $email);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return (int) ($result['cnt'] ?? 0);
    }

    /**
     * Create a new booking for a user
     *
     * @param string $email User email
     * @param int $bookId Book ID
     * @param int $days Booking duration in days
     * @return array ['success' => bool, 'message' => string, 'bookingId' => int|null, 'copyId' => int|null, 'isbn' => string|null]
     * @throws RuntimeException On database error
     */
    public function createBooking(string $email, int $bookId, int $days = 30): array
    {
        $copy = $this->bookService->getNextAvailableCopy($bookId);

        if (!$copy) {
            return ['success' => false, 'message' => 'Nessuna copia disponibile', 'bookingId' => null, 'copyId' => null, 'isbn' => null];
        }

        $this->pdo->beginTransaction();

        try {
            // Mark copy as unavailable
            $update = $this->pdo->prepare("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = :id");
            $update->bindParam(':id', $copy['id'], PDO::PARAM_INT);
            $update->execute();

            // Create booking
            $insert = $this->pdo->prepare(
                "INSERT INTO Prenotazione (`Email`, `idCopia`, `InizioPrenotazione`, `FinePrenotazione`) 
                 VALUES (:email, :id, CURDATE(), ADDDATE(CURDATE(), INTERVAL :giorni DAY))"
            );
            $insert->bindParam(':email', $email);
            $insert->bindParam(':id', $copy['id'], PDO::PARAM_INT);
            $insert->bindParam(':giorni', $days, PDO::PARAM_INT);
            $insert->execute();

            $bookingId = (int) $this->pdo->lastInsertId();

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Prenotazione effettuata con successo',
                'bookingId' => $bookingId,
                'copyId' => (int) $copy['id'],
                'isbn' => $copy['ISBN'],
            ];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore durante la prenotazione: ' . $e->getMessage());
        }
    }

    /**
     * Create multiple bookings for a premium user
     *
     * @param string $email User email
     * @param int $bookId Book ID
     * @param int $quantity Number of copies
     * @param int $days Booking duration in days
     * @return array ['success' => bool, 'message' => string, 'count' => int]
     * @throws RuntimeException On database error
     */
    public function createMultipleBookings(string $email, int $bookId, int $quantity, int $days = 30): array
    {
        $this->pdo->beginTransaction();
        $count = 0;
        $isbn = null;

        try {
            for ($i = 0; $i < $quantity; $i++) {
                $copy = $this->bookService->getNextAvailableCopy($bookId);
                if (!$copy) {
                    break;
                }

                $isbn = $copy['ISBN'];

                $update = $this->pdo->prepare("UPDATE copiaLibro SET Stato = '0' WHERE copiaLibro.idCopia = :id");
                $update->bindParam(':id', $copy['id'], PDO::PARAM_INT);
                $update->execute();

                $insert = $this->pdo->prepare(
                    "INSERT INTO Prenotazione (`Email`, `idCopia`, `InizioPrenotazione`, `FinePrenotazione`) 
                     VALUES (:email, :id, CURDATE(), ADDDATE(CURDATE(), INTERVAL :giorni DAY))"
                );
                $insert->bindParam(':email', $email);
                $insert->bindParam(':id', $copy['id'], PDO::PARAM_INT);
                $insert->bindParam(':giorni', $days, PDO::PARAM_INT);
                $insert->execute();

                $count++;
            }

            $this->pdo->commit();

            return [
                'success' => $count > 0,
                'message' => $count > 0 ? "Prenotazione di $count copie effettuata" : 'Nessuna copia disponibile',
                'count' => $count,
                'isbn' => $isbn,
            ];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore durante la prenotazione: ' . $e->getMessage());
        }
    }

    /**
     * Get all bookings for a user
     *
     * @param string $email User email
     * @return array Booking records with book info
     */
    public function getByUser(string $email): array
    {
        $query = $this->pdo->prepare(
            "SELECT p.idPrenotazione, o.id as idOpera, o.Copertina, p.idCopia, 
                    p.InizioPrenotazione, p.FinePrenotazione, p.InizioPrestito, 
                    p.FinePrestito, p.FineAttesa, o.Autore, o.Nome, o.CasaEditrice, o.ISBN 
             FROM Prenotazione p
             JOIN copiaLibro c ON c.idCopia = p.idCopia 
             JOIN Opera o ON o.ISBN = c.ISBN 
             WHERE p.Email = :email 
             ORDER BY p.idPrenotazione DESC"
        );
        $query->execute([':email' => $email]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all bookings (admin view) with filtering
     *
     * @param string|null $filtroStato Status filter
     * @param string|null $dataInizio Start date filter
     * @param string|null $dataFine End date filter
     * @param string $sortType Sort column
     * @param int $limit Records per page
     * @param int $offset Offset
     * @return array Booking records
     */
    public function getAll(
        ?string $filtroStato = null,
        ?string $dataInizio = null,
        ?string $dataFine = null,
        string $sortType = 'idPrenotazione DESC',
        int $limit = 10,
        int $offset = 0
    ): array {
        $sqlStato = "CASE 
            WHEN InizioPrestito IS NULL THEN 
                CASE WHEN FinePrenotazione < NOW() THEN 'Terminato' ELSE 'Prenotato' END
            WHEN FinePrestito IS NULL THEN 
                CASE WHEN NOW() > FineAttesa THEN 'In Ritardo' ELSE 'In Prestito' END
            ELSE 'Terminato'
        END";

        $params = [];
        $sql = "SELECT idPrenotazione, InizioPrenotazione, FinePrenotazione, InizioPrestito, 
                       Copertina, FinePrestito, FineAttesa, Autore, Nome, Opera.ISBN as ISBN, email,
                       ($sqlStato) as stato_calcolato 
                FROM Prenotazione 
                JOIN copiaLibro ON copiaLibro.idCopia = Prenotazione.idCopia 
                JOIN Opera ON Opera.ISBN = copiaLibro.ISBN 
                WHERE 1=1";

        if ($filtroStato !== null && $filtroStato !== 'tutti') {
            if ($filtroStato === 'In corso') {
                $sql .= " AND ($sqlStato) != 'Terminato'";
            } else {
                $sql .= " AND ($sqlStato) = :filtro_stato";
                $params[':filtro_stato'] = $filtroStato;
            }
        }

        if ($dataInizio) {
            $sql .= " AND InizioPrenotazione >= :data_inizio";
            $params[':data_inizio'] = $dataInizio;
        }
        if ($dataFine) {
            $sql .= " AND InizioPrenotazione <= :data_fine";
            $params[':data_fine'] = $dataFine;
        }

        $allowedSort = ['ISBN', 'Nome', 'InizioPrenotazione', 'idPrenotazione DESC'];
        if (!in_array($sortType, $allowedSort, true)) {
            $sortType = 'idPrenotazione DESC';
        }

        $sql .= " ORDER BY $sortType LIMIT :limite OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the most recent bookings for a user (for homepage display)
     *
     * @param string $email User email
     * @param int $limit Number of bookings
     * @return array
     */
    public function getRecentForUser(string $email, int $limit = 2): array
    {
        $q = "SELECT Nome, Autore, Copertina, InizioPrenotazione, FinePrenotazione, 
                     InizioPrestito, FinePrestito, FineAttesa, idPrenotazione
              FROM Prenotazione, Opera, copiaLibro
              WHERE copiaLibro.idCopia = Prenotazione.idCopia
              AND copiaLibro.ISBN = Opera.ISBN
              AND Prenotazione.Email = :email
              ORDER BY idPrenotazione DESC
              LIMIT :limite";

        $query = $this->pdo->prepare($q);
        $query->bindParam(':email', $email);
        $query->bindValue(':limite', $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single booking by ID
     *
     * @param int $id Booking ID
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $query = $this->pdo->prepare(
            "SELECT p.*, o.Nome, o.Autore, o.Copertina, o.ISBN 
             FROM Prenotazione p
             JOIN copiaLibro c ON c.idCopia = p.idCopia 
             JOIN Opera o ON o.ISBN = c.ISBN 
             WHERE p.idPrenotazione = :id"
        );
        $query->execute([':id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Cancel a booking (delete it and free the copy)
     *
     * @param int $bookingId Booking ID
     * @return bool True on success
     */
    public function cancel(int $bookingId): bool
    {
        $this->pdo->beginTransaction();

        try {
            // Get the copy ID first
            $query = $this->pdo->prepare("SELECT idCopia FROM Prenotazione WHERE idPrenotazione = :id");
            $query->execute([':id' => $bookingId]);
            $booking = $query->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->pdo->rollBack();
                return false;
            }

            // Free the copy
            $update = $this->pdo->prepare("UPDATE copiaLibro SET Stato = '1' WHERE idCopia = :id");
            $update->execute([':id' => $booking['idCopia']]);

            // Delete the booking
            $delete = $this->pdo->prepare("DELETE FROM Prenotazione WHERE idPrenotazione = :id");
            $delete->execute([':id' => $bookingId]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore durante l\'annullamento: ' . $e->getMessage());
        }
    }

    /**
     * Get active booking ID by copy ID
     *
     * @param int $copyId Copy ID
     * @return int|null Booking ID or null
     */
    public function getByCopyId(int $copyId): ?int
    {
        $query = $this->pdo->prepare("SELECT idPrenotazione FROM Prenotazione WHERE idCopia = :id AND FinePrestito IS NULL");
        $query->bindParam(':id', $copyId);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['idPrenotazione'] : null;
    }

    /**
     * Confirm a booking (start the loan)
     *
     * @param int $bookingId Booking ID
     * @param int $loanDays Loan duration in days
     * @return bool True on success
     */
    public function confirm(int $bookingId, int $loanDays = 30): bool
    {
        $query = $this->pdo->prepare("UPDATE Prenotazione SET InizioPrestito = CURDATE() WHERE idPrenotazione = :id AND CURDATE() <= FinePrenotazione");
        $query->bindParam(':id', $bookingId, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() <= 0) {
            return false;
        }

        $query = $this->pdo->prepare("UPDATE Prenotazione SET FineAttesa = ADDDATE(CURDATE(), INTERVAL :giorni DAY) WHERE idPrenotazione = :id");
        $query->bindParam(':giorni', $loanDays, PDO::PARAM_INT);
        $query->bindParam(':id', $bookingId, PDO::PARAM_INT);
        $query->execute();
        return true;
    }

    /**
     * Complete a booking (return the book)
     *
     * @param int $bookingId Booking ID
     * @return array ['success' => bool, 'late' => bool, 'message' => string]
     */
    public function complete(int $bookingId): array
    {
        $this->pdo->beginTransaction();

        try {
            $query = $this->pdo->prepare("UPDATE Prenotazione SET FinePrestito = CURDATE() WHERE idPrenotazione = :id");
            $query->bindParam(':id', $bookingId, PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() <= 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'late' => false, 'message' => 'Errore: prenotazione non trovata'];
            }

            // Free the copy
            $query = $this->pdo->prepare("UPDATE copiaLibro, Prenotazione SET copiaLibro.Stato = '1' WHERE copiaLibro.idCopia = Prenotazione.idCopia AND Prenotazione.idPrenotazione = :id");
            $query->bindParam(':id', $bookingId, PDO::PARAM_INT);
            $query->execute();

            // Check if late
            $queryInfo = $this->pdo->prepare("SELECT Email, FineAttesa FROM Prenotazione WHERE idPrenotazione = :id");
            $queryInfo->bindParam(':id', $bookingId, PDO::PARAM_INT);
            $queryInfo->execute();
            $info = $queryInfo->fetch(PDO::FETCH_ASSOC);

            $late = false;
            if ($info && strtotime(date('Y-m-d')) > strtotime($info['FineAttesa'])) {
                $queryPunti = $this->pdo->prepare("UPDATE Utente SET punteggio = punteggio - 10 WHERE Email = :email");
                $queryPunti->bindParam(':email', $info['Email']);
                $queryPunti->execute();
                $late = true;
            }

            $this->pdo->commit();
            return [
                'success' => true,
                'late' => $late,
                'message' => $late ? 'ok prestito terminato con ritardo. 10 punti sottratti!' : 'ok prestito terminato con successo!',
            ];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException('Errore durante la chiusura: ' . $e->getMessage());
        }
    }

    /**
     * Admin cancel a booking with full details return
     *
     * @param int $bookingId Booking ID
     * @return array ['success' => bool, 'userId' => int|null, 'bookingData' => array|null]
     */
    public function adminCancel(int $bookingId): array
    {
        $this->pdo->beginTransaction();

        try {
            $query = $this->pdo->prepare('SELECT idCopia, Email FROM Prenotazione WHERE idPrenotazione = :id');
            $query->bindParam(':id', $bookingId, PDO::PARAM_INT);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->pdo->rollBack();
                return ['success' => false, 'userId' => null, 'bookingData' => null];
            }

            $idCopia = $row['idCopia'];
            $email = $row['Email'];

            // Get user ID
            $query = $this->pdo->prepare("SELECT id FROM Utente WHERE Email = :email");
            $query->bindParam(':email', $email);
            $query->execute();
            $user = $query->fetch(PDO::FETCH_ASSOC);
            $idUtente = $user ? (int) $user['id'] : 0;

            // Get booking details for email
            $query = $this->pdo->prepare("SELECT Prenotazione.idCopia, Opera.ISBN, Opera.Nome as Titolo, InizioPrenotazione, FinePrenotazione
                                          FROM Prenotazione, Opera, copiaLibro
                                          WHERE Prenotazione.idCopia = copiaLibro.idCopia
                                          AND copiaLibro.ISBN = Opera.ISBN
                                          AND idPrenotazione = :id");
            $query->bindParam(':id', $bookingId, PDO::PARAM_INT);
            $query->execute();
            $bookingData = $query->fetch(PDO::FETCH_ASSOC);

            // Delete booking
            $delete = $this->pdo->prepare("DELETE FROM Prenotazione WHERE idPrenotazione = :id");
            $delete->bindParam(':id', $bookingId, PDO::PARAM_INT);
            $delete->execute();

            // Free copy
            $update = $this->pdo->prepare("UPDATE copiaLibro SET Stato = '1' WHERE idCopia = :idCopia");
            $update->bindParam(':idCopia', $idCopia, PDO::PARAM_INT);
            $update->execute();

            $this->pdo->commit();
            return ['success' => true, 'userId' => $idUtente, 'bookingData' => $bookingData];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException('Errore durante l\'eliminazione: ' . $e->getMessage());
        }
    }

    /**
     * Get terminated bookings for a user with review status
     *
     * @param string $email User email
     * @return array Booking records with 'recensito' flag
     */
    public function getTerminatedByEmail(string $email): array
    {
        $sql2 = "SELECT idOpera FROM recensione WHERE userEmail = :userEmail";
        $query2 = $this->pdo->prepare($sql2);
        $query2->bindParam(':userEmail', $email);
        $query2->execute();
        $recensiti = $query2->fetchAll(PDO::FETCH_COLUMN, 0);

        $sql = "SELECT idPrenotazione, InizioPrestito, FinePrestito, FineAttesa, Copertina, Nome, Autore, CasaEditrice, Opera.id as idOpera, Opera.ISBN
                FROM Prenotazione
                JOIN copiaLibro ON copiaLibro.idCopia = Prenotazione.idCopia
                JOIN Opera ON Opera.ISBN = copiaLibro.ISBN
                WHERE Prenotazione.Email = :email AND FinePrestito IS NOT NULL
                ORDER BY Prenotazione.idPrenotazione DESC";

        $query = $this->pdo->prepare($sql);
        $query->bindParam(':email', $email);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$row) {
            $row['recensito'] = in_array($row['idOpera'], $recensiti, false) ? 1 : 0;
        }

        return $results;
    }
}
