<?php
session_start(); 

ini_set('display_errors', 1); 
error_reporting(E_ALL);

$root = '../..';
require_once("../../utils/connect.php");

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    die("Errore durante la connessione al database: " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $idSegnalazione = $_GET['id'];
    
    // NOTA: Sarebbe meglio usare i prepared statements per la sicurezza, 
    // ma mantengo la tua logica originale per coerenza col file fornito.
    $query_select = "SELECT imgSegn FROM Segnalazione WHERE idSegnalazione = $idSegnalazione";
    $sql_delete = "DELETE FROM Segnalazione WHERE idSegnalazione = $idSegnalazione";
    
    $result_select = $pdo->query($query_select);
    $row = $result_select->fetch(PDO::FETCH_ASSOC);
    
    $file_path = "../../segnalazione/" . $row['imgSegn'];

    try {
        $result_delete = $pdo->query($sql_delete);
  
        if ($result_delete) {
            // Eliminazione file fisico
            if (!empty($row['imgSegn']) && file_exists($file_path)) {
                unlink($file_path);
            }
            
            $_SESSION['successo'] = "Segnalazione eliminata con successo";
            header("Location: dashboardSegnalazioni.php");
            exit;
        } else {
            $_SESSION['errore'] = "Errore durante l'eliminazione della segnalazione";
            header("Location: dettaglio.php?id=" . $idSegnalazione);
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['errore'] = "Errore durante l'esecuzione della query: " . $e->getMessage();
        header("Location: dettaglio.php?id=" . $idSegnalazione);
        exit;
    }
} else {
    $_SESSION['errore'] = "Parametro id mancante nella richiesta";
    header("Location: dashboardSegnalazioni.php");
    exit;
}
?>