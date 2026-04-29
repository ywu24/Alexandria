<?php
// LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$root = "../..";

require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");

// Controllo permessi 
if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {
    // if accesso consentito
} else {
    header("Location: ../../index.php");
    exit;
}


try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    echo "Errore durante la connessione al database: " . $e->getMessage();
    exit;
}


if (isset($_POST['btnOpera'])) {
    $titolo = $_POST['titolo'];
    $autore = $_POST['autore'];
    $casa_editrice = $_POST['casaed'];
    $isbn = $_POST['isbn'];
    $genere = $_POST['genere'];
    $descrizione = $_POST['desc']; 
    $anno_pubblicazione = $_POST['annopub'];
    $copie = (int)$_POST['qty'];

    // Gestione File
    $file = $_FILES['image'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $file_name_new = str_replace(' ', '', $titolo) . uniqid() . '.' . $file_ext;
    $file_destination = '../../img/books/' . $file_name_new;
    $copertina =  $file_name_new;

    // Controllo errori caricamento 
    if ($file['error'] === 0 && in_array($file_ext, ['jpg', 'jpeg', 'png']) && $file['size'] <= 5000000) {
        move_uploaded_file($file_tmp, $file_destination);
    }

    try {
        // Controllo duplicati
        $check = $pdo->prepare("SELECT ISBN FROM Opera WHERE ISBN = :isbn");
        $check->execute([':isbn' => $isbn]);

        if ($check->rowCount() > 0) {
            $_SESSION['error_msg'] = "Errore: il DataBase ha già i dati sul libro. Impossibile inserire Opera.";
            header("Location: dashboardLibri.php");
            exit;
        } else {
            // Inserimento Opera
            $q1 = "INSERT INTO Opera (`ISBN`, `Nome`, `Autore`, `Genere`, `Descrizione`, `Copertina`, `CasaEditrice`, `AnnoPubblicazione`)
                   VALUES (:isbn, :nome, :autore, :genere, :descr, :copertina, :casa, :anno)";
            $query1 = $pdo->prepare($q1);
            $query1->execute([
                ':isbn' => $isbn,
                ':nome' => $titolo,
                ':autore' => $autore,
                ':genere' => $genere,
                ':descr' => $descrizione,
                ':copertina' => $copertina,
                ':casa' => $casa_editrice,
                ':anno' => $anno_pubblicazione
            ]);

            // Inserimento Copie
            $q2 = "INSERT INTO copiaLibro (`ISBN`, `Stato`) VALUES (:isbn, '1')";
            $query2 = $pdo->prepare($q2);
            for ($i = 0; $i < $copie; $i++) {
                $query2->execute([':isbn' => $isbn]);
            }

            $_SESSION['success_msg'] = "Dati sul libro e relativa/e copia/e aggiunto al database";
            header("Location: dashboardLibri.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Errore DB: " . $e->getMessage();
        header("Location: dashboardLibri.php");
        exit;
    }

// --- LOGICA INSERIMENTO SOLO COPIE ---
} else if (isset($_POST['btnCopia'])) {
    $isbn = $_POST['isbn'];
    $copie = (int)$_POST['qty'];

    try {
        $check = $pdo->prepare("SELECT ISBN FROM Opera WHERE ISBN = :isbn");
        $check->execute([':isbn' => $isbn]);

        if ($check->rowCount() > 0) {
            $q = "INSERT INTO copiaLibro (`ISBN`, `Stato`) VALUES (:isbn, '1')";
            $query = $pdo->prepare($q);
            for ($i = 0; $i < $copie; $i++) {
                $query->execute([':isbn' => $isbn]);
            }
            $_SESSION['success_msg'] = "Copia/e libro aggiunto al database";
            header("Location: dashboardLibri.php");
            exit;
        } else {
            $_SESSION['error_msg'] = "Errore: il DataBase non ha i dati sul libro.";
            header("Location: dashboardLibri.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Errore: " . $e->getMessage();
        header("Location: dashboardLibri.php");
        exit;
    }
}
?>