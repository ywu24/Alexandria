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
     * Get number of total borrowed books (both active and finished)
     *
     * @return int
     */
    public function getBooksBorrowed(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM Prenotazione WHERE InizioPrestito IS NOT NULL"
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
     * @return array ['totali', 'inCorso', 'riconsegnate', 'prenotati']
     */
    public function getUserBookingStats(string $email): array
    {
        $totali = "SELECT count(idPrenotazione) as totali FROM Prenotazione WHERE Email = :email";
        $inCorso = "SELECT count(idPrenotazione) as incorso FROM Prenotazione WHERE Email = :email AND InizioPrestito IS NOT NULL AND FinePrestito IS NULL";
        $prenotazioni = "SELECT count(idPrenotazione) as prenotati FROM Prenotazione WHERE Email = :email AND InizioPrestito IS NULL";
        $riconsegnate = "SELECT count(idPrenotazione) as riconsegnate FROM Prenotazione WHERE Email = :email AND FinePrestito IS NOT NULL";

        $query = $this->pdo->prepare($totali);
        $query->bindParam(':email', $email);
        $query->execute();
        $pTotali = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        $query = $this->pdo->prepare($inCorso);
        $query->bindParam(':email', $email);
        $query->execute();
        $p_inCorso = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        $query = $this->pdo->prepare($riconsegnate);
        $query->bindParam(':email', $email);
        $query->execute();
        $p_riconsegnate = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        $query = $this->pdo->prepare($prenotazioni);
        $query->bindParam(':email', $email);
        $query->execute();
        $p_prenotati = $query->fetch(PDO::FETCH_ASSOC);
        $query->closeCursor();

        return [
            'totali' => (int) ($pTotali['totali'] ?? 0),
            'inCorso' => (int) ($p_inCorso['incorso'] ?? 0),
            'riconsegnate' => (int) ($p_riconsegnate['riconsegnate'] ?? 0),
            'prenotati' => (int) ($p_prenotati['prenotati'] ?? 0),
        ];
    }

    /**
     * Get global booking statistics for admin dashboard
     *
     * @return array ['totale', 'prenotati', 'in_prestito', 'in_ritardo', 'terminati']
     */
    public function getGlobalBookingStats(): array
    {
        $sql = "SELECT 
            COUNT(*) as totale,
            SUM(CASE WHEN InizioPrestito IS NULL THEN 1 ELSE 0 END) as prenotati,
            SUM(CASE WHEN InizioPrestito IS NOT NULL AND FinePrestito IS NULL AND NOW() <= FineAttesa THEN 1 ELSE 0 END) as in_prestito,
            SUM(CASE WHEN InizioPrestito IS NOT NULL AND FinePrestito IS NULL AND NOW() > FineAttesa THEN 1 ELSE 0 END) as in_ritardo,
            SUM(CASE WHEN FinePrestito IS NOT NULL THEN 1 ELSE 0 END) as terminati
        FROM Prenotazione";
        
        $result = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        
        return [
            'totale' => (int) ($result['totale'] ?? 0),
            'prenotati' => (int) ($result['prenotati'] ?? 0),
            'in_prestito' => (int) ($result['in_prestito'] ?? 0),
            'in_ritardo' => (int) ($result['in_ritardo'] ?? 0),
            'terminati' => (int) ($result['terminati'] ?? 0),
        ];
    }
}
