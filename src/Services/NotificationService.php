<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Services
 * @file Notification service for sending emails to librarians
 */

namespace Alexandria\Services;

use PDO;
use RuntimeException;

require_once __DIR__ . '/../../utils/mailer.php';

class NotificationService
{
    private string $librarianEmail;
    private ?PDO $pdo; // <-- AGGIUNTO

    public function __construct(?PDO $pdo = null) // <-- AGGIUNTO
    {
        $this->librarianEmail = getenv('EMAIL_BIBLIO') ?: '';
        $this->pdo = $pdo; // <-- AGGIUNTO
    }

    /**
     * Notify the librarian about a new booking
     *
     * @param string $userEmail The user who made the booking
     * @param string $userType User type string (Premium/Standard)
     * @param string $isbn Book ISBN
     * @param string $startDate Booking start date
     * @param string $endDate Booking end date
     * @param int|null $quantity Number of copies (for premium users)
     * @param int|null $copyId Copy ID (for standard users)
     * @return bool True if email sent successfully or no librarian email configured
     */
    public function notifyBooking(
        string $userEmail,
        string $userType,
        string $isbn,
        string $startDate,
        string $endDate,
        ?int $quantity = null,
        ?int $copyId = null
    ): bool {
        if (empty($this->librarianEmail)) {
            return true;
        }

        $details = [
            '<li>Email Utente: ' . htmlspecialchars($userEmail) . '</li>',
            '<li>Tipo Utente: ' . htmlspecialchars($userType) . '</li>',
            '<li>ISBN: ' . htmlspecialchars($isbn) . '</li>',
        ];

        if ($quantity !== null) {
            $details[] = '<li>Quantita: ' . $quantity . '</li>';
        }
        if ($copyId !== null) {
            $details[] = '<li>ID Copia: ' . $copyId . '</li>';
        }

        $details[] = '<li>Data inizio: ' . htmlspecialchars($startDate) . '</li>';
        $details[] = '<li>Data fine: ' . htmlspecialchars($endDate) . '</li>';

        $html = '<h2>E stata effettuata una nuova prenotazione!</h2>'
            . '<p>Informazioni sulla prenotazione:</p>'
            . '<ul>' . implode("\n", $details) . '</ul>';

        return sendEmail($this->librarianEmail, 'Bibliotecario', 'Nuova prenotazione', $html);
    }

    /**
     * Notify the librarian about a new report (segnalazione)
     *
     * @param string $userEmail The user who submitted the report
     * @param string $oggetto Report subject
     * @param string $messaggio Report message
     * @param string|null $imgSegn Screenshot filename if any
     * @return bool True if email sent successfully or no librarian email configured
     */
    public function notifyReport(string $userEmail, string $oggetto, string $messaggio, ?string $imgSegn = null): bool
    {
        if (empty($this->librarianEmail)) {
            return true;
        }

        $html = '<h2>E stata effettuata una nuova segnalazione!</h2>'
            . '<p>Informazioni sulla segnalazione:</p>'
            . '<ul>'
            . '<li>Email Utente: ' . htmlspecialchars($userEmail) . '</li>'
            . '<li>Oggetto: ' . htmlspecialchars($oggetto) . '</li>'
            . '<li>Messaggio: <p>' . nl2br(htmlspecialchars($messaggio)) . '</p></li>'
            . '<li>Immagine: ' . ($imgSegn ? htmlspecialchars($imgSegn) : 'Nessuna') . '</li>'
            . '</ul>';

        return sendEmail(
            $this->librarianEmail,
            'Bibliotecario',
            'Nuova segnalazione da ' . $userEmail,
            $html
        );
    }

    /**
     * Send a registration confirmation email
     *
     * @param string $toEmail Recipient email
     * @param string $toName Recipient full name
     * @param string $code Confirmation code
     * @return bool True on success
     */
    public function sendConfirmationEmail(string $toEmail, string $toName, string $code): bool
    {
        $html = '<h2>Conferma la registrazione!</h2>'
            . '<p>Ciao, ' . htmlspecialchars($toName) . '<br> conferma la tua mail inserendo questo codice sul sito:</p>'
            . '<ul>'
            . '<li>La tua email: ' . htmlspecialchars($toEmail) . '</li>'
            . '<li>Il codice di conferma: ' . htmlspecialchars($code) . '</li>'
            . '</ul>';

        return sendEmail($toEmail, $toName, 'Conferma Registrazione', $html);
    }


    /* NOTIFICHE */

    /**
     * Crea una nuova notifica nel database.
     *
     * @param int $utente_id ID dell'utente destinatario
     * @param string $titolo Titolo della notifica
     * @param string $messaggio Corpo della notifica
     * @param string|null $url_azione URL opzionale per l'azione della notifica
     * @return bool Ritorna true se l'inserimento ha successo, false in caso di errore
     */
    public function creaNotifica(int $utente_id, string $titolo, string $messaggio, ?string $url_azione = null): bool
    {
        try {
            $sql = "INSERT INTO notifiche (utente_id, titolo, messaggio, url_azione) 
                    VALUES (:utente_id, :titolo, :messaggio, :url_azione)";
            
            $stmt = $this->pdo->prepare($sql); // <-- ADATTATO PER USARE $this->pdo
            $stmt->execute([
                ':utente_id'  => $utente_id,
                ':titolo'     => $titolo,
                ':messaggio'  => $messaggio,
                ':url_azione' => $url_azione
            ]);
            
            return true;
        } catch (\PDOException $e) {
            // Log dell'errore (evita di mostrare dettagli sensibili del DB all'utente finale)
            error_log("Errore inserimento notifica: " . $e->getMessage());
            return false;
        }
    }


    public function getUserNotifications(string $email): array 
    {
        // Recupera l'ID utente dall'email (facendo una JOIN o usando un metodo di UserService)
        $sql = "SELECT n.* FROM notifiche n
                JOIN Utente u ON n.utente_id = u.id
                WHERE u.Email = :email 
                ORDER BY n.data_creazione DESC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function markAllAsRead(string $email): void 
    {
        $sql = "UPDATE notifiche n
                JOIN Utente u ON n.utente_id = u.id
                SET n.letta = 1 
                WHERE u.Email = :email AND n.letta = 0";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
    }


    /**
     * Conta il numero di notifiche non lette per un determinato utente.
     *
     * @param string $email L'email dell'utente
     * @return int Il numero di notifiche da leggere
     */
    public function getUnreadCount(string $email): int 
    {
        $sql = "SELECT COUNT(n.id) FROM notifiche n
                JOIN Utente u ON n.utente_id = u.id
                WHERE u.Email = :email AND n.letta = 0";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Recupera le ultime N notifiche di un utente.
     *
     * @param string $email L'email dell'utente
     * @param int $limit Numero massimo di notifiche da restituire
     * @return array Lista delle notifiche
     */
    public function getLatestNotifications(string $email, int $limit = 5): array 
    {
        // PDO non accetta il binding di parametri per la clausola LIMIT di default se non impostato come intero.
        // Dobbiamo assicurarci di usare bindValue con PDO::PARAM_INT
        $sql = "SELECT n.* FROM notifiche n
                JOIN Utente u ON n.utente_id = u.id
                WHERE u.Email = :email 
                ORDER BY n.data_creazione DESC 
                LIMIT :limit";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}