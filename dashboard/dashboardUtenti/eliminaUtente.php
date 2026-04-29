<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = '../..';
require_once("../../auth/cookies.php");

if (isset($_GET['id'])) {
    require_once("../../utils/connect.php");
    $table = "Utente";

    $id = $_GET['id'];
    $email = $_SESSION["email"];

    try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
    }

    $sql = "DELETE FROM $table WHERE id=:id AND Email!=:email";
    if ($query = $pdo->prepare($sql)) {
        $query->bindParam(':id', $id);
        $query->bindParam(':email', $email);
        $query->execute();
        $n = $query->rowCount();
        $query->closeCursor();
        if ($n == 0) {
            header("Location: dashboardUtenti.php?errore=2");
            exit;
        }
        header("Location: dashboardUtenti.php?rimosso=1");
        exit;
    } else {
        header("Location: dashboardUtenti.php?errore=1");
        exit;
    }
} else {
    header("Location: dashboardUtenti.php");
    exit;
}