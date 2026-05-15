<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file Report (segnalazione) service handling create, delete, and file upload
 */

namespace Alexandria\Services;

use PDO;
use PDOException;
use RuntimeException;

class ReportService
{
    private PDO $pdo;
    private string $uploadPath;

    public function __construct(PDO $pdo, string $uploadPath = __DIR__ . '/../../img/segnalazioni/')
    {
        $this->pdo = $pdo;
        $this->uploadPath = $uploadPath;
    }

    /**
     * Create a new report with optional file upload
     *
     * @param string $userEmail Reporter email
     * @param string $oggetto Report subject
     * @param string $messaggio Report message
     * @param array|null $file Uploaded file array from $_FILES
     * @return array ['success' => bool, 'id' => int|null, 'imgSegn' => string|null]
     * @throws RuntimeException On database or upload error
     */
    public function create(string $userEmail, string $oggetto, string $messaggio, ?array $file = null): array
    {
        $imgSegn = null;

        // Handle file upload if present
        if ($file !== null && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            $validation = \validate_upload($file, ['jpg', 'jpeg', 'png'], 5000000);
            if (!$validation['valid']) {
                throw new RuntimeException($validation['error']);
            }

            $fileName = \sanitize_filename($file['name']);
            $destination = $this->uploadPath . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new RuntimeException('Errore durante il salvataggio del file');
            }

            $imgSegn = $fileName;
        }

        try {
            if ($imgSegn !== null) {
                $query = $this->pdo->prepare(
                    "INSERT INTO Segnalazione (userEmail, Oggetto, Messaggio, imgSegn) VALUES (:email, :oggetto, :messaggio, :imgSegn)"
                );
                $query->bindParam(':imgSegn', $imgSegn);
            } else {
                $query = $this->pdo->prepare(
                    "INSERT INTO Segnalazione (userEmail, Oggetto, Messaggio) VALUES (:email, :oggetto, :messaggio)"
                );
            }

            $query->bindParam(':email', $userEmail);
            $query->bindParam(':oggetto', $oggetto);
            $query->bindParam(':messaggio', $messaggio);
            $query->execute();
            $query->closeCursor();

            return [
                'success' => true,
                'id' => (int) $this->pdo->lastInsertId(),
                'imgSegn' => $imgSegn,
            ];
        } catch (PDOException $e) {
            // Clean up uploaded file if DB failed
            if ($imgSegn !== null && file_exists($this->uploadPath . $imgSegn)) {
                unlink($this->uploadPath . $imgSegn);
            }
            throw new RuntimeException("Errore durante l'invio della segnalazione: " . $e->getMessage());
        }
    }

    /**
     * Get all reports ordered by newest first
     *
     * @param int $limit Maximum number of reports
     * @return array
     */
    public function getAll(int $limit = 100): array
    {
        $query = $this->pdo->prepare(
            "SELECT idSegnalazione, userEmail, Oggetto FROM Segnalazione ORDER BY idSegnalazione DESC LIMIT :limit"
        );
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single report by ID
     *
     * @param int $id Report ID
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $query = $this->pdo->prepare("SELECT * FROM Segnalazione WHERE idSegnalazione = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Delete a report by ID
     *
     * @param int $id Report ID
     * @return bool True on success
     */
    public function delete(int $id): bool
    {
        // Get the report first to clean up image
        $report = $this->getById($id);

        $query = $this->pdo->prepare("DELETE FROM Segnalazione WHERE idSegnalazione = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $success = $query->execute();

        // Clean up image file if exists
        if ($success && $report && !empty($report['imgSegn'])) {
            $path = $this->uploadPath . $report['imgSegn'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        return $success;
    }

    /**
     * Count total reports
     *
     * @return int
     */
    public function count(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM Segnalazione")->fetchColumn();
    }
}
