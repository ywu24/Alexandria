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

    public function __construct()
    {
        $this->librarianEmail = getenv('EMAIL_BIBLIO') ?: '';
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
}
