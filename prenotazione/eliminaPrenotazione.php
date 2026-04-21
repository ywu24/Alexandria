<?php
session_start();
require_once("../utils/connect.php");
$root = '..';

$table = "Prenotazione";
$table1 = "copiaLibro";
$table2 = "Opera";


if (isset($_POST["delete"]) and isset($_POST["id"])) {
    $idprenotazione = $_POST["id"]; 
    try{
        $conn->query("UPDATE copiaLibro, Prenotazione SET copiaLibro.Stato = '1' WHERE copiaLibro.idCopia = Prenotazione.idCopia AND Prenotazione.idPrenotazione = $idprenotazione");
        if($conn->affected_rows >0){
            $conn->query("DELETE FROM Prenotazione WHERE idPrenotazione = $idprenotazione AND InizioPrestito IS NULL");
            if($conn->affected_rows>0){
                echo"ok Prenotazione eliminata con successo!";
            }
            else{
                echo "Errore: eliminazione non riuscita";
            }
        } else{
            echo "Errore: eliminazione non riuscita";
        }
    }
    catch(Exception $e){
        echo("Errore: " . $e);
    }
    }
if (isset($_POST["termina"]) and isset($_POST["id"])) {
    $idprenotazione = $_POST["id"];
    try{
        $conn->query("UPDATE `Prenotazione` SET `FinePrestito` = CURDATE() WHERE `Prenotazione`.`idPrenotazione` = $idprenotazione ");
        if($conn->affected_rows>0){
            echo "ok Prenotazione Terminata con successo!";
        } else{
            echo "Errore: terminazione prenotazione non riuscita";
        }
    }
    catch(Exception $e){
        echo "Errore:" . $e;
    }
}
    ?>