<?php
session_start();
require_once("../utils/connect.php");
$root = '..';

$table = "Prenotazione";
$table1 = "copiaLibro";
$table2 = "Opera";

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

if (isset($_POST["delete"]) and isset($_POST["id"])) {
    $idprenotazione = $_POST["id"];
    try {
        $query = $pdo->prepare("UPDATE copiaLibro, Prenotazione SET copiaLibro.Stato = '1' 
                                WHERE copiaLibro.idCopia = Prenotazione.idCopia AND Prenotazione.idPrenotazione = :idprenotazione");
        $query->bindParam(':idprenotazione', $idprenotazione);
        $query->execute();

        if ($query->rowCount() > 0) {
            $query1 = $pdo->prepare("DELETE FROM Prenotazione WHERE idPrenotazione = :idprenotazione AND InizioPrestito IS NULL");
            $query1->bindParam(':idprenotazione', $idprenotazione);
            $query1->execute();

            if ($query1->rowCount() > 0) {
                echo "ok Prenotazione eliminata con successo!";
            } else {
                echo "Errore: eliminazione non riuscita";
            }
            $query1->closeCursor();
        } else {
            echo "Errore: eliminazione non riuscita";
        }
        $query->closeCursor();
    } catch (PDOException $e) {
        echo ("Errore: " . $e->getMessage());
    }
}
if (isset($_POST["termina"]) and isset($_POST["id"])) {
    $idprenotazione = $_POST["id"];
    try {
        $query = $pdo->prepare("UPDATE `Prenotazione` SET `FinePrestito` = CURDATE() WHERE `Prenotazione`.`idPrenotazione` = :idprenotazione");
        $query->bindParam(':idprenotazione', $idprenotazione);
        $query->execute();
        if ($query->rowCount() > 0) {
            echo "ok Prenotazione Terminata con successo!";
        } else {
            echo "Errore: terminazione prenotazione non riuscita";
        }
        $query->closeCursor();
    } catch (PDOException $e) {
        echo "Errore:" . $e->getMessage();
    }
}
?>