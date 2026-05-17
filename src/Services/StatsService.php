<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file Statistics service for dashboard and homepage metrics
 */

namespace Alexandria\Services;

use PDO;

class StatsService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get total number of books
     *
     * @return int
     */
    public function getTotalBooks(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM Opera")->fetchColumn();
    }

    /**
     * Get number of active users (non-admin)
     *
     * @return int
     */
    public function getActiveUsers(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM Utente WHERE Utenza != 1 AND Utenza != 2")->fetchColumn();
    }

    /**
     * Get number of currently borrowed books
     *
     * @return int
     */
    public function getBooksBorrowed(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM Prenotazione WHERE InizioPrestito IS NOT NULL AND FinePrestito IS NULL"
        )->fetchColumn();
    }

    /**
     * Get number of distinct genres
     *
     * @return int
     */
    public function getGenresAvailable(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(DISTINCT Genere) FROM Opera WHERE Genere IS NOT NULL AND Genere != ''"
        )->fetchColumn();
    }

    /**
     * Get genres with book counts
     *
     * @return array Array of ['Genere' => string, 'count' => int]
     */
    public function getGenresWithCounts(): array
    {
        $stmt = $this->pdo->query(
            "SELECT Genere, COUNT(*) as count FROM Opera WHERE Genere IS NOT NULL AND Genere != '' GROUP BY Genere ORDER BY count DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user's booking statistics
     *
     * @param string $email User email
     * @param bool $isAdmin Whether the user is an admin (includes all bookings)
     * @return array ['totali', 'inCorso', 'riconsegnate', 'prenotati']
     */
    public function getUserBookingStats(string $email, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            $totali = "SELECT count(idPrenotazione) as totali FROM Prenotazione WHERE Email = :email";
        } else {
            $totali = "SELECT count(idPrenotazione) as totali FROM Prenotazione WHERE Email = :email AND FinePrestito IS NULL";
        }

        $inCorso = "SELECT count(idPrenotazione) as incorso FROM Prenotazione WHERE Email = :email AND FinePrestito IS NULL";
        $prenotazioni = "SELECT count(idPrenotazione) as prenotati FROM Prenotazione WHERE Email = :email AND InizioPrestito IS NULL";
        $riconsegnate = "SELECT count(idPrenotazione) as riconsegnate FROM Prenotazione WHERE Email = :email AND FinePrestito IS NOT NULL";

        $query = $this->pdo->prepare($totali);
        $query->bindParam(':email', $email);
        $query->execute();
        $pTotali = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->pdo->prepare($inCorso);
        $query->bindParam(':email', $email);
        $query->execute();
        $p_inCorso = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->pdo->prepare($riconsegnate);
        $query->bindParam(':email', $email);
        $query->execute();
        $p_riconsegnate = $query->fetch(PDO::FETCH_ASSOC);

        $query = $this->pdo->prepare($prenotazioni);
        $query->bindParam(':email', $email);
        $query->execute();
        $p_prenotati = $query->fetch(PDO::FETCH_ASSOC);

        return [
            'totali' => (int) ($pTotali['totali'] ?? 0),
            'inCorso' => (int) ($p_inCorso['incorso'] ?? 0),
            'riconsegnate' => (int) ($p_riconsegnate['riconsegnate'] ?? 0),
            'prenotati' => (int) ($p_prenotati['prenotati'] ?? 0),
        ];
    }
}
