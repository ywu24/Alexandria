<?php
require_once("../../utils/connect.php");
if(!isset($_POST['idUtente']) && !isset($_POST['email'])){
    die();
}
$email = "";
if(isset($_POST['idUtente'])){
    $idUtente = $_POST['idUtente'];

    // Recupero l'email dell'utente
    $q = $conn->prepare('SELECT Email FROM Utente WHERE id=?');
    $q->bind_param('i', $idUtente);
    $q->execute();
    $email = $q->get_result()->fetch_assoc()['Email'];
}
else{
    $email = $_POST['email'];
}
// Query solo per Prenotazioni Terminate
$sql = "SELECT idPrenotazione, InizioPrestito, FinePrestito, FineAttesa 
        FROM Prenotazione, copiaLibro, Opera 
        WHERE copiaLibro.idCopia = Prenotazione.idCopia 
        AND Opera.ISBN = copiaLibro.ISBN 
        AND Prenotazione.Email = ? 
        AND FinePrestito IS NOT NULL
        ORDER BY Prenotazione.idPrenotazione DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

$terminate = [];
while ($row = $result->fetch_assoc()) {
    $terminate[] = $row;
}
echo json_encode($terminate);
?>