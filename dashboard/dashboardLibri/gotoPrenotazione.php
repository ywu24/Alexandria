<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");


if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
    header("Location: ../../index.php");
    exit(); 
}


if (!isset($_GET['id'])) {
    header("Location: dashboardLibri.php"); 
    exit();
}

$id = $_GET['id'];

try {
    
    $query = $conn->prepare("SELECT idPrenotazione FROM Prenotazione WHERE idCopia = ? AND FinePrestito IS NULL");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        header("Location: ../dashboardUtenti/dettaglioPrenotazione.php?id=" . $row['idPrenotazione']);
        exit();
    } else {
        $_SESSION['error_msg'] = "Nessuna prenotazione trovata per questa copia.";
        header("Location: dashboardLibri.php");
        exit();
    }
} catch (Exception $e) {
    echo "Errore di sistema: " . $e->getMessage();
} finally {
    
    if (isset($conn)) {
        $conn->close();
    }
}
?>