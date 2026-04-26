<?php
if (!isset($_POST['id'])) {
    echo "Errore: prenotazione non definita";
    exit;
}
$giorniPrenotazione = 30;

$id = (int) $_POST['id'];
$root = "../..";
require_once("../../utils/connect.php");
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