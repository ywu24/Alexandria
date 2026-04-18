<?php
header('Content-Type: application/json');
require_once("../../utils/connect.php");

if (isset($_POST['isbn'])) {
    $isbn = $_POST['isbn'];
    $stmt = $conn->prepare("SELECT idCopia, Stato FROM copiaLibro WHERE ISBN = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();

    $result = $stmt->get_result();
    // fetch_all trasforma il risultato direttamente in un array
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $copie = [];
    foreach ($rows as $row) {
        $copie[] = $row;
    }

    echo json_encode($copie);
}
?>