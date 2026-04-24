<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Connessione al database
require_once("../../utils/connect.php");

// Recupero i dati inviati dal form
$id = $_POST['id'];
$nome = $_POST['nome'];
$cognome = $_POST['cognome'];
$email = $_POST['email'];
$ruolo = $_POST['ruolo'];
$password = $_POST['password'];

if(empty($id)||empty($nome)||empty($cognome)||empty($email)||empty($ruolo)){
	header("Location: modificaUtente.php?error=1&id=".$id);
	exit();
	
}

// Controlla se l'utente ha inserito una nuova password
if (!empty($password)) {
	
	// Aggiorna la password e gli altri campi nel database 
	$password_hash = password_hash($password, PASSWORD_BCRYPT);
	$sql = "UPDATE Utente SET Nome=:nome, Cognome=:cognome, Email=:email, Utenza=:ruolo, Password=:password WHERE id=:id";

	try{
		$query = $conn->prepare($sql);
		$query->bindParam(':nome', $nome);
		$query->bindParam(':cognome', $cognome);
		$query->bindParam(':email', $email);
		$query->bindParam(':ruolo', $ruolo);
		$query->bindParam(':password', $password_hash);
		$query->bindParam(':id', $id);
		$query->execute();
		$query->closeCursor();
		header("Location: dashboardUtenti.php?aggiornato=1");
	} catch (PDOException $e) {
		#echo "Errore durante il salvataggio delle modifiche: " . $conn->error;
		header("Location: modificaUtente.php?error=2&id=".$id);
		exit;
	}
	
  } else {
	// L'utente non ha inserito una nuova password, quindi si aggiornano gli altri campi e non la password nel database
	$sql = "UPDATE Utente SET Nome=:nome, Cognome=:cognome, Email=:email, Utenza=:ruolo WHERE id=:id";

	try{
		$query = $conn->prepare($sql);
		$query->bindParam(':nome', $nome);
		$query->bindParam(':cognome', $cognome);
		$query->bindParam(':email', $email);
		$query->bindParam(':ruolo', $ruolo);
		$query->bindParam(':id', $id);
		$query->execute();
		$query->closeCursor();
		header("Location: dashboardUtenti.php?aggiornato=1");
	} catch (PDOException $e) {
		#echo "Errore durante il salvataggio delle modifiche: " . $conn->error;
		header("Location: modificaUtente.php?error=2&id=".$id);
		exit;
	}
  }
?>