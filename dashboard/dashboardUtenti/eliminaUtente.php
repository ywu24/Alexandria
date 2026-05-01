<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = '../..';
require_once("../../auth/cookies.php");

if ($_SESSION['utenza'] != 1) {
    if (isset($_POST["delete_account"])) {
        try {
            $pdo = DatabaseConnection::getInstance()->getConnection();
        } catch (PDOException $e) {
            echo "Errore durante la connessione al database: " . $e->getMessage();
            exit;
        }
        $table = "Utente";
        $email = $_SESSION["email"];

        // CONTROLLO CHE L'UTENTE CHE STA ELIMINANDO IL SUO ACCOUNT NON ABBIA PRENOTAZIONI/PRESTITI IN SOSPESO
        $checkSql = "SELECT COUNT(*) FROM Prenotazione 
                     WHERE Email = :email 
                     AND (
                        FinePrestito IS NULL
                     )";

        $checkQuery = $pdo->prepare($checkSql);
        $checkQuery->execute([':email' => $email]);
        $pendenze = $checkQuery->fetchColumn();

        if ($pendenze > 0) {
            $_SESSION["error_msg"] = "Impossibile eliminare l'account: hai ancora prestiti in corso o prenotazioni attive.";
            header("Location: ../../edit_profile/edit_profile.php");
            exit;
        }


        $sql = "DELETE FROM $table WHERE Email=:email";
        if ($query = $pdo->prepare($sql)) {
            $query->bindParam(':email', $email);
            $query->execute();
            $n = $query->rowCount();
            $query->closeCursor();
            if ($n == 0) {
                $_SESSION["error_msg"] = "errore2 nell'eliminazione";
                header("Location: ../../edit_profile/edit_profile.php");
                exit;
            }
            session_destroy();
            setcookie('email', '', time() - 3600, '/', $domain);
            setcookie('password', '', time() - 3600, '/', $domain);
            header("Location: ../../index.php");
            exit;
        } else {
            $_SESSION["error_msg"] = "errore1 nell'eliminazione";
            header("Location: ../../edit_profile/edit_profile.php");
            exit;
        }
    }
} else if (isset($_GET['id'])) {
    require_once("../../utils/connect.php");

    $table = "Utente";

    $email_to_delete = $_GET['id'];
    $email = $_SESSION["email"];

    try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
    }
    //SOLO AI FINI DEL DEBUG L' ADMIN PUO' ELIMINARE ANCHE ACCOUNT CHE HANNO LIBRI IN PRESTITO/PRENOTATO.
    //QUI CI ASSICURIAMO DI MANTENERE  PERO' LO STATO DEL DB COERENTE
    // 2. RIPRISTINO STATO LIBRI PRENOTATI (Libri prenotati ma NON ancora ritirati)

    $updateLibri = $pdo->prepare("
                UPDATE copiaLibro 
                SET Stato = 1 
                WHERE idCopia IN (
                    SELECT idCopia FROM Prenotazione 
                    WHERE Email = :email AND FinePrestito IS NULL
                )
            ");
    $updateLibri->execute([':email' => $email_to_delete]);


    $sql = "DELETE FROM $table WHERE Email=:email AND Email!=:adminemail";
    if ($query = $pdo->prepare($sql)) {
        $query->bindParam(':email',$email_to_delete);
        $query->bindParam(':adminemail', $email);
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
