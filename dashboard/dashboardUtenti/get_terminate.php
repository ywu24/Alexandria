<?php
require_once("../../utils/connect.php");

$idUtente = $_POST['idUtente'];

// Recupero l'email dell'utente
$q = $conn->prepare('SELECT Email FROM Utente WHERE id=?');
$q->bind_param('i', $idUtente);
$q->execute();
$email = $q->get_result()->fetch_assoc()['Email'];

// Query solo per lo stato 4 (Terminata)
$sql = "SELECT idPrenotazione, Copertina, Prenotazione.idCopia, Inizio, Fine, Autore, Nome, CasaEditrice, Prenotazione.Stato 
        FROM Prenotazione, copiaLibro, Opera 
        WHERE copiaLibro.idCopia = Prenotazione.idCopia 
        AND Opera.ISBN = copiaLibro.ISBN 
        AND Prenotazione.Email = ? 
        AND Prenotazione.Stato = 4 
        ORDER BY Prenotazione.idPrenotazione DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

$terminate = [];
while ($row = $result->fetch_assoc()) {
    $terminate[] = $row;
}

header('Content-Type: application/json');
echo json_encode($terminate);