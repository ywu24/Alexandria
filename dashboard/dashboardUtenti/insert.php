<?php

/*
NOTA: al momento questo metodo è insicuro: chiunque potrebbe fare richiesta ad insert.php con post per aggiungere utenti.
Controllare se l'utente che è loggato è autorizzato a farlo (utenza == 1)
*/

$table = "Utente";
require_once("../../utils/connect.php");
if (empty($_POST['nome']) || empty($_POST['cognome']) || empty($_POST['email']) || empty($_POST['email']) || empty($_POST['password'])) {
    header("Location: aggiungiUtente.php?error=1");
    exit();

} else {

    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $ruolo = '4';
    if (!empty($_POST['ruolo']))
        $ruolo = $_POST['ruolo'];
    $password = $_POST['password'];
    $password = password_hash($password, PASSWORD_BCRYPT);

    #echo $nome." ".$cognome." ".$email." ".$ruolo." ".$password;

    try {
        $pdo = DatabaseConnection::getInstance()->getConnection();
    } catch (PDOException $e) {
        echo "Errore durante la connessione al database: " . $e->getMessage();
        exit;
    }

    try {
        $query = $pdo->prepare("INSERT INTO $table (`Nome`, `Cognome`, `Email`,`Utenza`, `Password`) 
                                VALUES (:nome, :cognome, :email, :ruolo, :password)");
        $query->bindParam(':nome', $nome);
        $query->bindParam(':cognome', $cognome);
        $query->bindParam(':email', $email);
        $query->bindParam(':ruolo', $ruolo);
        $query->bindParam(':password', $password);
        $query->execute();
        $query->closeCursor();
        header("Location: dashboardUtenti.php?aggiunto=1");
        exit;
    } catch (PDOException $e) {
        header("Location: aggiungiUtente.php?error=2");
        exit;
    }
}
?>