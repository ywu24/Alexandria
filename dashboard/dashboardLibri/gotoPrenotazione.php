<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$root= "../..";
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
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

try {

    $query = $pdo->prepare("SELECT idPrenotazione FROM Prenotazione WHERE idCopia = :id AND FinePrestito IS NULL");
    $query->bindParam(':id', $id);
    $query->execute();
    $result = $query->fetch();

    if ($result) {
        header("Location: ../dashboardUtenti/dettaglioPrenotazione.php?id=" . $result['idPrenotazione']);
        exit();
    } else {
        $_SESSION['error_msg'] = "Nessuna prenotazione trovata per questa copia.";
        header("Location: dashboardLibri.php");
        exit();
    }
} catch (Exception $e) {
    echo "Errore di sistema: " . $e->getMessage();
}
?>