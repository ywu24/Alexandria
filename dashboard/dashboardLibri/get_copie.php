<?php
header('Content-Type: application/json');
require_once("../../utils/connect.php");

if (isset($_POST['isbn'])) {
    $isbn = $_POST['isbn'];

    try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
    }
    
    $query = $pdo->prepare("SELECT idCopia, Stato FROM copiaLibro WHERE ISBN = :isbn");
    $query->bindParam(':isbn', $isbn);
    $query->execute();

    $rows = $query->fetchAll();
    $copie = [];
    foreach ($rows as $row) {
        $copie[] = $row;
    }

    echo json_encode($copie);
}
?>