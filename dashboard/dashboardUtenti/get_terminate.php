<?php
$root = "../..";
session_start();
require_once("../../utils/connect.php");
if (!isset($_POST['idUtente']) && !isset($_POST['email'])) {
    exit;
}
$email = "";
$idUtente = 0;
try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}

if (isset($_POST['idUtente'])) {
    $idUtente = $_POST['idUtente'];

    // Recupero l'email dell'utente
    if ($query = $pdo->prepare('SELECT Email FROM Utente WHERE id = :id')) {
        $query->bindParam(':id', $idUtente);
        $query->execute();
        $email = $query->fetch()['Email'];
        $query->closeCursor();
    } else {
        throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
    }
} else {
    $email = $_POST['email'];
}
try {
    if ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2) {
        if ($_SESSION['email'] != $email) {
            http_response_code(500);
            echo json_encode(["error" => "Accesso Negato" ]);
        }
    }
    //$email = $_SESSION['email'];

    $idUtente = 0;
    $sql2 = "SELECT idOpera FROM recensione WHERE userEmail = :userEmail";
    $query2 = $pdo->prepare($sql2);
    $query2->bindParam(':userEmail', $email);
    $query2->execute();

    // FETCH_COLUMN crea un array piatto, es: [1, 5, 12, 22]
    $recensiti = $query2->fetchAll(PDO::FETCH_COLUMN, 0);
    $query2->closeCursor();

    // 2. Esegui la Query 1: Prenotazioni terminate
    $sql = "SELECT idPrenotazione, InizioPrestito, FinePrestito, FineAttesa, Copertina, Nome, Autore, CasaEditrice, id as idOpera, Opera.ISBN as ISBN
            FROM Prenotazione, copiaLibro, Opera 
            WHERE copiaLibro.idCopia = Prenotazione.idCopia 
            AND Opera.ISBN = copiaLibro.ISBN 
            AND Prenotazione.Email = :email 
            AND FinePrestito IS NOT NULL
            ORDER BY Prenotazione.idPrenotazione DESC";

    if ($query = $pdo->prepare($sql)) {
        $query->bindParam(':email', $email);
        $query->execute();

        $terminate = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {

            if (in_array($row['idOpera'], $recensiti)) {
                $row['recensito'] = 1;
            } else {
                $row['recensito'] = 0;
            }
            $terminate[] = $row;
        }
        $query->closeCursor();

        // Ritorna il JSON completo
        echo json_encode($terminate);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
