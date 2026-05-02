<?php
if (!isset($_POST['id'])) {
    echo "Errore: prenotazione non definita";
    exit;
}
$giorniPrenotazione = 30;

$id = (int) $_POST['id'];
$root = "../..";
require_once("../../utils/connect.php");
require_once("../../utils/mailer.php");
require_once("../../auth/cookies.php");

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

switch (true) {
    case isset($_POST['conferma']):
        #echo "ID = " . $id;
        try {
            $query = $pdo->prepare("UPDATE Prenotazione SET InizioPrestito = CURDATE() WHERE idPrenotazione = :id AND CURDATE()<=FinePrenotazione");
            $query->bindParam(':id', $id);
            $query->execute();
            if ($query->rowCount() > 0) {
                $query = $pdo->prepare("UPDATE `Prenotazione` SET `FineAttesa` = ADDDATE(CURDATE(), INTERVAL :giorni DAY) WHERE `Prenotazione`.`idPrenotazione` = :id");
                $query->bindParam(':giorni', $giorniPrenotazione);
                $query->bindParam(':id', $id);
                $query->execute();
                $query->closeCursor();
                echo "ok";

                //email 
                $query = $pdo->prepare("SELECT Email, Prenotazione.idPrenotazione AS idPrenotazione, Prenotazione.idCopia AS idCopia, Opera.ISBN AS ISBN, Opera.Nome AS Titolo, InizioPrestito, FineAttesa
                                        FROM Prenotazione, Opera, copiaLibro 
                                        WHERE Prenotazione.idCopia = copiaLibro.idCopia 
                                        AND copiaLibro.ISBN = Opera.ISBN
                                        AND idPrenotazione = :id");
                $query->bindParam(':id', $id);
                $query->execute();
                $result = $query->fetch();
                $query->closeCursor();
 
                if ($result) {
                    if (
                        sendEmail(
                            $result['Email'],
                            $result['Email'],
                            'Libro Ritirato',
                            '<h2>Hai ritirato il libro ' . $result['Titolo'] . '</h2>
                                    <p>Informazioni sul prestito:</p>
                                    <ul>
                                        <li>ISBN: ' . $result['ISBN'] . '</li>
                                        <li>ID Copia: ' . $result['idCopia'] . '</li>
                                        <li>ID Prenotazione: ' . $result['idPrenotazione'] . '</li>
                                        <li>Data inizio prestito: ' . $result['InizioPrestito'] . '</li>
                                        <li>Data fine prestito: ' . $result['FineAttesa'] . '</li>
                                    </ul>'
                        )
                    ) {
                        echo "Email mandato al bibliotecario"; // per debug
                    } else {
                        //echo "Errore nell'invio dell'email"; //per debug
                    }
                }
            } else {
                ####segnalale errore: prenotazione scaduta.
                echo "Errore prenotazione Scaduta?";
            }
        } catch (Exception $e) {
            echo "Errore " . $e->getMessage();
        }
        break;
    case isset($_POST['termina']):
        try {
            $query = $pdo->prepare("UPDATE Prenotazione SET FinePrestito = CURDATE() WHERE idPrenotazione = :id");
            $query->bindParam(':id', $id);
            $query->execute();
            if ($query->rowCount() > 0) {
                $query = $pdo->prepare("UPDATE copiaLibro, Prenotazione SET copiaLibro.Stato = '1' WHERE copiaLibro.idCopia = Prenotazione.idCopia AND Prenotazione.idPrenotazione = :id");
                $query->bindParam(':id', $id);
                $query->execute();
                $query->closeCursor();
                echo "ok prestito terminato con successo!";
                //email 
                $query = $pdo->prepare("SELECT Email, Prenotazione.idPrenotazione AS idPrenotazione, Prenotazione.idCopia AS idCopia, Opera.ISBN AS ISBN, Opera.Nome AS Titolo, InizioPrestito, FineAttesa, FinePrestito
                                        FROM Prenotazione, Opera, copiaLibro 
                                        WHERE Prenotazione.idCopia = copiaLibro.idCopia 
                                        AND copiaLibro.ISBN = Opera.ISBN
                                        AND idPrenotazione = :id");
                $query->bindParam(':id', $id);
                $query->execute();
                $result = $query->fetch();
                $query->closeCursor();
 
                if ($result) {
                    if (
                        sendEmail(
                            $result['Email'],
                            $result['Email'],
                            'Libro Restituito',
                            '<h2>Hai restituito il libro ' . $result['Titolo'] . '</h2>
                                    <p>Informazioni sul prestito:</p>
                                    <ul>
                                        <li>ISBN: ' . $result['ISBN'] . '</li>
                                        <li>ID Copia: ' . $result['idCopia'] . '</li>
                                        <li>ID Prenotazione: ' . $result['idPrenotazione'] . '</li>
                                        <li>Data inizio prestito: ' . $result['InizioPrestito'] . '</li>
                                        <li>Data fine prestito: ' . $result['FineAttesa'] . '</li>
                                        <li>Data restituzione: ' . $result['FinePrestito'] . '</li>
                                    </ul>'
                        )
                    ) {
                        echo "Email mandato al bibliotecario"; // per debug
                    } else {
                        //echo "Errore nell'invio dell'email"; //per debug
                    }
                }

            } else {
                echo "Errore: " . $id;
            }
        } catch (Exception $e) {
            echo "Errore " . $e->getMessage();
        }
        break;
    case isset($_POST['elimina']):
        try {
            $pdo->beginTransaction(); //inizia la transazione atomica

            $query = $pdo->prepare('SELECT idCopia, Email FROM Prenotazione WHERE idPrenotazione = :id');
            $query->bindParam(':id', $id);
            $query->execute();
            $row = $query->fetch();
            $query->closeCursor();

            if ($row) {
                $idCopia = $row['idCopia'];
                $email = $row['Email'];

                $query = $pdo->prepare("SELECT id FROM Utente WHERE Email=  :email");
                $query->bindParam(':email', $email);
                $query->execute();
                $result = $query->fetch();
                $query->closeCursor();

                $idUtente = $result['id'];

                // 1. Salviamo i dati della prenotazione per l'email prima di eliminarla

                $query = $pdo->prepare("SELECT Prenotazione.idCopia AS idCopia, Opera.ISBN AS ISBN, Opera.Nome AS Titolo, InizioPrenotazione, FinePrenotazione
                                        FROM Prenotazione, Opera, copiaLibro 
                                        WHERE Prenotazione.idCopia = copiaLibro.idCopia 
                                        AND copiaLibro.ISBN = Opera.ISBN
                                        AND idPrenotazione = :id");
                $query->bindParam(':id', $id);
                $query->execute();
                $result = $query->fetch();
                $query->closeCursor();

                // 2. Eliminiamo la prenotazione

                $query = $pdo->prepare("DELETE FROM Prenotazione WHERE idPrenotazione = :id");
                $query->bindParam(':id', $id);
                $query->execute();
                $query->closeCursor();

                // 3. Riportiamo la copia del libro a 'disponibile' (Stato 1)

                $query = $pdo->prepare("UPDATE copiaLibro SET Stato = '1' WHERE idCopia = :idCopia");
                $query->bindParam(':idCopia', $idCopia);
                $query->execute();
                $query->closeCursor();

                echo "ok " . $idUtente;

                //email 
                
                if ($result) {
                    $emailBiblio = getenv('EMAIL_BIBLIO') ? getenv('EMAIL_BIBLIO') : '';

                    if (
                        sendEmail(
                            $email,
                            $email,
                            'Prenotazione Annullata',
                            '<h2>Abbiamo annullato la tua prenotazione del libro ' . $result['Titolo'] . '</h2>
                                    <p>Informazioni sulla prenotazione:</p>
                                    <ul>
                                        <li>ISBN: ' . $result['ISBN'] . '</li>
                                        <li>ID Copia: ' . $result['idCopia'] . '</li>
                                        <li>ID Prenotazione: ' . $id . '</li>
                                        <li>Data inizio prenotazione: ' . $result['InizioPrenotazione'] . '</li>
                                        <li>Data fine prenotazione: ' . $result['FinePrenotazione'] . '</li>
                                    </ul>
                                    <br>
                                    <p>Per maggiori informazioni, si prega di contattare il bibliotecario (' . $emailBiblio . ').</p>
                                    '
                        )
                    ) {
                        echo "Email mandato al bibliotecario"; // per debug
                    } else {
                        //echo "Errore nell'invio dell'email"; //per debug
                    }
                }

            } else {
                echo "Errore: prenotazione non trovata";
            }

            $pdo->commit(); //committa tutto a partire da beginTransaction()
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "Errore durante l'eliminazione: " . $e->getMessage();
        }
        break;

    default:
        echo "ID = " . $id;
        break;
}
?>