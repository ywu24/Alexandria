<?php
session_start();
$root = "../..";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");

if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {
} else {
    header("Location: ../../index.php");
    exit;
}
if (isset($_POST['id'])) {

    try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
    }

    $id = (int) $_POST['id'];
    try {
        $query = $pdo->prepare("DELETE FROM copiaLibro WHERE idCopia= :id AND Stato=1");
        $query->bindParam(':id', $id);
        $result = $query->execute();
        if ($query->rowCount() <= 0) {  //rowCount restituisce il numero di righe affette dall'ultima query (DELETE, INSERT, o UPDATE)
            echo "Errore nell'eliminazione del libro con id " . $id;
            exit;
        }
        echo "Libro con id " . $id . " eliminato con successo!";
    } catch (Exception $e) {
        echo "Errore nell'eliminazione del libro con id " . $id . ", " . $e->getMessage();
        exit;
    }
}
?>