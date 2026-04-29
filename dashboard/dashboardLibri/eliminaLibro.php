<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = "../..";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");

if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {
} else {
    header("Location: ../../index.php");
    exit;
}
if (isset($_POST['isbn'])) {

    try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
    }

    $isbn = $_POST['isbn'];
    try {
        $query = $pdo->prepare("DELETE FROM Opera WHERE isbn= :isbn");
        $query->bindParam(':isbn', $isbn);
        $result = $query->execute();
        if ($query->rowCount() <= 0) {  //rowCount restituisce il numero di righe affette dall'ultima query (DELETE, INSERT, o UPDATE)
            echo "Errore nell'eliminazione del libro con isbn " . $isbn;
            exit;
        }
        echo "okLibro con isbn " . $isbn . " eliminato con successo!";
    } catch (Exception $e) {
        echo "Errore nell'eliminazione del libro con isbn " . $isbn . ", " . $e->getMessage();
        exit;
    }
}
?>