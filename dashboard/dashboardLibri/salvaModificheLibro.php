<?php
// Debug 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../../utils/connect.php");

// Recupero dati dalla POST
$isbn_old = $_POST["id"]; // L'ISBN originale usato come identificatore
$isbn_new = $_POST["isbn"]; // Il nuovo ISBN (se modificato)
$titolo = $_POST["titolo"];
$autore = $_POST["autore"];
$genere = $_POST["genere"];
$desc = $_POST["desc"];
$casaed = $_POST["casaed"];
$annopub = $_POST["annopub"];

try {
    // Otteniamo l'istanza della connessione PDO
    $pdo = DatabaseConnection::getInstance()->getConnection();

    // Prepariamo la query con i segnaposto nominati
    $sql = "UPDATE Opera 
            SET ISBN = :isbn_new, 
                Nome = :titolo, 
                Autore = :autore, 
                Genere = :genere, 
                Descrizione = :descr, 
                CasaEditrice = :casaed, 
                AnnoPubblicazione = :annopub 
            WHERE ISBN = :isbn_old";

    $stmt = $pdo->prepare($sql);

    // Esecuzione con binding dei parametri
    $stmt->execute([
        ':isbn_new' => $isbn_new,
        ':titolo'   => $titolo,
        ':autore'   => $autore,
        ':genere'   => $genere,
        ':descr'    => $desc,
        ':casaed'   => $casaed,
        ':annopub'  => $annopub,
        ':isbn_old' => $isbn_old
    ]);

    // Se l'esecuzione va a buon fine
    $_SESSION['success_msg'] = "Libro aggiornato con successo!";
    header("Location: dashboardLibri.php");
    exit;

} catch (PDOException $e) {
    // Gestione errore (es: ISBN duplicato o errore di sintassi)
    $_SESSION['error_msg'] = "Errore durante l'aggiornamento: " . $e->getMessage();
    header("Location: dashboardLibri.php?errore=1");
    exit;
}
?>