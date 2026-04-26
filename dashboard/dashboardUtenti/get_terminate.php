<?php
$root= "../..";
require_once("../../utils/connect.php");
if (!isset($_POST['idUtente']) && !isset($_POST['email'])) {
    exit;
}
$email = "";
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

if (isset($_POST['idUtente'])) {
    $idUtente = $_POST['idUtente'];

    // Recupero l'email dell'utente
    if ($query = $pdo->prepare('SELECT Email FROM Utente WHERE id = :id')) {
        $query->bindParam(':id', $idUtente);
        $query->execute();
        $email = $query->fetch()['Email'];
        $query->closeCursor();
    } else {
        throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
    }
} else {
    $email = $_POST['email'];
}

// Query solo per Prenotazioni Terminate
$sql = "SELECT idPrenotazione, InizioPrestito, FinePrestito, FineAttesa, Copertina, Nome, Autore,CasaEditrice
        FROM Prenotazione, copiaLibro, Opera 
        WHERE copiaLibro.idCopia = Prenotazione.idCopia 
        AND Opera.ISBN = copiaLibro.ISBN 
        AND Prenotazione.Email = :email 
        AND FinePrestito IS NOT NULL
        ORDER BY Prenotazione.idPrenotazione DESC";

if ($query = $pdo->prepare($sql)) {
    $query->bindParam(':email', $email);
    $query->execute();

    $terminate = [];
    foreach ($query->fetchAll() as $row) {
        $terminate[] = $row;
    }
    $query->closeCursor();
    echo json_encode($terminate);
} else {
    throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
}
?>