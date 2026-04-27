<?php
session_start();

if (isset($_POST['check'])) {
    // Connessione al database
    require_once("../../utils/connect.php");

    $isbn = $_POST['isbn-check'];

    // Controllo se il libro esiste già nel database
    try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
        if ($query = $pdo->prepare('SELECT * FROM Opera WHERE ISBN = :isbn')) {
            $query->bindParam(':isbn', $isbn);
            $query->execute();

            $exists = $query->fetch() ? true : false;
            $query->closeCursor();
            $_SESSION['libroEsiste'] = $exists;

            if ($exists) {
                header("Location: aggiungiLibro.php");
                exit;
            } else {
                header("Location: aggiungiLibro.php");
                exit;
            }
        } else {
            echo "Errore nella preparazione della query.";
        }

    } catch (PDOException $e) {
        echo "Errore durante controllo: " . $e->getMessage();
    }
}
?>